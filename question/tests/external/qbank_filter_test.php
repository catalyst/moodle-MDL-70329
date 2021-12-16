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

defined('MOODLE_INTERNAL') || die();

global $CFG;

use core_question\external\qbank_filter;

require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->libdir . '/externallib.php');

/**
 * Unit tests for question bank filter external class.
 *
 * @package     core_question
 * @copyright   2019 the Open University
 * @author      2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass core_question\external\qbank_filter
 */
class qbank_filter_test extends advanced_testcase {
    /**
     * Tests set up.
     */
    public function setUp(): void {
        global $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $this->course = $this->getDataGenerator()->create_course();
        $this->qcat = $this->generator->create_question_category(['contextid' => context_course::instance($this->course->id)->id]);
        $url = new moodle_url('/question/edit.php', ['courseid' => $this->course->id]);
        $PAGE->set_url($url);
        $this->filters[0] = [
            'filtertype' => 'courseid',
            'jointype' => 1,
            'values' => $this->course->id,
        ];
        $this->filters[1] = [
            'filtertype' => 'category',
            'jointype' => 1,
            'values' => $this->qcat->id
        ];
        $this->filteroptions = [
            'filterverb' => 2,
            'recurse' => true,
            'showhidden' => false
        ];
        $this->displayoptions = [
            'perpage' => 20,
            'page' => 0,
            'showtext' => false
        ];
        $this->sortdata[0] = [
            'sortby' => 'qbank_viewquestiontype\question_type_column',
            'sortorder' => 4
        ];
    }

    /**
     * Test the returned element from qbank_filter::execute when no questions exist.
     * @test
     */
    public function qbank_filter_return_empty(): void {
        $result = qbank_filter::execute($this->course->id, $this->qcat->id,
            $this->filters,
            $this->filteroptions,
            $this->displayoptions,
            $this->sortdata);
        $this->assertCount(4, $result);
        $this->assertEmpty($result['totalquestions']);
        $this->assertEmpty($result['questionhtml']);
        $this->assertEmpty($result['warnings']);
        $this->assertNotEmpty($result['jsfooter']);
    }

    /**
     * Test the returned element from qbank_filter::execute questions exist.
     * @test
     */
    public function qbank_filter_return_full(): void {
        $this->generator->create_question('truefalse', null, ['category' => $this->qcat->id]);
        $this->generator->create_question('numerical', null, ['category' => $this->qcat->id]);
        $result = qbank_filter::execute($this->course->id, $this->qcat->id,
            $this->filters,
            $this->filteroptions,
            $this->displayoptions,
            $this->sortdata);
        $this->assertCount(4, $result);
        $this->assertEquals(2, $result['totalquestions']);
        $this->assertStringContainsString('True/False', $result['questionhtml']);
        $this->assertStringContainsString('numerical', $result['questionhtml']);
        $this->assertEmpty($result['warnings']);
        $this->assertNotEmpty($result['jsfooter']);
    }

    /**
     * Test the returned element from qbank_filter::execute returns
     * a warning when no condition is specified.
     *
     * @test
     */
    public function qbank_filter_return_warnings(): void {
        $filters[0] = [
            'filtertype' => 'courseid',
            'jointype' => 1,
            'values' => $this->course->id,
        ];
        $filters[1] = [
            'filtertype' => 'category',
            'jointype' => 1,
            'values' => 'NaN'
        ];
        $result = qbank_filter::execute($this->course->id, $this->qcat->id,
            $filters,
            $this->filteroptions,
            $this->displayoptions,
            $this->sortdata);
        $this->assertNotEmpty($result['warnings']);
        $this->assertEquals($result['warnings'][0]['warningcode'], 'nocategoryconditionspecified');
        $this->assertEquals($result['warnings'][0]['message'], 'Please specify a condition to retrieve questions');
    }

    /**
     * Test the returned element from qbank_filter::execute for the none condition.
     * @test
     */
    public function qbank_filter_none_condition(): void {
        global $DB;
        $question = $DB->get_record('question', ['category' => $this->qcat->id]);
        $this->assertEmpty($question);
        $this->generator->create_question('truefalse', null, ['category' => $this->qcat->id]);
        $question = $DB->get_record('question', ['category' => $this->qcat->id]);
        $this->assertNotEmpty($question);
        // None category condition.
        $filters[0] = [
            'filtertype' => 'courseid',
            'jointype' => 1,
            'values' => $this->course->id,
        ];
        $filters[1] = [
            'filtertype' => 'category',
            'jointype' => 0,
            'values' => $this->qcat->id,
        ];
        $result = qbank_filter::execute($this->course->id, $this->qcat->id,
            $filters,
            $this->filteroptions,
            $this->displayoptions,
            $this->sortdata);
        $this->assertEmpty($result['warnings']);
        $this->assertEquals(0, $result['totalquestions']);
        $this->assertEmpty($result['questionhtml']);
        $this->assertEmpty($result['warnings']);
        $this->assertNotEmpty($result['jsfooter']);
    }

    /**
     * Test the returned element from qbank_filter::execute for the any condition.
     * @test
     */
    public function qbank_filter_any_condition(): void {
        global $DB;
        $question1 = $this->generator->create_question('truefalse', null, ['category' => $this->qcat->id]);
        $question2 = $this->generator->create_question('numerical', null, ['category' => $this->qcat->id]);
        $this->generator->create_question_tag(['questionid' => $question1->id, 'tag' => 123]);
        $this->generator->create_question_tag(['questionid' => $question1->id, 'tag' => 555]);
        $this->generator->create_question_tag(['questionid' => $question2->id, 'tag' => 123]);
        $tagid1 = $DB->get_record('tag', ['name' => 123])->id;
        $tagid2 = $DB->get_record('tag', ['name' => 555])->id;
        $filters[0] = [
            'filtertype' => 'courseid',
            'jointype' => 1,
            'values' => $this->course->id,
        ];
        $filters[1] = [
            'filtertype' => 'qtagids',
            'jointype' => 1,
            'values' => $tagid1,
        ];
        $result = qbank_filter::execute($this->course->id, $this->qcat->id,
            $filters,
            $this->filteroptions,
            $this->displayoptions,
            $this->sortdata);

        $this->assertEquals(2, $result['totalquestions']);
        $this->assertNotEmpty($result['questionhtml']);
        $this->assertEmpty($result['warnings']);
        $this->assertNotEmpty($result['jsfooter']);
        $this->assertStringContainsString('True/False', $result['questionhtml']);
        $this->assertStringContainsString('numerical', $result['questionhtml']);
    }

    /**
     * Test the returned element from qbank_filter::execute for a specific any condition.
     * @test
     */
    public function qbank_filter_specific_any_condition(): void {
        global $DB;
        $question1 = $this->generator->create_question('truefalse', null, ['category' => $this->qcat->id]);
        $question2 = $this->generator->create_question('numerical', null, ['category' => $this->qcat->id]);
        $this->generator->create_question_tag(['questionid' => $question1->id, 'tag' => 123]);
        $this->generator->create_question_tag(['questionid' => $question1->id, 'tag' => 555]);
        $this->generator->create_question_tag(['questionid' => $question2->id, 'tag' => 123]);
        $tagid1 = $DB->get_record('tag', ['name' => 123])->id;
        $tagid2 = $DB->get_record('tag', ['name' => 555])->id;
        $filters[0] = [
            'filtertype' => 'courseid',
            'jointype' => 1,
            'values' => $this->course->id,
        ];
        $filters[1] = [
            'filtertype' => 'qtagids',
            'jointype' => 1,
            'values' => $tagid2,
        ];
        $result = qbank_filter::execute($this->course->id, $this->qcat->id,
            $filters,
            $this->filteroptions,
            $this->displayoptions,
            $this->sortdata);

        $this->assertEquals(1, $result['totalquestions']);
        $this->assertNotEmpty($result['questionhtml']);
        $this->assertEmpty($result['warnings']);
        $this->assertNotEmpty($result['jsfooter']);
        $this->assertStringContainsString('True/False', $result['questionhtml']);
        $this->assertStringNotContainsString('numerical', $result['questionhtml']);
    }

    /**
     * Test the returned element from qbank_filter::execute for all condition.
     * @test
     */
    public function qbank_filter_all_condition(): void {
        global $DB;
        $question1 = $this->generator->create_question('truefalse', null, ['category' => $this->qcat->id]);
        $question2 = $this->generator->create_question('numerical', null, ['category' => $this->qcat->id]);
        $this->generator->create_question_tag(['questionid' => $question1->id, 'tag' => 123]);
        $this->generator->create_question_tag(['questionid' => $question1->id, 'tag' => 555]);
        $this->generator->create_question_tag(['questionid' => $question2->id, 'tag' => 123]);
        $tagid1 = $DB->get_record('tag', ['name' => 123])->id;
        $tagid2 = $DB->get_record('tag', ['name' => 555])->id;
        $filters[0] = [
            'filtertype' => 'courseid',
            'jointype' => 1,
            'values' => $this->course->id,
        ];
        $filters[1] = [
            'filtertype' => 'qtagids',
            'jointype' => 2,
            'values' => "{$tagid1},{$tagid2}",
        ];
        $result = qbank_filter::execute($this->course->id, $this->qcat->id,
            $filters,
            $this->filteroptions,
            $this->displayoptions,
            $this->sortdata);
        $this->assertEquals(1, $result['totalquestions']);
        $this->assertNotEmpty($result['questionhtml']);
        $this->assertEmpty($result['warnings']);
        $this->assertNotEmpty($result['jsfooter']);
        $this->assertStringContainsString('True/False', $result['questionhtml']);
        $this->assertStringNotContainsString('numerical', $result['questionhtml']);
    }
}
