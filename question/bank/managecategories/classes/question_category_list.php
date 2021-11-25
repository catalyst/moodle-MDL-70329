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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir. '/listlib.php');

use context_system;
use moodle_list;
use pix_icon;
use stdClass;

/**
 * Class representing a list of question categories.
 *
 * @package    qbank_managecategories
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_category_list extends moodle_list {

    /**
     * Table name.
     * @var $table
     */
    public $table = "question_categories";

    /**
     * List item class name.
     * @var $listitemclassname
     */
    public $listitemclassname = '\qbank_managecategories\question_category_list_item';

    /**
     * Reference to list displayed below this one.
     * @var $nextlist
     */
    public $nextlist = null;

    /**
     * Reference to list displayed above this one.
     * @var $lastlist
     */
    public $lastlist = null;

    /**
     * Context.
     * @var $context
     */
    public $context = null;

    /**
     * Sort by string.
     * @var $sortby
     */
    public $sortby = 'parent, sortorder, name';

    /**
     * Constructor.
     *
     * @param string $type
     * @param string $attributes
     * @param boolean $editable
     * @param \moodle_url $pageurl url for this page
     * @param integer $page if 0 no pagination. (These three params only used in top level list.)
     * @param string $pageparamname name of url param that is used for passing page no
     * @param integer $itemsperpage no of top level items.
     * @param \context $context
     */
    public function __construct($type='ul', $attributes='', $editable = false, $pageurl=null,
                                $page = 0, $pageparamname = 'page', $itemsperpage = 20, $context = null) {
        parent::__construct('ul', '', $editable, $pageurl, $page, 'cpage', $itemsperpage);
        $this->context = $context;
    }

    /**
     * Set the array of records of list items.
     */
    public function get_records() : void {
        $this->records = helper::get_categories_for_contexts($this->context->id, $this->sortby);
    }

    /**
     * Returns the highest category id that the $item can have as its parent.
     * Note: question categories cannot go higher than the TOP category.
     *
     * @param \list_item $item The item which its top level parent is going to be returned.
     * @return int
     */
    public function get_top_level_parent_id($item) : int {
        // Put the item at the highest level it can go.
        $topcategory = question_get_top_category($item->item->contextid, true);
        return $topcategory->id;
    }

    /**
     * Process any actions.
     *
     * @param integer $left id of item to move left
     * @param integer $right id of item to move right
     * @return bool
     */
    public function process_actions($left, $right) : bool {
        // Should this action be processed by this list object?
        if (!(array_key_exists($left, $this->records) || array_key_exists($right, $this->records))) {
            return false;
        }
        if (!empty($left)) {
            $oldparentitem = $this->move_item_left($left);
            if ($this->item_is_last_on_page($oldparentitem->id)) {
                // Item has jumped onto the next page, change page when we redirect.
                $this->page ++;
                $this->pageurl->params([$this->pageparamname => $this->page]);
            }
        } else if (!empty($right)) {
            $this->move_item_right($right);
            if ($this->item_is_first_on_page($right)) {
                // Item has jumped onto the previous page, change page when we redirect.
                $this->page --;
                $this->pageurl->params([$this->pageparamname => $this->page]);
            }
        } else {
            return false;
        }

        redirect($this->pageurl);
    }

    /**
     * Override to_html function.
     *
     * @param integer $indent depth of indentation.
     * @param array $extraargs extra argument.
     */
    public function to_html($indent=0, $extraargs=[]) {
        global $OUTPUT;
        $context = context_system::instance();
        $itemstab = [];
        if (count($this->items)) {
            $tabs = str_repeat("\t", $indent);
            $first = true;
            $itemiter = 1;
            $lastitem = '';
            foreach ($this->items as $item) {
                $last = (count($this->items) == $itemiter);
                if ($this->editable) {
                    if (has_capability('moodle/category:manage', $context)) {
                        $item->set_icon_html($first, $last, $lastitem);
                    }
                }
                if ($itemhtml = $item->to_html($indent + 1, $extraargs)) {
                    $itemtab = $tabs . $itemhtml;
                    $itemstab['items'][] = ['item' => $itemtab];
                }
                $first = false;
                $lastitem = $item;
                $itemiter++;
            }
        }
        if ($itemstab) {
            return $OUTPUT->render_from_template(helper::PLUGINNAME . '/categorylist', $itemstab);
        }
    }
}
