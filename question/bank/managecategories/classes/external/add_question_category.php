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
class add_question_category extends external_api {
    /**
     * Describes the parameters for add_question_category webservice.
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'parent' => new external_value(PARAM_TEXT, 'parent'),
                'name' => new external_value(PARAM_TEXT, 'name'),
                'info' => new external_value(PARAM_RAW, 'info'),
                'infoformat' => new external_value(PARAM_INT, 'infoformat'),
                'idnumber' => new external_value(PARAM_TEXT, 'idnumber')
            ]);
    }

    /**
     * Adds a question category.
     *
     * @param string $parent Parent of the category.
     * @param string $name Category's new name.
     * @param string $info Category's new information(s)/description.
     * @param int $infoformat description format. One of the FORMAT_ constants.
     * @param string $idnumber Category idnumber.
     * @return int $categoryid category id.
     */
    public static function execute($parent, $name, $info, $infoformat, $idnumber) {
        global $DB;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::execute_parameters(),
                                            ['parent' => $parent,
                                            'name' => $name,
                                            'info' => $info,
                                            'infoformat' => $infoformat,
                                            'idnumber' => $idnumber]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/category:manage', $context);

        $newparent = $params['parent'];
        $newcategory = $params['name'];
        $newinfo = format_text($params['info'], $params['infoformat'], ['noclean' => false]);
        $idnumber = $params['idnumber'];

        if (empty($newcategory)) {
            throw new moodle_exception('categorynamecantbeblank', 'question');
        }

        list($parentid, $contextid) = explode(',', $newparent);

        if (isset($idnumber)) {
            $exists = helper::idnumber_exists($idnumber, $contextid);
            if ($exists) {
                return false;
            }
        }

        if ($parentid) {
            if (!($DB->get_field('question_categories', 'contextid', ['id' => $parentid]) == $contextid)) {
                throw new moodle_exception('cannotinsertquestioncatecontext', 'question', '',
                    ['cat' => $newcategory, 'ctx' => $contextid]);
            }
        }

        if ((string) $idnumber === '') {
            $idnumber = null;
        } else if (!empty($contextid)) {
            // While this check already exists in the form validation, this is a backstop preventing unnecessary errors.
            if ($DB->record_exists('question_categories',
                    ['idnumber' => $idnumber, 'contextid' => $contextid])) {
                $idnumber = null;
            }
        }

        $cat = new stdClass();
        $cat->parent = clean_param($parentid, PARAM_INT);
        $cat->contextid = clean_param($contextid, PARAM_INT);
        $cat->name = $newcategory;
        $cat->info = $newinfo;
        $cat->infoformat = $params['infoformat'];
        $cat->sortorder = 999;
        $cat->stamp = make_unique_id_code();
        $cat->idnumber = $idnumber;
        $categoryid = $DB->insert_record("question_categories", $cat);

        // Log the creation of this category.
        $category = new stdClass();
        $category->id = clean_param($categoryid, PARAM_INT);
        $category->contextid = clean_param($contextid, PARAM_INT);
        $event = \core\event\question_category_created::create_from_question_category_instance($category);
        $event->trigger();
        $success = ($categoryid) ? $categoryid : -1;
        return $success;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_INT, 'Added question category id if successful -1 if fail');
    }
}
