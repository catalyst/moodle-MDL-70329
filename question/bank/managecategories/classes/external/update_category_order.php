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

use context_system;
use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use qbank_managecategories\helper;
use coding_exception;

/**
 * External qbank_managecategories API handling.
 *
 * External class used for category reordering using drag and drop,
 * it also handles the update of category parent when category descendant arrow is being used.
 *
 * @package    qbank_managecategories
 * @category   external
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_category_order extends external_api {
    /**
     * Describes the parameters for update_category_order webservice.
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'origincategory' => new external_value(PARAM_INT, 'Category being moved'),
            'insertaftercategory' => new external_value(PARAM_INT,
                'Target category after which the origin category will be inserted', VALUE_DEFAULT, 0),
            'newparentcategory' => new external_value(PARAM_INT, 'New parent category', VALUE_DEFAULT, 0)
        ]);
    }

    /**
     * Move category to new location.
     *
     * @param int $origincategory Category id from dragged category.
     * @param int $insertaftercategory Target category after which the 'origin category' will be inserted.
     * @param int $newparentcategory New parent category.
     * @return array contains result message
     */
    public static function execute(int $origincategory, int $insertaftercategory = 0, int $newparentcategory = 0): array {

        [
            'origincategory' => $origincategory,
            'insertaftercategory' => $insertaftercategory,
            'newparentcategory' => $newparentcategory,
        ] = self::validate_parameters(self::execute_parameters(), [
            'origincategory' => $origincategory,
            'insertaftercategory' => $insertaftercategory,
            'newparentcategory' => $newparentcategory,
        ]);

        // Update category location.
        helper::update_category_location($origincategory, $insertaftercategory, $newparentcategory);

        return ['message' => get_string('categorymoved', 'qbank_managecategories')];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     */
    public static function execute_returns() {
        return new external_single_structure([
            'message' => new external_value(PARAM_TEXT, 'Message', VALUE_OPTIONAL)
        ]);
    }
}
