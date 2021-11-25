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
require_once($CFG->dirroot . '/question/editlib.php');
use qbank_managecategories\form\question_category_edit_form;
use qbank_managecategories\form\question_category_delete_form;
use qbank_managecategories\question_category_object;

/**
 * Fragment for new category dialogue.
 *
 * @param array $args Arguments to the form.
 * @return null|string The rendered form.
 */
function qbank_managecategories_output_fragment_new_category_form($args) {
    global $DB;
    $args = (object) $args;
    $contexts = new question_edit_contexts($args->context);
    $contexts = $contexts->having_one_edit_tab_cap('categories');
    $customdata = ['contexts' => $contexts, 'top' => true, 'currentcat' => 0, 'nochildrenof' => 0];
    if (isset($args->modalid)) {
        $customdata['modalid'] = $args->modalid;
    }
    $formdata = [];
    if (!empty($args->jsonformdata)) {
        parse_str($args->jsonformdata, $formdata);
    }
    $mform = new question_category_edit_form(null, $customdata, 'post', '', null, true, $formdata);
    if (isset($args->id)) {
        $category = $DB->get_record("question_categories", ["id" => $args->id]);
        if (empty($category)) {
            throw new moodle_exception('invalidcategory', '', '', $args->id);
        } else if ($category->parent == 0) {
            throw new moodle_exception('cannotedittopcat', 'question', '', $args->id);
        } else {
            $category->parent = "{$category->parent},{$category->contextid}";
            $category->submitbutton = get_string('savechanges');
            $category->categoryheader = get_string('editcategory', 'qbank_managecategories');
            $mform->set_data($category);
        }
    }
    // Form submission check.
    if (!empty($args->jsonformdata)) {
        $mform->is_validated();
    }
    return $mform->render();
}

/**
 * Fragment for rendering new categories order.
 *
 * @param array $args Arguments to the form.
 * @return null|string The rendered form.
 */
function qbank_managecategories_output_fragment_new_category_order($args) {
    global $OUTPUT;
    $url = new moodle_url($args['url']);
    $thiscontext = $args['context'];
    $contexts = new question_edit_contexts($thiscontext);
    $defaultcategory = question_make_default_categories($contexts->all());
    $cat = $defaultcategory->id . ',' . $defaultcategory->contextid;
    $qcobject = new question_category_object(1, $url,
        $contexts->having_one_edit_tab_cap('categories'), 0,
        $cat, 0, $contexts->having_cap('moodle/question:add'));
    $data = [
        'categoriesrendered' => $qcobject->output_edit_lists(),
        'contextid' => $args['context']->id,
    ];
    return $OUTPUT->render_from_template('qbank_managecategories/categoryrendering', $data);
}
