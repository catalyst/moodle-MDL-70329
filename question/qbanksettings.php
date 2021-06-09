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
 * Question bank settings page class.
 *
 * @package    qbank_settingspage
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/question/classes/sort_form.php');

$PAGE->set_title(get_string('qbanksettings', 'admin'));
$PAGE->set_heading(get_string('qbanksettings', 'admin'));
$PAGE->requires->js_call_amd('core_question/drag_drop','init');

$context = array();
 
$corequestionbankcolumns = array(
    'checkbox_column',
    'question_type_column',
    'question_name_idnumber_tags_column',
    'edit_menu_column',
    'edit_action_column',
    'copy_action_column',
    'tags_action_column',
    'preview_action_column',
    'delete_action_column',
    'export_xml_action_column',
    'creator_name_column',
    'modifier_name_column'
);

foreach ($corequestionbankcolumns as $columnname) {
    $context['name'][] = $columnname;
}

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('question/setting_qbanksetting', $context);

echo $OUTPUT->footer();
