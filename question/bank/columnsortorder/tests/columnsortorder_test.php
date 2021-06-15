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

namespace qbank_columnsortorder;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use qbank_columnsortorder\external\set_columnbank_order;
use qbank_columnsortorder\column_sort_order;
use qbank_columnsortorder\column_sort_order_manager;
use core_question\local\bank\view;
use context_course;
use moodle_url;
use question_edit_contexts;
use ReflectionClass;

global $CFG;
require_once($CFG->dirroot . '/question/tests/fixtures/testable_core_question_column.php');
require_once($CFG->dirroot . '/question/classes/external.php');

/**
 * Test class for columnsortorder feature.
 *
 * @package    qbank_columnsortorder
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
        $columnsortorder = new column_sort_order();
        $questionlistcolumns = $columnsortorder->get_question_list_columns();
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

        $columnsortorder = new column_sort_order();
        $questionlistcolumns = $columnsortorder->get_question_list_columns();
        $columnclasses = [];
        foreach ($questionlistcolumns as $columnnobject) {
            $columnclasses[] = $columnnobject->class;
        }
        shuffle($columnclasses);
        $columnclasses = implode(',', $columnclasses);
        $currentconfig = (array)get_config('column_sortorder');
        $this->assertEmpty($currentconfig);
        set_columnbank_order::execute($columnclasses);

        $currentconfig = (array)get_config('column_sortorder');
        asort($currentconfig);
        $currentconfig = array_flip($currentconfig);
        $columnclasses = explode(',', $columnclasses);
        $this->assertSame($columnclasses, $currentconfig);
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
        foreach ($questionbank->get_visiblecolumns() as $columnn) {
            $classname = new ReflectionClass(get_class($columnn));
            $name[] = $classname->getShortName();
        }
        $columnorder = new column_sort_order();
        $neworder = implode(',', $columnorder->sort_columns($name));
        set_columnbank_order::execute($neworder);
        $currentconfig = get_config('column_sortorder');
        $currentconfig = (array)$currentconfig;
        asort($currentconfig);
        $currentconfig = array_flip($currentconfig);
        $neworder = explode(',', $neworder);
        $this->assertSame($neworder, $currentconfig);
    }

    /**
     * Test function that clean disabled/uninstalled columns.
     *
     */
    public function test_remove_unused_column_from_db() {
        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $columnindb = (array)get_config('column_sortorder');
        $this->assertEmpty($columnindb);
        $columnsortorder = new column_sort_order();
        $questionlistcolumns = $columnsortorder->get_question_list_columns();
        $columnclasses = [];
        foreach ($questionlistcolumns as $columnnobject) {
            $columnclasses[] = $columnnobject->class;
        }
        shuffle($columnclasses);
        $columnclasses = implode(',', $columnclasses);
        set_columnbank_order::execute($columnclasses);
        $columnindb = (array)get_config('column_sortorder');
        $this->assertNotEmpty($columnindb);
        $pluginclassname = reset($questionlistcolumns)->class;
        $plugintoremove = explode('\\', reset($questionlistcolumns)->class)[0];
        $this->assertArrayHasKey($pluginclassname, $columnindb);
        column_sort_order_manager::remove_unused_column_from_db($plugintoremove);
        $columnupdated = (array)get_config('column_sortorder');
        array_flip($columnupdated);
        $this->assertArrayNotHasKey($pluginclassname, $columnupdated);
    }
}
