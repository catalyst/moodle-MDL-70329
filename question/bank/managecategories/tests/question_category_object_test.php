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

namespace qbank_managecategories;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/editlib.php');

use context;
use context_course;
use context_module;
use moodle_url;
use question_edit_contexts;
use qbank_managecategories\external\add_question_category;
use qbank_managecategories\external\update_question_category;
use stdClass;

/**
 * Unit tests for qbank_managecategories\question_category_object.
 *
 * @package     qbank_managecategories
 * @copyright   2019 the Open University
 * @author      2021, Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \qbank_managecategories\question_category_object
 */
class question_category_object_test extends \advanced_testcase {

    /**
     * @var question_category_object used in the tests.
     */
    protected $qcobject;

    /**
     * @var context a context to use.
     */
    protected $context;

    /**
     * @var stdClass top category in context.
     */
    protected $topcat;

    /**
     * @var stdClass course object.
     */
    protected $course;

    /**
     * @var stdClass quiz object.
     */
    protected $quiz;

    /**
     * @var question_edit_contexts
     */
    private $qcontexts;

    /**
     * @var false|object|stdClass|null
     */
    private $defaultcategoryobj;

    /**
     * @var string
     */
    private $defaultcategory;

    /**
     * @var question_category_object
     */
    private $qcobjectquiz;

    protected function setUp(): void {
        parent::setUp();
        self::setAdminUser();
        $this->resetAfterTest();
        $this->context = context_course::instance(SITEID);
        $contexts = new question_edit_contexts($this->context);
        $this->topcat = question_get_top_category($this->context->id, true);

        // Set up tests in a quiz context.
        $this->course = $this->getDataGenerator()->create_course();
        $this->quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $this->course->id]);
        $this->qcontexts = new question_edit_contexts(context_module::instance($this->quiz->cmid));

        $this->qcobject = new question_category_object(null,
            new moodle_url('/question/bank/managecategories/category.php', ['courseid' => SITEID]),
            $contexts->having_one_edit_tab_cap('categories'), 0, null, 0,
            $contexts->having_cap('moodle/question:add'), $this->quiz->cmid);

        $this->defaultcategoryobj = question_make_default_categories([$this->qcontexts->lowest()]);
        $this->defaultcategory = $this->defaultcategoryobj->id . ',' . $this->defaultcategoryobj->contextid;

        $this->qcobjectquiz = new question_category_object(
            1,
            new moodle_url('/mod/quiz/edit.php', ['cmid' => $this->quiz->cmid]),
            $this->qcontexts->having_one_edit_tab_cap('categories'),
            $this->defaultcategoryobj->id,
            $this->defaultcategory,
            null,
            $this->qcontexts->having_cap('moodle/question:add'),
            $this->quiz->cmid);

    }

    /**
     * Test the question category created event.
     *
     * @covers ::add_category
     */
    public function test_question_category_created() {
        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $idparent = explode(',', $this->defaultcategory)[0];
        $id = add_question_category::execute($idparent, 'Old name', 'Old description', 1, 'frog');
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\question_category_created', $event);
        $this->assertEquals(context_module::instance($this->quiz->cmid), $event->get_context());
        $expected = [$this->course->id, 'quiz', 'addcategory', 'view.php?id=' . $this->quiz->cmid , $id,
                        $this->quiz->cmid];
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    /**
     * Test the question category deleted event.
     *
     * @covers ::delete_category
     */
    public function test_question_category_deleted() {
        // Create the category.
        $idparent = explode(',', $this->defaultcategory)[0];
        $id = add_question_category::execute($idparent, 'Old name', 'Old description', 1, 'frog');

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $this->qcobjectquiz->delete_category($id);
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\question_category_deleted', $event);
        $this->assertEquals(context_module::instance($this->quiz->cmid), $event->get_context());
        $this->assertEquals($id, $event->objectid);
        $this->assertDebuggingNotCalled();
    }

    /**
     * Test the question category updated event.
     *
     * @covers ::update_category
     */
    public function test_question_category_updated() {
        // Create the category.
        $idparent = explode(',', $this->defaultcategory)[0];
        $id = add_question_category::execute($idparent, 'Old name', 'Old description', 1, 'frog');

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        update_question_category::execute($idparent, 'new name', 'new cat info', 1, 'bla', $id);
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\question_category_updated', $event);
        $this->assertEquals(context_module::instance($this->quiz->cmid), $event->get_context());
        $this->assertEquals($id, $event->objectid);
        $this->assertDebuggingNotCalled();
    }

    /**
     * Test the question category viewed event.
     * There is no external API for viewing the category, so the unit test will simply
     * create and trigger the event and ensure data is returned as expected.
     *
     * @covers ::add_category
     */
    public function test_question_category_viewed() {
        // Create the category.
        $idparent = explode(',', $this->defaultcategory)[0];
        $id = add_question_category::execute($idparent, 'Old name', 'Old description', 1, 'frog');

        // Log the view of this category.
        $category = new stdClass();
        $category->id = $id;
        $context = context_module::instance($this->quiz->cmid);
        $event = \core\event\question_category_viewed::create_from_question_category_instance($category, $context);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\question_category_viewed', $event);
        $this->assertEquals(context_module::instance($this->quiz->cmid), $event->get_context());
        $this->assertEquals($id, $event->objectid);
        $this->assertDebuggingNotCalled();

    }
}
