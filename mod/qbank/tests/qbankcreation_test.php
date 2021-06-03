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
 * Testing method qbankcreation in helper class.
 *
 * @package    mod_qbank
 * @category   test
 * @copyright  2021 Catalyst IT Canada Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_qbank;

use context_coursecat;

defined('MOODLE_INTERNAL') || die();

class qbankcreation_test extends \advanced_testcase {

    function test_qbank_instancecreated() : void {
        $this->resetAfterTest();

        $category = $this->getDataGenerator()->create_category();
        $courseshortname = substr(CONTEXT_COURSECAT . '-' . $category->id .': This is some course', 0, 254);
        $course = $this->getDataGenerator()->create_course(['shortname' => $courseshortname, 'category' => $category->id]);

        // Test create_qbank_instance function.
        $qbank = helper::create_qbank_instance($courseshortname, $course);
        $this->assertEquals($qbank->name, $courseshortname);
    }
}
