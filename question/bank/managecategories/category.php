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
require_once($CFG->dirroot."/question/editlib.php");

use qbank_managecategories\form\question_move_form;
use qbank_managecategories\helper;
use qbank_managecategories\question_category_object;

require_login();
core_question\local\bank\helper::require_plugin_enabled(helper::PLUGINNAME);
$PAGE->requires->js_call_amd('qbank_managecategories/order_categories', 'init');

list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) =
        question_edit_setup('categories', '/question/bank/managecategories/category.php');

// Get values from form for actions on this page.
$param = new stdClass();
$param->left = optional_param('left', 0, PARAM_INT);
$param->right = optional_param('right', 0, PARAM_INT);

$url = new moodle_url($thispageurl);
$PAGE->set_url($url);

$PAGE->requires->js_call_amd(
    'qbank_managecategories/addcategory_dialogue', 'initModal',
    ['[data-action=addcategory]', 
    \qbank_managecategories\form\question_category_edit_form_modal::class, 
    $cmid, 
    $pagevars['cat'],],
);

$qcobject = new question_category_object($pagevars['cpage'], $thispageurl,
        $contexts->having_one_edit_tab_cap('categories'), 0,
        $pagevars['cat'], 0, $contexts->having_cap('moodle/question:add'));

if ($param->left || $param->right) {
    require_sesskey();
    foreach ($qcobject->editlists as $list) {
        // Processing of these actions is handled in the method where appropriate and page redirects.
        $list->process_actions($param->left, $param->right);
    }
}

if ($qcobject->catform->is_cancelled()) {
    redirect($thispageurl);
} else if ($catformdata = $qcobject->catform->get_data()) {
    $catformdata->infoformat = $catformdata->info['format'];
    $catformdata->info       = $catformdata->info['text'];
    if (!$catformdata->id) {// New category.
        $qcobject->add_category($catformdata->parent, $catformdata->name,
                $catformdata->info, false, $catformdata->infoformat, $catformdata->idnumber);
    } else {
        $qcobject->update_category($catformdata->id, $catformdata->parent,
                $catformdata->name, $catformdata->info, $catformdata->infoformat, $catformdata->idnumber);
    }
    redirect($thispageurl);
}

$PAGE->set_title(get_string('editcategories', 'question'));
$PAGE->set_heading($COURSE->fullname);
echo $OUTPUT->header();

// Print horizontal nav if needed.
$renderer = $PAGE->get_renderer('core_question', 'bank');
echo $renderer->extra_horizontal_navigation();

// Display the user interface.
$qcobject->display_user_interface();

echo $OUTPUT->footer();
