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

        $course = null; // Initialize course object that will be used in each case.
        $context = null; // Initialize context description.
        $shortdescription = null; // Initialize mod_qbank short description.

        // Get the data required to migrate question_categories to new mod_qbank.
        switch ($contextlevel) {
            case CONTEXT_SYSTEM:
                $course = get_course(SITEID);
                $context = get_string('coresystem');
                $shortdescription = $context;
                break;
            case CONTEXT_COURSECAT:
                $context = get_string('category');
                $shortdescription = helper::get_category_name($qcategory->instanceid);

                // Create a new course for each category with questions.
                $shortname = substr(get_string('coursenamebydefault', 'mod_qbank',
                    [
                        'context' => $context,
                        'shortdescription' => $shortdescription,
                    ]
                ), 0, 254);
                $course = helper::get_course($shortname, $qcategory->instanceid);
                if (!$course) {
                    $course = helper::create_category_course($shortname, $qcategory->instanceid);
                }
                break;
            case CONTEXT_COURSE:
                $course = get_course($qcategory->instanceid);
                $context = get_string('course');
                $shortdescription = $course->shortname;
                break;
            case CONTEXT_MODULE:
                $cm = helper::get_coursemodule($qcategory->instanceid);
                $course = get_course($cm->course);
                $context = get_string('pluginname', 'mod_quiz');
                $shortdescription = $cm->name;
                break;
        }

        // Create the qbank module.
        $namedata = ['context' => $context, 'instanceid' => $qcategory->instanceid,'shortdescription' => $shortdescription];
        $qbankname = substr(get_string('modulenamebydefault', 'mod_qbank', $namedata), 0, 254);
        $qbank = helper::create_qbank_instance($qbankname, $course);

        // Migrate the question_categories data to new mod_qbank.
        if ($qbank) {
            helper::migrate_question_categories($qbank, $qcategory->contextid);
        }
    }
    $questioncategories->close();
}
