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
use html_writer;

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

    /**
     * Private method, only for the use of add_indented_names().
     *
     * Recursively adds an indentedname field to each category, starting with the category
     * with id $id, and dealing with that category and all its children, and
     * return a new array, with those categories in the right order.
     *
     * @param array $categories an array of categories which has had childids
     *          fields added by flatten_category_tree(). Passed by reference for
     *          performance only. It is not modfied.
     * @param int $id the category to start the indenting process from.
     * @param int $depth the indent depth. Used in recursive calls.
     * @param int $nochildrenof
     * @return array a new array of categories, in the right order for the tree.
     */
    protected static function flatten_category_tree(array &$categories, $id, int $depth = 0, int $nochildrenof = -1): array {

        // Indent the name of this category.
        $newcategories = [];
        $newcategories[$id] = $categories[$id];
        $newcategories[$id]->indentedname = str_repeat('&nbsp;&nbsp;&nbsp;', $depth) .
            $categories[$id]->name;

        // Recursively indent the children.
        foreach ($categories[$id]->childids as $childid) {
            if ($childid != $nochildrenof) {
                $newcategories = $newcategories + self::flatten_category_tree(
                        $categories, $childid, $depth + 1, $nochildrenof);
            }
        }

        // Remove the childids array that were temporarily added.
        unset($newcategories[$id]->childids);

        return $newcategories;
    }

    /**
     * Format categories into an indented list reflecting the tree structure.
     *
     * @param array $categories An array of category objects, for example from the.
     * @param int $nochildrenof
     * @return array The formatted list of categories.
     */
    protected static function add_indented_names(array $categories, int $nochildrenof = -1): array {

        // Add an array to each category to hold the child category ids. This array
        // will be removed again by flatten_category_tree(). It should not be used
        // outside these two functions.
        foreach (array_keys($categories) as $id) {
            $categories[$id]->childids = array();
        }

        // Build the tree structure, and record which categories are top-level.
        // We have to be careful, because the categories array may include published
        // categories from other courses, but not their parents.
        $toplevelcategoryids = array();
        foreach (array_keys($categories) as $id) {
            if (!empty($categories[$id]->parent) &&
                array_key_exists($categories[$id]->parent, $categories)) {
                $categories[$categories[$id]->parent]->childids[] = $id;
            } else {
                $toplevelcategoryids[] = $id;
            }
        }

        // Flatten the tree to and add the indents.
        $newcategories = array();
        foreach ($toplevelcategoryids as $id) {
            $newcategories = $newcategories + self::flatten_category_tree(
                    $categories, $id, 0, $nochildrenof);
        }

        return $newcategories;
    }

    /**
     * Output a select menu of question categories.
     *
     * Categories from this course and (optionally) published categories from other courses
     * are included. Optionally, only categories the current user may edit can be included.
     *
     * @param array $contexts
     * @param bool $top
     * @param int $currentcat
     * @param string $selected optionally, the id of a category to be selected by
     *      default in the dropdown.
     * @param int $nochildrenof
     * @throws \coding_exception|\dml_exception
     */
    public static function question_category_select_menu(array $contexts, bool $top = false, int $currentcat = 0,
                                           string $selected = "", int $nochildrenof = -1): void {
        $categoriesarray = self::question_category_options($contexts, $top, $currentcat,
            false, $nochildrenof, false);
        if ($selected) {
            $choose = '';
        } else {
            $choose = 'choosedots';
        }
        $options = array();
        foreach ($categoriesarray as $group => $opts) {
            $options[] = array($group => $opts);
        }
        echo html_writer::label(get_string('questioncategory', 'core_question'), 'id_movetocategory', false, array('class' => 'accesshide'));
        $attrs = array(
            'id' => 'id_movetocategory',
            'class' => 'custom-select',
            'data-action' => 'toggle',
            'data-togglegroup' => 'qbank',
            'data-toggle' => 'action',
            'disabled' => true,
        );
        echo html_writer::select($options, 'category', $selected, $choose, $attrs);
    }

    /**
     * Get all the category objects, including a count of the number of questions in that category,
     * for all the categories in the lists $contexts.
     *
     * @param mixed $contexts either a single contextid, or a comma-separated list of context ids.
     * @param string $sortorder used as the ORDER BY clause in the select statement.
     * @param bool $top Whether to return the top categories or not.
     * @return array of category objects.
     * @throws \dml_exception
     */
    public static function get_categories_for_contexts($contexts, string $sortorder = 'parent, sortorder, name ASC',
                                                       bool $top = false): array {
        global $DB;
        $topwhere = $top ? '' : 'AND c.parent <> 0';
        return $DB->get_records_sql("
            SELECT c.*, (SELECT count(1) FROM {question} q
                        WHERE c.id = q.category AND q.hidden='0' AND q.parent='0') AS questioncount
              FROM {question_categories} c
             WHERE c.contextid IN ($contexts) $topwhere
          ORDER BY $sortorder");
    }

    /**
     * Output an array of question categories.
     *
     * @param array $contexts The list of contexts.
     * @param bool $top Whether to return the top categories or not.
     * @param int $currentcat
     * @param bool $popupform
     * @param int $nochildrenof
     * @param boolean $escapecontextnames Whether the returned name of the thing is to be HTML escaped or not.
     * @return array
     * @throws \coding_exception|\dml_exception
     */
    public static function question_category_options(array $contexts, bool $top = false, int $currentcat = 0,
                                                     bool $popupform = false, int $nochildrenof = -1,
                                                     bool $escapecontextnames = true): array {
        global $CFG;
        $pcontexts = array();
        foreach ($contexts as $context) {
            $pcontexts[] = $context->id;
        }
        $contextslist = join(', ', $pcontexts);

        $categories = self::get_categories_for_contexts($contextslist, 'parent, sortorder, name ASC', $top);

        if ($top) {
            $categories = self::question_fix_top_names($categories);
        }

        $categories = self::question_add_context_in_key($categories);
        $categories = self::add_indented_names($categories, $nochildrenof);

        // sort cats out into different contexts
        $categoriesarray = array();
        foreach ($pcontexts as $contextid) {
            $context = \context::instance_by_id($contextid);
            $contextstring = $context->get_context_name(true, true, $escapecontextnames);
            foreach ($categories as $category) {
                if ($category->contextid == $contextid) {
                    $cid = $category->id;
                    if ($currentcat != $cid || $currentcat == 0) {
                        $a = new \stdClass;
                        $a->name = format_string($category->indentedname, true,
                            array('context' => $context));
                        if ($category->idnumber !== null && $category->idnumber !== '') {
                            $a->idnumber = s($category->idnumber);
                        }
                        if (!empty($category->questioncount)) {
                            $a->questioncount = $category->questioncount;
                        }
                        if (isset($a->idnumber) && isset($a->questioncount)) {
                            $formattedname = get_string('categorynamewithidnumberandcount', 'question', $a);
                        } else if (isset($a->idnumber)) {
                            $formattedname = get_string('categorynamewithidnumber', 'question', $a);
                        } else if (isset($a->questioncount)) {
                            $formattedname = get_string('categorynamewithcount', 'question', $a);
                        } else {
                            $formattedname = $a->name;
                        }
                        $categoriesarray[$contextstring][$cid] = $formattedname;
                    }
                }
            }
        }
        if ($popupform) {
            $popupcats = array();
            foreach ($categoriesarray as $contextstring => $optgroup) {
                $group = array();
                foreach ($optgroup as $key => $value) {
                    $key = str_replace($CFG->wwwroot, '', $key);
                    $group[$key] = $value;
                }
                $popupcats[] = array($contextstring => $group);
            }
            return $popupcats;
        } else {
            return $categoriesarray;
        }
    }

    /**
     * Add context in categories key.
     *
     * @param array $categories The list of categories.
     * @return array
     */
    protected static function question_add_context_in_key(array $categories): array {
        $newcatarray = array();
        foreach ($categories as $id => $category) {
            $category->parent = "$category->parent,$category->contextid";
            $category->id = "$category->id,$category->contextid";
            $newcatarray["$id,$category->contextid"] = $category;
        }
        return $newcatarray;
    }

    /**
     * Finds top categories in the given categories hierarchy and replace their name with a proper localised string.
     *
     * @param array $categories An array of question categories.
     * @param boolean $escape Whether the returned name of the thing is to be HTML escaped or not.
     * @return array The same question category list given to the function, with the top category names being translated.
     * @throws \coding_exception
     */
    protected static function question_fix_top_names(array $categories, bool $escape = true): array {

        foreach ($categories as $id => $category) {
            if ($category->parent == 0) {
                $context = \context::instance_by_id($category->contextid);
                $categories[$id]->name = get_string('topfor', 'question', $context->get_context_name(false, false, $escape));
            }
        }

        return $categories;
    }
}
