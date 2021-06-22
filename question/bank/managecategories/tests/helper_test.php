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

namespace qbank_managecategories;

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->dirroot . '/question/bank/managecategories/tests/manage_category_test_base.php');

/**
 * Unit tests for helper class.
 *
 * @package    qbank_managecategories
 * @copyright  2006 The Open University
 * @author     2021, Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \qbank_managecategories\helper
 */
class helper_test extends manage_category_test_base {
    /**
     * Test question_remove_stale_questions_from_category function.
     *
     * @covers ::question_remove_stale_questions_from_category
     */
    public function test_question_remove_stale_questions_from_category() {
        global $DB;

        $this->setAdminUser();
        $this->resetAfterTest();

        // Quiz and its context.
        $quiz = $this->create_quiz();

        // Create category 1 and one question.
        $qcat1 = $this->create_question_category_for_a_quiz($quiz);
        $q1a = $this->create_question_in_a_category('shortanswer', $qcat1->id);
        $DB->set_field('question_versions', 'status', 'hidden', ['questionid' => $q1a->id]);

        // Create category 2 and two questions.
        $qcat2 = $this->create_question_category_for_a_quiz($quiz);
        $q2a = $this->create_question_in_a_category('shortanswer', $qcat2->id);
        $q2b = $this->create_question_in_a_category('shortanswer', $qcat2->id);
        $DB->set_field('question_versions', 'status', 'hidden', ['questionid' => $q2a->id]);
        $DB->set_field('question_versions', 'status', 'hidden', ['questionid' => $q2b->id]);

        // Add question to the quiz.
        quiz_add_quiz_question($q2b->id, $quiz);

        // Adding a new random question does not add a new question, adds a question_set_references record.
        quiz_add_random_questions($quiz, 0, $qcat2->id, 1, false);

        // We added one random question to the quiz and we expect the quiz to have only one random question.
        $q2d = $DB->get_record_sql("SELECT qsr.*
                                      FROM {quiz_slots} qs
                                      JOIN {question_set_references} qsr ON qsr.itemid = qs.id
                                     WHERE qs.quizid = ?
                                       AND qsr.component = ?
                                       AND qsr.questionarea = ?",
            [$quiz->id, 'mod_quiz', 'slot'], MUST_EXIST);

        // The following 2 lines have to be after the quiz_add_random_questions() call above.
        // Otherwise, quiz_add_random_questions() will to be "smart" and use them instead of creating a new "random" question.
        $q1b = $this->create_question_in_a_category('random', $qcat1->id);
        $q2c = $this->create_question_in_a_category('random', $qcat2->id);

        $sql = "SELECT count(q.id)
                  FROM {question} q
                  JOIN {question_versions} qv ON qv.questionid = q.id
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                 WHERE qbe.questioncategoryid = ?";
        $this->assertEquals(2, $DB->count_records_sql($sql, [$qcat1->id]));
        $this->assertEquals(3, $DB->count_records_sql($sql, [$qcat2->id]));

        // Non-existing category, nothing will happen.
        helper::question_remove_stale_questions_from_category(0);
        $sql = "SELECT count(q.id)
                  FROM {question} q
                  JOIN {question_versions} qv ON qv.questionid = q.id
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                 WHERE qbe.questioncategoryid = ?";
        $this->assertEquals(2, $DB->count_records_sql($sql, [$qcat1->id]));
        $this->assertEquals(3, $DB->count_records_sql($sql, [$qcat2->id]));

        // First category, should be empty afterwards.
        helper::question_remove_stale_questions_from_category($qcat1->id);
        $sql = "SELECT count(q.id)
                  FROM {question} q
                  JOIN {question_versions} qv ON qv.questionid = q.id
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                 WHERE qbe.questioncategoryid = ?";
        $this->assertEquals(0, $DB->count_records_sql($sql, [$qcat1->id]));
        $this->assertEquals(3, $DB->count_records_sql($sql, [$qcat2->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q1a->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q1b->id]));

        // Second category, used questions should be left untouched.
        helper::question_remove_stale_questions_from_category($qcat2->id);
        $sql = "SELECT count(q.id)
                  FROM {question} q
                  JOIN {question_versions} qv ON qv.questionid = q.id
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                 WHERE qbe.questioncategoryid = ?";
        $this->assertEquals(0, $DB->count_records_sql($sql, [$qcat1->id]));
        $this->assertEquals(1, $DB->count_records_sql($sql, [$qcat2->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q2a->id]));
        $this->assertTrue($DB->record_exists('question', ['id' => $q2b->id]));
        $this->assertFalse($DB->record_exists('question', ['id' => $q2c->id]));
        $this->assertTrue($DB->record_exists('question_set_references',
            ['id' => $q2d->id, 'component' => 'mod_quiz', 'questionarea' => 'slot']));
    }

    /**
     * Test delete top category in function question_can_delete_cat.
     *
     * @covers ::question_can_delete_cat
     * @covers ::question_is_top_category
     */
    public function test_question_can_delete_cat_top_category() {
        $this->setAdminUser();
        $this->resetAfterTest();

        // Create a category.
        $quiz = $this->create_quiz();
        $qcategory1 = $this->create_question_category_for_a_quiz($quiz);

        // Try to delete a top category.
        $categorytop = question_get_top_category($qcategory1->id, true)->id;
        $this->expectException('moodle_exception');
        $this->expectExceptionMessage(get_string('cannotdeletetopcat', 'question'));
        helper::question_can_delete_cat($categorytop);
    }

    /**
     * Test delete only child category in function question_can_delete_cat.
     *
     * @covers ::question_can_delete_cat
     * @covers ::question_is_only_child_of_top_category_in_context
     */
    public function test_question_can_delete_cat_child_category() {
        $this->setAdminUser();
        $this->resetAfterTest();

        // Create a category.
        $quiz = $this->create_quiz();
        $qcategory1 = $this->create_question_category_for_a_quiz($quiz);

        // Try to delete an only child of top category having also at least one child.
        $this->expectException('moodle_exception');
        $this->expectExceptionMessage(get_string('cannotdeletecate', 'question'));
        helper::question_can_delete_cat($qcategory1->id);
    }

    /**
     * Test delete category in function question_can_delete_cat without capabilities.
     *
     * @covers ::question_can_delete_cat
     */
    public function test_question_can_delete_cat_capability() {
        $this->setAdminUser();
        $this->resetAfterTest();

        // Create 2 categories.
        $quiz = $this->create_quiz();
        $qcategory1 = $this->create_question_category_for_a_quiz($quiz);
        $qcategory2 = $this->create_question_category_for_a_quiz($quiz, ['parent' => $qcategory1->id]);

        // This call should not throw an exception as admin user has the capabilities moodle/question:managecategory.
        helper::question_can_delete_cat($qcategory2->id);

        // Try to delete a category with and user without the capability.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(\required_capability_exception::class);
        $this->expectExceptionMessage(get_string('nopermissions', 'error', get_string('question:managecategory', 'role')));
        helper::question_can_delete_cat($qcategory2->id);
    }

    /**
     * Test question_category_select_menu function.
     *
     * @covers ::question_category_select_menu
     * @covers ::question_category_options
     */
    public function test_question_category_select_menu() {
        $this->setAdminUser();
        $this->resetAfterTest();

        // Create category.
        $quiz = $this->create_quiz();
        $this->create_question_category_for_a_quiz($quiz, ['name' => 'Test this question category']);
        $contexts = new \core_question\local\bank\question_edit_contexts(\context_module::instance($quiz->cmid));

        ob_start();
        helper::question_category_select_menu($contexts->having_cap('moodle/question:add'));
        $output = ob_get_clean();

        // Test the select menu of question categories output.
        $this->assertStringContainsString('Question category', $output);
        $this->assertStringContainsString('Test this question category', $output);
    }

    /**
     * Test that question_category_options function returns the correct category tree.
     *
     * @covers ::question_category_options
     * @covers ::get_categories_for_contexts
     * @covers ::question_fix_top_names
     * @covers ::question_add_context_in_key
     * @covers ::add_indented_names
     */
    public function test_question_category_options() {
        $this->setAdminUser();
        $this->resetAfterTest();

        // Create categories.
        $quiz = $this->create_quiz();
        $qcategory1 = $this->create_question_category_for_a_quiz($quiz);
        $this->create_question_category_for_a_quiz($quiz, ['parent' => $qcategory1->id]);
        $this->create_question_category_for_a_quiz($quiz);

        $contexts = new \core_question\local\bank\question_edit_contexts(\context_module::instance($quiz->cmid));

        // Validate that we have the array with the categories tree.
        $categorycontexts = helper::question_category_options($contexts->having_cap('moodle/question:add'));
        foreach ($categorycontexts as $categorycontext) {
            $this->assertCount(3, $categorycontext);
        }

        // Validate that we have the array with the categories tree and that top category is there.
        $categorycontexts = helper::question_category_options($contexts->having_cap('moodle/question:add'), true);
        foreach ($categorycontexts as $categorycontext) {
            $this->assertCount(4, $categorycontext);
        }
    }

    /**
     * Test get children.
     *
     * @covers ::get_children
     */
    public function test_get_children() {
        $this->setAdminUser();
        $this->resetAfterTest();

        $parents = [
            1 => 0,
            2 => 1,
            3 => 1,
            4 => 2,
            5 => 2,
            6 => 1,
            7 => 5
        ];

        // Child of 5.
        $children = helper::get_children(5, $parents);
        $this->assertSame([7], $children);

        // Children of 2 (include child of 5).
        $children = helper::get_children(2, $parents);
        $this->assertSame([4, 5, 7], $children);

        // Children of 1 (all categories).
        $children = helper::get_children(1, $parents);
        // Immediate children: 2,3,6 plus children of 2.
        $this->assertSame([2, 3, 6, 4, 5, 7], $children);
    }

    /**
     * Test get children.
     *
     * @covers ::create_ordered_tree
     * @covers ::get_max_sortorder
     */
    public function test_create_order_tree() {
        $this->setAdminUser();
        $this->resetAfterTest();

        // Create question categories for a course.
        $course = $this->create_course();
        $context = \context_course::instance($course->id);
        $qcat1 = $this->create_question_category_for_a_course($course);
        $this->assertEquals(1, helper::get_max_sortorder($context->id));

        $qcat2 = $this->create_question_category_for_a_course($course, ['parent' => $qcat1->id]);
        $this->assertEquals(2, helper::get_max_sortorder($context->id));

        $qcat3 = $this->create_question_category_for_a_course($course);
        $this->assertEquals(3, helper::get_max_sortorder($context->id));

        $qcat4 = $this->create_question_category_for_a_course($course, ['parent' => $qcat2->id]);
        $this->assertEquals(4, helper::get_max_sortorder($context->id));

        // Create ordered tree.
        $items = helper::get_categories_for_contexts(\context_course::instance($course->id)->id);
        $items = helper::create_ordered_tree($items);

        // Two top categories (1 and 3) in the course.
        $this->assertCount(2, $items);
        $this->assertArrayHasKey($qcat1->id, $items);
        $this->assertArrayHasKey($qcat3->id, $items);

        // Category 2 is the only child of Category 1.
        $children = $items[$qcat1->id]->children;
        $this->assertCount(1, $children);
        $this->assertArrayHasKey($qcat2->id, $children);

        // Category 4 is the only child of Category 2.
        $children = $children[$qcat2->id]->children;
        $this->assertCount(1, $children);
        $this->assertArrayHasKey($qcat4->id, $children);
    }
}
