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

use action_menu;
use action_menu_link;
use context_system;
use list_item;
use moodle_url;
use navigation_node;
use pix_icon;

/**
 * An item in a list of question categories.
 *
 * @package    qbank_managecategories
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_category_list_item extends list_item {

    /**
     * Override set_icon_html function.
     *
     * @param bool $first Is the first on the list.
     * @param bool $last Is the last on the list.
     * @param \list_item $lastitem Last item.
     */
    public function set_icon_html($first, $last, $lastitem) : void {
        global $CFG;
        $strmoveleft = get_string('maketoplevelitem', 'question');
        if (right_to_left()) {   // Exchange arrows on RTL
            $rightarrow = 'left';
            $leftarrow  = 'right';
        } else {
            $rightarrow = 'right';
            $leftarrow  = 'left';
        }

        if (isset($this->parentlist->parentitem)) {
            $parentitem = $this->parentlist->parentitem;
            if (isset($parentitem->parentlist->parentitem)) {
                $action = get_string('makechildof', 'question', $parentitem->parentlist->parentitem->name);
            } else {
                $action = $strmoveleft;
            }
            $url = new moodle_url($this->parentlist->pageurl, (['sesskey' => sesskey(), 'left' => $this->id]));
            $this->icons['left'] = $this->image_icon($action, $url, $leftarrow);
        } else {
            $this->icons['left'] = $this->image_spacer();
        }

        if (!empty($lastitem)) {
            $makechildof = get_string('makechildof', 'question', $lastitem->name);
            $url = new moodle_url($this->parentlist->pageurl, (['sesskey' => sesskey(), 'right' => $this->id]));
            $this->icons['right'] = $this->image_icon($makechildof, $url, $rightarrow);
        } else {
            $this->icons['right'] = $this->image_spacer();
        }
    }

    /**
     * Override item_html function.
     *
     * @param array $extraargs
     * @return string Item html.
     * @throws \moodle_exception
     */
    public function item_html($extraargs = []) : string {
        global $PAGE, $OUTPUT;
        $str = $extraargs['str'];
        $category = $this->item;
        $context = context_system::instance();
        $cmid = optional_param('cmid', 0, PARAM_INT);
        $courseid = optional_param('courseid', 0, PARAM_INT);
        // Each section adds html to be displayed as part of this list item.
        $nodeparent = $PAGE->settingsnav->find('questionbank', navigation_node::TYPE_CONTAINER);
        $questionbankurl = new moodle_url($nodeparent->action->get_path(), $this->parentlist->pageurl->params());
        $questionbankurl->param('cat', $category->id . ',' . $category->contextid);
        $categoryname = format_string($category->name, true, ['context' => $this->parentlist->context]);
        $idnumber = null;
        if ($category->idnumber !== null && $category->idnumber !== '') {
            $idnumber = $category->idnumber;
        }
        $questioncount = ' (' . $category->questioncount . ')';
        $checked = get_user_preferences('question_bank_qbshowdescr');
        if ($checked) {
            $categorydesc = format_text($category->info, $category->infoformat,
                ['context' => $this->parentlist->context, 'noclean' => true]);
        } else {
            $categorydesc = '';
        }
        $menu = new action_menu();
        $menu->set_menu_trigger(get_string('edit'));
        if ($this->children->editable) {
            // Sets up edit link.
            if (has_capability('moodle/category:manage', $context)) {
                $thiscontext = (int)$this->item->contextid;
                $editurl = new moodle_url('#');
                $selector = '[data-action=editcategory-'. $category->id .']';
                $PAGE->requires->js_call_amd('qbank_managecategories/addcategory_dialogue', 'initModal',
                    [$selector, $thiscontext, $category->id]);
                $menu->add(new action_menu_link(
                    $editurl,
                    new pix_icon('t/edit', 'edit'),
                    get_string('editsettings'),
                    false,
                    ['data-action' => "editcategory-{$category->id}"]
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
        }

        // Sets up export to XML link.
        if (class_exists('\\qbank_exportquestions\\form\\export_form')) {
            $exporturl = new moodle_url('/question/bank/exportquestions/export.php',
                ['cat' => $category->id . ',' . $category->contextid]);
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
        if (has_capability('moodle/category:manage', $context)) {
            if (!helper::question_is_only_child_of_top_category_in_context($category->id)) {
                $handle = $OUTPUT->pix_icon('i/move_2d', 'gripvsol');
            } else {
                $handle = '';
            }
        }
        // Render each question category.
        $data =
            [
                'questionbankurl' => $questionbankurl,
                'categoryname' => $categoryname,
                'idnumber' => $idnumber,
                'questioncount' => $questioncount,
                'categorydesc' => $categorydesc,
                'editactionmenu' => $menu,
                'handle' => $handle,
            ];

        return $OUTPUT->render_from_template(helper::PLUGINNAME . '/listitem', $data);
    }
}
