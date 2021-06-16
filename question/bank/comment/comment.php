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
 * This page displays the comments of a question.
 *
 * @package    qbank_comment
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot.'/comment/locallib.php');
require_once($CFG->dirroot.'/comment/lib.php');

\core_question\local\bank\helper::check_qbank_status('qbank_comment');

// Get and validate question id.
$id = required_param('id', PARAM_INT);
$question = question_bank::load_question($id);

// Were we given a particular context to run the question in?
// This affects things like filter settings, or forced theme or language.
if ($cmid = optional_param('cmid', 0, PARAM_INT)) {
    $cm = get_coursemodule_from_id(false, $cmid);
    require_login($cm->course, false, $cm);
    $context = context_module::instance($cmid);

} else if ($courseid = optional_param('courseid', 0, PARAM_INT)) {
    require_login($courseid);
    $context = context_course::instance($courseid);

} else {
    require_login();
    $category = $DB->get_record('question_categories',
            array('id' => $question->category), '*', MUST_EXIST);
    $context = context::instance_by_id($category->contextid);
    $PAGE->set_context($context);
    // Note that in the other cases, require_login will set the correct page context.
}

question_require_capability_on($question, 'comment');
$PAGE->set_url(\qbank_comment\comment_helper::question_comment_url($question->id, $context));
$PAGE->set_title(get_string('comment', 'qbank_comment'));
$PAGE->set_heading($COURSE->fullname);
echo $OUTPUT->header();
list($context, $course, $cm) = get_context_info_array($PAGE->context->id);
$args = new stdClass;
$args->context   = $context;
$args->course    = $course;
$args->area      = 'question_comments';
$args->itemid    = $id;
$args->component = 'qbank_comment';
$args->linktext  = get_string('showcomments');
$args->notoggle  = true;
$args->autostart = true;
$args->displaycancel = false;
$comment = new comment($args);
$comment->set_view_permission(true);
$comment->set_fullwidth();
$comment->output(false);
echo $OUTPUT->footer();
