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

namespace mod_quiz\question\bank;

/**
 * Helper class for question bank and its associated data.
 *
 * @package    mod_quiz
 * @category   question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbank_helper {

    /**
     * Check if the slot is a random question or not.
     *
     * @param int $slotid
     * @return bool
     */
    public static function is_random($slotid): bool {
        global $DB;
        return $DB->record_exists('question_set_references', ['itemid' => $slotid]);
    }

    /**
     * Get the version options for the question.
     *
     * @param int $questionid
     * @return array
     */
    public static function get_version_options($questionid): array {
        global $DB;
        $sql = "SELECT qv.id AS versionid, qv.version
                  FROM {question_versions} qv
                 WHERE qv.questionbankentryid = (SELECT DISTINCT qbe.id
                                                   FROM {question_bank_entry} qbe
                                                   JOIN {question_versions} qv ON qbe.id = qv.questionbankentryid
                                                   JOIN {question} q ON qv.questionid = q.id
                                                  WHERE q.id = ?)";

        $versionsoptions = $DB->get_records_sql($sql, [$questionid]);
        return $versionsoptions;
    }

    /**
     * Get the reference data for a slotid.
     *
     * @param $slotid
     * @return false|mixed|\stdClass
     * @throws \dml_exception
     */
    public static function get_reference_data($slotid) {
        global $DB;
        return $DB->get_record('question_references', ['itemid' => $slotid]);
    }

    /**
     * Get the current version.
     *
     * @param int $questionid
     * @return false|mixed|\stdClass
     */
    public static function get_current_version($questionid) {
        global $DB;
        return $DB->get_record('question_versions', ['questionid' => $questionid]);
    }

    /**
     * Sort the elements of an array according to a key.
     *
     * @param array $array
     * @param string $on
     * @param int $order
     * @return array
     */
    public static function question_array_sort($array, $on, $order = SORT_ASC): array {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

    /**
     * Get the question id from slot id.
     *
     * @param int $slotid
     * @return mixed
     */
    public static function get_question_id_from_slot($slotid) {
        global $DB;
        $referencerecord = $DB->get_record('question_references', ['itemid' => $slotid]);
        $questionid = $DB->get_record('question_versions', ['questionbankentryid' => $referencerecord->questionbankentryid,
                                                                    'version' => $referencerecord->version])->questionid;
        return $questionid;
    }

    /**
     * Get random question object from the slot id.
     *
     * @param int $slotid
     * @return false|mixed|\stdClass
     */
    public static function get_random_question_data_from_slot($slotid) {
        global $DB;
        return $DB->get_record('question_set_references', ['itemid' => $slotid]);
    }

    /**
     * Get the usage count for the question in quiz.
     *
     * @param int $questionid
     * @return int
     */
    public static function get_question_usage_count_in_quiz($questionid) {
        global $DB;
        $bankentry = get_question_bank_entry($questionid);
        $versiondata = self::get_current_version($questionid);
        return $DB->count_records('question_references', ['questionbankentryid' => $bankentry->id,
                                                                'version' => $versiondata->version]);
    }

    /**
     * Get question attempt count for the question.
     *
     * @param int $questionid
     * @return int
     */
    public static function get_question_attempt_count($questionid) {
        global $DB;
        $sql = 'SELECT COUNT(qatt.id)
                  FROM {quiz_slots} qs
                  JOIN {quiz_attempts} qa ON qa.quiz = qs.quizid
                  JOIN {question_usages} qu ON qu.id = qa.uniqueid
                  JOIN {question_attempts} qatt ON qatt.questionusageid = qu.id
                  JOIN {question} q ON q.id = qatt.questionid
                 WHERE qatt.questionid = ?
                       AND qa.preview = 0';
        return $DB->count_records_sql($sql, [$questionid]);
    }

    /**
     * Get the question ids for always latest options.
     *
     * @param int $quizid
     * @return array
     */
    public static function get_always_latest_version_question_ids($quizid) {
        global $DB;
        $questionids = [];
        $sql = 'SELECT qr.questionbankentryid as entry
                  FROM {quiz_slots} qs
                  JOIN {question_references} qr ON qr.itemid = qs.id
                 WHERE qr.version IS NULL
                       AND qs.quizid = ?';
        $entryids = $DB->get_records_sql($sql, [$quizid]);
        $questionentries = [];
        foreach ($entryids as $entryid) {
            $questionentries [] = $entryid->entry;
        }
        if (empty($questionentries)) {
            return $questionids;
        }
        list($questionidcondition, $params) = $DB->get_in_or_equal($questionentries);
        $extracondition = 'AND qv.questionbankentryid ' . $questionidcondition;
        $questionsql = "SELECT q.id
                          FROM {question} q
                          JOIN {question_versions} qv ON qv.questionid = q.id
                         WHERE qv.version = (SELECT MAX(v.version)
                                               FROM {question_versions} v
                                               JOIN {question_bank_entry} be
                                                 ON be.id = v.questionbankentryid
                                              WHERE be.id = qv.questionbankentryid)
                         $extracondition";
        $questions = $DB->get_records_sql($questionsql, $params);
        foreach ($questions as $question) {
            $questionids [] = $question->id;
        }
        return $questionids;
    }

    /**
     * Get the question structure data for the given quiz or question ids.
     *
     * @param null $quizid
     * @param array $questionids
     * @return array
     */
    public static function get_question_structure_data($quizid, $questionids = [], $attempt = false) {
        global $DB;
        $params = ['quizid' => $quizid];
        $condition = '';
        $joinon = 'AND qr.version = qv.version';
        if (!empty($questionids)) {
            list($condition, $param) = $DB->get_in_or_equal($questionids,SQL_PARAMS_NAMED, 'questionid');
            $condition = 'AND q.id ' . $condition;
            $joinon = '';
            $params = array_merge($params, $param);
        }
        if ($attempt) {
            $selectstart = 'q.*, slot.id AS slotid,';
        } else {
            $selectstart = 'slot.id AS slotid, q.*,';
        }
        $sql = "SELECT $selectstart
                       slot.slot,
                       q.id AS questionid,
                       slot.page,
                       slot.maxmark,
                       slot.requireprevious,
                       qc.id as category,
                       qc.contextid,qv.status,
                       qv.id as versionid,
                       qv.version,
                       qv.questionbankentryid
                  FROM {quiz_slots} slot
             LEFT JOIN {question_references} qr ON qr.itemid = slot.id                  
             LEFT JOIN {question_bank_entry} qbe ON qbe.id = qr.questionbankentryid
             LEFT JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id $joinon
             LEFT JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid   
             LEFT JOIN {question} q ON q.id = qv.questionid
                 WHERE slot.quizid = :quizid
             $condition";
        $questiondatas = $DB->get_records_sql($sql, $params);
        foreach ($questiondatas as $questiondata) {
            $questiondata->_partiallyloaded = true;
        }
        if (!empty($questiondatas)) {
            return $questiondatas;
        }
        return [];
    }

    /**
     * Get question structure.
     *
     * @param int $quizid
     * @return array
     */
    public static function get_question_structure($quizid) {
        $firstslotsets = self::get_question_structure_data($quizid);
        $latestquestionids = self::get_always_latest_version_question_ids($quizid);
        $secondslotsets = self::get_question_structure_data($quizid, $latestquestionids);
        foreach ($firstslotsets as $key => $firstslotset) {
            foreach ($secondslotsets as $secondslotset) {
                if ($firstslotset->slotid === $secondslotset->slotid) {
                    unset($firstslotsets[$key]);
                }
            }
        }
        return self::question_array_sort(array_merge($firstslotsets, $secondslotsets), 'slot');
    }

    /**
     * Get the question structure report data for the given quiz or question ids.
     *
     * @param null $quizid
     * @param array $questionids
     * @return array|void
     */
    public static function get_question_report_structure_data($quizid, $questionids = []) {
        global $DB;
        $params = ['quizid' => $quizid];
        $condition = '';
        if (!empty($questionids)) {
            list($condition, $param) = $DB->get_in_or_equal($questionids,SQL_PARAMS_NAMED, 'questionid');
            $condition = 'AND q.id ' . $condition;
            $params = array_merge($params, $param);
        }

        //$sql = "SELECT q.id,
        //               q.qtype,
        //               q.length,
        //               qs.maxmark
        //          FROM {quiz_slots} qs
        //          LEFT JOIN {quiz_attempts} qa ON qa.quiz = qs.quizid
        //          LEFT JOIN {question_usages} qu ON qu.id = qa.uniqueid
        //          LEFT JOIN {question_attempts} qatt ON qatt.questionusageid = qu.id
        //          LEFT JOIN {question} q ON q.id = qatt.questionid
        //          WHERE qs.quizid = :quizid
        //          $condition
        //          GROUP BY q.id
        //          ORDER BY qs.slot";
        $sql = "SELECT slot.slot,
                       q.id,
                       q.qtype,
                       q.length,
                       slot.maxmark
                  FROM {quiz_slots} slot
             LEFT JOIN {question_references} qr ON qr.itemid = slot.id
             LEFT JOIN {question_bank_entry} qbe ON qbe.id = qr.questionbankentryid
             LEFT JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
             LEFT JOIN {question} q ON q.id = qv.questionid
                 WHERE slot.quizid = :quizid
             $condition";
        $questiondatas = $DB->get_records_sql($sql, $params);
        if (!empty($questiondatas)) {
            return $questiondatas;
        }
        return [];
    }

    /**
     * Get the question data for the ids.
     *
     * @param array $questionids
     * @return false|mixed
     */
    public static function get_report_structure_random_data($questionids) {
        global $DB;
        if (empty($questionids)) {
            return [];
        }
        list($condition, $param) = $DB->get_in_or_equal($questionids,SQL_PARAMS_NAMED, 'questionid');
        $condition = 'WHERE q.id ' . $condition;
        $sql = "SELECT q.id,
                       q.qtype,
                       q.length,
                       qc.contextid,
                       qc.id as categoryid
                  FROM {question} q
                  JOIN {question_versions} qv ON qv.questionid = q.id
                  JOIN {question_bank_entry} qbe ON qbe.id = qv.questionbankentryid
                  JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
                  $condition";
        $questions = $DB->get_records_sql($sql, $param);
        return $questions;
    }

    /**
     * Get the question ids for the quiz attempts.
     *
     * @param $quizid
     * @return array
     */
    public static function get_questionids_for_attempts_in_quiz($quizid) {
        global $DB;
        $questionids = [];
        $sql = 'SELECT DISTINCT q.id
                  FROM {quiz} as qz
                  JOIN {quiz_attempts} qa ON qa.quiz = qz.id
                  JOIN {question_usages} qu ON qu.id = qa.uniqueid
                  JOIN {question_attempts} qatt ON qatt.questionusageid = qu.id
                  JOIN {question} q ON q.id = qatt.questionid
                 WHERE qz.id = ?';
        $questions = $DB->get_records_sql($sql, [$quizid]);
        foreach ($questions as $question) {
            $questionids [] = $question->id;
        }
        return $questionids;
    }

    /**
     * Get question structure report.
     *
     * @param int $quizid
     * @return array
     */
    public static function get_question_report_structure($quizid) {
        global $DB;
        $questionids = self::get_questionids_for_attempts_in_quiz($quizid);
        $firstsets = self::get_question_report_structure_data($quizid, $questionids);
        $randomslots = $DB->get_records_sql('SELECT qs.slot,
                                                    qs.id as slotid,
                                                    qs.maxmark,
                                                    qsr.*
                                               FROM {quiz_slots} qs
                                               JOIN {question_set_references} qsr ON qsr.itemid = qs.id
                                              WHERE qs.quizid = ?', [$quizid]);
        foreach ($firstsets as $firstset) {
            foreach ($questionids as $key => $questionid) {
                if ($firstset->id === $questionid) {
                    unset($questionids[$key]);
                }
            }
        }

        $secondsets = array_values(self::get_report_structure_random_data($questionids));
        $conditions = self::get_conditions($questionids);
        foreach($conditions as $condition) {
            // Get the question sets if belong to the same context and category.
            $questionsets = [];
            foreach ($secondsets as $secondset) {
                if ($condition->contextid === $secondset->contextid
                    && $condition->categoryid === $secondset->categoryid) {
                      $questionsets[] = $secondset;
                }
            }

            // Now create an array with the random slots to be matched with each attempted question.
            $randomslotobjects = [];
            $position = 0;
            foreach ($randomslots as $randomslot) {
                $filtercondition = json_decode($randomslot->filtercondition);
                if ($condition->contextid === $randomslot->questionscontextid
                    && $condition->categoryid === $filtercondition->questioncategoryid) {
                    $randomslotobject = new \stdClass();
                    $randomslotobject->questionscontextid = $randomslot->questionscontextid;
                    $randomslotobject->questioncategoryid = $filtercondition->questioncategoryid;
                    $randomslotobject->slot = $randomslot->slot;
                    $randomslotobject->maxmark = $randomslot->maxmark;
                    $randomslotobjects[$position] = $randomslotobject;
                    $position++;
                }
            }

            // Create the question record and add it to the set.
            foreach ($randomslotobjects as $key => $randomslotobject) {
                $questionrecord = new \stdClass();
                $questionrecord->id = $questionsets[$key]->id;
                $questionrecord->qtype = $questionsets[$key]->qtype;
                $questionrecord->length = $questionsets[$key]->length;
                $questionrecord->maxmark = $randomslotobject->maxmark;
                $questionrecord->slot = $randomslotobject->slot;
                $firstsets[] = $questionrecord;
            }
        }
        return $firstsets;
    }

    /**
     * Get the contexts and categories for question ids.
     *
     * @param array $questionids
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_conditions(array $questionids): array {
        global $DB;
        if (empty($questionids)) {
            return [];
        }
        list($condition, $param) = $DB->get_in_or_equal($questionids,SQL_PARAMS_NAMED, 'questionid');
        $condition = 'WHERE q.id ' . $condition;
        $sql = "SELECT DISTINCT qc.id as categoryid, qc.contextid
                  FROM {question} q
                  JOIN {question_versions} qv ON qv.questionid = q.id
                  JOIN {question_bank_entry} qbe ON qbe.id = qv.questionbankentryid
                  JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
                  $condition";

        $conditions = $DB->get_records_sql($sql, $param);
        return $conditions;
    }
}
