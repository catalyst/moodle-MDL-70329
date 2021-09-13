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
use qbank_managecategories\external\set_category_order;
use qbank_managecategories\external\submit_add_category_form;

/**
 * Unit tests for qbank_managecategories enhancememt component.
 *
 * @package    qbank_managecategories
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class managecategories_enhancement_test extends advanced_testcase {
    /**
     * Tests setting a new category order.
     *
     */
    public function test_set_category_order() {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

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
            $ordertoset[] = [$category->contextid . ' ' . $category->id];
        }
        $destination = $category4->contextid . ' ' . $category4->id;
        $origin = $category2->contextid . ' ' . $category2->id;
        $ordertoset = [$ordertoset, $destination, $origin];
        $ordertoset = json_encode($ordertoset);
        set_category_order::execute($ordertoset);

        $newcategories = $DB->get_records('question_categories');
        $neworder = [];
        foreach ($newcategories as $category) {
            if ((int)$category->id === $category2->id) {
                $this->assertEquals($category->contextid, $category4->contextid);
            }
            $neworder[] = [$category->contextid . ' ' . $category->id];
        }
        $ordertoset = json_decode($ordertoset);
        $this->assertNotEquals($ordertoset[0], $neworder);
    }

    /**
     * Tests adding a new category order.
     *
     */
    public function test_submit_add_category_form() {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $generator->create_question_category();
        $jsonformdata = "\"parent={$category->id}%2C{$category->contextid}&" .
            "name=Dummy%20name&" .
            "info%5Btext%5D=%3Cp%20dir%3D%22ltr%22%20style%3D%22text-align%3A%20left%3B%22%3EDummy%20category%20info%3C%2Fp%3E&" .
            "info%5Bformat%5D=1&idnumber=Dummy%20id%20num\"";
        $newcatid = submit_add_category_form::execute($jsonformdata);
        $record = $DB->get_record('question_categories', ['id' => $newcatid]);

        $this->assertNotEmpty($record);
        $this->assertEquals($newcatid, $record->id);
        $this->assertEquals('Dummy name', $record->name);
        $this->assertEquals($category->contextid, $record->contextid);
        $this->assertEquals('<p dir="ltr" style="text-align: left;">Dummy category info</p>', $record->info);
        $this->assertEquals('1', $record->infoformat);
        $this->assertEquals($category->id, $record->parent);
        $this->assertEquals('Dummy id num', $record->idnumber);
    }
}
