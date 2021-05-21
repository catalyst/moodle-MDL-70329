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
error_reporting(-1);
ini_set('display_errors', true);
require_once($CFG->dirroot . '/mod/qbank/classes/createcourse_helper.php');

use mod_qbank\createcourse_helper;

/**
 * Custom code to be run on installing the plugin.
 * 
 * @package     mod_qbank
 * @author      Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_qbank_install() {

    // Data from populated questions.
    $rec = createcourse_helper::get_populated();
    
    foreach($rec as $cat) {
        $contextlevel = $cat->contextlevel;
        // Add course here.
        $newcourse = new stdClass();
        switch ($contextlevel) {
            // case "10":
            //     echo "";
            //     break;
            // case "40":
            //     echo "";
            //     break;
            case "50":
                // Retrieving Course category id from course table where id = instanceid.
                $catid = createcourse_helper::get_coursecatid($cat->instanceid);
                $newcrs = createcourse_helper::populate_course($newcourse, $cat, $catid);
                create_course($newcrs);
                break;
            case "70":
                // Retrieving Course id from course_module table where id = instanceid.
                $courseid = createcourse_helper::get_courseid($cat->instanceid);
                // Retrieving Course category id from course table where id = courseid.
                $catid = createcourse_helper::get_coursecatid($courseid);
                $newcrs = createcourse_helper::populate_course($newcourse, $cat, $catid);
                create_course($newcourse);
                break;
            case "80":
                echo "this is a block";
                break;
        }
    }
    return true;
}
