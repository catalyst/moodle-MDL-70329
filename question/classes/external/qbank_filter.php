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

namespace core_question\external;

require_once($CFG->dirroot . '/question/editlib.php');

use core_question\local\bank\condition;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use external_warnings;

/**
 * Core question external functions.
 *
 * @package    core_question
 * @category   external
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021 Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbank_filter extends external_api {

    /**
     * Describes the parameters for fetching the table html.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {

        $params = [
            'defaultcourseid' => new external_value(
                PARAM_INT,
                'Default course ID',
                VALUE_REQUIRED,
            ),
            'defaultcategoryid' => new external_value(
                PARAM_INT,
                'Default question category ID',
                VALUE_REQUIRED,
            ),
            'filters' => new external_multiple_structure (
                new external_single_structure(
                    [
                        'filtertype' => new external_value(PARAM_ALPHANUM, 'Filter type'),
                        'jointype' => new external_value(PARAM_INT, 'Join type'),
                        'values' => new external_value(PARAM_RAW, 'list of ids'),
                        'rangetype' => new external_value(PARAM_INT, 'Range type', VALUE_OPTIONAL),
                    ]
                ),
                'Filter params',
                VALUE_DEFAULT,
                [],
            ),
            'filteroptions' => new external_single_structure(
                [
                    'filterverb' => new external_value(
                        PARAM_INT,
                        'Main join types',
                        VALUE_DEFAULT,
                        condition::JOINTYPE_DEFAULT,
                    ),
                ]
            ),
            'displayoptions' => new external_single_structure(
                [
                    'perpage' => new external_value(
                        PARAM_INT,
                        'The number of records per page',
                        VALUE_DEFAULT,
                        20,
                    ),
                    'page' => new external_value(
                        PARAM_INT,
                        'The page number',
                        VALUE_DEFAULT,
                        0
                    ),
                ]
            ),
            'sortdata' => new external_multiple_structure(
                new external_single_structure([
                    'sortby' => new external_value(
                        PARAM_TEXT,
                        'The name of a sortable column',
                        VALUE_REQUIRED
                    ),
                    'sortorder' => new external_value(
                        PARAM_ALPHANUMEXT,
                        'The direction that this column should be sorted by',
                        VALUE_REQUIRED
                    ),
                ]),
                'The combined sort order of the table. Multiple fields can be specified.',
                VALUE_OPTIONAL,
                []
            ),
        ];

        return new external_function_parameters($params);
    }

    /**
     * External function to get the table view content.
     *
     * @param array $filters
     * @param array $filteroptions
     * @param array $displayoptions
     * @param array $sortdata
     * @param int $defaultcourseid
     * @param int $defaultcategoryid
     * @return array
     */
    public static function execute(
        int $defaultcourseid,
        int $defaultcategoryid,
        array $filters = [],
        array $filteroptions = [],
        array $displayoptions = [],
        array $sortdata = []
    ): array {
        global $DB;

        $courseid = $defaultcourseid;

        $params = [
            'courseid' => $courseid,
            'filterverb' => $filteroptions['filterverb'],
            'qperpage' => $displayoptions['perpage'],
            'qpage' => $displayoptions['page'],
            'tabname' => 'questions'
        ];

        foreach ($filters as $filter) {
            $params['filters'][$filter['filtertype']] = [
                'jointype' => $filter['jointype'],
                'rangetype' => $filter['rangetype'] ?? null,
                'values' => empty($filter['values'])  && $filter['values'] != 0 ? [] : explode(',', $filter['values']),
            ];
        }

        // Set default category if it's empty.
        if (empty($params['filters']['category'])) {
            $params['filters']['category'] = [
                'values' => [$defaultcategoryid],
            ];
        }
        // Add contextID for the category filter.
        $categoryids = $params['filters']['category']['values'];
        // Currently, we support only one category for the list because of new/edit/delete buttons.
        $categoryid = array_pop($categoryids);
        if (!is_numeric($categoryid)) {
            $warnings[] = [
                'warningcode' => 'nocategoryconditionspecified',
                'message' => get_string('nocategoryconditionspecified', 'question')
            ];
            return [
                'filtercondition' => '',
                'warnings' => $warnings
            ];
        }

        // Error management for range conditions.
        foreach ($params['filters'] as $filter) {
            if (isset($filter['rangetype'])) {
                if ($filter['rangetype'] === condition::RANGETYPE_BETWEEN) {
                    if (count($filter['values']) === 1) {
                        $warnings[] = [
                            'warningcode' => 'nocategoryconditionspecified',
                            'message' => get_string('nocategoryconditionspecified', 'question')
                        ];
                        return [
                            'filtercondition' => '',
                            'warnings' => $warnings
                        ];
                    }
                }
                foreach ($filter['values'] as $filtervalue) {
                    if (!is_numeric($filtervalue)) {
                        $warnings[] = [
                            'warningcode' => 'nocategoryconditionspecified',
                            'message' => get_string('nocategoryconditionspecified', 'question')
                        ];
                        return [
                            'filtercondition' => '',
                            'warnings' => $warnings
                        ];
                    }
                }
            }
        }

        $categories = $DB->get_records('question_categories', ['id' => $categoryid]);
        $categories = \qbank_managecategories\helper::question_add_context_in_key($categories);
        $category = array_pop($categories);
        $category = $category->id;
        $params['cat'] = $category;

        // Add sort to param.
        $sortnum = 1;
        foreach ($sortdata as $data) {
            $sortby = $data['sortby'];
            if ($data['sortorder'] == SORT_DESC) {
                $sortby = '-' . $sortby;
            }
            $params['qbs' . $sortnum] = $sortby;
            $sortnum++;
        }
        return [
            'filtercondition' => json_encode($params),
            'warnings' => []
        ];
    }

    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'filtercondition' => new external_value(PARAM_RAW, 'Question filter conditions'),
            'warnings' => new external_warnings()
        ]);
    }

}
