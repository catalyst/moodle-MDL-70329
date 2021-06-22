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

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

/**
 * External qbank_managecategories API
 *
 * @package    qbank_managecategories
 * @category   external
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_category_parent extends external_api {
    /**
     * Describes the parameters for update_category_parent webservice.
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'tomove' => new external_value(PARAM_INT, 'Category id to move under new category'),
            'tocategory' => new external_value(PARAM_INT, 'Destination category id')
        ]);
    }

    /**
     * Set category order the add category form.
     *
     * @param int $tomove Category id to move under new category.
     * @param int $tocategory Destination category id.
     */
    public static function execute(int $tomove, int $tocategory) {
        global $DB;
        $categorytomove = $DB->get_record('question_categories', ['id' => $tomove]);
        $categorytomove->parent = $tocategory;
        $DB->update_record('question_categories', $categorytomove);
    }

    /**
     * Returns description of method result value.
     */
    public static function execute_returns() {
    }
}
