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
use context;
use qbank_managecategories\helper;
use stdClass;
use moodle_exception;

class submit_edit_category_form extends external_api {
    /**
     * Describes the parameters for submit_edit_category_form webservice.
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'jsonformdata' => new external_value(PARAM_RAW, 'The data from the edit group form, encoded as a json array')
            ]);
    }

    /**
     * Submit the add category form.
     *
     * @param string $jsonformdata The data from the form, encoded as a json array.
     * @return int $categoryid category id.
     */
    public static function execute($jsonformdata) {
        global $CFG, $USER, $DB;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::execute_parameters(),
                                            ['jsonformdata' => $jsonformdata]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/question:editmine', $context);

        $serialiseddata = json_decode($params['jsonformdata'], true);
        $data = [];
        parse_str($serialiseddata, $data);

        $updateid = $data['id'];
        $newname = $data['name'];
        $newparent = $data['parent'];
        $updatedcategory = $data['name'];
        $newinfo = $data['info']['text'];
        $newinfoformat = $data['info']['format'];
        $idnumber = $data['idnumber'];

        if (empty($newname)) {
            throw new moodle_exception('categorynamecantbeblank', 'question');
        }

        // Get the record we are updating.
        $oldcat = $DB->get_record('question_categories', ['id' => $updateid]);
        $lastcategoryinthiscontext = helper::question_is_only_child_of_top_category_in_context($updateid);

        if (!empty($newparent) && !$lastcategoryinthiscontext) {
            list($parentid, $tocontextid) = explode(',', $newparent);
        } else {
            $parentid = $oldcat->parent;
            $tocontextid = $oldcat->contextid;
        }

        $fromcontext = context::instance_by_id($oldcat->contextid);
        require_capability('moodle/question:editmine', $fromcontext);

        // If moving to another context, check permissions some more, and confirm contextid,stamp uniqueness.
        $newstamprequired = false;
        if ($oldcat->contextid != $tocontextid) {
            $tocontext = context::instance_by_id($tocontextid);
            require_capability('moodle/question:editmine', $tocontext);

            // Confirm stamp uniqueness in the new context. If the stamp already exists, generate a new one.
            if ($DB->record_exists('question_categories', ['contextid' => $tocontextid, 'stamp' => $oldcat->stamp])) {
                $newstamprequired = true;
            }
        }

        if ((string) $idnumber === '') {
            $idnumber = null;
        } else if (!empty($tocontextid)) {
            // While this check already exists in the form validation, this is a backstop preventing unnecessary errors.
            if ($DB->record_exists_select('question_categories',
                    'idnumber = ? AND contextid = ? AND id <> ?',
                    [$idnumber, $tocontextid, $updateid])) {
                $idnumber = null;
            }
        }

        // Update the category record.
        $cat = new stdClass();
        $cat->id = $updateid;
        $cat->name = $newname;
        $cat->info = $newinfo;
        $cat->infoformat = $newinfoformat;
        $cat->parent = $parentid;
        $cat->contextid = $tocontextid;
        $cat->idnumber = $idnumber;
        if ($newstamprequired) {
            $cat->stamp = make_unique_id_code();
        }
        $success = $DB->update_record('question_categories', $cat);
        return $success;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_BOOL, 'Returns true on successful update');
    }
}
