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
 * Categories related functions.
 *
 * This file was created just because Fragment API expects callbacks to be defined on lib.php.
 *
 * @package   qbank_managecategories
 * @copyright 2021 Catalyst IT Australia Pty Ltd
 * @author    Marc-Alexandre Ghaly <marc-alexandreghaly@catalyst-ca.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */

defined('MOODLE_INTERNAL') || die();

use qbank_managecategories\form\question_category_edit_form;
require_once($CFG->dirroot . '/question/editlib.php');

/**
 * Question tags fragment callback.
 *
 * @param array $args Arguments to the form.
 * @return null|string The rendered form.
 */
function qbank_managecategories_output_fragment_new_category_form($args) {
    $args = (object) $args;
    $thiscontext = context_module::instance((int)$args->cmid);
    $contexts = new question_edit_contexts($thiscontext);
    $contexts = $contexts->having_one_edit_tab_cap('categories');
    $customdata = ['contexts' => $contexts, 'top' => true, 'currentcat' => 0, 'nochildrenof' => 0];
    $mform = new question_category_edit_form(null, $customdata);
    return $mform->render();
}

function qbank_managecategories_output_fragment_questions_rendered_reload($args) {
    list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) =
        question_edit_setup('categories', '/question/bank/managecategories/category.php');
    
    $qcobject = new question_category_object($pagevars['cpage'], $thispageurl,
    $contexts->having_one_edit_tab_cap('categories'), $param->edit,
    $pagevars['cat'], $param->delete, $contexts->having_cap('moodle/question:add'));
    //return $qcobject->display_user_interface();
    return "<h1>teststring</h1>";
}