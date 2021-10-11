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
 * Question external API.
 *
 * @package    core_question
 * @category   external
 * @copyright  2021 Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_question\external;

require_once($CFG->dirroot . '/question/editlib.php');

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;


/**
 * Core question external functions.
 *
 * @copyright  2021 Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class editquestion extends external_api {

    /**
     * Describes the parameters for fetching the table html.
     *
     * @return external_function_parameters
     */
    public static function get_questions_parameters(): external_function_parameters {
        return new external_function_parameters ([
            'courseid' => new external_value(
                PARAM_INT,
                'Course ID',
                VALUE_REQUIRED,
            ),
            'category' => new external_value(
                PARAM_SEQUENCE,
                'Question category ID',
                VALUE_OPTIONAL,
            ),
            'qtagids' => new external_value(
                PARAM_SEQUENCE,
                'Tag ID',
                VALUE_OPTIONAL,
            ),
            'qperpage' => new external_value(
                PARAM_INT,
                'The number of records per page',
                VALUE_OPTIONAL,
                false,
            ),
            'qpage' => new external_value(
                PARAM_INT,
                'The page number',
                VALUE_OPTIONAL,
                0
            ),
            'qbshowtext' => new external_value(
                PARAM_BOOL,
                'Flag to show question text',
                VALUE_OPTIONAL,
                false,
            ),
            'recurse' => new external_value(
                PARAM_BOOL,
                'Type of join to join all filters together',
                VALUE_OPTIONAL,
                false,
            ),
            'showhidden' => new external_value(
                PARAM_BOOL,
                'Flag to show question text',
                VALUE_OPTIONAL,
                false,
            ),
        ]);
    }

    /**
     * External function to get the table view content.
     *
     * @param int $courseid
     * @param string $category
     * @param string $qtagids
     * @param int $qperpage
     * @param int $qpage
     * @param bool $qbshowtext
     * @param bool $recurse
     * @param bool $showhidden
     * @return array
     */
    public static function get_questions(
        int $courseid,
        ?string $category = null,
        ?string $qtagids = null,
        ?int $qperpage = null,
        ?int $qpage = null,
        bool $qbshowtext = false,
        bool $recurse = false,
        bool $showhidden = false
    ): array {
        global $PAGE;

        $params = self::validate_parameters(self::get_questions_parameters(), [
            'courseid' => $courseid,
            'category' => $category,
            'qtagids' => $qtagids,
            'qperpage' => $qperpage,
            'qpage' => $qpage,
            'qbshowtext' => $qbshowtext,
            'recurse' => $recurse,
            'showhidden' => $showhidden,
        ]);

        list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) =
            question_build_edit_resources('questions', '/question/edit.php', $params);
        $course = get_course($params['courseid']);
        $questionbank = new \core_question\local\bank\view($contexts, $thispageurl, $course, $cm);
        ob_start();
        $questionbank->display_for_api($pagevars['cat']);
        $tablehtml = ob_get_flush();

        return [
            'html' => $tablehtml,
            'warnings' => []
        ];
    }

    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_questions_returns(): external_single_structure {
        return new external_single_structure([
            'html' => new external_value(PARAM_RAW, 'The raw html of the requested table.'),
            'warnings' => new external_warnings()
        ]);
    }
}
