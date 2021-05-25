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
 */
function xmldb_qbank_install() {

    // Get data from populated questions.
    $questioncategories = helper::get_categories_populated();
    foreach ($questioncategories as $qcategory) {
        $contextlevel = (int)$qcategory->contextlevel;

        if ($contextlevel === CONTEXT_COURSECAT) {
            $categoryname = helper::get_category_name($qcategory->instanceid);
            $shortname = substr("Question bank: " . $qcategory->instanceid . '-' . $categoryname, 0, 254);
            $course = helper::get_course($shortname);
            if (!$course) {
                // Create a new course for each category with questions.
                $course = helper::create_category_course($shortname, $qcategory->instanceid);
            }
            // TODO: Create the mod_activity here using this course: $course.
        }
    }
}
