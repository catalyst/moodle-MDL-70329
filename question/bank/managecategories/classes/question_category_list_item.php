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
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_managecategories;

defined('MOODLE_INTERNAL') || die();

use html_writer;
use moodle_url;

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
            ($this->parentlist->pageurl->params() + array('edit'=>$category->id)));
        $this->icons['edit']= $this->image_icon(get_string('editthiscategory', 'question'), $url, 'edit');
        parent::set_icon_html($first, $last, $lastitem);
        $toplevel = ($this->parentlist->parentitem === null);//this is a top level item
        if (($this->parentlist->nextlist !== null) && $last && $toplevel && (count($this->parentlist->items)>1)) {
            $url = new moodle_url($this->parentlist->pageurl,
                array('movedowncontext'=>$this->id, 'tocontext'=>$this->parentlist->nextlist->context->id, 'sesskey'=>sesskey()));
            $this->icons['down'] = $this->image_icon(
                    get_string('shareincontext', 'question',
                        $this->parentlist->nextlist->context->get_context_name()), $url, 'down');
        }
        if (($this->parentlist->lastlist !== null) && $first && $toplevel && (count($this->parentlist->items)>1)) {
            $url = new moodle_url($this->parentlist->pageurl,
                array('moveupcontext'=>$this->id, 'tocontext'=>$this->parentlist->lastlist->context->id, 'sesskey'=>sesskey()));
            $this->icons['up'] = $this->image_icon(
                    get_string('shareincontext', 'question',
                        $this->parentlist->lastlist->context->get_context_name()), $url, 'up');
        }
    }

    /**
     * Override item_html function.
     *
     * @param array $extraargs
     * @return string Item html.
     */
    public function item_html($extraargs = array()) : string {
        global $CFG, $OUTPUT;
        $str = $extraargs['str'];
        $category = $this->item;

        $editqestions = get_string('editquestions', 'question');

        // Each section adds html to be displayed as part of this list item.
        $questionbankurl = new moodle_url('/question/edit.php', $this->parentlist->pageurl->params());
        $questionbankurl->param('cat', $category->id . ',' . $category->contextid);
        $item = '';
        $text = format_string($category->name, true, ['context' => $this->parentlist->context]);
        if ($category->idnumber !== null && $category->idnumber !== '') {
            $text .= ' ' . html_writer::span(
                            html_writer::span(get_string('idnumber', 'question'), 'accesshide') .
                            ' ' . $category->idnumber, 'badge badge-primary');
        }
        $text .= ' (' . $category->questioncount . ')';
        $item .= html_writer::tag('b', html_writer::link($questionbankurl, $text,
                        ['title' => $editqestions]) . ' ');
        $item .= format_text($category->info, $category->infoformat,
                array('context' => $this->parentlist->context, 'noclean' => true));

        // Don't allow delete if this is the top category, or the last editable category in this context.
        if ($category->parent && !question_is_only_child_of_top_category_in_context($category->id)) {
            $deleteurl = new moodle_url($this->parentlist->pageurl, array('delete' => $this->id, 'sesskey' => sesskey()));
            $item .= html_writer::link($deleteurl,
                    $OUTPUT->pix_icon('t/delete', $str->delete),
                    array('title' => $str->delete));
        }

        return $item;
    }
}
