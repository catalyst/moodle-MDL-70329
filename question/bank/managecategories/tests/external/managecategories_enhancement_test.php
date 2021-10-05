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
 * Unit tests for managecategories enhancement.
 *
 * @package    qbank_managecategories
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_managecategories;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use qbank_managecategories\external\update_category_order;
use qbank_managecategories\external\add_question_category;
use qbank_managecategories\external\update_question_category;

/**
 * Unit tests for qbank_managecategories enhancememt component.
 *
 * @package    qbank_managecategories
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class managecategories_enhancement_test extends advanced_testcase {
    /**
     * @var core_question_generator Generator for core_question.
     */
    private $generator;

    /**
     * @var object Question category.
     */
    private $category;

    /**
     * @var string Concatenated string containing idparent and contextid, ie: "6,21".
     */
    private $idparent;

    /**
     * @var string Category name.
     */
    private $name;

    /**
     * @var string Category description.
     */
    private $categoryinfo;

    /**
     * @var string Category idnumber.
     */
    private $idnumber;

    /**
     * Setup function. Will set an admin user, create a question category.
     */
    protected function setUp(): void {
        parent::setUp();
        self::setAdminUser();
        $this->resetAfterTest();

        $this->generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $this->category = $this->generator->create_question_category();
        $this->idparent = $this->category->parent . ',' . $this->category->contextid;
        $this->name = 'Dummy name';
        $this->categoryinfo = 'Dummy category info';
        $this->idnumber = 'Dummy id num';
    }
    /**
     * Tests setting a new category order.
     *
     */
    public function test_update_category_order() {
        global $DB;

        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $this->getDataGenerator()->create_course();

        $category1 = $generator->create_question_category(['contextid' => 1200]);
        $category2 = $generator->create_question_category(['contextid' => 1201]);
        $category3 = $generator->create_question_category(['contextid' => 1202]);
        $category4 = $generator->create_question_category(['contextid' => 1203]);
        $category5 = $generator->create_question_category(['contextid' => 1204]);

        $categories = $DB->get_records('question_categories');

        $this->assertNotEmpty($categories);
        $ordertoset = [];
        foreach ($categories as $category) {
            $ordertoset[] = [$category->id . ',' . $category->contextid];
        }
        $ordertoset = json_encode($ordertoset);
        update_category_order::execute($ordertoset, $category2->id, $category4->contextid, $category2->contextid);

        $newcategories = $DB->get_records('question_categories');
        $neworder = [];
        foreach ($newcategories as $category) {
            if ((int)$category->id === $category2->id) {
                $this->assertEquals($category->contextid, $category4->contextid);
            }
            $neworder[] = [$category->id . ',' . $category->contextid];
        }
        $ordertoset = json_decode($ordertoset);
        $this->assertNotSame($ordertoset, $neworder);
    }

    /**
     * Tests adding a new category.
     *
     */
    public function test_add_question_category() {
        global $DB;

        $id = add_question_category::execute($this->idparent, $this->name, $this->categoryinfo, 1, $this->idnumber);
        $record = $DB->get_record('question_categories', ['id' => $id]);

        $this->assertNotEmpty($record);
        $this->assertEquals('Dummy name', $record->name);
        $this->assertEquals($this->category->contextid, $record->contextid);
        $this->assertEquals('Dummy category info', $record->info);
        $this->assertEquals('1', $record->infoformat);
        $this->assertEquals($id, $record->id);
        $this->assertEquals('Dummy id num', $record->idnumber);
    }

    /**
     * Tests updating category order.
     *
     */
    public function test_update_question_category() {
        global $DB;

        $id = add_question_category::execute($this->idparent, $this->name, $this->categoryinfo, 1, $this->idnumber);
        $record = $DB->get_record('question_categories', ['id' => $id]);

        $this->assertNotEmpty($record);
        $this->assertEquals('Dummy name', $record->name);
        $this->assertEquals($this->category->contextid, $record->contextid);
        $this->assertEquals('Dummy category info', $record->info);
        $this->assertEquals('1', $record->infoformat);
        $this->assertEquals($id, $record->id);
        $this->assertEquals('Dummy id num', $record->idnumber);

        $newname = 'Updated dummy name';
        $newcategoryinfo = 'Updated dummy category info';
        $newidnumber = 'Updated dummy id num';
        update_question_category::execute($this->idparent, $newname, $newcategoryinfo, 1, $newidnumber, $this->category->id);
        $record = $DB->get_record('question_categories', ['name' => 'Updated dummy name']);

        $this->assertNotEmpty($record);
        $this->assertEquals('Updated dummy name', $record->name);
        $this->assertEquals($this->category->contextid, $record->contextid);
        $this->assertEquals('Updated dummy category info', $record->info);
        $this->assertEquals('1', $record->infoformat);
        $this->assertEquals('Updated dummy id num', $record->idnumber);
    }

    /**
     * Test creating a category.
     *
     * @covers ::add_category
     */
    public function test_add_category_no_idnumber() {
        global $DB;
        $idnumber = null;

        add_question_category::execute($this->idparent, $this->name, $this->categoryinfo, 1, $idnumber);

        $newcat = $DB->get_record('question_categories', ['name' => $this->name], '*', MUST_EXIST);
        $this->assertSame($this->name, $newcat->name);
        $this->assertNull($newcat->idnumber);
    }

    /**
     * Test creating a category with a tricky idnumber.
     *
     * @covers ::add_category
     */
    public function test_add_category_set_idnumber_0() {
        global $DB;
        $idnumber = 0;

        add_question_category::execute($this->idparent, $this->name, $this->categoryinfo, 1, $idnumber);

        $newcat = $DB->get_record('question_categories', ['name' => $this->name], '*', MUST_EXIST);
        $this->assertSame($this->name, $newcat->name);
        $this->assertSame('0', $newcat->idnumber);
    }

    /**
     * Trying to add a category with duplicate idnumber blanks it.
     * (In reality, this would probably get caught by form validation.)
     *
     * @covers ::add_category
     */
    public function test_add_category_try_to_set_duplicate_idnumber() {
        global $DB;
        $idnumber = 'frog';
        $firstadd = add_question_category::execute($this->idparent, $this->name, $this->categoryinfo, 1, $idnumber);
        $secondadd = add_question_category::execute($this->idparent, $this->name, $this->categoryinfo, 1, $idnumber);
        $this->assertIsInt($firstadd);
        $this->assertFalse($secondadd);
    }


    /**
     * Test updating a category to remove the idnumber.
     *
     * @covers ::update_category
     */
    public function test_update_category_removing_idnumber() {
        global $DB;
        $idnumber = 'frog';
        $newidnumber = null;
        $id = add_question_category::execute($this->idparent, $this->name, $this->categoryinfo, 1, $idnumber);
        $categoryjustadded = $DB->get_record('question_categories', ['id' => $id], '*', MUST_EXIST);
        update_question_category::execute($this->idparent, $this->name, $this->categoryinfo, 1, $newidnumber,
            $categoryjustadded->id);

        $newcat = $DB->get_record('question_categories', ['name' => $this->name], '*', MUST_EXIST);
        $this->assertSame($this->name, $newcat->name);
        $this->assertNull($newcat->idnumber);
    }

    /**
     * Test updating a category without changing the idnumber.
     *
     * @covers ::update_category
     */
    public function test_update_category_dont_change_idnumber() {
        global $DB;

        $id = add_question_category::execute($this->idparent, 'Old name', 'Old description', 1, 'frog');

        update_question_category::execute($this->idparent, 'New name', 'New description', 1, 'frog', $id);

        $newcat = $DB->get_record('question_categories', ['id' => $id], '*', MUST_EXIST);
        $this->assertSame('New name', $newcat->name);
        $this->assertSame('frog', $newcat->idnumber);
    }

    /**
     * Trying to update a category so its idnumber duplicates idnumber blanks it.
     * (In reality, this would probably get caught by form validation.)
     *
     * @covers ::update_category
     */
    public function test_update_category_try_to_set_duplicate_idnumber() {
        global $DB;

        $id = add_question_category::execute($this->idparent, 'Old name', 'Old description', 1, 'frog');
        $secondid = add_question_category::execute($this->idparent, 'Aborted update', 'Old description', 1, 'toad');

        update_question_category::execute($this->idparent, 'Old name', 'Old description', 1, 'frog', $secondid);

        $newcat = $DB->get_record('question_categories', ['id' => $secondid], '*', MUST_EXIST);
        $this->assertSame('Aborted update', $newcat->name);
        $this->assertSame($newcat->idnumber, 'toad');
    }
}
