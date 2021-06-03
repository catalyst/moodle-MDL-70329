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
 * Adhoc task handling the question banks migration to the new qbank modules created.
 *
 * @package    core
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_qbank\task;

use core\task\adhoc_task;
use mod_qbank\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Class handling the question banks migration to the new qbank modules created.
 *
 * @package    mod_qbank
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class migrate_question_banks_task extends adhoc_task {

    /**
     * Run the task to migrate question banks:
     *
     * 1. Create the course for category contexts.
     * 2. Create the qbank module.
     * 3. Migrate the question categories to new context id (mod_qbank).
     */
    public function execute() {

        // Validate if the required data is set.
        $contextlevel = null;
        if (isset($this->get_custom_data()->contextlevel)) {
            $contextlevel = $this->get_custom_data()->contextlevel;
        }

        $course = null;
        if (isset($this->get_custom_data()->course)) {
            $course = $this->get_custom_data()->course;
        }

        $qbankname = null;
        if (isset($this->get_custom_data()->qbankname)) {
            $qbankname = $this->get_custom_data()->qbankname;
        }

        $qcategoryinstanceid = null;
        if (isset($this->get_custom_data()->qcategoryinstanceid)) {
            $qcategoryinstanceid = $this->get_custom_data()->qcategoryinstanceid;
        }

        $contextid = null;
        if (isset($this->get_custom_data()->contextid)) {
            $contextid = $this->get_custom_data()->contextid;
        }

        // Create a new course for each category with questions.
        if (!$course && $contextlevel === CONTEXT_COURSECAT) {
            $course = helper::create_category_course($qbankname, $qcategoryinstanceid);
        }

        // Create the qbank module and change the context id to point to the new mod_bank.
        if ($course && $qbankname && $contextid) {
            $qbank = helper::create_qbank_instance($qbankname, $course);
            helper::migrate_question_categories($qbank, $contextid);
        }
    }
}
