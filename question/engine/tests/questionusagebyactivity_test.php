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

use mod_quiz\external\submit_question_version;
use mod_quiz\question\bank\qbank_helper;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/helpers.php');
require_once($CFG->dirroot . '/mod/quiz/tests/quiz_question_helper_test_trait.php');

/**
 * Unit tests for the question_usage_by_activity class.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass question_usage_by_activity
 */
class question_usage_by_activity_test extends advanced_testcase {
    use \quiz_question_helper_test_trait;

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
     * Test question regrade for selected versions.
     *
     * @covers ::regrade_question
     */
    public function test_regrade_question() {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->create_test_quiz($course);
        $student = $this->getDataGenerator()->create_user();
        // Test for questions from a different context.
        $context = \context_module::instance(get_coursemodule_from_instance("quiz", $quiz->id, $course->id)->id);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        // Create a couple of questions.
        $cat = $questiongenerator->create_question_category(['contextid' => $context->id]);
        $numq = $questiongenerator->create_question('essay', null,
            ['category' => $cat->id, 'name' => 'This is the first version', 'correctanswer' => false]);
        // Create two version.
        $numq2 = $questiongenerator->update_question($numq, null,
            ['name' => 'This is the second version','correctanswer' => true]);
        $numq3 = $questiongenerator->update_question($numq, null,
            ['name' => 'This is the third version', 'correctanswer' => false]);
        quiz_add_quiz_question($numq->id, $quiz);
        // Create the quiz object.
        $quizobj = \quiz::create($quiz->id);
        $structure = \mod_quiz\structure::create_for_quiz($quizobj);
        $slots = $structure->get_slots();
        $slot = reset($slots);
        // Now change the version using the external service.
        $versions = qbank_helper::get_version_options($slot->questionid);
        // We dont want the current version.
        $selectversions = [];
        foreach ($versions as $version) {
            if ($version->version === $slot->version) {
                continue;
            }
            $selectversions [$version->version] = $version;
        }
        // Change to version 1, with correct response.
        $this->expectException('moodle_exception');
        submit_question_version::execute($slot->id, (int)$selectversions[1]->version);
        list($quizobj, $quba, $attemptobj) = $this->attempt_quiz($quiz, $student);
        $this->assertEquals(10, $attemptobj->get_question_usage()->get_total_mark());

        // Change to version 2, with wrong response.
        submit_question_version::execute($slot->id, (int)$selectversions[2]->version);
        $quba->regrade_question(1, quiz_attempt::FINISHED, null, $numq2->id);
        $this->assertEquals(0, $attemptobj->get_question_usage()->get_total_mark());

        // Change to version 3, with correct response.
        submit_question_version::execute($slot->id, (int)$selectversions[3]->version);
        $quba->regrade_question(1, quiz_attempt::FINISHED, null, $numq3->id);
        $this->assertEquals(10, $attemptobj->get_question_usage()->get_total_mark());
    }
}
