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

namespace qbank_columnsortorder\external;

defined('MOODLE_INTERNAL') || die();

use context_system;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use qbank_columnsortorder\column_sort_order_manager;

/**
 * External qbank_columnsortorder_set_columnbank_order API
 *
 * @package    qbank_columnsortorder
 * @category   external
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_columnbank_order extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
                ['columns' => new external_value(PARAM_RAW, 'JSON String containing column order to set in config_plugins table')]
        );
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value_structure
     */
    public static function execute_returns() {
        return new external_value(PARAM_BOOL, 'result: true if success');
    }

    /**
     * Returns the columns plugin order.
     *
     * @param string $columns json string representing new column order.
     * @return bool
     */
    public static function execute(string $columns) {
        $params = self::validate_parameters(self::execute_parameters(), ['columns' => $columns]);
        self::validate_context(context_system::instance());
        $columns = str_replace('"', "", $params['columns']);
        $result = column_sort_order_manager::set_order($columns);
        return $result;
    }
}
