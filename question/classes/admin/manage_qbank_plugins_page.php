<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Manage question banks page.
 *
 * @package    core_question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_question\admin;

defined('MOODLE_INTERNAL') || die();

/**
 * Class manage_qbank_plugins_page.
 *
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_qbank_plugins_page extends \admin_setting {

    /**
     * Class admin_page_manageqbanks constructor.
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct('manageqbanks',
                new \lang_string('manageqbanks', 'admin'), '', '');
    }

    public function get_setting(): bool {
        return true;
    }

    public function get_defaultsetting(): bool {
        return true;
    }

    public function write_setting($data): string {
        // Do not write any setting.
        return '';
    }

    public function is_related($query): bool {
        if (parent::is_related($query)) {
            return true;
        }
        $types = \core_plugin_manager::instance()->get_plugins_of_type('qbank');
        foreach ($types as $type) {
            if (strpos($type->component, $query) !== false ||
                    strpos(\core_text::strtolower($type->displayname), $query) !== false) {
                return true;
            }
        }
        return false;
    }

    public function output_html($data, $query = ''): string {
        global $CFG, $OUTPUT;
        $return = '';

        $pluginmanager = \core_plugin_manager::instance();
        $types = $pluginmanager->get_plugins_of_type('qbank');
        if (empty($types)) {
            return get_string('noquestionbanks', 'question');
        }
        $txt = get_strings(array('settings', 'name', 'enable', 'disable', 'default'));
        $txt->uninstall = get_string('uninstallplugin', 'core_admin');

        $table = new \html_table();
        $table->head  = array($txt->name, $txt->enable, $txt->settings, $txt->uninstall);
        $table->align = array('left', 'center', 'center', 'center', 'center');
        $table->attributes['class'] = 'manageqbanktable generaltable admintable';
        $table->data  = array();

        $totalenabled = 0;
        $count = 0;
        foreach ($types as $type) {
            if ($type->is_enabled() && $type->is_installed_and_upgraded()) {
                $totalenabled++;
            }
        }

        foreach ($types as $type) {
            $url = new \moodle_url('/admin/qbankplugins.php',
                    array('sesskey' => sesskey(), 'name' => $type->name));

            $class = '';
            if ($pluginmanager->get_plugin_info('qbank_'.$type->name)->get_status() ===
                    \core_plugin_manager::PLUGIN_STATUS_MISSING) {
                $strtypename = $type->displayname.' ('.get_string('missingfromdisk').')';
            } else {
                $strtypename = $type->displayname;
            }

            if ($type->is_enabled()) {
                $hideshow = \html_writer::link($url->out(false, array('action' => 'disable')),
                        $OUTPUT->pix_icon('t/hide', $txt->disable, 'moodle', array('class' => 'iconsmall')));
            } else {
                $class = 'dimmed_text';
                $hideshow = \html_writer::link($url->out(false, array('action' => 'enable')),
                        $OUTPUT->pix_icon('t/show', $txt->enable, 'moodle', array('class' => 'iconsmall')));
            }

            $settings = '';
            if ($type->get_settings_url()) {
                $settings = \html_writer::link($type->get_settings_url(), $txt->settings);
            }

            $uninstall = '';
            if ($uninstallurl = \core_plugin_manager::instance()->get_uninstall_url(
                    'qbank_'.$type->name, 'manage')) {
                $uninstall = \html_writer::link($uninstallurl, $txt->uninstall);
            }

            $row = new \html_table_row(array($strtypename, $hideshow, $settings, $uninstall));
            if ($class) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
            $count++;
        }
        $return .= \html_writer::table($table);
        return highlight($query, $return);
    }
}