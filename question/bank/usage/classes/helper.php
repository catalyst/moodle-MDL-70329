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

namespace qbank_usage;

/**
 * Helper class for usage.
 *
 * @package    qbank_usage
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Get the usage count for a question.
     *
     * @param \question_definition $question
     * @return int
     */
    public static function get_question_entry_usage_count($question) {
        global $DB;

        $sql = 'SELECT COUNT(quizid) FROM (' . self::question_usage_sql() . ') AS quizid';

        return $DB->count_records_sql($sql, [$question->id, $question->id]);
    }

    /**
     * Get the sql for usage data.
     *
     * @return string
     */
    public static function question_usage_sql(): string {
        $sqlset = "(SELECT qz.id as quizid,
                           qz.name as modulename,
                           qz.course as courseid
                      FROM {quiz} as qz
                      JOIN {quiz_attempts} qa ON qa.quiz = qz.id
                      JOIN {question_usages} qu ON qu.id = qa.uniqueid
                      JOIN {question_attempts} qatt ON qatt.questionusageid = qu.id
                      JOIN {question} q ON q.id = qatt.questionid
                      WHERE qa.preview = 0
                        AND q.id = ?)
                    UNION
                    (SELECT qz.id as quizid,
                            qz.name as modulename,
                            qz.course as courseid
                      FROM {quiz_slots} slot
                      JOIN {quiz} qz ON qz.id = slot.quizid
                      JOIN {question_references} qr ON qr.itemid = slot.id                  
                      JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
                      JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                     WHERE qv.questionbankentryid = ?)";
        return $sqlset;
    }

    /**
     * Get question attempt count for the question.
     *
     * @param int $questionid
     * @param int $quizid
     * @return int
     */
    public static function get_question_attempts_count_in_quiz($questionid, $quizid): int {
        global $DB;
        $sql = 'SELECT COUNT(qatt.id)
                  FROM {quiz} as qz
                  JOIN {quiz_attempts} qa ON qa.quiz = qz.id
                  JOIN {question_usages} qu ON qu.id = qa.uniqueid
                  JOIN {question_attempts} qatt ON qatt.questionusageid = qu.id
                  JOIN {question} q ON q.id = qatt.questionid
                 WHERE qatt.questionid = :questionid
                   AND qa.preview = 0
                   AND qz.id = :quizid';
        return $DB->count_records_sql($sql, [ 'questionid' => $questionid, 'quizid' => $quizid]);
    }

}
