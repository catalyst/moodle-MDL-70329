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
 * A question bank column which gathers together all the actions into a menu.
 *
 * @package   qbank_viewquestionactions
 * @copyright 2019 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_viewquestionactions;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\column_base;
use core_question\local\bank\menuable_action;

/**
 * A question bank column which gathers together all the actions into a menu.
 *
 * This question bank column, if added to the question bank, will
 * replace all of the other columns which implement the
 * {@link menuable_action} interface and replace them with a single
 * column containing an Edit menu.
 *
 * @copyright 2019 The Open University
 * @author    2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class viewquestionactions_column extends column_base {
    /**
     * @var menuable_action[]
     */
    protected $actions;

    /**
     * Set up the list of actions that should be shown in the menu.
     *
     * This takes a list of column object (the list from a question
     * bank view). It extracts all the ones that should go in the menu
     * and stores them for later use. Then it returns the remaining columns.
     *
     * @param column_base[] $allcolumns a set of columns.
     * @return column_base[] the non-action columns from the set.
     */
    public function claim_menuable_columns($allcolumns): array {
        $remainingcolumns = [];
        foreach ($allcolumns as $key => $column) {
            if ($column instanceof menuable_action) {
                $this->actions[$key] = $column;
            } else {
                $remainingcolumns[$key] = $column;
            }
        }
        return $remainingcolumns;
    }

    /**
     * Title for this column. Not used if is_sortable returns an array.
     *
     * @return \lang_string|string
     */
    protected function get_title() {
        return get_string('actions');
    }

    /**
     * Get the internal name for this column. Used as a CSS class name,
     * and to store information about the current sort. Must match PARAM_ALPHA.
     *
     * @return string column name.
     */
    public function get_name(): string {
        return 'editmenu';
    }

    /**
     * Output the contents of this column.
     * @param object $question the row from the $question table, augmented with extra information.
     * @param string $rowclasses CSS class names that should be applied to this row of output.
     */
    protected function display_content($question, $rowclasses): void {
        global $OUTPUT;

        $menu = new \action_menu();
        $menu->set_menu_trigger(get_string('edit'));
        $menu->set_alignment(\action_menu::TL, \action_menu::BL);
        foreach ($this->actions as $actioncolumn) {
            $action = $actioncolumn->get_action_menu_link($question);
            if ($action) {
                $menu->add($action);
            }
        }

        $qtypeactions = \question_bank::get_qtype($question->qtype, false)
                ->get_extra_question_bank_actions($question);
        foreach ($qtypeactions as $action) {
            $menu->add($action);
        }

        echo $OUTPUT->render($menu);
    }

    /**
     * Use table alias 'q' for the question table, or one of the
     * ones from get_extra_joins. Every field requested must specify a table prefix.
     * @return array fields required.
     */
    public function get_required_fields(): array {
        return ['q.qtype'];
    }
}
