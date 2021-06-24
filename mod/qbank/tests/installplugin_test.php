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
 * Testing method get_categories_populated, create_category_course and get_course in helper class.
 *
 * @package    mod_qbank
 * @category   test
 * @copyright  2021 Catalyst IT Australia Ltd
 * @author     Guillermo Gomez Arias <guillermogomez@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_qbank;

use context_coursecat;

defined('MOODLE_INTERNAL') || die();

class installplugin_test extends \advanced_testcase {

    /**
     *  Test get_categories_populated function in helper class.
     */
    public function test_populated_only(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $category1 = $this->getDataGenerator()->create_category();
        $catcontext = context_coursecat::instance($category1->id);
        $qcategory1 = $generator->create_question_category(
            [
                'name' => 'This category is populated',
                'contextid' => $catcontext->id
            ]
        );

        $qcategory2 = $generator->create_question_category(['name' => 'This category is also populated']);
        $qcategory3 = $generator->create_question_category(['name' => 'This category is not populated']);

        $generator->create_question('description', null, ['category' => $qcategory1->id]);
        $generator->create_question('description', null, ['category' => $qcategory2->id]);

        // Test that after executing get_categories_populated we get only populated categories.
        $retrievedcat = helper::get_categories_populated();
        $this->assertCount(2, $retrievedcat);
    }

    /**
     *  Test the course creation in helper class.
     */
    public function test_course_creation(): void {
        $this->resetAfterTest();

        $category1 = $this->getDataGenerator()->create_category();
        $courseshortname1 = substr(CONTEXT_COURSECAT . '-' . $category1->id .': This is some course', 0, 255);
        $course1 = $this->getDataGenerator()->create_course(['shortname' => $courseshortname1, 'category' => $category1->id]);

        $category2 = $this->getDataGenerator()->create_category();
        $courseshortname2 = substr(CONTEXT_COURSECAT . '-' . $category2->id .': This is some course', 0, 255);
        $coursename2 = substr("Question bank: This course does not exist yet", 0, 255);

        // Test that course_exists function returns true for courses that already exist.
        $courseexists = helper::get_course($course1->shortname, $category1->id);
        $this->assertEquals($course1->shortname, $courseexists->shortname);

        // Test that course does not exist before being created.
        $course2 = helper::get_course($courseshortname2, $category2->id);
        $this->assertNull($course2);

        // Test that course exists after being created with create_category_course function.
        if (!$course2) {
            $course2 = helper::create_category_course($courseshortname2, $category2->id);
        }
        $this->assertEquals($courseshortname2, $course2->shortname);
    }

    /**
     *  Test the mod_qbank creation in helper class.
     */
    function test_qbank_instancecreated(): void {
        $this->resetAfterTest();

        $category = $this->getDataGenerator()->create_category();
        $courseshortname = substr(CONTEXT_COURSECAT . '-' . $category->id .': This is some course', 0, 254);
        $course = $this->getDataGenerator()->create_course(['shortname' => $courseshortname, 'category' => $category->id]);

        // Test create_qbank_instance function.
        $qbank = helper::create_qbank_instance($courseshortname, $course);
        $this->assertEquals($qbank->name, $courseshortname);
    }

    /**
     *  Test the question categories migration in helper class.
     */
    function test_question_categories_migration(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');

        // Create a question category in a system context.
        $systemcontext = \context_system::instance()->id;
        $generator->create_question_category(['name' => 'Question category - System context', 'contextid' => $systemcontext]);

        // Create question category in a category context.
        $category = $this->getDataGenerator()->create_category();
        $categorycontext = context_coursecat::instance($category->id)->id;
        $generator->create_question_category(['name' => 'Question category - Category context', 'contextid' => $categorycontext]);

        // Create a question category in a course context.
        $course = $this->getDataGenerator()->create_course(['category' => $category->id]);
        $coursecontext = \context_course::instance($course->id)->id;
        $generator->create_question_category(['name' => 'Question category - Course context', 'contextid' => $coursecontext]);

        // Create a question category in a quiz context.
        $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $quizgenerator->create_instance(['course' => $course->id]);
        $quizcontext = \context_module::instance($quiz->cmid)->id;
        $generator->create_question_category(['name' => 'Question category - Quiz context', 'contextid' => $quizcontext]);

        // Create mod_qbank instances.
        $qbanksystemcontext = helper::create_qbank_instance('Question bank System context', get_course(SITEID));
        $categorycourse = helper::create_category_course('Category course', $category->id);
        $qbankcategorycontext = helper::create_qbank_instance('Question bank Category context', $categorycourse);
        $qbankcoursecontext = helper::create_qbank_instance('Question bank System context', $course);
        $qbankquizcontext = helper::create_qbank_instance('Question bank Quiz context', $course);

        // Test that the categories of questions (system context) belong to the correct context before and after being migrated.
        $this->assertCount(1, \qbank_managecategories\helper::get_categories_for_contexts($systemcontext));
        helper::migrate_question_categories($qbanksystemcontext, $systemcontext);
        $this->assertCount(0, \qbank_managecategories\helper::get_categories_for_contexts($systemcontext));
        $this->assertCount(1, \qbank_managecategories\helper::get_categories_for_contexts(\context_module::instance($qbanksystemcontext->coursemodule)->id));

        // Test that the categories of questions (category context) belong to the correct context before and after being migrated.
        $this->assertCount(1, \qbank_managecategories\helper::get_categories_for_contexts($categorycontext));
        helper::migrate_question_categories($qbankcategorycontext, $categorycontext);
        $this->assertCount(0, \qbank_managecategories\helper::get_categories_for_contexts($categorycontext));
        $this->assertCount(1, \qbank_managecategories\helper::get_categories_for_contexts(\context_module::instance($qbankcategorycontext->coursemodule)->id));

        // Test that the categories of questions (course context) belong to the correct context before and after being migrated.
        $this->assertCount(1, \qbank_managecategories\helper::get_categories_for_contexts($coursecontext));
        helper::migrate_question_categories($qbankcoursecontext, $coursecontext);
        $this->assertCount(0, \qbank_managecategories\helper::get_categories_for_contexts($coursecontext));
        $this->assertCount(1, \qbank_managecategories\helper::get_categories_for_contexts(\context_module::instance($qbankcoursecontext->coursemodule)->id));

        // Test that the categories of questions (quiz context) belong to the correct context before and after being migrated.
        $this->assertCount(1, \qbank_managecategories\helper::get_categories_for_contexts($quizcontext));
        helper::migrate_question_categories($qbankquizcontext, $quizcontext);
        $this->assertCount(0, \qbank_managecategories\helper::get_categories_for_contexts($quizcontext));
        $this->assertCount(1, \qbank_managecategories\helper::get_categories_for_contexts(\context_module::instance($qbankquizcontext->coursemodule)->id));
    }
}
