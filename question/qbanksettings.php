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
error_reporting(-1);
ini_set('display_errors', true);
require_once(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/question/classes/sort_form.php');
 
// $PAGE->requires->js_call_amd('core_question/drag_drop', 'init');
$PAGE->requires->js_call_amd('core_question/drag_drop','init');
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/question/amd/src/drag_drop.js'));
$context = array();
$context['header'] = get_string('qbanksettings', 'admin');
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

//$neworder = array_keys($corequestionbankcolumns);

// function question_reorder_qtypes($corequestionbankcolumns, $tomove, $direction) {
//     $neworder = array_keys($corequestionbankcolumns);
//     // Find the element to move.
//     $key = array_search($tomove, $neworder);
//     if ($key === false) {
//         return $neworder;
//     }
//     // Work out the other index.
//     $otherkey = $key + $direction;
//     if (!isset($neworder[$otherkey])) {
//         return $neworder;
//     }
//     // Do the swap.
//     $swap = $neworder[$otherkey];
//     $neworder[$otherkey] = $neworder[$key];
//     $neworder[$key] = $swap;
//     return $neworder;
// }

foreach ($corequestionbankcolumns as $columnname) {
    $context['name'][] = $columnname;
    $outp[] = $columnname;
}

$outp['html'] = $OUTPUT->render_from_template('question/setting_qbanksetting', $context);
$mform = new sort_column_form(null, $outp);

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    //$param = optional_param('list', '', PARAM_TEXT);
    $v = 0;
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    //Set default data (if any)
    //$mform->set_data($toform);

    //displays the form
    //$this->content->text = $mform->render();
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
