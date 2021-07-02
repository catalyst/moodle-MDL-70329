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
 * @return array
 */
function qbank_comment_comment_display($comments, $args): array {
    if ($args->commentarea != 'core_question' && $args->component != 'qbank_comment') {
        throw new comment_exception('core_question');
    }
    return $comments;
}