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
use core\notification;
use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use moodle_exception;
use qbank_managecategories\helper;

/**
 * External qbank_managecategories API
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
            'neworder' => new external_value(PARAM_RAW, 'Category order string, encoded as a json array, ie:
            [["9,19"],["2,17","8,19"],["6,3","4,3"],["10,1"]]'),
            'origincategory' => new external_value(PARAM_INT, 'Category being moved'),
            'destinationcontext' => new external_value(PARAM_INT, 'Destination where the moved category is being put'),
            'origincontext' => new external_value(PARAM_INT, 'Context from where the category was moved')
        ]);
    }

    /**
     * Set category order the add category form.
     *
     * @param string $neworder Category order, encoded as a json array, ie: [["9,19"],["2,17","8,19"],["6,3","4,3"],["10,1"]].
     * @param int $origincategory Category id from dragged category.
     * @param int $destinationcontext Destination context id where category is dropped.
     * @param int $origincontext Context id where the category was dragged from.
     * @return string $categories.
     */
    public static function execute(string $neworder, int $origincategory, int $destinationcontext, int $origincontext) {
        global $DB;
        $params = self::validate_parameters(self::execute_parameters(), [
            'neworder' => $neworder,
            'origincategory' => $origincategory,
            'destinationcontext' => $destinationcontext,
            'origincontext' => $origincontext
        ]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/category:manage', $context);
        // New order insertion.
        $neworder = $params['neworder'];
        $neworder = json_decode($neworder, true);

        $origincategory = $params['origincategory'];
        $origincontext = $params['origincontext'];
        $newctxid = $params['destinationcontext'];

        // Question_categories table modifications.
        if (!is_null($newctxid)) {
            // Retrieves new and old context categories.
            $sql = 'SELECT id, contextid, parent, sortorder, idnumber
                        FROM {question_categories}
                        WHERE (contextid = ?) OR (contextid = ?)';

            $records = $DB->get_records_sql($sql, [$newctxid, $origincontext]);
            foreach ($records as $record) {
                // Top category where to add newly moved one(s).
                if (((int)$record->contextid === $newctxid) && ((int)$record->parent === 0)) {
                    $topcategoryid = (int)$record->id;
                }
                // Top category of old context to unset in records - for descendant research below.
                if (((int)$record->contextid === $origincontext) && ((int)$record->parent === 0)) {
                    $topcategoryidtounset = (int)$record->id;
                }
            }
            $destinationcontext = $records[$topcategoryid];
            $categorytoupdate = $records[$origincategory];
            unset($records[$topcategoryid]);
            unset($records[$topcategoryidtounset]);
            $transaction = $DB->start_delegated_transaction();
            if (isset($categorytoupdate->idnumber)) {
                // We don't want errors when reordering in same context.
                if ($destinationcontext->contextid !== $categorytoupdate->contextid) {
                    $exists = helper::get_idnumber($categorytoupdate->idnumber, $destinationcontext->contextid);
                    if ($exists) {
                        return [
                            'success' => false,
                            'error' => 'idnumberexists'
                        ];
                    }
                }
            }

            $parents = [];
            // Check if moved category has descendants - update those categories with new contextid.
            foreach ($records as $child => $record) {
                $parents[$child] = (int)$record->parent;
            }
            $descendants = helper::get_childs((int)$categorytoupdate->id, $parents);

            foreach ($descendants as $descendantid) {
                $records[$descendantid]->contextid = $newctxid;
                // Checks if the descendant idnumber exists or not.
                if (isset($records[$descendantid]->idnumber)) {
                    // We don't want errors when reordering in same context.
                    if ($destinationcontext->contextid !== $categorytoupdate->contextid) {
                        $exists = helper::get_idnumber($records[$descendantid]->idnumber, $destinationcontext->contextid);
                        if ($exists) {
                            return [
                                'success' => false,
                                'error' => 'idnumberexists'
                            ];
                        }
                    }
                }
                $DB->update_record('question_categories', $records[$descendantid]);
            }

            $categorytoupdate->parent = $destinationcontext->id;
            $updatedcontextcat = $categorytoupdate->id . ',' . $categorytoupdate->contextid;
            $categorytoupdate->contextid = $newctxid;
            $DB->update_record('question_categories', $categorytoupdate);
            // Retrieve sortorder in concerned contexts (only 2 context).
            $tosort = [];
            foreach ($neworder as $category) {
                foreach ($category as $innerorder => $innervalue) {
                    if ($innervalue === $updatedcontextcat) {
                        $innervalue = $categorytoupdate->id . ',' . $categorytoupdate->contextid;
                    }
                    $tosort[$innervalue] = $innerorder;
                }
            }

            // Sets new sortorder.
            foreach ($tosort as $ctxcat => $sortorder) {
                $currentctx = clean_param(explode(',', $ctxcat)[1], PARAM_INT);
                $currentid = clean_param(explode(',', $ctxcat)[0], PARAM_INT);
                if (($currentctx === $origincontext) || ($currentctx === $newctxid)) {
                    if (isset($records[$currentid])) {
                        $rec = $records[$currentid];
                        $rec->sortorder = $sortorder;
                        $DB->update_record('question_categories', $rec);
                    }
                }
            }
        }
        $transaction->allow_commit();
        return [
            'success' => true,
            'error' => '',
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     */
    public static function execute_returns() {
        return new external_single_structure([
                'success' => new external_value(PARAM_BOOL, 'Returns success or failure'),
                'error'   => new external_value(PARAM_TEXT, 'Error code')
        ]);
    }
}
