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

namespace mod_quiz;

use mod_quiz\external\submit_question_version;
use mod_quiz\question\bank\qbank_helper;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/quiz_question_helper_test_trait.php');

/**
 * Qbank helper test for quiz.
 *
 * @package    mod_quiz
 * @category   test
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbank_helper_test extends \advanced_testcase {
    use \quiz_question_helper_test_trait;

    /**
     * Called before every test.
     */
    public function setUp(): void {
        global $USER;
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->course = $this->getDataGenerator()->create_course();
        $this->student = $this->getDataGenerator()->create_user();
        $this->user = $USER;
    }

    /**
     * Test is random.
     */
    public function test_is_random() {
        $quiz = $this->create_test_quiz($this->course);
        // Test for questions from a different context.
        $context = \context_module::instance(get_coursemodule_from_instance("quiz", $quiz->id, $this->course->id)->id);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $this->add_random_questions($questiongenerator, $quiz, ['contextid' => $context->id]);
        // Create the quiz object.
        $quizobj = \quiz::create($quiz->id);
        $structure = \mod_quiz\structure::create_for_quiz($quizobj);
        $slots = $structure->get_slots();
        foreach ($slots as $slot) {
            $this->assertEquals(true, qbank_helper::is_random($slot->id));
            // Test random data for slot.
            $this->assertEquals($slot->id, qbank_helper::get_random_question_data_from_slot($slot->id)->itemid);
        }

    }

    /**
     * Test reference records.
     */
    public function test_reference_records() {
        $quiz = $this->create_test_quiz($this->course);
        // Test for questions from a different context.
        $context = \context_module::instance(get_coursemodule_from_instance("quiz", $quiz->id, $this->course->id)->id);
        // Create a couple of questions.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category(['contextid' => $context->id]);
        $numq = $questiongenerator->create_question('essay', null,
            ['category' => $cat->id, 'name' => 'This is the first version']);
        // Create two version.
        $questiongenerator->update_question($numq, null, ['name' => 'This is the second version']);
        $questiongenerator->update_question($numq, null, ['name' => 'This is the third version']);
        quiz_add_quiz_question($numq->id, $quiz);
        // Create the quiz object.
        $quizobj = \quiz::create($quiz->id);
        $quizobj->preload_questions();
        $quizobj->load_questions();
        $questions = $quizobj->get_questions();
        $question = reset($questions);
        $structure = \mod_quiz\structure::create_for_quiz($quizobj);
        $slots = $structure->get_slots();
        $slot = reset($slots);
        $this->assertEquals(3, count(qbank_helper::get_version_options($question->id)));
        $quizobj->preload_questions();
        $quizobj->load_questions();
        $questions = $quizobj->get_questions();
        $question = reset($questions);
        $this->assertEquals($question->id, qbank_helper::get_question_for_redo($slot->id));
        // Create another version.
        $questiongenerator->update_question($numq, null, ['name' => 'This is the latest version']);
        // Change to always latest.
        submit_question_version::execute($slot->id, 0);
        $quizobj->preload_questions();
        $quizobj->load_questions();
        $questions = $quizobj->get_questions();
        $question = reset($questions);
        $this->assertEquals($question->id, qbank_helper::get_question_for_redo($slot->id));
        // Test always latest version question ids.
        $latestquestionids = qbank_helper::get_always_latest_version_question_ids($quiz->id);
        $this->assertEquals($question->id, reset($latestquestionids));
    }

    /**
     * Test question structure data.
     */
    public function test_get_question_structure() {
        $quiz = $this->create_test_quiz($this->course);
        // Test for questions from a different context.
        $context = \context_module::instance(get_coursemodule_from_instance("quiz", $quiz->id, $this->course->id)->id);
        // Create a couple of questions.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category(['contextid' => $context->id]);
        $numq = $questiongenerator->create_question('essay', null,
            ['category' => $cat->id, 'name' => 'This is the first version']);
        // Create two version.
        $questiongenerator->update_question($numq, null, ['name' => 'This is the second version']);
        $questiongenerator->update_question($numq, null, ['name' => 'This is the third version']);
        quiz_add_quiz_question($numq->id, $quiz);
        // Create the quiz object.
        $quizobj = \quiz::create($quiz->id);
        $quizobj->preload_questions();
        $quizobj->load_questions();
        $questions = $quizobj->get_questions();
        $question = reset($questions);
        $structure = \mod_quiz\structure::create_for_quiz($quizobj);
        $slots = $structure->get_slots();
        $slot = reset($slots);
        $structuredatas = qbank_helper::get_question_structure($quiz->id);
        $structuredata = reset($structuredatas);
        $this->assertEquals($structuredata->slotid, $slot->id);
        $this->assertEquals($structuredata->id, $question->id);
    }
}
