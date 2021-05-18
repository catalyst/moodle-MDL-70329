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
 * @author      Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_qbank_install() {
    global $DB;

    // Retrieve names of question banks that are populated
    $sqlq = 'SELECT name FROM mdl_question_categories qc WHERE qc.infoformat <> 0';
    $rec = $DB->get_records_sql($sqlq);
    $catname = $rec[0]->name;

    $newcourses = array();
    foreach($catname as $cat) {
        $newcourses[] = $cat . ' course';
    }

    $v = 0;
    return true;
}
