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
 * Question related functions.
 *
 * This file was created just because Fragment API expects callbacks to be defined on lib.php.
 *
 * @package   qbank_viewlist
 * @copyright 2021 Catalyst IT Australia Pty Ltd
 * @author    Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/editlib.php');

use core_question\bank\search\question_condition;

/**
 * View list fragment callback.
 *
 * @param array $args Arguments to the form.
 * @return null|string The rendered form.
 */
function qbank_viewlist_output_fragment_question_list($args) {

    if (empty($args['questions'])) {
        return '';
    }
    $questions = json_decode($args['questions']);
    if (empty($questions)) {
        return '';
    }
    $questionids = [];
    foreach ($questions as $question) {
        $questionids[] = $question->id;
    }
    $context = $args['context'];
    $courseid = $context->instanceid;

    $thispageurl = new \moodle_url('/question/edit.php');
    $thispageurl->param('courseid', $courseid);
    $contexts = new \question_edit_contexts($context);
    $course = get_course($courseid);
    $cm = null;
    $questionbank = new \core_question\local\bank\view($contexts, $thispageurl, $course, $cm);

    $questionbank->add_searchcondition(new question_condition($questionids));
    $questions = $questionbank->load_questions();
    ob_start();
    $questionbank->display_for_api($questions);
    $tablehtml = ob_get_clean();
    return $tablehtml;
}
