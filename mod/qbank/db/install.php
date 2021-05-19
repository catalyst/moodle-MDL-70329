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
    $sqlq = 'SELECT DISTINCT q.category, qc.name, qc.contextid, ctx.contextlevel
            FROM mdl_question q
            JOIN mdl_question_categories qc ON qc.id = q.category
            JOIN mdl_context ctx ON ctx.id = qc.contextid
            ORDER BY ctx.contextlevel;';
    
    $rec = $DB->get_records_sql($sqlq);

    // Data from populated questions.
    $populatedcat = array();
    foreach($rec as $cat) {
        $populatedcat[] = $cat->category;
        
        // Add course here.
        $newcourse = new stdClass();
        $newcourse->category    = $cat->category;
        $newcourse->fullname    = $cat->name;
        $newcourse->shortname   = $cat->name;

        // Same category id is needed to create course.
        create_course($newcourse);
        //$DB->insert_record('course', $newcourse);
    }


    return true;
}
