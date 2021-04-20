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

require_once($CFG->dirroot.'/course/modlib.php');

/**
 * Class helper contains all library functions.
 *
 * @package mod_qbank
 */
class helper {

    /**
     * Gets only course categories where question bank are populated.
     *
     * @return \moodle_recordset Return a moodle_recordset instance.
     * @throws \dml_exception
     */
    public static function get_categories_populated(): \moodle_recordset {
        global $DB;

        $sql = "SELECT DISTINCT qc.contextid, ctx.contextlevel, ctx.instanceid
                  FROM {question} q
                  JOIN {question_versions} qv ON q.id = qv.questionid
                  JOIN {question_bank_entries} qbe ON qv.questionbankentryid = qbe.id
                  JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
                  JOIN {context} ctx ON ctx.id = qc.contextid
              ORDER BY ctx.contextlevel";

        return $DB->get_recordset_sql($sql);
    }

    /**
     * Get a course object given the short name.
     *
     * @param string $shortname Course shortname to check
     * @param int $categoryid Course category id.
     * @return null|object Returns a course.
     * @throws \dml_exception A DML specific exception is thrown for any errors.
     */
    public static function get_course(string $shortname, int $categoryid): ?object {
        global $DB;

        $course = $DB->get_record('course', ['shortname' => $shortname, 'category' => $categoryid]);
        if (!$course) {
            return null;
        }

        return $course;
    }

    /**
     * Get a qbank object given the short name.
     *
     * @param string $name mod_qbank name to check.
     * @param object $course Course object.
     * @return null|object Returns a qbank object.
     * @throws \dml_exception A DML specific exception is thrown for any errors.
     */
    public static function get_qbank(string $name, object $course): ?object {
        global $DB;

        $qbank = $DB->get_record('qbank', ['name' => $name, 'course' => $course->id]);
        if (!$qbank) {
            return null;
        }

        return $qbank;
    }

    /**
     * Get data from course_modules and quiz tables.
     *
     * @param int $instanceid course_module instance id.
     * @return object|null Returns a course module object.
     * @throws \dml_exception A DML specific exception is thrown for any errors.
     */
    public static function get_coursemodule(int $instanceid): ?object {
        global $DB;

        $sql = "SELECT cm.course, q.name
                  FROM {course_modules} cm
                  JOIN {quiz} q ON q.id = cm.instance
                 WHERE cm.id = :instanceid";

        $coursemodule = $DB->get_record_sql($sql, ['instanceid' => $instanceid]);
        if (!$coursemodule) {
            return null;
        }

        return $coursemodule;
    }

    /**
     * Get the category name.
     *
     * @param int $categoryid Category id
     * @return null|string Returns the category name.
     * @throws \dml_exception A DML specific exception is thrown for any errors.
     */
    public static function get_category_name(int $categoryid): ?string {
        global $DB;

        $categoryname = $DB->get_field('course_categories', 'name', ['id' => $categoryid]);
        if (!$categoryname) {
            return null;
        }

        return $categoryname;
    }

    /**
     * Creates course.
     *
     * @param string $shortname Course short name to create
     * @param int $categoryid Category id parent to the course
     * @return object Returns a course object.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function create_category_course(string $shortname, int $categoryid): object {
        $newcourse = new stdClass();

        $newcourse->fullname = $shortname;
        $newcourse->shortname = $shortname;
        $newcourse->category = $categoryid;
        $newcourse->summary = get_string('course_summary', 'mod_qbank');

        return create_course($newcourse);
    }

    /**
     * Creates qbank instance.
     *
     * @param string $name qbank name to create.
     * @param object $course Course parent to the qbank.
     * @return object New qbank instance object created.
     * @throws \moodle_exception
     */
    public static function create_qbank_instance(string $name, object $course): ?object {
        global $DB;

        $qbank = self::get_qbank($name, $course);
        if (!$qbank) {
            $moduleinfo = new stdClass();
            $moduleinfo->modulename = 'qbank';
            $moduleinfo->name = $name;
            $moduleinfo->intro = get_string('module_summary', 'mod_qbank');
            if ($course->id === SITEID) {
                if (!$DB->get_record('course_sections', ['course' => SITEID, 'section' => 1])) {
                    course_create_section(SITEID, 1);
                }
                $moduleinfo->section = 1;
            } else {
                if (!$DB->get_record('course_sections', ['course' => $course->id, 'section' => 0])) {
                    course_create_section($course->id);
                }
                $moduleinfo->section = 0;
            }
            $moduleinfo->course = $course->id;
            $moduleinfo->visible = false;

            $module = $DB->get_record('modules', ['name' => $moduleinfo->modulename]);
            $moduleinfo->module = $module->id;

            $qbank = add_moduleinfo($moduleinfo, $course);
        }
        return $qbank;
    }

    /**
     * Migrates question categories with newly created qbank contextid.
     *
     * @param object $qbank Newly created qbank object.
     * @param int $oldcontextid Context id to migrate.
     * @return void
     * @throws \moodle_exception
     */
    public static function migrate_question_categories(object $qbank, int $oldcontextid): void {
        global $DB;

        $contextid = \context_module::instance($qbank->coursemodule)->id;

        // Get the top category so that the child categories are migrated recursively.
        $category = $DB->get_record('question_categories', ['contextid' => $oldcontextid, 'parent' => 0, 'sortorder' => 0]);
        // Migrate question categories (top children).
        question_move_category_to_context($category->id, $oldcontextid, $contextid, false);

        // Migrate top categories to preserve the same parent category.
        $DB->set_field('question_categories', 'contextid', $contextid, ['contextid' => $oldcontextid]);
    }
}
