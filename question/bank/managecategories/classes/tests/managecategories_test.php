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
 * Manage categories tests.
 *
 * @package core_question
 * @copyright 2019 the Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_managecategories;

global $CFG;
require_once($CFG->dirroot . '/question/editlib.php');

use context_module;
use moodle_url;
use question_edit_contexts;
use stdClass;

/**
 * Unit tests for qbank_managecategories.
 *
 * @package    qbank_managecategories
 * @copyright  2014 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class managecategories_test extends \advanced_testcase {

    /**
     * Tests set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test the question category created event.
     */
    public function test_question_category_created() {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', array('course' => $course->id));

        $contexts = new question_edit_contexts(context_module::instance($quiz->cmid));

        $defaultcategoryobj = question_make_default_categories(array($contexts->lowest()));
        $defaultcategory = $defaultcategoryobj->id . ',' . $defaultcategoryobj->contextid;

        $qcobject = new question_category_object(
            1,
            new moodle_url('/mod/quiz/edit.php', array('cmid' => $quiz->cmid)),
            $contexts->having_one_edit_tab_cap('categories'),
            $defaultcategoryobj->id,
            $defaultcategory,
            null,
            $contexts->having_cap('moodle/question:add'));

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $categoryid = $qcobject->add_category($defaultcategory, 'newcategory', '', true);
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\question_category_created', $event);
        $this->assertEquals(context_module::instance($quiz->cmid), $event->get_context());
        $expected = array($course->id, 'quiz', 'addcategory', 'view.php?id=' . $quiz->cmid , $categoryid, $quiz->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    /**
     * Test the question category deleted event.
     */
    public function test_question_category_deleted() {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);

        $contexts = new question_edit_contexts(context_module::instance($quiz->cmid));

        $defaultcategoryobj = question_make_default_categories([$contexts->lowest()]);
        $defaultcategory = $defaultcategoryobj->id . ',' . $defaultcategoryobj->contextid;

        $qcobject = new question_category_object(
            1,
            new moodle_url('/mod/quiz/edit.php', ['cmid' => $quiz->cmid]),
            $contexts->having_one_edit_tab_cap('categories'),
            $defaultcategoryobj->id,
            $defaultcategory,
            null,
            $contexts->having_cap('moodle/question:add'));

        // Create the category.
        $categoryid = $qcobject->add_category($defaultcategory, 'newcategory', '', true);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $qcobject->delete_category($categoryid);
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\question_category_deleted', $event);
        $this->assertEquals(context_module::instance($quiz->cmid), $event->get_context());
        $this->assertEquals($categoryid, $event->objectid);
        $this->assertDebuggingNotCalled();
    }

    /**
     * Test the question category updated event.
     */
    public function test_question_category_updated() {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);

        $contexts = new question_edit_contexts(context_module::instance($quiz->cmid));

        $defaultcategoryobj = question_make_default_categories([$contexts->lowest()]);
        $defaultcategory = $defaultcategoryobj->id . ',' . $defaultcategoryobj->contextid;

        $qcobject = new question_category_object(
            1,
            new moodle_url('/mod/quiz/edit.php', ['cmid' => $quiz->cmid]),
            $contexts->having_one_edit_tab_cap('categories'),
            $defaultcategoryobj->id,
            $defaultcategory,
            null,
            $contexts->having_cap('moodle/question:add'));

        // Create the category.
        $categoryid = $qcobject->add_category($defaultcategory, 'newcategory', '', true);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $qcobject->update_category($categoryid, $defaultcategory, 'updatedcategory', '', FORMAT_HTML, '', false);
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\question_category_updated', $event);
        $this->assertEquals(context_module::instance($quiz->cmid), $event->get_context());
        $this->assertEquals($categoryid, $event->objectid);
        $this->assertDebuggingNotCalled();
    }

    /**
     * Test the question category viewed event.
     * There is no external API for viewing the category, so the unit test will simply
     * create and trigger the event and ensure data is returned as expected.
     */
    public function test_question_category_viewed() {

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);

        $contexts = new question_edit_contexts(context_module::instance($quiz->cmid));

        $defaultcategoryobj = question_make_default_categories([$contexts->lowest()]);
        $defaultcategory = $defaultcategoryobj->id . ',' . $defaultcategoryobj->contextid;

        $qcobject = new question_category_object(
            1,
            new moodle_url('/mod/quiz/edit.php', ['cmid' => $quiz->cmid]),
            $contexts->having_one_edit_tab_cap('categories'),
            $defaultcategoryobj->id,
            $defaultcategory,
            null,
            $contexts->having_cap('moodle/question:add'));

        // Create the category.
        $categoryid = $qcobject->add_category($defaultcategory, 'newcategory', '', true);

        // Log the view of this category.
        $category = new stdClass();
        $category->id = $categoryid;
        $context = context_module::instance($quiz->cmid);
        $event = \core\event\question_category_viewed::create_from_question_category_instance($category, $context);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\question_category_viewed', $event);
        $this->assertEquals(context_module::instance($quiz->cmid), $event->get_context());
        $this->assertEquals($categoryid, $event->objectid);
        $this->assertDebuggingNotCalled();

    }
}
