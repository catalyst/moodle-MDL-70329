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
 * Question category object tests.
 *
 * @package     qbank_managecategories
 * @copyright   2019 the Open University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_managecategories;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/editlib.php');

use context;
use context_course;
use context_module;
use moodle_url;
use core_question\lib\question_edit_contexts;
use stdClass;

/**
 * Unit tests for qbank_managecategories\question_category_object.
 *
 * @package     qbank_managecategories
 * @copyright   2019 the Open University
 * @author      2021, Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        $this->qcobject = new question_category_object(null,
            new moodle_url('/question/bank/managecategories/category.php', ['courseid' => SITEID]),
            $contexts->having_one_edit_tab_cap('categories'), 0, null, 0,
            $contexts->having_cap('moodle/question:add'));

        // Set up tests in a quiz context.
        $this->course = $this->getDataGenerator()->create_course();
        $this->quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $this->course->id]);
        $this->qcontexts = new question_edit_contexts(context_module::instance($this->quiz->cmid));

        $this->defaultcategoryobj = question_make_default_categories([$this->qcontexts->lowest()]);
        $this->defaultcategory = $this->defaultcategoryobj->id . ',' . $this->defaultcategoryobj->contextid;

        $this->qcobjectquiz = new question_category_object(
            1,
            new moodle_url('/mod/quiz/edit.php', ['cmid' => $this->quiz->cmid]),
            $this->qcontexts->having_one_edit_tab_cap('categories'),
            $this->defaultcategoryobj->id,
            $this->defaultcategory,
            null,
            $this->qcontexts->having_cap('moodle/question:add'));

    }

    /**
     * Test creating a category.
     */
    public function test_add_category_no_idnumber() {
        global $DB;

        $id = $this->qcobject->add_category($this->topcat->id . ',' . $this->topcat->contextid,
            'New category', '', true, FORMAT_HTML, ''); // No idnumber passed as '' to match form data.

        $newcat = $DB->get_record('question_categories', ['id' => $id], '*', MUST_EXIST);
        $this->assertSame('New category', $newcat->name);
        $this->assertNull($newcat->idnumber);
    }

    /**
     * Test creating a category with a tricky idnumber.
     */
    public function test_add_category_set_idnumber_0() {
        global $DB;

        $id = $this->qcobject->add_category($this->topcat->id . ',' . $this->topcat->contextid,
            'New category', '', true, FORMAT_HTML, '0');

        $newcat = $DB->get_record('question_categories', ['id' => $id], '*', MUST_EXIST);
        $this->assertSame('New category', $newcat->name);
        $this->assertSame('0', $newcat->idnumber);
    }

    /**
     * Trying to add a category with duplicate idnumber blanks it.
     * (In reality, this would probably get caught by form validation.)
     */
    public function test_add_category_try_to_set_duplicate_idnumber() {
        global $DB;

        $this->qcobject->add_category($this->topcat->id . ',' . $this->topcat->contextid,
            'Existing category', '', true, FORMAT_HTML, 'frog');

        $id = $this->qcobject->add_category($this->topcat->id . ',' . $this->topcat->contextid,
            'New category', '', true, FORMAT_HTML, 'frog');

        $newcat = $DB->get_record('question_categories', ['id' => $id], '*', MUST_EXIST);
        $this->assertSame('New category', $newcat->name);
        $this->assertNull($newcat->idnumber);
    }

    /**
     * Test updating a category.
     */
    public function test_update_category() {
        global $DB;

        $id = $this->qcobject->add_category($this->topcat->id . ',' . $this->topcat->contextid,
            'Old name', 'Description', true, FORMAT_HTML, 'frog');

        $this->qcobject->update_category($id, $this->topcat->id . ',' . $this->topcat->contextid,
            'New name', 'New description', FORMAT_HTML, '0', false);

        $newcat = $DB->get_record('question_categories', ['id' => $id], '*', MUST_EXIST);
        $this->assertSame('New name', $newcat->name);
        $this->assertSame('0', $newcat->idnumber);
    }

    /**
     * Test updating a category to remove the idnumber.
     */
    public function test_update_category_removing_idnumber() {
        global $DB;

        $id = $this->qcobject->add_category($this->topcat->id . ',' . $this->topcat->contextid,
            'Old name', 'Description', true, FORMAT_HTML, 'frog');

        $this->qcobject->update_category($id, $this->topcat->id . ',' . $this->topcat->contextid,
            'New name', 'New description', FORMAT_HTML, '', false);

        $newcat = $DB->get_record('question_categories', ['id' => $id], '*', MUST_EXIST);
        $this->assertSame('New name', $newcat->name);
        $this->assertNull($newcat->idnumber);
    }

    /**
     * Test updating a category without changing the idnumber.
     */
    public function test_update_category_dont_change_idnumber() {
        global $DB;

        $id = $this->qcobject->add_category($this->topcat->id . ',' . $this->topcat->contextid,
            'Old name', 'Description', true, FORMAT_HTML, 'frog');

        $this->qcobject->update_category($id, $this->topcat->id . ',' . $this->topcat->contextid,
            'New name', 'New description', FORMAT_HTML, 'frog', false);

        $newcat = $DB->get_record('question_categories', ['id' => $id], '*', MUST_EXIST);
        $this->assertSame('New name', $newcat->name);
        $this->assertSame('frog', $newcat->idnumber);
    }

    /**
     * Trying to update a category so its idnumber duplicates idnumber blanks it.
     * (In reality, this would probably get caught by form validation.)
     */
    public function test_update_category_try_to_set_duplicate_idnumber() {
        global $DB;

        $this->qcobject->add_category($this->topcat->id . ',' . $this->topcat->contextid,
            'Existing category', '', true, FORMAT_HTML, 'toad');
        $id = $this->qcobject->add_category($this->topcat->id . ',' . $this->topcat->contextid,
            'old name', '', true, FORMAT_HTML, 'frog');

        $this->qcobject->update_category($id, $this->topcat->id . ',' . $this->topcat->contextid,
            'New name', '', FORMAT_HTML, 'toad', false);

        $newcat = $DB->get_record('question_categories', ['id' => $id], '*', MUST_EXIST);
        $this->assertSame('New name', $newcat->name);
        $this->assertNull($newcat->idnumber);
    }

    /**
     * Test the question category created event.
     */
    public function test_question_category_created() {
        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $categoryid = $this->qcobjectquiz->add_category($this->defaultcategory, 'newcategory', '', true);
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\question_category_created', $event);
        $this->assertEquals(context_module::instance($this->quiz->cmid), $event->get_context());
        $expected = [$this->course->id, 'quiz', 'addcategory', 'view.php?id=' . $this->quiz->cmid , $categoryid, $this->quiz->cmid];
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    /**
     * Test the question category deleted event.
     */
    public function test_question_category_deleted() {
        // Create the category.
        $categoryid = $this->qcobjectquiz->add_category($this->defaultcategory, 'newcategory', '', true);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $this->qcobjectquiz->delete_category($categoryid);
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\question_category_deleted', $event);
        $this->assertEquals(context_module::instance($this->quiz->cmid), $event->get_context());
        $this->assertEquals($categoryid, $event->objectid);
        $this->assertDebuggingNotCalled();
    }

    /**
     * Test the question category updated event.
     */
    public function test_question_category_updated() {
        // Create the category.
        $categoryid = $this->qcobjectquiz->add_category($this->defaultcategory, 'newcategory', '', true);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();
        $this->qcobjectquiz->update_category($categoryid, $this->defaultcategory, 'updatedcategory', '', FORMAT_HTML, '', false);
        $events = $sink->get_events();
        $event = reset($events);

        // Check that the event data is valid.
        $this->assertInstanceOf('\core\event\question_category_updated', $event);
        $this->assertEquals(context_module::instance($this->quiz->cmid), $event->get_context());
        $this->assertEquals($categoryid, $event->objectid);
        $this->assertDebuggingNotCalled();
    }

    /**
     * Test the question category viewed event.
     * There is no external API for viewing the category, so the unit test will simply
     * create and trigger the event and ensure data is returned as expected.
     */
    public function test_question_category_viewed() {
        // Create the category.
        $categoryid = $this->qcobjectquiz->add_category($this->defaultcategory, 'newcategory', '', true);

        // Log the view of this category.
        $category = new stdClass();
        $category->id = $categoryid;
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
        $this->assertEquals($categoryid, $event->objectid);
        $this->assertDebuggingNotCalled();

    }
}