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
 * This is an abstract class so it will be skipped by manager when it finds all areas
 *
 * @package    tool_brickfield
 * @copyright  2020 onward: Brickfield Education Labs, www.brickfield.ie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_brickfield\local\areas\core_question;

use core\event\question_created;
use core\event\question_updated;
use tool_brickfield\area_base;
use core_question\admin\tool\brickfield;

/**
 * Base class for various question-related areas.
 *
 * This is an abstract class so it will be skipped by manager when it finds all areas
 *
 * @package    tool_brickfield
 * @copyright  2020 onward: Brickfield Education Labs, www.brickfield.ie
 * @author     2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base extends area_base {

    /**
     * Find recordset of the relevant areas.
     *
     * @param \core\event\base $event
     * @return \moodle_recordset|null
     */
    public function find_relevant_areas(\core\event\base $event): ?\moodle_recordset {

        if (($event instanceof question_created) || ($event instanceof question_updated)) {
            $areas = new brickfield($this->get_type(), $this->get_standard_area_fields_sql(), $this->get_fieldname());
            return $areas->find_relevant_question_areas($this->get_course_and_cat_sql($event), $event->objectid);
        }
        return null;
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
        return $courseareas->find_question_course_areas($courseid, $param);
    }

    /**
     * Return an array of area objects that contain content at the site and system levels only. This would be question content from
     * question categories at the system context only.
     *
     * @return \moodle_recordset
     */
    public function find_system_areas(): ?\moodle_recordset {
        $params = [
            'syscontext' => CONTEXT_SYSTEM,
            'coursecat' => CONTEXT_COURSECAT,
            'coursecat2' => CONTEXT_COURSECAT,
        ];
        $systemareas = new brickfield($this->get_type(), $this->get_standard_area_fields_sql(), $this->get_fieldname());
        return $systemareas->find_system_question_areas($params);
    }

    /**
     * Returns the moodle_url of the page to edit the error.
     *
     * @param \stdClass $componentinfo
     * @return \moodle_url
     */
    public static function get_edit_url(\stdClass $componentinfo): \moodle_url {
        $questionid = $componentinfo->itemid;
        // Question answers are editable on main question page.
        // Hence, use refid for these links.
        if ($componentinfo->tablename == 'question_answers') {
            $questionid = $componentinfo->refid;
        }
        // Default to SITEID if courseid is null, i.e. system or category level questions.
        $thiscourseid = ($componentinfo->courseid !== null) ? $componentinfo->courseid : SITEID;
        return brickfield::get_edit_question_url(['courseid' => $thiscourseid, 'id' => $questionid]);
    }

    /**
     * Determine the course and category id SQL depending on the specific context associated with question data.
     *
     * @param \core\event\base $event
     * @return string
     */
    protected function get_course_and_cat_sql(\core\event\base $event): string {
        $courseid = 'null';
        $catid = 'null';

        if ($record = brickfield::get_course_and_category(CONTEXT_MODULE, $event->objectid)) {
            if ($record->contextlevel == CONTEXT_MODULE) {
                $courseid = $record->courseid;
            } else if ($record->contextlevel == CONTEXT_COURSE) {
                $courseid = $record->instanceid;
            } else if ($record->contextlevel == CONTEXT_COURSECAT) {
                $catid = $record->instanceid;
            } else if ($record->contextlevel == CONTEXT_SYSTEM) {
                $courseid = 1;
            }
        }

        return "
            {$courseid} AS courseid,
            {$catid} AS categoryid,
        ";
    }
}
