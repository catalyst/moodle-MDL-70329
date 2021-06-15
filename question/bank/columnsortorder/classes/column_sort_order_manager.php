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

use core_question\local\bank\view;
use moodle_url;
use stdClass;
use context_system;

/**
 * Class column_sort_order_manager responsible for loading and saving order to the config setting.
 *
 * @package    qbank_columnsortorder
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class column_sort_order_manager {
    /**
     * @var array Column order as set in config_plugins.
     */
    protected $columnorder;

    /**
     * Constructor for column_sort_order_manager class.
     *
     */
    public function __construct() {
        $this->load_order();
    }

    /**
     * Loads the current column order from config_plugin table.
     *
     */
    protected function load_order() {
        $this->columnorder = (array)get_config('column_sortorder');
    }

    /**
     * Get the columns of the question list.
     *
     * @return array
     */
    public function get_question_list_columns(): array {
        $result = [];
        $course = new stdClass();
        $course->id = 0;
        $contexts = context_system::instance();
        // Dummy call to get the objects without error.
        $questionbank = new view($contexts, new moodle_url('/question/dummyurl.php'), $course, null, $this);
        foreach ($questionbank->get_visiblecolumns() as $key => $column) {
            if ($column->get_name() === 'checkbox') {
                continue;
            }
            $element = new stdClass();
            $element->class = $key;
            if (substr($key, 0, 5) === 'qbank') {
                $classelements = explode('\\', $key);
                $element->name = get_string('pluginname', $classelements[0]);
                $element->colname = $classelements[1];
            } else {
                $classelements = explode('\\', $key);
                $element->name = ucfirst($column->get_name());
                $element->colname = end($classelements);
            }
            $result[] = $element;
        }
        return $result;
    }

    /**
     * Sets the current column order in config_plugin table.
     *
     * @param string $columns Plugin class ie: qbank_viewcreator\\modifier_name_column.
     * @return bool
     */
    public static function set_order($columns) {
        global $DB;
        $columns = explode(',', $columns);
        foreach ($columns as $key => $column) {
            $status = set_config($column, $key, 'column_sortorder');
            if ($status !== true) {
                return false;
            }
        }
        return true;
    }

    /**
     * Removes any uninstalled or disabled plugin column in the config_plugins 'column_sortorder' in core_question.
     *
     * @param string $plugintoremove Plugin type and name ie: qbank_viewcreator.
     */
    public static function remove_unused_column_from_db(string $plugintoremove) : void {
        $config = get_config('column_sortorder');
        if (!self::is_disabled() && $plugintoremove !== 'qbank_columnsortorder') {
            foreach ($config as $class => $position) {
                if (strpos($class, $plugintoremove) !== false) {
                    unset_config($class, 'column_sortorder');
                }
            }
        } else {
            foreach ($config as $class => $position) {
                unset_config($class, 'column_sortorder');
            }
        }
    }

    /**
     *  Loads the current configuration from config_plugin table, disabled or not.
     *
     * @return bool true when qbank_columnsortorder is disabled.
     */
    public static function is_disabled() {
        if (get_config('qbank_columnsortorder', 'disabled')) {
            return true;
        } else {
            return false;
        }
    }
}
