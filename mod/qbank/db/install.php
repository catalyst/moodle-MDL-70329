<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Question bank installation.
 *
 * @package     mod_qbank
 * @copyright   2021 Catalyst IT Australia Pty Ltd
 * @author      Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_qbank\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 * @throws \dml_exception
 * @throws \coding_exception|moodle_exception
 */
function xmldb_qbank_install() {

    // Get data from populated questions.
    $questioncategories = helper::get_categories_populated();
    foreach ($questioncategories as $qcategory) {
        $contextlevel = (int)$qcategory->contextlevel;

        // Initialize variables qbank name and course object that will be used in each case.
        $qbankname = null;
        $course = null;

        switch ($contextlevel) {
            case CONTEXT_SYSTEM:
                $qbankname = substr("Question bank: " . $qcategory->instanceid . '-' . get_string('coresystem'), 0, 254);
                $course = get_course(SITEID);
                break;
            case CONTEXT_COURSECAT:
                $categoryname = helper::get_category_name($qcategory->instanceid);
                $shortname = substr("Question bank: " . $qcategory->instanceid . '-' . $categoryname, 0, 254);
                $course = helper::get_course($shortname, $qcategory->instanceid);
                if (!$course) {
                    // Create a new course for each category with questions.
                    $course = helper::create_category_course($shortname, $qcategory->instanceid);
                }
                $qbankname = $shortname;
                break;
            case CONTEXT_COURSE:
                $course = get_course($qcategory->instanceid);
                $qbankname = substr("Question bank: " . $qcategory->instanceid . '-' . $course->shortname, 0, 254);
                break;
            case CONTEXT_MODULE:
                $cm = helper::get_coursemodule($qcategory->instanceid);
                $course = get_course($cm->course);
                $qbankname = substr("Question bank: " . $qcategory->instanceid . '-' .$course->shortname, 0, 254);
                break;
        }

        // Create the qbank module.
        $qbank = helper::create_qbank_instance($qbankname, $course);
        if ($qbank) {
            helper::migrate_question_categories($qbank, $qcategory->contextid);
        }
    }
}
