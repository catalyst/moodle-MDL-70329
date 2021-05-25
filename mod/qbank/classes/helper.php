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
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_qbank;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Class helper contains all library functions.
 *
 * @package mod_qbank
 */
class helper {

    /**
     * Gets only course categories where question bank are populated.
     *
     * @return array Return a moodle_recordset instance.
     * @throws \dml_exception
     */
    public static function get_categories_populated(): array {
        global $DB;

        $sql = "SELECT DISTINCT qc.name, qc.contextid, ctx.contextlevel, ctx.instanceid
                FROM {question} q
                JOIN {question_categories} qc ON qc.id = q.category
                JOIN {context} ctx ON ctx.id = qc.contextid
            ORDER BY ctx.contextlevel";

        return $DB->get_records_sql($sql);
    }

    /**
     * Checks if course exists.
     *
     * @param string $coursename Course name to check
     * @return bool Returns a boolean.
     * @throws \dml_exception A DML specific exception is thrown for any errors.
     */
    public static function course_exists(string $coursename): bool {
        global $DB;

        return $DB->record_exists('course', array('fullname' => $coursename));
    }

    /**
     * Creates course.
     *
     * @param string $coursename Course name to create
     * @param int $categoryid Category id parent to the course
     * @return bool Returns a boolean.
     * @throws \moodle_exception
     */
    public static function create_category_course(string $coursename, int $categoryid): stdClass {
        $newcourse = new stdClass();

        $newcourse->fullname = $coursename;
        $newcourse->category = $categoryid;
        create_course($newcourse);

        return $newcourse;
    }
}
