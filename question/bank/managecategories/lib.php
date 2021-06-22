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

use qbank_managecategories\question_category_object;

/**
 * Fragment for rendering categories.
 *
 * @param array $args Arguments to the form.
 * @return null|string The rendered form.
 */
function qbank_managecategories_output_fragment_category_rendering($args) {
    global $PAGE;
    $url = new moodle_url($args['url']);
    $context = $args['context'];
    $cmid = $args['cmid'] ?? null;
    $courseid = $args['courseid'] ?? null;
    $contexts = new question_edit_contexts($context);
    $qcobject = new question_category_object(1, $url,
        $contexts->having_one_edit_tab_cap('categories'), 0,
        null, 0, $contexts->having_cap('moodle/question:add'),
        $cmid, $courseid, $context->id);
    $categoriesrenderer = $PAGE->get_renderer('qbank_managecategories');
    $categoriesdata = $qcobject->categories_data()['categoriesrendered'];
    $data = [
        'categoriesrendered' => $categoriesdata,
        'contextid' => $args['context']->id,
    ];
    return $categoriesrenderer->render_qbank_categories($data);
}
