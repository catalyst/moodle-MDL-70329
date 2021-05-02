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
 * Provides an overview of installed qbanks
 *
 * Displays the list of found qbanks, their version (if found) and
 * a link to uninstall the qbank.
 *
 * The code is based on admin/localplugins.php by David Mudrak.
 *
 * @package    core
 * @subpackage questionbank
 * @copyright 2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

admin_externalpage_setup('manageqbanks');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('questionbanks', 'admin'));

// Print the table of all installed qbank plugins.

$table = new flexible_table('manageqbanks_administration_table');
$table->define_columns(array('name', 'version', 'uninstall'));
$table->define_headers(array(get_string('plugin'), get_string('version'), get_string('uninstallplugin', 'core_admin')));
$table->define_baseurl($PAGE->url);
$table->set_attribute('id', 'manageqbanks');
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

$plugins = array();
foreach (core_component::get_plugin_list('qbank') as $plugin => $plugindir) {
    if (get_string_manager()->string_exists('pluginname', 'qbank_' . $plugin)) {
        $strpluginname = get_string('pluginname', 'qbank_' . $plugin);
    } else {
        $strpluginname = $plugin;
    }
    $plugins[$plugin] = $strpluginname;
}
core_collator::asort($plugins);

foreach ($plugins as $plugin => $name) {
    $uninstall = '';
    if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('qbank_'.$plugin, 'manage')) {
        $uninstall = html_writer::link($uninstallurl, get_string('uninstallplugin', 'core_admin'));
    }

    $version = get_config('qbank_' . $plugin);
    if (!empty($version->version)) {
        $version = $version->version;
    } else {
        $version = '?';
    }

    $table->add_data(array($name, $version, $uninstall));
}

$table->print_html();

echo $OUTPUT->footer();

