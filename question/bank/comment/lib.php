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
 * Helper functions and callbacks.
 *
 * @package    qbank_comment
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Validate comment parameter before perform other comments actions.
 *
 * @param stdClass $comment_param
 * {
 * context     => context the context object
 * courseid    => int course id
 * cm          => stdClass course module object
 * commentarea => string comment area
 * itemid      => int itemid
 * }
 * @return boolean
 */
function qbank_comment_comment_validate($comment_param): bool {
    if ($comment_param->commentarea != 'core_question' && $comment_param->component != 'qbank_comment') {
        throw new comment_exception('invalidcommentarea');
    }
    return true;
}

/**
 * Running additional permission check on plugins.
 *
 * @param stdClass $args
 * @return array
 */
function qbank_comment_comment_permissions($args): array {
    return array('post'=>true, 'view'=>true);
}

/**
 * Validate comment data before displaying comments.
 *
 * @param stdClass $comments
 * @param stdClass $args
 * @return stdClass
 */
function qbank_comment_comment_display($comments, $args): array {
    if ($args->commentarea != 'core_question' && $args->component != 'qbank_comment') {
        throw new comment_exception('core_question');
    }
    return $comments;
}

function qbank_comment_test($question, $context, $course) {
    $args['questionid'] = $question->id;
    $args['context'] = $context;
    $args['courseid'] = $course->id;
    return qbank_comment_output_fragment_question_comment($args);
}

/**
 * Comment content for callbacks.
 *
 * @param stdClass $question
 * @param context $context
 * @param stdClass $course
 * @param int $itemid
 * @return string
 */
function qbank_comment_preview_display($question, $context, $course, $itemid): string {
    global $CFG;
    if (question_has_capability_on($question, 'comment') && $CFG->usecomments) {
        \comment::init();
        $args = new \stdClass;
        $args->context   = $context;
        $args->course    = $course;
        $args->area      = 'core_question';
        $args->itemid    = $itemid;
        $args->component = 'qbank_comment';
        $args->notoggle  = true;
        $args->autostart = true;
        $args->displaycancel = false;
        $args->linktext = get_string('commentheader', 'qbank_comment');
        $comment = new \comment($args);
        $comment->set_view_permission(true);
        $comment->set_fullwidth();
        return $comment->output();
    } else {
        return '';
    }
}

/**
 * Question comment fragment callback.
 *
 * @param $args
 * @return string rendered output
 * @todo cleanup after classrenaming to remove check for previewlib.php
 */
function qbank_comment_output_fragment_question_comment($args): string {
    global $USER, $PAGE, $CFG;
    $displaydata = [];
    $question = question_bank::load_question($args['questionid']);
    $quba = question_engine::make_questions_usage_by_activity(
            'core_question_preview', context_user::instance($USER->id));

    if (class_exists('\\qbank_previewquestion\\question_preview_options')) {
        $options = new \qbank_previewquestion\question_preview_options($question);
    } else {
        require_once($CFG->dirroot . '/question/previewlib.php');
        $options = new question_preview_options($question);
    }

    $options->load_user_defaults();
    $options->set_from_request();
    $quba->set_preferred_behaviour($options->behaviour);
    $slot = $quba->add_question($question, $options->maxmark);
    $quba->start_question($slot, $options->variant);
    $displaydata['question'] = $quba->render_question($slot, $options, '1');
    $course = get_course($args['courseid']);
    $context = context_course::instance($args['courseid']);
    $displaydata['comment'] = qbank_comment_preview_display($question, $context, $course, $args['questionid']);
    $displaydata['commenstdisabled'] = false;
    if (empty($displaydata['comment']) && !$CFG->usecomments) {
        $displaydata['commenstdisabled'] = true;
    }

    return $PAGE->get_renderer('qbank_comment')->render_comment_fragment($displaydata);
}
