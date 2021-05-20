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

defined('MOODLE_INTERNAL') || die();

require_once('../config.php');
require_once($CFG->dirroot.'/course/lib.php');

/**
 * Custom code to be run on installing the plugin.
 * 
 * @package     mod_qbank
 * @author      Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_qbank_install() {
    global $DB;

    // Retrieve question bank informations that are populated.
    $sqlq = "SELECT DISTINCT qc.name, qc.contextid, ctx.contextlevel, ctx.instanceid
            FROM mdl_question q
            JOIN mdl_question_categories qc ON qc.id = q.category
            JOIN mdl_context ctx ON ctx.id = qc.contextid
            ORDER BY ctx.contextlevel;";
    
    $rec = $DB->get_records_sql($sqlq);

    // Retrieve same category id.
    
    // Data from populated questions.
    foreach($rec as $cat) {
        $contextlevel = $cat->contextlevel;
        // Add course here.
        $newcourse = new stdClass();
        switch ($contextlevel) {
            // case "10":
            //     echo "";
            //     break;
            // case "30":
            //     echo "";
            //     break;
            // case "40":
            //     echo "";
            //     break;
            case "50":
                // Retrieving Course category id from course table where id = instanceid.
                $qu = "SELECT category FROM mdl_course WHERE id = ?";
                $catid = $DB->get_records_sql($qu, [$cat->instanceid]);
                $catid = array_values(json_decode(json_encode($catid), true))[0]['category'];
                // Populating object / should add a time() later on
                $newcourse->fullname    = "New course from question bank " . $cat->name;
                $newcourse->category    = +$catid;
                create_course($newcourse);
                break;
            case "70":
                // Retrieving Course id from course_module table where id = instanceid.
                $q = "SELECT course FROM mdl_course_modules WHERE id = ?";
                $courseid = $DB->get_records_sql($q, [$cat->instanceid]);
                $courseid = array_values(json_decode(json_encode($courseid), true))[0]['course'];
                // Retrieving Course category id from course table where id = courseid.
                $qu = "SELECT category FROM mdl_course WHERE id = ?";
                $catid = $DB->get_records_sql($qu, [$courseid]);
                $catid = array_values(json_decode(json_encode($catid), true))[0]['category'];
                // Populating object / should add a time() later on
                $newcourse->fullname    = "New course from question bank " . $cat->name;
                $newcourse->category    = +$catid;
                create_course($newcourse);
                break;
            case "80":
                echo "this is a block";
                break;
        }
    }

    $v = 0;
    return true;
}
