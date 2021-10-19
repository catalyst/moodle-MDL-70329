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

namespace core_question;

defined('MOODLE_INTERNAL') || die();

/**
 * Code handling question bank and question versioning.
 *
 * @package    core_question
 * @subpackage questionbank
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 class question_bank_manager {
    /**
     * Create a new version, bank_entry and reference for each question.
     *
     * @param $question object question object with all the information required for additional tables.
     * @param $form object Form data object.
     * @param $context object Context object.
     * @param object|null $questionbankentry object Question bank entry object.
     * @param bool $newquestion
     * @throws dml_exception
     */
    public function save_question_versions(object $question, object $form, object $context, object $questionbankentry = null,
                                    bool $newquestion = true) : void {
        global $DB;

        if (!$questionbankentry) {
            // Create a record for question_bank_entry, question_versions and question_references.
            $questionbankentry = new \stdClass();
            $questionbankentry->questioncategoryid = $form->category;
            $questionbankentry->idnumber = $question->idnumber;
            $questionbankentry->ownerid = $question->createdby;
            $questionbankentry->id = $DB->insert_record('question_bank_entry', $questionbankentry);
        } else {
            $questionbankentryold = new \stdClass();
            $questionbankentryold->id = $questionbankentry->id;
            $questionbankentryold->idnumber = $question->idnumber;
            $DB->update_record('question_bank_entry', $questionbankentryold);
        }

        $questionversiondata = $DB->get_record('question_versions', ['questionbankentryid' => $questionbankentry->id,
                'questionid' => $question->id]);
        if ($newquestion || !$questionversiondata) {
            // Create question_versions records.
            $questionversion = new \stdClass();
            $questionversion->questionbankentryid = $questionbankentry->id;
            $questionversion->questionid = $question->id;
            // Get the version and status from the parent question if parent is set.
            if (!$question->parent) {
                // Get the status field. It comes from the form, but for testing we can.
                if (isset($form->status)) {
                    $status = $form->status;
                } else if (isset($question->status)) {
                    $status = $question->status;
                } else {
                    $status = \core_question\local\bank\constants::QUESTION_STATUS_READY;
                }
                $questionversion->version = get_next_version($questionbankentry->id);
                $questionversion->status = $status;
            } else {
                $parentversion = get_question_version($form->parent);
                $questionversion->version = $parentversion[array_key_first($parentversion)]->version;
                $questionversion->status = $parentversion[array_key_first($parentversion)]->status;
            }
            $questionversion->id = $DB->insert_record('question_versions', $questionversion);
        } else {
            $questionversion = new \stdClass();
            $questionversion->id = $questionversiondata->id;
            // Get the status field. It comes from the form, but for testing we can.
            $questionversion->status = $form->status ?? $question->status;
            $DB->update_record('question_versions', $questionversion);
        }
    }
}
