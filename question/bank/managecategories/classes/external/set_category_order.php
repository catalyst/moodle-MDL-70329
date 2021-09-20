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
 * External qbank_managecategories API
 *
 * @package    qbank_managecategories
 * @category   external
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_managecategories\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

use context_system;
use external_api;
use external_function_parameters;
use external_value;
use qbank_managecategories\helper;

class set_category_order extends external_api {
    /**
     * Describes the parameters for set_category_order webservice.
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'categories' => new external_value(PARAM_RAW, 'JSON String - category order'),
        ]);
    }

    /**
     * Set category order the add category form.
     *
     * @param string $categories Category order, encoded as a json array.
     * @return string $categories.
     */
    public static function execute(string $categories) {
        global $DB;
        $params = self::validate_parameters(self::execute_parameters(),
            ['categories' => $categories]);
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/category:manage', $context);
        // New order insertion.
        $categories = clean_param($categories,  PARAM_TEXT);
        $categories = json_decode($categories, true);
        $neworder = $categories[0];
        $catid = (int)explode(' ', $categories[2])[1];
        $oldctxid = (int)explode(' ', $categories[2])[0];
        $newctxid = (int)explode(' ', $categories[1])[0];

        // Question_categories table modifications.
        if (!is_null($categories[1])) {
            // Retrieves top category parent where neighbor category is located.
            $sql = 'SELECT id, contextid, parent, sortorder
                      FROM {question_categories}
                     WHERE (contextid = ?) OR (id = ?)';

            $records = $DB->get_records_sql($sql, [$newctxid, $catid]);
            $destinationcontext = reset($records);
            $categorytoupdate = $records[$catid];
            $categorytoupdate->parent = $destinationcontext->id;
            $updatedcontextcat = $categorytoupdate->contextid . ' ' . $categorytoupdate->id;
            $categorytoupdate->contextid = $newctxid;
            $DB->update_record('question_categories', $categorytoupdate);

            // Retrieve sortorder in concerned contexts (only 2 context).
            $tosort = [];
            foreach ($neworder as $category) {
                foreach ($category as $innerorder => $innervalue) {
                    if ($innervalue === $updatedcontextcat) {
                        $innervalue = $categorytoupdate->contextid . ' ' . $categorytoupdate->id;
                    }
                    $tosort[$innervalue] = $innerorder;
                }
            }

            foreach ($tosort as $ctxcat => $sortorder) {
                $currentctx = (int)explode(' ', $ctxcat)[0];
                $currentid = (int)explode(' ', $ctxcat)[1];
                if (($currentctx === $oldctxid) || ($currentctx === $newctxid)) {
                    if (isset($records[$currentid])) {
                        $rec = $records[$currentid];
                        $rec->sortorder = $sortorder;
                        $DB->update_record('question_categories', $rec);
                    }
                }
            }
        }

        $categories = json_encode($categories);
        return $categories;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_RAW, 'Returns cleaned JSON string');
    }
}
