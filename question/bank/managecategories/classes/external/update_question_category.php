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
use context_system;
use external_api;
use external_function_parameters;
use external_value;
use moodle_exception;
use qbank_managecategories\helper;
use stdClass;

/**
 * External qbank_managecategories API
 *
 * @package    qbank_managecategories
 * @category   external
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_question_category extends external_api {
    /**
     * Describes the parameters for update_question_category webservice.
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'parent' => new external_value(PARAM_INT, 'Parent of the category'),
                'name' => new external_value(PARAM_TEXT, 'New category name'),
                'info' => new external_value(PARAM_RAW, 'New category informations/description'),
                'infoformat' => new external_value(PARAM_INT, 'Description format. One of the FORMAT_ constants'),
                'idnumber' => new external_value(PARAM_RAW, 'Category idnumber, unique'),
                'id' => new external_value(PARAM_INT, 'Category id')
            ]);
    }

    /**
     * Updates question category.
     *
     * @param string $parent Parent of the category.
     * @param string $name Category's new name.
     * @param string $info Category's new information(s)/description.
     * @param int $infoformat description format. One of the FORMAT_ constants.
     * @param string $idnumber Category idnumber.
     * @param int $id Category id that we are updating.
     * @return int $id category id.
     */
    public static function execute($parent, $name, $info, $infoformat, $idnumber, $id) {
        global $DB;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::execute_parameters(),
                                            ['parent' => $parent,
                                            'name' => $name,
                                            'info' => $info,
                                            'infoformat' => $infoformat,
                                            'idnumber' => $idnumber,
                                            'id' => $id]);

        $contextid = $DB->get_field('question_categories', 'contextid', ['id' => $params['parent']]);
        $context = context::instance_by_id($contextid);
        self::validate_context($context);
        require_capability('moodle/category:manage', $context);

        $updateid = $params['id'];
        $newname = $params['name'];
        $newparent = $params['parent'];
        $newinfo = format_text($params['info'], $params['infoformat'], ['noclean' => false]);
        $idnumber = $params['idnumber'];

        // Get the record we are updating.
        $oldcat = $DB->get_record('question_categories', ['id' => $updateid]);
        $lastcategoryinthiscontext = helper::question_is_only_child_of_top_category_in_context($updateid);

        if (!empty($newparent) && !$lastcategoryinthiscontext) {
            $parentid = $params['parent'];
            $tocontextid = $contextid;
        } else {
            $parentid = $oldcat->parent;
            $tocontextid = $oldcat->contextid;
        }

        $exists = helper::get_idnumber($idnumber, $tocontextid);
        if ($exists && $exists !== $updateid) {
            throw new moodle_exception('idnumberexists', 'qbank_managecategories');
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
        }

        // Update the category record.
        $cat = new stdClass();
        $cat->id = $updateid;
        $cat->name = $newname;
        $cat->info = $newinfo;
        $cat->infoformat = $params['infoformat'];
        $cat->parent = $parentid;
        $cat->contextid = $tocontextid;
        $cat->idnumber = $idnumber;
        if ($newstamprequired) {
            $cat->stamp = make_unique_id_code();
        }
        $DB->update_record('question_categories', $cat);
        // Log the update of this category.
        $event = \core\event\question_category_updated::create_from_question_category_instance($cat);
        $event->trigger();

        return $updateid;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_INT, 'Updated category id');
    }
}
