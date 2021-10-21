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
                                                   FROM {question_bank_entries} qbe
                                                   JOIN {question_versions} qv ON qbe.id = qv.questionbankentryid
                                                   JOIN {question} q ON qv.questionid = q.id
                                                  WHERE q.id = ?)
              ORDER BY qv.version DESC";

        return $DB->get_records_sql($sql, [$questionid]);
    }

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
     * @param array $arrays
     * @param string $on
     * @param int $order
     * @return array
     */
    public static function question_array_sort($arrays, $on, $order = SORT_ASC): array {
        $element = [];
        foreach ($arrays as $array) {
            $element[$array->$on] = $array;
        }
        ksort($element, $order);
        return $element;
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
                                                JOIN {question_bank_entries} be
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
            $selectstart = 'q.*, slot.id AS slotid, slot.slot,';
        } else {
            $selectstart = 'slot.slot, slot.id AS slotid, q.*,';
        }
        $sql = "SELECT $selectstart
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
             LEFT JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
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
        $selectstart = 'slot.slot, q.id,';
        $sql = "SELECT $selectstart
                       q.qtype,
                       q.length,
                       slot.maxmark,
                       q.qtype as type
                  FROM {quiz_slots} slot
             LEFT JOIN {question_references} qr ON qr.itemid = slot.id
             LEFT JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
             LEFT JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
             LEFT JOIN {question} q ON q.id = qv.questionid
                 WHERE slot.quizid = :quizid
             $condition
             ORDER BY slot.slot";
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
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                  JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
                  $condition";
        return $DB->get_records_sql($sql, $param);
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
        $firstsets = self::get_question_report_structure_data($quizid, []);
        $randomslots = $DB->get_records_sql('SELECT qs.slot,
                                                        qs.id as slotid,
                                                        qs.maxmark,
                                                        qsr.*
                                                   FROM {quiz_slots} qs
                                                   JOIN {question_set_references} qsr ON qsr.itemid = qs.id
                                                   WHERE qs.quizid = ?', [$quizid]);
        foreach ($firstsets as $firstset) {
            if ($firstset->qtype === null) {
                $firstset->qtype = 'random';
                $firstset->type = 'random';
                $firstset->length = '1'; // Might need to check later.
                $firstset->setreference = $randomslots[$firstset->slot];
                $filtercondition = json_decode($randomslots[$firstset->slot]->filtercondition);
                $categoryobject = $DB->get_record('question_categories', ['id' => $filtercondition->questioncategoryid]);
                $firstset->categoryobject = $categoryobject;
                $firstset->category = $filtercondition->questioncategoryid;
            }
        }
        return $firstsets;
    }

    /**
     * Load random questions.
     *
     * @param int $quizid
     * @param array $questiondata
     * @return array
     */
    public static function question_load_random_questions($quizid, $questiondata) {
        global $DB, $USER;
        $sql = 'SELECT slot.id AS slotid,
                   slot.maxmark,
                   slot.slot,
                   slot.page,
                   qsr.filtercondition
             FROM {question_set_references} qsr
             JOIN {quiz_slots} slot ON slot.id = qsr.itemid
            WHERE slot.quizid = ?';
        $randomquestiondatas = $DB->get_records_sql($sql, [$quizid]);

        $randomquestions = [];
        // Questions already added.
        $usedquestionids = [];
        foreach ($questiondata as $question) {
            if (isset($usedquestions[$question->id])) {
                $usedquestionids[$question->id] += 1;
            } else {
                $usedquestionids[$question->id] = 1;
            }
        }
        // Usages for this user's previous quiz attempts.
        $qubaids = new \mod_quiz\question\qubaids_for_users_attempts($quizid, $USER->id);
        $randomloader = new \core_question\local\bank\random_question_loader($qubaids, $usedquestionids);

        foreach ($randomquestiondatas as $randomquestiondata) {
            $filtercondition = json_decode($randomquestiondata->filtercondition);
            $tagids = [];
            if (isset($filtercondition->tags)) {
                foreach ($filtercondition->tags as $tag) {
                    $tagstring = explode(',', $tag);
                    $tagids [] = $tagstring[0];
                }
            }
            $randomquestiondata->randomfromcategory = $filtercondition->questioncategoryid;
            $randomquestiondata->randomincludingsubcategories = $filtercondition->includingsubcategories;
            $randomquestiondata->questionid = $randomloader->get_next_question_id($randomquestiondata->randomfromcategory,
                $randomquestiondata->randomincludingsubcategories, $tagids);
            $randomquestions [] = $randomquestiondata;
        }

        foreach ($randomquestions as $randomquestion) {
            // Should not add if there is no question found from the ramdom question loader, maybe empty category.
            if ($randomquestion->questionid === null) {
                continue;
            }
            $question = new stdClass();
            $question->slotid = $randomquestion->slotid;
            $question->maxmark = $randomquestion->maxmark;
            $question->slot = $randomquestion->slot;
            $question->page = $randomquestion->page;
            $qdatas = question_preload_questions($randomquestion->questionid);
            $qdatas = reset($qdatas);
            foreach ($qdatas as $key => $qdata) {
                $question->$key = $qdata;
            }
            $questiondata[$question->id] = $question;
        }

        return $questiondata;
    }
}
