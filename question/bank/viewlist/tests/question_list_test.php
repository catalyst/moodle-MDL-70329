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

namespace qbank_viewlist;

/**
 * Tests for the data of question usage from differnet areas like helper or usage table.
 *
 * @package    qbank_viewlist
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_list_test extends \advanced_testcase {

    /**
     * Test question usage data.
     */
    public function test_question_list() {

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $questiongenerator = $generator->get_plugin_generator('core_question');

        // Make a course and a quiz.
        $course = $generator->create_course();
        $coursecontext = \context_course::instance($course->id);
        $cat = $questiongenerator->create_question_category(['contextid' => $coursecontext->id]);
        $mod = $generator->create_module('quiz', ['course' => $course->id, 'name' => 'quiz']);

        // Make questions.
        $questionname1 = 'Question:1';
        $question = ['category' => $cat->id, 'name' => $questionname1];
        $q = $questiongenerator->create_question('shortanswer', null, $question);
        quiz_add_quiz_question($q->id, $mod);
        $questionid1 = $q->id;

        $questionname2 = 'Question:2';
        $question = ['category' => $cat->id, 'name' => $questionname2];
        $q = $questiongenerator->create_question('shortanswer', null, $question);
        quiz_add_quiz_question($q->id, $mod);
        $questionid2 = $q->id;

        $questionname3 = 'Question:3';
        $question = ['category' => $cat->id, 'name' => $questionname3];
        $q = $questiongenerator->create_question('shortanswer', null, $question);
        quiz_add_quiz_question($q->id, $mod);
        $questionid3 = $q->id;

        // Search by question id.
        $questions = [
            (object)['id' => $questionid1],
            (object)['id' => $questionid3],
        ];
        $questionlisthtml = qbank_viewlist_output_fragment_question_list([
            'questions' => json_encode($questions),
            'context' => $coursecontext,
        ]);

        $this->assertStringContainsString($questionname1, $questionlisthtml);
        $this->assertStringNotContainsString($questionname2, $questionlisthtml);
        $this->assertStringContainsString($questionname3, $questionlisthtml);
    }
}
