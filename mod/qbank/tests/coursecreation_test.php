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
 * Testing method course_exists in helper class.
 *
 * @package    mod_qbank
 * @category   test
 * @copyright  2021 Catalyst IT Canada Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_qbank;

defined('MOODLE_INTERNAL') || die();

class coursecreation_test extends \advanced_testcase {

    /**
     *  Test get_categories_populated method in helper class.
     */
    public function test_populated_only(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $category1 = $generator->create_question_category(['name' => 'This category is populated']);
        $category2 = $generator->create_question_category(['name' => 'This category is also populated']);
        $category3 = $generator->create_question_category(['name' => 'This category is not populated']);

        $generator->create_question('description', null, ['category' => $category1->id]);
        $generator->create_question('description', null, ['category' => $category2->id]);

        // Test that after executing get_categories_populated we get only populated categories.
        $retrievedcat = helper::get_categories_populated();
        $this->assertCount(2, $retrievedcat);
    }

    /**
     *  Test course_exists and create_category_course methods in helper class.
     */
    public function test_course_creation(): void {
        $this->resetAfterTest();
        $coursename1 = substr("qbank_" . CONTEXT_COURSECAT . "This is some course", 0, 254);
        $coursename2 = substr("qbank_" . CONTEXT_COURSECAT . "This course does not exist yet", 0, 254);
        $course1 = $this->getDataGenerator()->create_course(['fullname' => $coursename1]);
        $category1 = $this->getDataGenerator()->create_category();

        // Test that course_exists function returns true for courses that already exist.
        $this->assertTrue(helper::course_exists($course1->fullname));

        // Test that course does not exist before being created.
        $course2 = null;
        $this->assertFalse(helper::course_exists($coursename2));

        // Test that course exists after being created with create_category_course function.
        if (!helper::course_exists($coursename2)) {
            $course2 = helper::create_category_course($coursename2, $category1->id);
        }
        $this->assertTrue(helper::course_exists($course2->fullname));
    }
}
