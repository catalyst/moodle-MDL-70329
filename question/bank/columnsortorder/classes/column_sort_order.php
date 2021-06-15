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

namespace qbank_columnsortorder;

use context_system;
use core_question\local\bank\view;
use moodle_url;
use stdClass;

/**
 * Class column_sort_order representing the column order in the question bank view.
 *
 * @package    qbank_columnsortorder
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class column_sort_order extends column_sort_order_manager {
    /**
     * Orders columns in the question bank view according to config_plugins table 'column_sortorder' setting.
     *
     * @param array $oldorder Unordered array of columns
     * @return array $properorder|$oldorder Returns array ordered if 'column_sortorder' setting exists.
     */
    public function sort_columns($oldorder) {
        // Check if db has order set.
        if (!empty($this->columnorder)) {
            // Merge new order with old one.
            $columnsortorder = $this->columnorder;
            asort($columnsortorder);
            $columnorder = [];
            foreach ($columnsortorder as $classname => $colposition) {
                $colname = explode('\\', $classname);
                $columnorder[end($colname)] = $colposition;
            }
            $properorder = array_merge($columnorder, $oldorder);
            // If plugin/column disabled unset the proper key.
            $diffkey = array_diff_key($properorder, $oldorder);
            foreach ($diffkey as $keytounset => $class) {
                unset($properorder[$keytounset]);
            }
            // Always have the checkbox at first column position.
            if (isset($properorder['checkbox_column'])) {
                $checkboxfirstelement = $properorder['checkbox_column'];
                unset($properorder['checkbox_column']);
                $properorder = array_merge(['checkbox_column' => $checkboxfirstelement], $properorder);
            }
            return $properorder;
        } else {
            return $oldorder;
        }
    }
}
