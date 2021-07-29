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

defined('MOODLE_INTERNAL') || die();
use core_question\local\bank\helper;
use core_question\local\bank\view;

global $CFG;
require_once($CFG->dirroot . '/question/tests/fixtures/testable_core_question_column.php');
require_once($CFG->dirroot . '/question/classes/external.php');

/**
 * Question bank settings page class.
 *
 * @package    core_question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class columnsortorder_test extends advanced_testcase {

    /**
     * Test function get_question_list_columns in helper class, that proper data is returned.
     *
     */
    public function test_getcolumn_function() {
        $questionlistcolumns = helper::get_question_list_columns();
        $this->assertIsArray($questionlistcolumns);
        foreach ($questionlistcolumns as $columnnobject) {
            $this->assertObjectHasAttribute('class', $columnnobject);
            $this->assertObjectHasAttribute('name', $columnnobject);
            $this->assertObjectHasAttribute('colname', $columnnobject);
        }
    }

    /**
     * Test that external call core_question_external::set_columnbank_order($oldorder) sets proper
     * data in config_plugins table.
     *
     */
    public function test_columnorder_external() {
        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $questionlistcolumns = helper::get_question_list_columns();
        $columnclasses = [];
        foreach ($questionlistcolumns as $columnnobject) {
            $classelements = explode('\\', $columnnobject->class);
            $columnclasses[] = end($classelements);
        }
        shuffle($columnclasses);
        $jsontoappend = json_encode($columnclasses);
        core_question_external::set_columnbank_order($jsontoappend);

        $currentconfig = get_config('question', 'qbanksortorder');
        $this->assertEquals($jsontoappend, $currentconfig);
    }

    /**
     * Test function proper order is set in the question bank view.
     *
     */
    public function test_view_ordering() {
        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $course = $this->getDataGenerator()->create_course();
        // Creates question bank view.
        $questionbank = new view(
            new question_edit_contexts(context_course::instance($course->id)),
            new moodle_url('/'),
            $course
        );

        // Get current view columns.
        $name = [];
        foreach ($questionbank->visiblecolumns as $columnn) {
            $classname = new ReflectionClass(get_class($columnn));
            $name[] = $classname->getShortName();
        }

        shuffle($name);
        $neworder = implode(',', $questionbank->get_neworder($name));
        core_question_external::set_columnbank_order($neworder);
        $currentorder = get_config('question', 'qbanksortorder');

        $this->assertEquals($neworder, $currentorder);
    }
}
