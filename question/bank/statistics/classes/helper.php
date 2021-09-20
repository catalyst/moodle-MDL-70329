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

namespace qbank_statistics;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/quiz/report/statistics/statisticslib.php');
require_once($CFG->dirroot . '/mod/quiz/report/default.php');
require_once($CFG->dirroot . '/mod/quiz/report/statistics/report.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
require_once($CFG->dirroot . '/mod/quiz/classes/question/bank/qbank_helper.php');
use core_question\statistics\questions\all_calculated_for_qubaid_condition;
use quiz_statistics_report;
use mod_quiz\question\bank\qbank_helper;
/**
 * Helper for statistics
 *
 * @package    qbank_statistics
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Return all quizzes that use the quesion
     *
     * @param $questionid id of the question
     * @return array list of quizids
     * @throws \dml_exception
     */
    public static function get_quizzes($questionid): array {
        global $DB;
        $version = qbank_helper::get_current_version($questionid);

        $quizzes = $DB->get_records_sql("
            SELECT q.id
              FROM {quiz} q
              JOIN {quiz_slots} slot ON q.id = slot.quizid
              JOIN {question_references} qr ON qr.itemid = slot.id
             WHERE qr.questionbankentryid = :questionbankentryid
          ORDER BY quizid", ['questionbankentryid' => $version->questionbankentryid]);
        return $quizzes;
    }

    /**
     * Load question stats from a quiz
     *
     * @param $quiz quiz object
     * @return all_calculated_for_qubaid_condition
     */
    private static function load_question_stats($quiz): all_calculated_for_qubaid_condition {
        // All questions, no groups.
        $report = new quiz_statistics_report();
        $questions = $report->load_and_initialise_questions_for_calculations($quiz);
        $qubaids = quiz_statistics_qubaids_condition($quiz->id, new \core\dml\sql_join());
        $progress = new \core\progress\none();
        $qcalc = new \core_question\statistics\questions\calculator($questions, $progress);
        $quizcalc = new \quiz_statistics\calculator($progress);
        if ($quizcalc->get_last_calculated_time($qubaids) === false) {
            $questionstats = $qcalc->calculate($qubaids);
        } else {
            $questionstats = $qcalc->get_cached($qubaids);
        }
        return $questionstats;
    }

    /**
     * Load facility for a question
     *
     * @param $quiz quiz object
     * @param $questionid question id
     * @return float|int
     */
    public static function load_question_facility($quiz, $questionid): float {
        $questionstats = self::load_question_stats($quiz);
        foreach ($questionstats->questionstats as $stats) {
            if ($stats->questionid == $questionid) {
                return empty($stats->facility) ? 0 : $stats->facility;
            }
        }
        return 0;
    }

    /**
     * Calculate average facility index
     *
     * @param $quizzes
     * @param $questionid
     * @return float|int
     */

    public static function calculate_average_question_facility($quizzes, $questionid): float {
        $total = 0;
        foreach ($quizzes as $quiz) {
            $total += helper::load_question_facility($quiz, $questionid);
        }
        // Average facility index per quiz.
        $facility = $total / count($quizzes);
        return $facility;
    }

    /**
     * Load discriminative efficiency for a question
     *
     * @param $quiz quiz object
     * @param $questionid question id
     * @return float|int
     */
    public static function load_question_discriminative_efficiency($quiz, $questionid): float {
        $questionstats = self::load_question_stats($quiz);
        foreach ($questionstats->questionstats as $stats) {
            if ($stats->questionid == $questionid) {
                return empty($stats->discriminativeefficiency) ? 0 : $stats->discriminativeefficiency;
            }
        }
        return 0;
    }

    /**
     * Calculate average discriminative efficiency
     *
     * @param $quizzes
     * @param $questionid
     * @return float|int
     */
    public static function calculate_average_question_discriminative_efficiency($quizzes, $questionid): float {
        $total = 0;
        foreach ($quizzes as $quiz) {
            $total += helper::load_question_discriminative_efficiency($quiz, $questionid);
        }
        // Average discriminative efficiency per quiz.
        $discriminativeefficiency = $total / count($quizzes);
        return $discriminativeefficiency;
    }

    /**
     * Format a number to a localised percentage with specified decimal points.
     *
     * @param float $number The number being formatted
     * @param bool $fraction An indicator for whether the number is a fraction or is already multiplied by 100
     * @param int $decimals Sets the number of decimal points
     * @return string
     */
    public static function format_percentage(float $number, bool $fraction = true, int $decimals = 2): string {
        $coefficient = $fraction ? 100 : 1;
        return get_string('percents', 'moodle', format_float($number * $coefficient, $decimals));
    }
}
