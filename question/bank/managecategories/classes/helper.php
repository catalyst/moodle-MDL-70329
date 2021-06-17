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
 * Library functions used by qbank_managecategories.
 *
 * This code is based on lib/questionlib.php by Martin Dougiamas.
 *
 * @package    qbank_managecategories
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_managecategories;

use context;
use moodle_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class helper contains all the library functions.
 *
 * @package helper
 */
class helper {

    /**
     * Name of this plugin.
     */
    const PLUGINNAME = 'qbank_managecategories';

    /**
     * Remove stale questions from a category.
     *
     * While questions should not be left behind when they are not used any more,
     * it does happen, maybe via restore, or old logic, or uncovered scenarios. When
     * this happens, the users are unable to delete the question category unless
     * they move those stale questions to another one category, but to them the
     * category is empty as it does not contain anything. The purpose of this function
     * is to detect the questions that may have gone stale and remove them.
     *
     * You will typically use this prior to checking if the category contains questions.
     *
     * The stale questions (unused and hidden to the user) handled are:
     * - hidden questions
     * - random questions
     *
     * @param int $categoryid The category ID.
     * @throws \dml_exception
     */
    public static function question_remove_stale_questions_from_category(int $categoryid): void {
        global $DB;

        $select = 'category = :categoryid AND (qtype = :qtype OR hidden = :hidden)';
        $params = ['categoryid' => $categoryid, 'qtype' => 'random', 'hidden' => 1];
        $questions = $DB->get_recordset_select("question", $select, $params, '', 'id');
        foreach ($questions as $question) {
            // The function question_delete_question does not delete questions in use.
            question_delete_question($question->id);
        }
        $questions->close();
    }

    /**
     * Checks whether this is the only child of a top category in a context.
     *
     * @param int $categoryid a category id.
     * @return bool
     * @throws \dml_exception
     */
    public static function question_is_only_child_of_top_category_in_context(int $categoryid): bool {
        global $DB;
        return 1 == $DB->count_records_sql("
            SELECT count(*)
              FROM {question_categories} c
              JOIN {question_categories} p ON c.parent = p.id
              JOIN {question_categories} s ON s.parent = c.parent
             WHERE c.id = ? AND p.parent = 0", array($categoryid));
    }

    /**
     * Checks whether the category is a "Top" category (with no parent).
     *
     * @param int $categoryid a category id.
     * @return bool
     * @throws \dml_exception
     */
    protected static function question_is_top_category(int $categoryid): bool {
        global $DB;
        return 0 == $DB->get_field('question_categories', 'parent', array('id' => $categoryid));
    }

    /**
     * Ensures that this user is allowed to delete this category.
     *
     * @param int $todelete a category id.
     * @throws \required_capability_exception
     * @throws \dml_exception|moodle_exception
     */
    public static function question_can_delete_cat(int $todelete): void {
        global $DB;
        if (self::question_is_top_category($todelete)) {
            throw new moodle_exception('cannotdeletetopcat', 'question');
        } else if (self::question_is_only_child_of_top_category_in_context($todelete)) {
            throw new moodle_exception('cannotdeletecate', 'question');
        } else {
            $contextid = $DB->get_field('question_categories', 'contextid', array('id' => $todelete));
            require_capability('moodle/question:managecategory', context::instance_by_id($contextid));
        }
    }
}
