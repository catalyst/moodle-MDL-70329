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
 * Testing method question categories actions in a module and course context.
 *
 * @package    core_question
 * @category   test
 * @copyright  2021 Catalyst IT Australia Ltd
 * @author     Guillermo Gomez Arias <guillermogomez@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_question;

use context_module;
use core_course_category;
use core_question\local\bank\question_edit_contexts;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class question_categories_test  extends \advanced_testcase {

    /**
     * @var stdClass Course used in the tests.
     */
    protected $course;

    /**
     * @var stdClass mod_qbank used in the tests.
     */
    protected $modqbank;

    /**
     * @var context_module used in the tests.
     */
    protected $modcontext;

    /**
     * Setup test data
     *
     */
    protected function setUp(): void {
        parent::setUp();
        self::setAdminUser();
        $this->resetAfterTest();

        // Create a course and question bank activity.
        $this->course = $this->getDataGenerator()->create_course();
        $this->modqbank = $this->getDataGenerator()->create_module('qbank', ['course' => $this->course->id]);
        $this->modcontext = context_module::instance($this->modqbank->cmid);
        $contexts = new question_edit_contexts($this->modcontext);
        question_make_default_categories($contexts->all());
    }

    /**
     * Test action in a module context.
     */
    public function test_question_categories_actions_module_context() {
        global $DB, $USER;

        // Test that the question category has been created.
        $qcategory = $DB->count_records_select('question_categories', 'contextid = ? AND parent <> 0',
            [$this->modcontext->id]);
        $this->assertEquals(1, $qcategory);

        // Test that the question category has been removed.
        $qbankcm = get_coursemodule_from_id('qbank', $this->modqbank->cmid);
        // Execute the task.
        $removaltask = new \core_course\task\course_delete_modules();
        $data = [
            'cms' => [$qbankcm],
            'userid' => $USER->id,
            'realuserid' => $USER->id
        ];
        $removaltask->set_custom_data($data);
        $removaltask->execute();
        $qcategory = $DB->count_records_select('question_categories', 'contextid = ? AND parent <> 0',
            [$this->modcontext->id]);
        $this->assertEquals(0, $qcategory);
    }

    /**
     * Test action in a course context.
     */
    public function test_question_categories_actions_course_context() {
        global $DB;

        // Test that the question category has been created.
        $qcategory = $DB->count_records_select('question_categories', 'contextid = ? AND parent <> 0',
            [$this->modcontext->id]);
        $this->assertEquals(1, $qcategory);

        // Test that the question category has been removed.
        delete_course($this->course, false);
        $qcategory = $DB->count_records_select('question_categories', 'contextid = ? AND parent <> 0',
            [$this->modcontext->id]);
        $this->assertEquals(0, $qcategory);
    }

    /**
     * Test action in a category context.
     */
    public function test_question_categories_actions_category_context() {
        global $DB;

        // Test that the question category has been created.
        $qcategory = $DB->count_records_select('question_categories', 'contextid = ? AND parent <> 0',
            [$this->modcontext->id]);
        $this->assertEquals(1, $qcategory);

        // Test that the question category has been removed.
        $category = core_course_category::get($this->course->category);
        $category->delete_full(true);
        $qcategory = $DB->count_records_select('question_categories', 'contextid = ? AND parent <> 0',
            [$this->modcontext->id]);
        $this->assertEquals(0, $qcategory);
    }
}
