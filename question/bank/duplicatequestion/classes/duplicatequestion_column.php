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
 * Question bank column for the duplicate action icon.
 *
 * @package   qbank_duplicatequestion
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_duplicatequestion;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\menu_action_column_base;
use moodle_url;

/**
 * Question bank column for the duplicate action icon.
 *
 * @copyright 2013 The Open University
 * @author    2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class duplicatequestion_column extends menu_action_column_base {

    /** @var string avoids repeated calls to get_string('duplicate'). */
    protected $strcopy;

    /**
     * Contains the url of the edit question page.
     * @var moodle_url|string
     */
    public $duplicatequestionurl;

    /**
     * A chance for subclasses to initialise themselves, for example to load lang strings,
     * without having to override the constructor.
     */
    public function init(): void {
        parent::init();
        $this->strcopy = get_string('duplicate');

        $this->duplicatequestionurl = new \moodle_url('/question/bank/editquestion/question.php',
                array('returnurl' => $this->qbank->returnurl));
        if ($this->qbank->cm !== null) {
            $this->duplicatequestionurl->param('cmid', $this->qbank->cm->id);
        } else {
            $this->duplicatequestionurl->param('courseid', $this->qbank->course->id);
        }
    }

    /**
     * Get the internal name for this column. Used as a CSS class name,
     * and to store information about the current sort. Must match PARAM_ALPHA.
     *
     * @return string column name.
     */
    public function get_name() {
        return 'copyaction';
    }

    /**
     * Get the URL for duplicating a question as a {@link \moodle_url}.
     *
     * @param int $questionid the question id.
     * @return \moodle_url the URL.
     */
    public function duplicate_question_moodle_url($questionid): moodle_url {
        return new \moodle_url($this->duplicatequestionurl, ['id' => $questionid, 'makecopy' => 1]);
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

        // To copy a question, you need permission to add a question in the same
        // category as the existing question, and ability to access the details of
        // the question being copied.
        if (question_has_capability_on($question, 'add') &&
                (question_has_capability_on($question, 'edit') || question_has_capability_on($question, 'view'))) {
            return [$this->duplicate_question_moodle_url($question->id), 't/copy', $this->strcopy];
        }
        return [null, null, null];
    }
}
