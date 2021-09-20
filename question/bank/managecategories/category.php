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
 * This script allows a teacher to create, edit and delete question categories.
 *
 * @package    qbank_managecategories
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @author     2021, Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/question/editlib.php');

use qbank_managecategories\form\question_move_form;
use qbank_managecategories\helper;
use qbank_managecategories\question_category_object;
use core\event\questions_exported;
use context_module;

require_login();
core_question\local\bank\helper::require_plugin_enabled(helper::PLUGINNAME);

list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) =
        question_edit_setup('categories', '/question/bank/managecategories/category.php');
$courseid = optional_param('courseid', 0, PARAM_INT);

if (!is_null($cmid)) {
    $thiscontext = context_module::instance($cmid)->id;
} else {
    $course = get_course($courseid);
    $thiscontext = context_course::instance($course->id)->id;
}

$PAGE->requires->js_call_amd('qbank_managecategories/addcategory_dialogue', 'initModal',
    ['[data-action=addcategory]', $thiscontext]);

// Get values from form for actions on this page.
$param = new stdClass();
$param->left = optional_param('left', 0, PARAM_INT);
$param->right = optional_param('right', 0, PARAM_INT);
$param->delete = optional_param('delete', 0, PARAM_INT);
$param->edit = optional_param('edit', 0, PARAM_INT);
$param->showdescr = optional_param('qbshowdescr', 0, PARAM_INT);

$PAGE->set_url($thispageurl);

$cmidorcourseid = !is_null($cmid) ? $cmid : $courseid;
$iscmid = !is_null($cmid) ? true : false;
$qcobject = new question_category_object($pagevars['cpage'], $thispageurl,
        $contexts->having_one_edit_tab_cap('categories'), $param->edit,
        $pagevars['cat'], $param->delete, $contexts->having_cap('moodle/question:add'), $cmidorcourseid, $iscmid);

if ($param->left || $param->right) {
    require_sesskey();
    foreach ($qcobject->editlists as $list) {
        // Processing of these actions is handled in the method where appropriate and page redirects.
        $list->process_actions($param->left, $param->right);
    }
}

if ($param->delete) {
    if (!$category = $DB->get_record("question_categories", ["id" => $param->delete])) {
        throw new moodle_exception('nocate', 'question', $thispageurl->out(), $param->delete);
    }

    helper::question_remove_stale_questions_from_category($param->delete);
    $questionstomove = $DB->count_records("question", ["category" => $param->delete]);

    // Second pass, if we still have questions to move, setup the form.
    if ($questionstomove) {
        $categorycontext = context::instance_by_id($category->contextid);
        $qcobject->moveform = new question_move_form($thispageurl,
            ['contexts' => [$categorycontext], 'currentcat' => $param->delete]);
        if ($qcobject->moveform->is_cancelled()) {
            $thispageurl->remove_all_params();
            if (!is_null($cmid)) {
                $thispageurl->param('cmid', $cmid);
            } else {
                $thispageurl->param('courseid', $courseid);
            }
            redirect($thispageurl);
        } else if ($formdata = $qcobject->moveform->get_data()) {
            list($tocategoryid, $tocontextid) = explode(',', $formdata->category);
            $qcobject->move_questions_and_delete_category($formdata->delete, $tocategoryid);
            $thispageurl->remove_params('cat', 'category');
            redirect($thispageurl);
        }
    }
} else {
    $questionstomove = 0;
}

if ((!empty($param->delete) and (!$questionstomove) and confirm_sesskey())) {
    $qcobject->delete_category($param->delete);// Delete the category now no questions to move.
    $thispageurl->remove_params('cat', 'category');
    redirect($thispageurl);
}

if ($checkboxform = $qcobject->checkboxform->get_data()) {
    if (isset($checkboxform->qbshowdescr)) {
        set_user_preference('question_bank_qbshowdescr', $checkboxform->qbshowdescr);
    } else {
        set_user_preference('question_bank_qbshowdescr', 0);
    }
}

$PAGE->set_title(get_string('editcategories', 'question'));
$PAGE->set_heading($COURSE->fullname);
echo $OUTPUT->header();

// Print horizontal nav if needed.
$renderer = $PAGE->get_renderer('core_question', 'bank');
echo $renderer->extra_horizontal_navigation();

if ($questionstomove) {
    $qcobject->display_move_form($questionstomove, $category);
} else {
    // Display the user interface.
    echo $qcobject->display_user_interface();
}

echo $OUTPUT->footer();
