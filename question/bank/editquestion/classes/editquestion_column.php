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
 * Base class for question bank columns that just contain an action icon.
 *
 * @package   qbank_editquestion
 * @copyright 2009 Tim Hunt
 * @author    2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_editquestion;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\menu_action_column_base;
use moodle_url;

/**
 * Base class for question bank columns that just contain an action icon.
 *
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editquestion_column extends menu_action_column_base {

    /**
     * Contains the string.
     * @var string
     */
    protected $stredit;

    /**
     * Contains the string.
     * @var string
     */
    protected $strview;

    /**
     * Contains the url of the edit question page.
     * @var moodle_url|string
     */
    public $editquestionurl;

    /**
     * A chance for subclasses to initialise themselves, for example to load lang strings,
     * without having to override the constructor.
     */
    public function init() {
        parent::init();
        $this->stredit = get_string('editquestion', 'question');
        $this->strview = get_string('view');
        $this->editquestionurl = new \moodle_url('/question/bank/editquestion/question.php',
                array('returnurl' => $this->qbank->returnurl));
        if ($this->qbank->cm !== null) {
            $this->editquestionurl->param('cmid', $this->qbank->cm->id);
        } else {
            $this->editquestionurl->param('courseid', $this->qbank->course->id);
        }
    }

    /**
     * Get the internal name for this column. Used as a CSS class name,
     * and to store information about the current sort. Must match PARAM_ALPHA.
     *
     * @return string column name.
     */
    public function get_name() {
        return 'editaction';
    }

    /**
     * Get the URL for editing a question as a link.
     *
     * @param int $questionid the question id.
     * @return \moodle_url the URL, HTML-escaped.
     */
    public function edit_question_moodle_url($questionid) {
        return new \moodle_url($this->editquestionurl, ['id' => $questionid]);
    }

    /**
     * Get the information required to display this action either as a menu item or a separate action column.
     *
     * If this action cannot apply to this question (e.g. because the user does not have
     * permission, then return [null, null, null].
     *
     * @param \stdClass $question the row from the $question table, augmented with extra information.
     * @return array with three elements.
     *      $url - the URL to perform the action.
     *      $icon - the icon for this action. E.g. 't/delete'.
     *      $label - text label to display in the UI (either in the menu, or as a tool-tip on the icon)
     */
    protected function get_url_icon_and_label(\stdClass $question): array {
        if (!\question_bank::is_qtype_installed($question->qtype)) {
            // It sometimes happens that people end up with junk questions
            // in their question bank of a type that is no longer installed.
            // We cannot do most actions on them, because that leads to errors.
            return [null, null, null];
        }

        if (question_has_capability_on($question, 'edit')) {
            return [$this->edit_question_moodle_url($question->id), 't/edit', $this->stredit];
        } else if (question_has_capability_on($question, 'view')) {
            return [$this->edit_question_moodle_url($question->id), 'i/info', $this->strview];
        } else {
            return [null, null, null];
        }
    }
}
