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
 * External question API
 *
 * @package    qbank/managecategories
 * @category   external
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

class qbank_managecategories_external extends external_api {

    public static function set_category_order_parameters() {
        return new external_function_parameters(
                ['categories' => new external_value(PARAM_RAW, 'JSON String')]
        );
    }

    public static function set_category_order(string $categories) {
        global $DB;
        $params = self::validate_parameters(self::set_category_order_parameters(), ['categories' => $categories]);

        $categories = json_decode($categories, true);
        $neworder = $categories[0];
        $catid = (int)explode(' ', $categories[2])[1];
        $oldctxid = (int)explode(' ', $categories[2])[0];
        $newctxid = (int)explode(' ', $categories[1])[0];

        // Question_categories table modifications.
        if (!is_null($categories[1])){
            // Retrieves top category parent where neighbor category is located.
            $destparentcat = $DB->get_record_select('question_categories', 'contextid = :newcontextid AND parent = :parentcat',
                    ['newcontextid' => $newctxid, 'parentcat' => 0]);
            // Sets new parent.
            $DB->set_field('question_categories', 'parent', $destparentcat->id, ['id' => $catid]);
            // Sets new context id.
            $DB->set_field('question_categories', 'contextid', $newctxid, ['id'=> $catid]);
            // Sets sortorder field.
            foreach ($neworder as $order => $category) {
                foreach ($category as $innerorder => $innervalue) {
                    $DB->set_field('question_categories', 'sortorder', $innerorder, ['id' => explode(' ', $innervalue)[1]]);
                }
            }
        }
        $sortorder = json_encode($sortorder);
        return $sortorder;
    }

    public static function set_category_order_returns() {
        return new external_value(PARAM_RAW, 'Return cleaned JSON string');
    }
}