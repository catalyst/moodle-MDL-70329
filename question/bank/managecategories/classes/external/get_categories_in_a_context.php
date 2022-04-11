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

namespace qbank_managecategories\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

use context;
use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;
use core_question\local\bank\question_edit_contexts;
use qbank_managecategories\helper;

/**
 * External qbank_managecategories API handling.
 *
 * External class used for getting categories in a given context and its parent contexts
 *
 * @package    qbank_managecategories
 * @category   external
 * @copyright  2022 Catalyst IT Australia Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_categories_in_a_context extends external_api {
    /**
     * Describes the parameters for update_category_order webservice.
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'Context ID')
        ]);
    }

    /**
     * Get categories at a given context and its parent contexts.
     *
     * @param int $contextid context id.
     * @return array contains categories
     */
    public static function execute(int $contextid): array {
        [
            'contextid' => $contextid,
        ] = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
        ]);

        $contexts = new question_edit_contexts(context::instance_by_id($contextid));
        $contexts = $contexts->all();
        $contextcategories = [];
        foreach ($contexts as $context) {
            $items = helper::get_categories_for_contexts($context->id);
            $items = helper::create_ordered_tree($items);
            $contextcategories[] =
                [
                    'contextid' => $context->id,
                    'contextname' => $context->get_context_name(),
                    'categories' => $items
                ];
        }

        return ['contexts' => $contextcategories];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'contexts' => new external_multiple_structure(new external_single_structure(
                    [
                        'contextid' => new external_value(PARAM_INT, 'Context ID'),
                        'contextname' => new external_value(PARAM_TEXT, 'Context name'),
                        'categories' => new external_multiple_structure(new external_single_structure(
                            [
                                'id' => new external_value(PARAM_INT, 'Category ID'),
                                'name' => new external_value(PARAM_TEXT, 'Category name'),
                            ]
                        ), 'Categories under each context', VALUE_DEFAULT, [])
                    ]
                    ), 'List of contexts and their question categories.', VALUE_DEFAULT, []
                )
            ]);
    }
}
