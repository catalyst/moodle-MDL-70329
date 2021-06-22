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

/**
 * QUESTION_PAGE_LENGTH - Number of categories to display on page.
 */
define('QUESTION_PAGE_LENGTH', 25);

use action_menu;
use action_menu_link;
use context;
use context_system;
use \core\plugininfo\qbank;
use moodle_exception;
use moodle_url;
use pix_icon;
use question_bank;
use stdClass;
use qbank_managecategories\form\question_category_edit_form;
use qbank_managecategories\form\question_category_checkbox_form;

/**
 * Class for performing operations on question categories.
 *
 * @package    qbank_managecategories
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_category_object {

    /**
     * @var array nested lists to display categories.
     */
    public $editlists = [];

    /**
     * @var moodle_url Object representing url for this page
     */
    public $pageurl;

    /**
     * @var question_category_edit_form Object representing form for adding / editing categories.
     */
    public $catform;

    /**
     * @var question_category_checkbox_form Object representing form to display category description.
     */
    public $checkboxform;

    /**
     * @var int cmid.
     */
    public $cmid;

    /**
     * @var int courseid.
     */
    public $courseid;

    /**
     * Constructor.
     *
     * @param int $page page number.
     * @param moodle_url $pageurl base URL of the display categories page. Used for redirects.
     * @param context[] $contexts contexts where the current user can edit categories.
     * @param int $currentcat id of the category to be edited. 0 if none.
     * @param int|null $defaultcategory id of the current category. null if none.
     * @param int $todelete id of the category to delete. 0 if none.
     * @param context[] $addcontexts contexts where the current user can add questions.
     * @param int|null $cmid course module id for the current page.
     * @param int|null $courseid course id for the current page.
     * @param int $thiscontext integer representing course context.
     */
    public function __construct($page, $pageurl, $contexts, $currentcat, $defaultcategory, $todelete, $addcontexts,
                                $cmid = null, $courseid = null, $thiscontext = null) {

        $this->str = new stdClass();
        $this->str->edit = get_string('editthiscategory', 'question');
        $this->cmid = $cmid;
        $this->courseid = $courseid;

        $this->pageurl = $pageurl;
        $this->contextid = $thiscontext;
        $this->initialize($contexts, $currentcat, $defaultcategory, $todelete, $addcontexts, $cmid, $courseid);
    }

    /**
     * Initializes this classes general category-related variables
     *
     * @param context[] $contexts contexts where the current user can edit categories.
     * @param int $currentcat id of the category to be edited. 0 if none.
     * @param int|null $defaultcategory id of the current category. null if none.
     * @param int $todelete id of the category to delete. 0 if none.
     * @param context[] $addcontexts contexts where the current user can add questions.
     * @param int|null $cmid course module id for the current page.
     * @param int|null $courseid course id for the current page.
     */
    public function initialize($contexts, $currentcat, $defaultcategory, $todelete, $addcontexts,
                               $cmid, $courseid): void {

        foreach ($contexts as $context) {
            $items = helper::get_categories_for_contexts($context->id);
            $items = helper::create_ordered_tree($items);
            $this->editlists[$context->id] = (object)[
                'items' => $items,
                'context' => $context
            ];
        }

        $this->catform = new question_category_edit_form($this->pageurl, compact('contexts', 'currentcat'));
        if (!$currentcat) {
            $this->catform->set_data(['parent' => $defaultcategory]);
        }
        // Checkbox Form.
        $checked = get_user_preferences('qbank_managecategories_showdescr');
        $customdata = ['checked' => $checked, 'cmid' => $cmid, 'courseid' => $courseid];
        $this->checkboxform = new question_category_checkbox_form(null, $customdata, 'post', null, ['id' => 'qbshowdescr-form']);
    }

    /**
     * Returns data for categories.
     *
     * @return array $data
     */
    public function categories_data(): array {
        global $OUTPUT;

        $helpstringhead = $OUTPUT->heading_with_help(get_string('editcategories', 'question'), 'editcategories', 'question');
        if (has_capability('moodle/question:managecategory', context::instance_by_id($this->contextid))) {
            $hascapability = true;
        } else {
            $hascapability = false;
        }

        $categories = [];
        foreach ($this->editlists as $contextid => $list) {
            // Get list elements.
            $context = context::instance_by_id($contextid);
            $itemstab = [];
            if (count($list->items)) {
                $lastitem = '';
                foreach ($list->items as $item) {
                    $itemstab['items'][] = $this->item_data($list, $item, $context, $lastitem);
                    $lastitem = $item;
                }
            }
            if (isset($itemstab['items'])) {
                $ctxlvl = "contextlevel" . $list->context->contextlevel;
                $heading = get_string('questioncatsfor', 'question', $list->context->get_context_name());

                // Get categories context.
                $categories[] = [
                    'ctxlvl' => $ctxlvl,
                    'heading' => $heading,
                    'items' => $itemstab['items']
                ];
            }
        }
        $data = [
            'helpstringhead' => $helpstringhead,
            'checkbox' => $this->checkboxform->render(),
            'categoriesrendered' => $categories,
            'hascapability' => $hascapability,
            'contextid' => $this->contextid,
            'cmid' => $this->cmid,
            'courseid' => $this->courseid,
        ];
        return $data;
    }

    /**
     * Creates and returns each item data.
     *
     * @param object $list
     * @param object $category
     * @param context $context
     * @param object|string $lastitem page number
     * @return array $itemdata item data
     */
    public function item_data(object $list, object $category, context $context, $lastitem): array {
        global $OUTPUT, $PAGE;
        if (has_capability('moodle/question:managecategory', $context)) {
            $icons = $this->get_arrow_descendant($category, $lastitem);
        }
        $iconleft = isset($icons['left']) ? $icons['left'] : null;
        $iconright = isset($icons['right']) ? $icons['right'] : null;
        $params = $this->pageurl->params();
        $cmid = $params['cmid'] ?? 0;
        $courseid = $params['courseid'] ?? 0;

        // Each section adds html to be displayed as part of this list item.
        $questionbankurl = new moodle_url('/question/edit.php', $params);
        $questionbankurl->param('cat', helper::combine_id_context($category));
        $categoryname = format_string($category->name, true, ['context' => $list->context]);
        $idnumber = null;
        if ($category->idnumber !== null && $category->idnumber !== '') {
            $idnumber = $category->idnumber;
        }
        $checked = get_user_preferences('qbank_managecategories_showdescr');
        if ($checked) {
            $categorydesc = format_text($category->info, $category->infoformat,
                ['context' => $list->context, 'noclean' => true]);
        } else {
            $categorydesc = '';
        }
        $menu = new action_menu();
        $menu->set_menu_trigger(get_string('edit'));

        // Sets up edit link.
        if (has_capability('moodle/question:managecategory', $context)) {
            $thiscontext = (int)$category->contextid;
            $editurl = new moodle_url('#');
            $menu->add(new action_menu_link(
                $editurl,
                new pix_icon('t/edit', 'edit'),
                get_string('editsettings'),
                false,
                [
                    'data-action' => 'addeditcategory',
                    'data-actiontype' => 'edit',
                    'data-contextid' => $thiscontext,
                    'data-categoryid' => $category->id,
                    'data-cmid' => $cmid,
                    'data-courseid' => $courseid,
                ]
            ));
            // Don't allow delete if this is the top category, or the last editable category in this context.
            if (!helper::question_is_only_child_of_top_category_in_context($category->id)) {
                // Sets up delete link.
                $deleteurl = new moodle_url('/question/bank/managecategories/category.php',
                    ['delete' => $category->id, 'sesskey' => sesskey()]);
                if ($courseid !== 0) {
                    $deleteurl->param('courseid', $courseid);
                } else {
                    $deleteurl->param('cmid', $cmid);
                }
                $menu->add(new action_menu_link(
                    $deleteurl,
                    new pix_icon('t/delete', 'delete'),
                    get_string('delete'),
                    false
                ));
            }
        }

        // Sets up export to XML link.
        if (qbank::is_plugin_enabled('qbank_exportquestions')) {
            $exporturl = new moodle_url('/question/bank/exportquestions/export.php',
                ['cat' => helper::combine_id_context($category)]);
            if ($courseid !== 0) {
                $exporturl->param('courseid', $courseid);
            } else {
                $exporturl->param('cmid', $cmid);
            }

            $menu->add(new action_menu_link(
                $exporturl,
                new pix_icon('t/download', 'download'),
                get_string('exportasxml', 'question'),
                false
            ));
        }

        // Menu to string/html.
        $menu = $OUTPUT->render($menu);
        // Don't allow movement if only subcat.
        $handle = false;
        if (has_capability('moodle/question:managecategory', $context)) {
            if (!helper::question_is_only_child_of_top_category_in_context($category->id)) {
                $handle = true;
            } else {
                $handle = false;
            }
        }

        $children = [];
        if (!empty($category->children)) {
            $lastitem = '';
            foreach ($category->children as $itm) {
                $children[] = $this->item_data($list, $itm, $context, $lastitem);
                $lastitem = $itm;
            }
        }
        $itemdata =
            [
                'categoryid' => $category->id,
                'contextid' => $category->contextid,
                'questionbankurl' => $questionbankurl,
                'categoryname' => $categoryname,
                'idnumber' => $idnumber,
                'questioncount' => $category->questioncount,
                'categorydesc' => $categorydesc,
                'editactionmenu' => $menu,
                'handle' => $handle,
                'iconleft' => $iconleft,
                'iconright' => $iconright,
                'children' => $children
            ];
        return $itemdata;
    }

    /**
     * Gets the arrow for category.
     *
     * @param bool $category Is the first on the list.
     * @param bool $lastitem Is the last on the list.
     * @return array $icons.
     */
    public function get_arrow_descendant($category, $lastitem): array {
        global $OUTPUT, $PAGE;
        $icons = [];
        $strmoveleft = get_string('maketoplevelitem', 'question');
        // Exchange arrows on RTL.
        if (right_to_left()) {
            $rightarrow = 'left';
            $leftarrow = 'right';
        } else {
            $rightarrow = 'right';
            $leftarrow = 'left';
        }

        if (isset($category->parentitem)) {
            if (isset($category->parentitem)) {
                $action = get_string('makechildof', 'question', $category->parentitem->name);
            } else {
                $action = $strmoveleft;
            }
            $pix = new pix_icon('t/' . $leftarrow, $action);
            $icons['left'] = $OUTPUT->action_icon('#', $pix, null,
                ['data-tomove' => $category->id, 'data-tocategory' => $category->parentitem->parent]);
        }

        if (!empty($lastitem)) {
            $makechildof = get_string('makechildof', 'question', $lastitem->name);
            $pix = new pix_icon('t/' . $rightarrow, $makechildof);
            $icons['right'] = $OUTPUT->action_icon('#', $pix, null,
                ['data-tomove' => $category->id, 'data-tocategory' => $lastitem->id]);
        }
        return $icons;
    }

    /**
     * Outputs a table to allow entry of a new category
     */
    public function output_new_table(): void {
        $this->catform->display();
    }

    /**
     * Gets all the courseids for the given categories.
     *
     * @param array $categories contains category objects in  a tree representation
     * @return array courseids flat array in form categoryid=>courseid
     */
    public function get_course_ids(array $categories): array {
        $courseids = [];
        foreach ($categories as $key => $cat) {
            $courseids[$key] = $cat->course;
            if (!empty($cat->children)) {
                $courseids = array_merge($courseids, $this->get_course_ids($cat->children));
            }
        }
        return $courseids;
    }

    /**
     * Edit a category, or add a new one if the id is zero.
     *
     * @param int $categoryid Category id.
     * @deprecated since Moodle 4.0 MDL-72397 - please do not use this function any more.
     * @todo Final deprecation on Moodle 4.4 MDL-72438.
     * @see qbank_managecategories\form\question_category_edit_form::process_dynamic_submission()
     */
    public function edit_single_category(int $categoryid): void {
        debugging('edit_single_category() is deprecated.
            Please use qbank_managecategories\form\question_category_edit_form::process_dynamic_submission() instead.',
            DEBUG_DEVELOPER);
        // Interface for adding a new category.
        global $DB;

        if ($categoryid) {
            // Editing an existing category.
            $category = $DB->get_record("question_categories", ["id" => $categoryid], '*', MUST_EXIST);
            if ($category->parent == 0) {
                throw new moodle_exception('cannotedittopcat', 'question', '', $categoryid);
            }

            $category->parent = "{$category->parent},{$category->contextid}";
            $category->submitbutton = get_string('savechanges');
            $category->categoryheader = $this->str->edit;
            $this->catform->set_data($category);
        }

        // Show the form.
        $this->catform->display();
    }

    /**
     * Sets the viable parents.
     *
     *  Viable parents are any except for the category itself, or any of it's descendants
     *  The parentstrings parameter is passed by reference and changed by this function.
     *
     * @param array $parentstrings a list of parentstrings
     * @param object $category Category object
     */
    public function set_viable_parents(array &$parentstrings, object $category): void {

        unset($parentstrings[$category->id]);
        if (isset($category->children)) {
            foreach ($category->children as $child) {
                $this->set_viable_parents($parentstrings, $child);
            }
        }
    }

    /**
     * Gets question categories.
     *
     * @param int|null $parent - if given, restrict records to those with this parent id.
     * @param string $sort - [[sortfield [,sortfield]] {ASC|DESC}].
     * @return array categories.
     */
    public function get_question_categories(int $parent = null, string $sort = "sortorder ASC"): array {
        global $COURSE, $DB;
        if (is_null($parent)) {
            $categories = $DB->get_records('question_categories', ['course' => $COURSE->id], $sort);
        } else {
            $select = "parent = ? AND course = ?";
            $categories = $DB->get_records_select('question_categories', $select, [$parent, $COURSE->id], $sort);
        }
        return $categories;
    }

    /**
     * Deletes an existing question category.
     *
     * @param int $categoryid id of category to delete.
     */
    public function delete_category(int $categoryid): void {
        global $CFG, $DB;
        helper::question_can_delete_cat($categoryid);
        if (!$category = $DB->get_record("question_categories", ["id" => $categoryid])) {  // Security.
            throw new moodle_exception('unknowcategory');
        }
        // Send the children categories to live with their grandparent.
        $DB->set_field("question_categories", "parent", $category->parent, ["parent" => $category->id]);

        // Finally delete the category itself.
        $DB->delete_records("question_categories", ["id" => $category->id]);

        // Log the deletion of this category.
        $event = \core\event\question_category_deleted::create_from_question_category_instance($category);
        $event->add_record_snapshot('question_categories', $category);
        $event->trigger();

    }

    /**
     * Move questions and then delete the category.
     *
     * @param int $oldcat id of the old category.
     * @param int $newcat id of the new category.
     */
    public function move_questions_and_delete_category(int $oldcat, int $newcat): void {
        helper::question_can_delete_cat($oldcat);
        $this->move_questions($oldcat, $newcat);
        $this->delete_category($oldcat);
    }

    /**
     * Display the form to move a category.
     *
     * @param int $questionsincategory
     * @param object $category
     * @throws \coding_exception
     */
    public function display_move_form($questionsincategory, $category): void {
        global $OUTPUT;
        $vars = new stdClass();
        $vars->name = $category->name;
        $vars->count = $questionsincategory;
        echo $OUTPUT->box(get_string('categorymove', 'question', $vars), 'generalbox boxaligncenter');
        $this->moveform->display();
    }

    /**
     * Move questions to another category.
     *
     * @param int $oldcat id of the old category.
     * @param int $newcat id of the new category.
     * @throws \dml_exception
     */
    public function move_questions(int $oldcat, int $newcat): void {
        $questionids = $this->get_real_question_ids_in_category($oldcat);
        question_move_questions_to_category($questionids, $newcat);
    }

    /**
     * Create a new category.
     *
     * Data is expected to come from question_category_edit_form.
     *
     * By default redirects on success, unless $return is true.
     *
     * @param string $newparent 'categoryid,contextid' of the parent category.
     * @param string $newcategory the name.
     * @param string $newinfo the description.
     * @param bool $return if true, return rather than redirecting.
     * @param int|string $newinfoformat description format. One of the FORMAT_ constants.
     * @param null $idnumber the idnumber. '' is converted to null.
     * @return bool|int New category id if successful, else false.
     */
    public function add_category($newparent, $newcategory, $newinfo, $return = false, $newinfoformat = FORMAT_HTML,
                                 $idnumber = null): int {
        global $DB;
        if (empty($newcategory)) {
            throw new moodle_exception('categorynamecantbeblank', 'question');
        }
        list($parentid, $contextid) = explode(',', $newparent);
        // ...moodle_form makes sure select element output is legal no need for further cleaning.
        require_capability('moodle/question:managecategory', context::instance_by_id($contextid));

        if ($parentid) {
            if (!($DB->get_field('question_categories', 'contextid', ['id' => $parentid]) == $contextid)) {
                throw new moodle_exception('cannotinsertquestioncatecontext', 'question', '',
                    ['cat' => $newcategory, 'ctx' => $contextid]);
            }
        }

        if ((string)$idnumber === '') {
            $idnumber = null;
        } else if (!empty($contextid)) {
            // While this check already exists in the form validation, this is a backstop preventing unnecessary errors.
            if ($DB->record_exists('question_categories',
                    ['idnumber' => $idnumber, 'contextid' => $contextid])) {
                $idnumber = null;
            }
        }

        $cat = new stdClass();
        $cat->parent = $parentid;
        $cat->contextid = $contextid;
        $cat->name = $newcategory;
        $cat->info = $newinfo;
        $cat->infoformat = $newinfoformat;
        $cat->sortorder = helper::get_max_sortorder($contextid) + 1;
        $cat->stamp = make_unique_id_code();
        $cat->idnumber = $idnumber;
        $categoryid = $DB->insert_record("question_categories", $cat);

        // Log the creation of this category.
        $category = new stdClass();
        $category->id = $categoryid;
        $category->contextid = $contextid;
        $event = \core\event\question_category_created::create_from_question_category_instance($category);
        $event->trigger();

        if ($return) {
            return $categoryid;
        } else {
            // Always redirect after successful action.
            redirect($this->pageurl);
        }
    }

    /**
     * Updates an existing category with given params.
     *
     * Warning! parameter order and meaning confusingly different from add_category in some ways!
     *
     * @param int $updateid id of the category to update.
     * @param int $newparent 'categoryid,contextid' of the parent category to set.
     * @param string $newname category name.
     * @param string $newinfo category description.
     * @param int|string $newinfoformat description format. One of the FORMAT_ constants.
     * @param int $idnumber the idnumber. '' is converted to null.
     * @param bool $redirect if true, will redirect once the DB is updated (default).
     * @deprecated since Moodle 4.0 MDL-72397 - please do not use this function any more.
     * @todo Final deprecation on Moodle 4.4 MDL-72438.
     * @see qbank_managecategories\form\question_category_edit_form::process_dynamic_submission()
     */
    public function update_category($updateid, $newparent, $newname, $newinfo, $newinfoformat = FORMAT_HTML,
                                    $idnumber = null, $redirect = true): void {
        debugging('update_category() is deprecated.
            Please use qbank_managecategories\form\question_category_edit_form::process_dynamic_submission() instead.',
            DEBUG_DEVELOPER);
        global $CFG, $DB;
        if (empty($newname)) {
            throw new moodle_exception('categorynamecantbeblank', 'question');
        }

        // Get the record we are updating.
        $oldcat = $DB->get_record('question_categories', ['id' => $updateid]);
        $lastcategoryinthiscontext = helper::question_is_only_child_of_top_category_in_context($updateid);

        if (!empty($newparent) && !$lastcategoryinthiscontext) {
            list($parentid, $tocontextid) = explode(',', $newparent);
        } else {
            $parentid = $oldcat->parent;
            $tocontextid = $oldcat->contextid;
        }

        // Check permissions.
        $fromcontext = context::instance_by_id($oldcat->contextid);
        require_capability('moodle/question:managecategory', $fromcontext);

        // If moving to another context, check permissions some more, and confirm contextid,stamp uniqueness.
        $newstamprequired = false;
        if ($oldcat->contextid != $tocontextid) {
            $tocontext = context::instance_by_id($tocontextid);
            require_capability('moodle/question:managecategory', $tocontext);

            // Confirm stamp uniqueness in the new context. If the stamp already exists, generate a new one.
            if ($DB->record_exists('question_categories', ['contextid' => $tocontextid, 'stamp' => $oldcat->stamp])) {
                $newstamprequired = true;
            }
        }

        if ((string)$idnumber === '') {
            $idnumber = null;
        } else if (!empty($tocontextid)) {
            // While this check already exists in the form validation, this is a backstop preventing unnecessary errors.
            if ($DB->record_exists_select('question_categories',
                    'idnumber = ? AND contextid = ? AND id <> ?',
                    [$idnumber, $tocontextid, $updateid])) {
                $idnumber = null;
            }
        }

        // Update the category record.
        $cat = new stdClass();
        $cat->id = $updateid;
        $cat->name = $newname;
        $cat->info = $newinfo;
        $cat->infoformat = $newinfoformat;
        $cat->parent = $parentid;
        $cat->contextid = $tocontextid;
        $cat->idnumber = $idnumber;
        if ($newstamprequired) {
            $cat->stamp = make_unique_id_code();
        }
        $DB->update_record('question_categories', $cat);

        // Log the update of this category.
        $event = \core\event\question_category_updated::create_from_question_category_instance($cat);
        $event->trigger();

        // If the category name has changed, rename any random questions in that category.
        if ($oldcat->name != $cat->name) {
            // Get the question ids for each question category.
            $questionids = $this->get_real_question_ids_in_category($cat->id);

            foreach ($questionids as $question) {
                $where = "qtype = 'random' AND id = ? AND " . $DB->sql_compare_text('questiontext') . " = ?";

                $randomqtype = question_bank::get_qtype('random');
                $randomqname = $randomqtype->question_name($cat, false);
                $DB->set_field_select('question', 'name', $randomqname, $where, [$question->id, '0']);

                $randomqname = $randomqtype->question_name($cat, true);
                $DB->set_field_select('question', 'name', $randomqname, $where, [$question->id, '1']);
            }
        }

        if ($oldcat->contextid != $tocontextid) {
            // Moving to a new context. Must move files belonging to questions.
            question_move_category_to_context($cat->id, $oldcat->contextid, $tocontextid);
        }

        // Cat param depends on the context id, so update it.
        $this->pageurl->param('cat', $updateid . ',' . $tocontextid);
        if ($redirect) {
            // Always redirect after successful action.
            redirect($this->pageurl);
        }
    }

    /**
     * Returns ids of the question in the given question category.
     *
     * This method only returns the real question. It does not include
     * subquestions of question types like multianswer.
     *
     * @param int $categoryid id of the category.
     * @return int[] array of question ids.
     */
    public function get_real_question_ids_in_category(int $categoryid): array {
        global $DB;

        $sql = "SELECT q.id
                  FROM {question} q
                  JOIN {question_versions} qv ON qv.questionid = q.id
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                 WHERE qbe.questioncategoryid = :categoryid
                   AND (q.parent = 0 OR q.parent = q.id)";

        $questionids = $DB->get_records_sql($sql, ['categoryid' => $categoryid]);
        return array_keys($questionids);
    }
}
