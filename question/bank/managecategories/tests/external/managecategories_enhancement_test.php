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

use advanced_testcase;
use moodle_exception;
use qbank_managecategories\external\update_category_order;

/**
 * Unit tests for qbank_managecategories enhancememt component.
 *
 * @package    qbank_managecategories
 * @copyright  2021 Catalyst IT Australia Pty Ltd
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
        $this->idparent = $this->category->parent;
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
}
