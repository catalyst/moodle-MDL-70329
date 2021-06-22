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
use context_course;
use qbank_managecategories\external\set_category_order;

/**
 * Unit tests for qbank_managecategories enhancememt component.
 *
 * @package    qbank_managecategories
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class managecategories_enhancement_test extends advanced_testcase {

    public function test_set_category_order() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $course = $this->getDataGenerator()->create_course();
        $category1 = $generator->create_question_category(['contextid' => 1200, 'id' => 1]);
        $category2 = $generator->create_question_category(['contextid' => 1201, 'id' => 2]);
        $category3 = $generator->create_question_category(['contextid' => 1202, 'id' => 3]);
        $category4 = $generator->create_question_category(['contextid' => 1203, 'id' => 4]);
        $category5 = $generator->create_question_category(['contextid' => 1204, 'id' => 5]);
        $categories = $DB->get_records('question_categories');
        $this->assertNotEmpty($categories);
        $ordertoset = [];
        foreach ($categories as $category) {
            $ordertoset[] = [$category->contextid . ' ' . $category->id];
        }

        $destination = array_rand($ordertoset, 1);
        $origin = array_rand($ordertoset, 1);

        $ordertoset = [$ordertoset, $ordertoset[$destination][0], $ordertoset[$origin][0]];
        $ordertoset = json_encode($ordertoset);
        set_category_order::execute($ordertoset);
        $newcategories = $DB->get_records('question_categories');
        $neworder = [];
        foreach ($newcategories as $category) {
            $neworder[] = [$category->contextid . ' ' . $category->id];
        }
        $ordertoset = json_decode($ordertoset);

        $this->assertNotEquals($ordertoset[0], $neworder);
    }
}