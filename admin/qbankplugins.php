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
 * @copyright  2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

admin_externalpage_setup('manageqbanks');

$url = new moodle_url('/admin/qbankplugins.php');
$qbankplugings = core_component::get_plugin_list('qbank');

// Disable qbank plugin.
if (($disable = optional_param('disable', '', PARAM_PLUGIN)) && confirm_sesskey()) {
    if (!isset($qbankplugings[$disable])) {
        throw new moodle_exception('unknownquestiontype', 'question', $url, $disable);
    }

    set_config('disabled', 1, 'qbank_' . $disable);
    redirect($url);
}

// Enable qbank plugin.
if (($enable = optional_param('enable', '', PARAM_PLUGIN)) && confirm_sesskey()) {
    if (!isset($qbankplugings[$enable])) {
        throw new moodle_exception('unknownquestiontype', 'question', $url, $enable);
    }

    unset_config('disabled', 'qbank_' . $enable);
    redirect($url);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('questionbanks', 'admin'));

// Print the table of all installed qbank plugins.
$table = new flexible_table('manageqbanks_administration_table');
$table->define_columns(
    [
        'name',
        'version',
        'available',
        'uninstall'
    ]
);
$table->define_headers(
    [
        get_string('plugin'),
        get_string('version'),
        get_string('availableq', 'question'),
        get_string('uninstallplugin', 'core_admin')
    ]
);
$table->define_baseurl($url);
$table->set_attribute('id', 'manageqbanks');
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

$plugins = [];
foreach ($qbankplugings as $qbankplugin => $plugindir) {
    if (get_string_manager()->string_exists('pluginname', 'qbank_' . $qbankplugin)) {
        $strpluginname = get_string('pluginname', 'qbank_' . $qbankplugin);
    } else {
        $strpluginname = $qbankplugin;
    }
    $plugins[$qbankplugin] = $strpluginname;
}
core_collator::asort($plugins);

foreach ($plugins as $qbankplugin => $name) {
    $row = [];

    // Add plugin name to row.
    $row[] = $name;

    // Get qbank plugin version.
    $version = get_config('qbank_' . $qbankplugin, 'version');
    if ($version) {
        $row[] = $version;
    } else {
        $row[] = html_writer::tag('span', get_string('nodatabase', 'admin'), array('class' => 'text-muted'));
    }

    // Get qbank plugin status and add icon to row.
    $rowclass = '';
    $disabled = get_config('qbank_' . $qbankplugin, 'disabled');
    if (!$disabled) {
        $row[] = qbank_plugins_enable_disable_icons($qbankplugin, true);
    } else {
        $row[] = qbank_plugins_enable_disable_icons($qbankplugin, false);
        $rowclass = 'dimmed_text';
    }

    // Uninstall plugin.
    if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('qbank_'.$qbankplugin, 'manage')) {
        $row[] = html_writer::link($uninstallurl, get_string('uninstallplugin', 'core_admin'));
    }

    $table->add_data($row, $rowclass);
}

$table->finish_output();

echo $OUTPUT->footer();

/**
 * Helper function that calls the qbank_plugins_icon_html function
 * depending on parameters.
 */
function qbank_plugins_enable_disable_icons($qbankplugin, $enabled) {
    if ($enabled) {
        return qbank_plugins_icon_html('disable', $qbankplugin, 't/hide',
            get_string('enabled', 'question'), get_string('disable'));
    } else {
        return qbank_plugins_icon_html('enable', $qbankplugin, 't/show',
            get_string('disabled', 'question'), get_string('enable'));
    }
}

/**
 * Helper function that returns the icon if the plugin is
 * disabled or enabled.
 */
function qbank_plugins_icon_html($action, $qbankplugin, $icon, $alt, $tip) {
    global $OUTPUT;
    return $OUTPUT->action_icon(
        new moodle_url('/admin/qbankplugins.php',
            array($action => $qbankplugin, 'sesskey' => sesskey())),
        new pix_icon($icon, $alt, 'moodle', array('title' => '', 'class' => 'iconsmall')),
        null,
        array('title' => $tip)
    );
}
