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
 * Unit tests for helper class.
 *
 * @package    qbank_managecategories
 * @copyright  2006 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_managecategories;

defined('MOODLE_INTERNAL') || die();

use context_system;

/**
 * Unit tests for helper class.
 *
 * @package    qbank_managecategories
 * @copyright  2006 The Open University
 * @author     2021, Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper_test extends \advanced_testcase {

    public function test_question_remove_stale_questions_from_category() {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $dg = $this->getDataGenerator();
        $course = $dg->create_course();
        $quiz = $dg->create_module('quiz', ['course' => $course->id]);

        $qgen = $dg->get_plugin_generator('core_question');
        $context = context_system::instance();

        $qcat1 = $qgen->create_question_category(['contextid' => $context->id]);
        $q1a = $qgen->create_question('shortanswer', null, ['category' => $qcat1->id]);     // Will be hidden.
        $DB->set_field('question', 'hidden', 1, ['id' => $q1a->id]);

        $qcat2 = $qgen->create_question_category(['contextid' => $context->id]);
        $q2a = $qgen->create_question('shortanswer', null, ['category' => $qcat2->id]);     // Will be hidden.
        $q2b = $qgen->create_question('shortanswer', null, ['category' => $qcat2->id]);     // Will be hidden but used.
        $DB->set_field('question', 'hidden', 1, ['id' => $q2a->id]);
        $DB->set_field('question', 'hidden', 1, ['id' => $q2b->id]);
        quiz_add_quiz_question($q2b->id, $quiz);
        quiz_add_random_questions($quiz, 0, $qcat2->id, 1, false);

        // We added one random question to the quiz and we expect the quiz to have only one random question.
        $q2d = $DB->get_record_sql("SELECT q.*
                                      FROM {question} q
                                      JOIN {quiz_slots} s ON s.questionid = q.id
                                     WHERE q.qtype = :qtype
                                           AND s.quizid = :quizid",
            ['qtype' => 'random', 'quizid' => $quiz->id], MUST_EXIST);

        // The following 2 lines have to be after the quiz_add_random_questions() call above.
        // Otherwise, quiz_add_random_questions() will to be "smart" and use them instead of creating a new "random" question.
        $q1b = $qgen->create_question('random', null, ['category' => $qcat1->id]);          // Will not be used.
        $q2c = $qgen->create_question('random', null, ['category' => $qcat2->id]);          // Will not be used.

        $this->assertEquals(2, $DB->count_records('question', ['category' => $qcat1->id]));
        $this->assertEquals(4, $DB->count_records('question', ['category' => $qcat2->id]));

        // Non-existing category, nothing will happen.
        helper::question_remove_stale_questions_from_category(0);
        $this->assertEquals(2, $DB->count_records('question', ['category' => $qcat1->id]));
        $this->assertEquals(4, $DB->count_records('question', ['category' => $qcat2->id]));

        // First category, should be empty afterwards.
        helper::question_remove_stale_questions_from_category($qcat1->id);
        $this->assertEquals(0, $DB->count_records('question', ['category' => $qcat1->id]));
        $this->assertEquals(4, $DB->count_records('question', ['category' => $qcat2->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q1a->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q1b->id]));

        // Second category, used questions should be left untouched.
        helper::question_remove_stale_questions_from_category($qcat2->id);
        $this->assertEquals(0, $DB->count_records('question', ['category' => $qcat1->id]));
        $this->assertEquals(2, $DB->count_records('question', ['category' => $qcat2->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q2a->id]));
        $this->assertTrue($DB->record_exists('question', ['id' => $q2b->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q2c->id]));
        $this->assertTrue($DB->record_exists('question', ['id' => $q2d->id]));
    }
}
