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

namespace qbank_previewquestion;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/editlib.php');

use advanced_testcase;
use qbank_previewquestion\previewquestion_helper;
/**
 * Unit tests for qbank_previewquestion\previewquestion_helper.
 *
 * @package     qbank_previewquestion
 * @copyright   2019 the Open University
 * @author      2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \qbank_managecategories\previewquestion_helper
 */
class previewquestion_helper_test extends advanced_testcase {

    /**
     * Test method is_latest().
     *
     */
    public function test_is_latest() {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $qcat1 = $generator->create_question_category([
            'name' => 'My category', 'sortorder' => 1, 'idnumber' => 'myqcat']);
        $question = $generator->create_question('shortanswer', null,
        ['name' => 'sa1', 'category' => $qcat1->id, 'idnumber' => 'myquest_3']);
        $record = $DB->get_record('question_versions', ['questionid' => $question->id]);
        $firstversion = $record->version;
        $questionbankentryid = $record->questionbankentryid;
        $islatest = previewquestion_helper::is_latest($firstversion, $questionbankentryid);
        $this->assertTrue($islatest);
        //$islatest = previewquestion_helper::is_latest($version, $questionbankentryid);
    }

}
