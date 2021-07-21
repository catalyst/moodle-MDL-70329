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
 * A column to show the number of comments.
 *
 * @package    qbank_comment
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_comment;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\column_base;
use question_bank;

/**
 * Class comment_count_column.
 *
 * @package    qbank_comment
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class comment_count_column extends column_base {

    public function get_name(): string {
        return 'commentcount';
    }

    protected function get_title(): string {
        return get_string('commentplural', 'qbank_comment');
    }

    protected function display_content($question, $rowclasses): void {
        global $DB, $OUTPUT, $PAGE;
        $target = 'questioncommentpreview_' . $question->id;
        $datatarget = '[data-target="' . $target . '"]';
        $PAGE->requires->js_call_amd('qbank_comment/comment', 'init', ['#questionscontainer', $datatarget]);
        $args = [
            'component' => 'qbank_comment',
            'commentarea' => 'core_question',
            'itemid' => $question->id,
            'contextid' => 1
        ];
        $commentcount = $DB->count_records('comments', $args);
        if (question_has_capability_on($question, 'comment')) {
            $url = $this->qbank->base_url();
            $attributes = [
                'data-target' => $target,
                'data-questionid' => $question->id,
                'data-courseid' => $this->qbank->course->id
            ];
            $link = new \action_menu_link_secondary($url, null, $commentcount, $attributes);
            echo $OUTPUT->render($link);
        } else {
            echo \html_writer::tag('a', $commentcount);
        }
    }

}
