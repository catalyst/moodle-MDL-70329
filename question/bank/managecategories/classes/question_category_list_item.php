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
 * An item in a list of question categories.
 *
 * @package    qbank_managecategories
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_managecategories;

defined('MOODLE_INTERNAL') || die();

use action_menu;
use action_menu_link;
use html_writer;
use moodle_list;
use moodle_url;
use pix_icon;

/**
 * An item in a list of question categories.
 *
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_category_list_item extends \list_item {

    /**
     * Override set_icon_html function.
     *
     * @param bool $first Is the first on the list.
     * @param bool $last Is the last on the list.
     * @param \list_item $lastitem Last item.
     */
    public function set_icon_html($first, $last, $lastitem) : void {
        global $CFG;
        $category = $this->item;
        $url = new moodle_url('/question/bank/managecategories/category.php',
            ($this->parentlist->pageurl->params() + ['edit' => $category->id]));
        $this->icons['edit'] = $this->image_icon(get_string('editthiscategory', 'question'), $url, 'edit');
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
        $cmid = required_param('cmid', PARAM_INT);
        // Each section adds html to be displayed as part of this list item.
        $nodeparent = $PAGE->settingsnav->find('questionbank', \navigation_node::TYPE_CONTAINER);
        $questionbankurl = new moodle_url($nodeparent->action->get_path(), $this->parentlist->pageurl->params());
        $questionbankurl->param('cat', $category->id . ',' . $category->contextid);
        $categoryname = format_string($category->name, true, ['context' => $this->parentlist->context]);
        $idnumber = null;
        if ($category->idnumber !== null && $category->idnumber !== '') {
            $idnumber = $category->idnumber;
        }
        $questioncount = ' (' . $category->questioncount . ')';
        $categorydesc = format_text($category->info, $category->infoformat,
            ['context' => $this->parentlist->context, 'noclean' => true]);

        $menu = new action_menu();
        $menu->set_menu_trigger(get_string('edit'));
        if ($this->children->editable) {
            // Sets up edit link.
            $editurl = new moodle_url('/question/bank/managecategories/category.php',
                ['cmid' => $cmid, 'edit' => $category->id]);
            $menu->add(new action_menu_link(
                $editurl,
                new pix_icon('t/edit', 'edit'),
                get_string('editsettings'),
                false
            ));
            // Don't allow delete if this is the top category, or the last editable category in this context.
            if (!helper::question_is_only_child_of_top_category_in_context($category->id)) {
                // Sets up delete link.
                $deleteurl = new moodle_url('/question/bank/managecategories/category.php',
                    ['cmid' => $cmid, 'delete' => $category->id, 'sesskey' => sesskey()]);
                $menu->add(new action_menu_link(
                    $deleteurl,
                    new pix_icon('t/delete', 'delete'),
                    get_string('delete'),
                    false
                ));
            }
        }
        // Sets up export to XML link.
        $exporturl = new moodle_url('/question/export.php',
            ['cmid' => $cmid, 'cat' => $category->id . ',' . $category->contextid]);
        $menu->add(new action_menu_link(
            $exporturl,
            new pix_icon('t/download', 'download'),
            get_string('exportasxml', 'question'),
            false
        ));
        // Don't allow movement if only subcat.
        if (!helper::question_is_only_child_of_top_category_in_context($category->id)) {
            $handle = $OUTPUT->render_from_template('core/drag_handle', []);
        } else {
            $handle = '';
        }
        // Render each question category.
        $data =
            [
                'questionbankurl' => $questionbankurl,
                'categoryname' => $categoryname,
                'idnumber' => $idnumber,
                'questioncount' => $questioncount,
                'categorydesc' => $categorydesc,
                'editactionmenu' => $OUTPUT->render($menu),
                'handle' => $handle,
            ];

        return $OUTPUT->render_from_template(helper::PLUGINNAME . '/listitem', $data);
    }
}
