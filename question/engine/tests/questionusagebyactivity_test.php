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
 * This file contains tests for the question_usage_by_activity class.
 *
 * @package   core_question
 * @copyright 2009 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/helpers.php');
require_once(__DIR__ . '/../../../mod/quiz/report/default.php');
require_once(__DIR__ . '/../../../mod/quiz/report/overview/report.php');

/**
 * Unit tests for the question_usage_by_activity class.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_usage_by_activity_test extends advanced_testcase {

    public function test_set_get_preferred_model() {
        // Set up
        $quba = question_engine::make_questions_usage_by_activity('unit_test',
                context_system::instance());

        // Exercise SUT and verify.
        $quba->set_preferred_behaviour('deferredfeedback');
        $this->assertEquals('deferredfeedback', $quba->get_preferred_behaviour());
    }

    public function test_set_get_id() {
        // Set up
        $quba = question_engine::make_questions_usage_by_activity('unit_test',
                context_system::instance());

        // Exercise SUT and verify
        $quba->set_id_from_database(123);
        $this->assertEquals(123, $quba->get_id());
    }

    public function test_fake_id() {
        // Set up
        $quba = question_engine::make_questions_usage_by_activity('unit_test',
                context_system::instance());

        // Exercise SUT and verify
        $this->assertNotEmpty($quba->get_id());
    }

    public function test_create_usage_and_add_question() {
        // Exercise SUT
        $context = context_system::instance();
        $quba = question_engine::make_questions_usage_by_activity('unit_test', $context);
        $quba->set_preferred_behaviour('deferredfeedback');
        $tf = test_question_maker::make_question('truefalse', 'true');
        $slot = $quba->add_question($tf);

        // Verify.
        $this->assertEquals($slot, 1);
        $this->assertEquals('unit_test', $quba->get_owning_component());
        $this->assertSame($context, $quba->get_owning_context());
        $this->assertEquals($quba->question_count(), 1);
        $this->assertEquals($quba->get_question_state($slot), question_state::$notstarted);
    }

    public function test_get_question() {
        // Set up.
        $quba = question_engine::make_questions_usage_by_activity('unit_test',
                context_system::instance());
        $quba->set_preferred_behaviour('deferredfeedback');
        $tf = test_question_maker::make_question('truefalse', 'true');
        $slot = $quba->add_question($tf);

        // Exercise SUT and verify.
        $this->assertSame($tf, $quba->get_question($slot, false));

        $this->expectException('moodle_exception');
        $quba->get_question($slot + 1, false);
    }

    public function test_extract_responses() {
        // Start a deferred feedback attempt with CBM and add the question to it.
        $tf = test_question_maker::make_question('truefalse', 'true');
        $quba = question_engine::make_questions_usage_by_activity('unit_test',
                context_system::instance());
        $quba->set_preferred_behaviour('deferredcbm');
        $slot = $quba->add_question($tf);
        $quba->start_all_questions();

        // Prepare data to be submitted
        $prefix = $quba->get_field_prefix($slot);
        $answername = $prefix . 'answer';
        $certaintyname = $prefix . '-certainty';
        $getdata = array(
            $answername => 1,
            $certaintyname => 3,
            'irrelevant' => 'should be ignored',
        );

        // Exercise SUT
        $submitteddata = $quba->extract_responses($slot, $getdata);

        // Verify.
        $this->assertEquals(array('answer' => 1, '-certainty' => 3), $submitteddata);
    }

    public function test_access_out_of_sequence_throws_exception() {
        // Start a deferred feedback attempt with CBM and add the question to it.
        $tf = test_question_maker::make_question('truefalse', 'true');
        $quba = question_engine::make_questions_usage_by_activity('unit_test',
                context_system::instance());
        $quba->set_preferred_behaviour('deferredcbm');
        $slot = $quba->add_question($tf);
        $quba->start_all_questions();

        // Prepare data to be submitted
        $prefix = $quba->get_field_prefix($slot);
        $answername = $prefix . 'answer';
        $certaintyname = $prefix . '-certainty';
        $postdata = array(
            $answername => 1,
            $certaintyname => 3,
            $prefix . ':sequencecheck' => 1,
            'irrelevant' => 'should be ignored',
        );

        // Exercise SUT - no exception yet.
        $quba->process_all_actions($slot, $postdata);

        $postdata = array(
            $answername => 1,
            $certaintyname => 3,
            $prefix . ':sequencecheck' => 3,
            'irrelevant' => 'should be ignored',
        );

        // Exercise SUT - now it should fail.
        $this->expectException('question_out_of_sequence_exception');
        $quba->process_all_actions($slot, $postdata);
    }

    /**
     * Test function preload all step users.
     */
    public function test_preload_all_step_users() {
        $this->resetAfterTest();
        $this->setAdminUser();
        // Set up.
        $quba = question_engine::make_questions_usage_by_activity('unit_test',
                context_system::instance());

        // Create an essay question in the DB.
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category();
        $essay = $generator->create_question('essay', 'editorfilepicker', ['category' => $cat->id]);

        // Start attempt at the question.
        $q = question_bank::load_question($essay->id);
        $quba->set_preferred_behaviour('deferredfeedback');
        $slot = $quba->add_question($q, 10);
        $quba->start_question($slot, 1);

        // Finish the attempt.
        $quba->finish_all_questions();
        question_engine::save_questions_usage_by_activity($quba);

        // The user information of question attempt step should be loaded.
        $quba->preload_all_step_users();
        $qa = $quba->get_attempt_iterator()->current();
        $steps = $qa->get_full_step_iterator();
        $this->assertEquals('Admin User', $steps[0]->get_user_fullname());
    }

    /**
     * Test question regrading taking into account versions.
     */
    public function test_regrade_question() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        // Make a user to do the quiz.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        // Make a quiz.
        $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $quizgenerator->create_instance(['course' => $course->id,
            'grade' => 100.0, 'sumgrades' => 10.0]);
        $quizobj = quiz::create($quiz->id, $user->id);

        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $question = $questiongenerator->create_question('truefalse', null, ['category' => $cat->id, 'name' => 'Alpha Question']);
        $originalid = $question->id;
        $questionbankentryid = $DB->get_record('question_versions', ['questionid' => $question->id], 'questionbankentryid')->questionbankentryid;
        $questionattempts = $DB->get_records('question_attempts');

        $this->assertEmpty($questionattempts);
        // Attempt question.
        $q = question_bank::load_question($question->id);
        $quba->set_preferred_behaviour('adaptive');
        $slot = $quba->add_question($q);
        $quba->start_question($slot);
        $quba->process_all_actions(null, ['answer' => 'False']);
        $quba->finish_all_questions();
        $quba->get_question_attempt($slot)->manual_grade('Comment', 1, FORMAT_HTML);
        question_engine::save_questions_usage_by_activity($quba, $DB);

        $attempt1 = quiz_create_attempt($quizobj, 1, null, time());
        quiz_attempt_save_started($quizobj, $quba, $attempt1);
        $attemptobj1 = quiz_attempt::create($attempt1->id);
        $questionattemptsupdated = $DB->get_records('question_attempts');

        $this->assertNotEmpty($questionattemptsupdated);
        $this->assertCount(1, $questionattemptsupdated);
        $this->assertEquals($originalid, reset($questionattemptsupdated)->questionid);

        $questiongenerator->update_question($question, null, ['name' => 'This is the second version']);
        $versions = $DB->get_records('question_versions', ['questionbankentryid' => $questionbankentryid]);
        $this->assertCount(2, $versions);
        $quizattempts = $DB->get_records('quiz_attempts');

        $update = new stdClass();
        $update->id = $attemptobj1->get_attemptid();
        $update->timemodified = time();
        $update->sumgrades = $attemptobj1->get_question_usage()->get_total_mark();
        $DB->update_record('quiz_attempts', $update);
        $quizattempts = $DB->get_records('quiz_attempts');
        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $quizoverviewreport = new quiz_overview_report();
        $foo = self::getMethod($quizoverviewreport, 'regrade_attempt', $cm->id);
        $foo->invokeArgs($quizoverviewreport, [reset($quizattempts)]);
        $latestquestionattempts = $DB->get_records('question_attempts');

        $this->assertNotEmpty($latestquestionattempts);
        $this->assertCount(1, $latestquestionattempts);
        $this->assertNotEquals($originalid, reset($latestquestionattempts)->questionid);
    }

    /**
     * Gets protected method in quiz_overview_report class for tests.
     *
     * @return object $method
     */
    protected static function getMethod($instance, $name, $cmid) {
        $class = new ReflectionClass($instance);
        $context = $class->getProperty('context');
        $context->setAccessible(true);
        $context->setValue($instance, context_module::instance($cmid));
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
      }
}
