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

use qbank_managecategories\external\update_category_order;

/**
 * Unit tests for qbank_managecategories enhancememt component.
 *
 * @package    qbank_managecategories
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \qbank_managecategories\external\update_category_order
 */
class category_order_update_test extends manage_category_test_base {
    /**
     * Return order of those categories grouped by contexts
     * @param array $categories
     * @return string category order
     */
    private function encode_question_category_order(array $categories): string {
        // Group array by contexts.
        $categoriesbycontext = [];
        foreach ($categories as $category) {
            $categoriesbycontext[$category->contextid][] = helper::combine_id_context($category);
        }

        // Remove key.
        $ordertoset = [];
        foreach ($categoriesbycontext as $categories) {
            $ordertoset[] = $categories;
        }

        return json_encode($ordertoset);
    }

    /**
     * Return order of specified categories
     *
     * @param array $categoryids ids of categories
     * @return string show the category order
     */
    private function get_current_order(array $categoryids): string {
        global $DB;
        list($wheresql, $params) = $DB->get_in_or_equal($categoryids);
        $wheresql = "id $wheresql";
        $newcategories = $DB->get_records_select('question_categories', $wheresql, $params, 'sortorder ASC');
        return $this->encode_question_category_order($newcategories);
    }

    /**
     * Tests setting a new category order.
     *
     * @covers ::execute
     */
    public function test_update_category_order() {
        global $DB;
        $this->setAdminUser();
        $this->resetAfterTest();

        // Create context for question categories.
        $course = $this->create_course();
        $coursecategory = $this->create_course_category();

        // Question categories.
        $qcat1 = $this->create_question_category_for_a_course($course);
        $qcat2 = $this->create_question_category_for_a_course($course);
        $qcat3 = $this->create_question_category_for_a_course_category($coursecategory);
        $qcat4 = $this->create_question_category_for_a_course_category($coursecategory);
        $qcat5 = $this->create_question_category_for_the_system();
        $categories = [$qcat1->id, $qcat2->id, $qcat3->id, $qcat4->id, $qcat5->id];

        // Check current order.
        $currentorder = $this->get_current_order($categories);
        $expectedorder = '['
            . '["' . helper::combine_id_context($qcat1) . '","' . helper::combine_id_context($qcat2) . '"],'
            . '["' . helper::combine_id_context($qcat3) . '","' . helper::combine_id_context($qcat4) . '"],'
            . '["' . helper::combine_id_context($qcat5) . '"]'
            . ']';
        $this->assertSame($expectedorder, $currentorder);

        // Move question category 2 to after question category 3.
        update_category_order::execute($qcat2->id, $qcat3->id);

        // Get new sort order of those question categories.
        $neworder = $this->get_current_order($categories);
        // Refresh value of question category 2.
        $qcat2 = $DB->get_record('question_categories', ['id' => $qcat2->id]);
        $newexpectedorder = '['
            . '["' . helper::combine_id_context($qcat1) . '"],'
            . '["'
            . helper::combine_id_context($qcat3) . '","'
            . helper::combine_id_context($qcat2) . '","'
            . helper::combine_id_context($qcat4)
            . '"],'
            . '["' . helper::combine_id_context($qcat5) . '"]'
            . ']';
        $this->assertSame($newexpectedorder, $neworder);
    }

    /**
     * Tests updating a category parent.
     *
     * @covers ::execute
     */
    public function test_update_category_parent() {
        $this->setAdminUser();
        $this->resetAfterTest();

        // Create a course category.
        $coursecategory = $this->create_course_category();

        // Create a question category for the course category.
        $categoryqcat = $this->create_question_category_for_a_course_category($coursecategory);

        // Create a question category for the system.
        $systemqcat = $this->create_question_category_for_the_system();

        // The question category of "course category" is not on the system.
        $parent = $this->get_parent_of_a_question_category($categoryqcat->id);
        $this->assertNotEquals($systemqcat->id, $parent);

        // Move question category of "course category" to system.
        update_category_order::execute($categoryqcat->id, 0, $systemqcat->id);
        $newparent = $this->get_parent_of_a_question_category($categoryqcat->id);
        $this->assertEquals($systemqcat->id, $newparent);
    }
}
