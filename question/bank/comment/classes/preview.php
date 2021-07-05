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
 * Plugin entrypoint for preview page elements.
 *
 * @package    qbank_comment
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_comment;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/comment/lib.php');

/**
 * Class preview_page_comment.
 *
 * @package    qbank_comment
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class preview extends \qbank_previewquestion\preview_page_base {

    public function get_display_html() {
        if (question_has_capability_on($this->question, 'comment')) {
            \comment::init();
            $args = new \stdClass;
            $args->context   = $this->context;
            $args->course    = $this->course;
            $args->area      = 'core_question';
            $args->itemid    = $this->itemid;
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
}
