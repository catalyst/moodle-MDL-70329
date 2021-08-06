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
            'data' => new external_value(PARAM_RAW, 'JSON String - data for mustache file'),  
            'cmid' => new external_value(PARAM_INT, 'Int - cmid')  
        ]);
    }

    /**
     * Set category order the add category form.
     *
     * @param string $categories Category order, encoded as a json array.
     * @param string $data Data to use to render from mustache.
     * @return string $sortorder sororder.
     */
    public static function execute(string $categories, string $data, int $cmid) {
        global $DB;
        $params = self::validate_parameters(self::execute_parameters(), 
            ['categories' => $categories, 'data' => $data, 'cmid' => $cmid]);
        // New order insertion.
        $categories = json_decode($categories, true);
        $neworder = $categories[0];
        $catid = (int)explode(' ', $categories[2])[1];
        $oldctxid = (int)explode(' ', $categories[2])[0];
        $newctxid = (int)explode(' ', $categories[1])[0];

        // Question_categories table modifications.
        if (!is_null($categories[1])) {
            // Retrieves top category parent where neighbor category is located.
            $destparentcat = $DB->get_record_select('question_categories', 'contextid = :newcontextid AND parent = :parentcat',
                    ['newcontextid' => $newctxid, 'parentcat' => 0]);
            // Sets new parent.
            $DB->set_field('question_categories', 'parent', $destparentcat->id, ['id' => $catid]);
            // Sets new context id.
            $DB->set_field('question_categories', 'contextid', $newctxid, ['id' => $catid]);
            // Sets sortorder field and retrieval of all category ids.
            $categorylistids = [];
            foreach ($neworder as $order => $category) {
                foreach ($category as $innerorder => $innervalue) {
                    $DB->set_field('question_categories', 'sortorder', $innerorder, ['id' => explode(' ', $innervalue)[1]]);
                    $categorylistids[] = explode(' ', $innervalue)[1];
                }
            }
        }

        // Data to pass to mustache file
        $data = json_decode($data, true);
        $records = $DB->get_records_list('question_categories', 'id', $categorylistids);
        $records = json_decode(json_encode($records), true);
        $contexts = [];
        foreach ($records as $categoryid => $record) {
            $editactionmenu = helper::create_category_action_menu($cmid, $record['id'], $record['contextid']);
            $questionbankurl = helper::create_category_questionbankurl($cmid, $record['id'], $record['contextid']);
            $handle = helper::create_category_handle($record['id']);
            // $record['contextid']
            $contexts[$record['contextid']][] = [
                'categoryname' => $record['name'],
                'categorydescr' => $record['info'],
                'editactionmenu' => $editactionmenu,
                'questionbankurl' => $questionbankurl,
                'handle' => $handle,
            ];
        }
        // foreach ($data as $key => $dat) {
        //     foreach ($contexts as $context) {
        //         $data[$key]['items'] = $context;
        //     }
        // }
        //$data = array_merge_recursive($data, $contexts);
        $data = json_encode($data);
        return $data;
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
