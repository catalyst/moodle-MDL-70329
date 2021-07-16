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
     * Get the sql for question usage count
     *
     * @return array|int|void
     */
    public static function get_question_entry_usage_data($entryid, $count = false) {
        global $DB;
        //$firstsets = $DB->get_records_sql(self::question_usage_first_set_sql(), [$entryid]);
        //$secondsets = $DB->get_records_sql(self::question_usage_second_set_sql(), [$entryid]);
        //foreach ($firstsets as $firstset) {
        //    if (!array_key_exists($firstset->quizid, $secondsets)) {
        //        $secondsets [] = $firstset;
        //    }
        //}
        //if ($count) {
        //    return count($secondsets);
        //}
        //return $secondsets;
        $records = $DB->get_records_sql(self::question_usage_sql(), [$entryid, $entryid]);
        if ($count) {
            return count($records);
        }
        return $records;
    }

    public static function question_usage_sql() {
        $sqlset = "(SELECT qz.id as quizid,
                           qz.name as modulename,
                           qz.course as courseid,
                           qv.version
                      FROM {quiz} as qz
                      JOIN {quiz_attempts} qa ON qa.quiz = qz.id
                      JOIN {question_usages} qu ON qu.id = qa.uniqueid
                      JOIN {question_attempts} qatt ON qatt.questionusageid = qu.id
                      JOIN {question} q ON q.id = qatt.questionid
                      JOIN {question_versions} qv ON qv.questionid = q.id
                      WHERE qa.preview = 0
                        AND qv.questionbankentryid = ?)
                    UNION
                    (SELECT qz.id as quizid,
                           qz.name as modulename,
                           qz.course as courseid,
                           qr.version
                      FROM {quiz_slots} slot
                      JOIN {quiz} qz ON qz.id = slot.quizid
                      JOIN {question_references} qr ON qr.itemid = slot.id                  
                      JOIN {question_bank_entry} qbe ON qbe.id = qr.questionbankentryid
                      JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                     WHERE qv.questionbankentryid = ?)";
        return $sqlset;
    }

    public static function question_usage_first_set_sql() {
        $sqlset1 = "SELECT qz.id as quizid,
                           qz.name as modulename,
                           qz.course as courseid,
                           qv.version
                      FROM {quiz} as qz
                      JOIN {quiz_attempts} qa ON qa.quiz = qz.id
                      JOIN {question_usages} qu ON qu.id = qa.uniqueid
                      JOIN {question_attempts} qatt ON qatt.questionusageid = qu.id
                      JOIN {question} q ON q.id = qatt.questionid
                      JOIN {question_versions} qv ON qv.questionid = q.id
                      WHERE qa.preview = 0
                        AND qv.questionbankentryid = ?";
        return $sqlset1;
    }

    public static function question_usage_second_set_sql() {
        $sqlset2 = "SELECT qz.id as quizid,
                           qz.name as modulename,
                           qz.course as courseid,
                           qr.version
                      FROM {quiz_slots} slot
                      JOIN {quiz} qz ON qz.id = slot.quizid
                      JOIN {question_references} qr ON qr.itemid = slot.id                  
                      JOIN {question_bank_entry} qbe ON qbe.id = qr.questionbankentryid
                      JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                     WHERE qv.questionbankentryid = ?";
        return $sqlset2;
    }

    /**
     * Get question attempt count for the question.
     *
     * @param int $questionid
     * @return int
     */
    public static function get_question_attempts_count_in_quiz($questionid, $quizid) {
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
