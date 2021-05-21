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
 * Library functions used by db/install.php.
 *
 * @package    mod_qbank
 * @copyright  2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_qbank;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot .'/course/lib.php');

use core_course_category;

class createcourse_helper {

    /**
     * Get all populated question banks.
     *
     * @return stdClass $rec Return question category records.
     */
    public static function get_populated() {

        global $DB;

        // Retrieve question bank informations that are populated.
        $sqlq = "SELECT DISTINCT qc.name, qc.contextid, ctx.contextlevel, ctx.instanceid
                FROM mdl_question q
                JOIN mdl_question_categories qc ON qc.id = q.category
                JOIN mdl_context ctx ON ctx.id = qc.contextid
                ORDER BY ctx.contextlevel;";

        $rec = $DB->get_records_sql($sqlq);

        return $rec;
    }

    /**
     * Get course category id from course table.
     *
     * @param  string $courseid Course id.
     * @return string $catid Returns course category id.
     */
    public static function get_coursecatid(string $courseid) {

        global $DB;

        $qu = "SELECT category FROM mdl_course WHERE id = ?";
        $catid = $DB->get_records_sql($qu, [$courseid]);
        $catid = array_values(json_decode(json_encode($catid), true))[0]['category'];

        return $catid;
    }

    /**
     * Get course id from course_modules table.
     *
     * @param  string $instanceid Course instance id.
     * @return string $Course id Returns course id.
     */
    public static function get_module_courseid(string $instanceid) {

        global $DB;

        $q = "SELECT course FROM mdl_course_modules WHERE id = ?";
        $courseid = $DB->get_records_sql($q, [$instanceid]);
        $courseid = array_values(json_decode(json_encode($courseid), true))[0]['course'];

        return $courseid;
    }

    /**
     * Populate new course object.
     *
     * @param   stdClass $newcourse Object to populate.
     * @param   stdClass $cat Question bank category informations.
     * @param   string  $catid Category id where the new course should be added.
     * @return  stdClass $newcourse Populated object.
     */
    public static function populate_course(object $newcourse, object $cat, string $catid) {
        $newcourse->fullname    = "New course from question bank " . $cat->contextlevel . ' ' . $cat->name;
        $newcourse->category    = +$catid;

        return $newcourse;
    }

    /**
     * Checks if course already exists.
     *
     * @param   string $coursename Course name.
     * @return  bool true or false.
     */
    public static function course_exists(string $coursename) {

        global $DB;

        return $DB->record_exists('course', array('fullname' => $coursename)) ? true : false;
    }

    /**
     * Create a single category for system question banks.
     *
     * @param   string $categoryname Category name.
     * @param   stdClass $newcategory Object containing new category informations.
     * @return  bool true or false.
     */
    public static function create_system_category(string $categoryname, object $newcategory) {

        global $DB;

        $newcategory->id = 0;
        $newcategory->name = $categoryname;

        if ($DB->record_exists('course_categories', array('name' => $categoryname))) {
            return 0;
        } else {
            return core_course_category::create($newcategory);
        }
    }

    public static function get_system_coursecatid(string $categoryname) {

        global $DB;

        $q = "SELECT id FROM mdl_course_categories WHERE name = ?";
        $catid = $DB->get_records_sql($q, [$categoryname]);
        $catid = array_values(json_decode(json_encode($catid), true))[0]['id'];

        return $catid;
    }
}
