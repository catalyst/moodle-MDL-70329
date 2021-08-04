<?php
// This file is part of the Query submission plugin
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
 * Base for various question-related areas.
 *
 * This is an abstract class so it will be skipped by manager when it finds all areas.
 *
 * @package    tool_brickfield
 * @copyright  2020 onward: Brickfield Education Labs, www.brickfield.ie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_brickfield\local\areas\core_question;

use core\event\question_created;
use core\event\question_updated;
use core_question\admin\tool\brickfield;

/**
 * Base class for various question-related areas.
 *
 * This is an abstract class so it will be skipped by manager when it finds all areas.
 *
 * @package    tool_brickfield
 * @copyright  2020 onward: Brickfield Education Labs, www.brickfield.ie
 * @author     2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class answerbase extends base {

    /**
     * Get table name reference.
     *
     * @return string
     */
    public function get_ref_tablename(): string {
        return 'question';
    }

    /**
     * Find recordset of the relevant areas.
     *
     * @param \core\event\base $event
     * @return \moodle_recordset|null
     */
    public function find_relevant_areas(\core\event\base $event): ?\moodle_recordset {
        if (($event instanceof question_created) || ($event instanceof question_updated)) {
            $areas = new brickfield($this->get_type(), $this->get_standard_area_fields_sql(), $this->get_fieldname());
            return $areas->find_relevant_question_answer_areas($this->get_course_and_cat_sql($event),
                                                                $event->objectid, $this->get_reftable_field_sql());
        }
        return null;
    }

    /**
     * Return an array of area objects that contain content at the site and system levels only. This would be question content from
     * question categories at the system context, or course category context.
     *
     * @return mixed
     */
    public function find_system_areas(): ?\moodle_recordset {
        $params = [
            'syscontext' => CONTEXT_SYSTEM,
            'coursecat' => CONTEXT_COURSECAT,
            'coursecat2' => CONTEXT_COURSECAT,
        ];
        $systemareas = new brickfield($this->get_type(), $this->get_standard_area_fields_sql(), $this->get_fieldname());
        return $systemareas->find_system_question_answer_areas($params, $this->get_reftable_field_sql());
    }

    /**
     * Find recordset of the course areas.
     *
     * @param int $courseid
     * @return \moodle_recordset
     */
    public function find_course_areas(int $courseid): ?\moodle_recordset {
        global $DB;

        $coursecontext = \context_course::instance($courseid);
        $param = [
            'ctxcourse' => CONTEXT_COURSE,
            'courseid' => $courseid,
            'module' => CONTEXT_MODULE,
            'coursecontextpath' => $DB->sql_like_escape($coursecontext->path) . '/%',
        ];
        $courseareas = new brickfield($this->get_type(), $this->get_standard_area_fields_sql(), $this->get_fieldname());
        return $courseareas->find_question_answer_area($courseid, $param, $this->get_reftable_field_sql());
    }
}
