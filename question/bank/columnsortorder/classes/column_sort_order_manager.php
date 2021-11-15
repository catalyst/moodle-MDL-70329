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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');

use core_question\local\bank\view;
use moodle_url;
use stdClass;
use context_system;
use question_edit_contexts;

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
     * @var array Column order as set in config_plugins 'class' => 'position', ie: question_type_column => 3.
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
    protected function load_order(): void {
        $this->columnorder = (array)get_config('qbank_columnsortorder');

        // Cleans rows that are not columns.
        if (array_key_exists('version', $this->columnorder)) {
            unset($this->columnorder['version']);
        }

        if (array_key_exists('disabled', $this->columnorder)) {
            unset($this->columnorder['disabled']);
        }
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
        $context = context_system::instance();
        $contexts = new question_edit_contexts($context);
        // Dummy call to get the objects without error.
        $questionbank = new view($contexts, new moodle_url('/question/dummyurl.php'), $course, null);

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
            $element->classcol = explode('\\', get_class($column))[0] . '\\' . $element->colname;
            $result[] = $element;
        }
        return $result;
    }

    /**
     * Removes any uninstalled or disabled plugin column in the config_plugins for 'qbank_columnsortorder' plugin.
     *
     * @param string $plugintoremove Plugin type and name ie: qbank_viewcreator.
     */
    public function remove_unused_column_from_db(string $plugintoremove): void {
        $qbankplugins = $this->get_question_list_columns();
        foreach ($qbankplugins as $plugin) {
            if (strpos($plugin->classcol, $plugintoremove) !== false) {
                if ($plugintoremove === 'qbank_customfields') {
                    unset_config($plugin->class, 'qbank_columnsortorder');
                } else {
                    unset_config($plugin->colname, 'qbank_columnsortorder');
                }
            }
        }
    }

    /**
     * Orders columns in the question bank view according to config_plugins table 'qbank_columnsortorder' config.
     *
     * @param array $ordertosort Unordered array of columns
     * @return array $properorder|$ordertosort Returns array ordered if 'qbank_columnsortorder' config exists.
     */
    public function sort_columns($ordertosort): array {
        // Check if db has order set.
        if (!empty($this->columnorder)) {
            // Merge new order with old one.
            $columnsortorder = $this->columnorder;
            asort($columnsortorder);
            $columnorder = [];
            foreach ($columnsortorder as $classname => $colposition) {
                $colname = explode('\\', $classname);
                if (count($colname) > 1) {
                    $classname = str_replace('\\\\', '\\', $classname);
                    $columnorder[$classname] = $colposition;
                } else {
                    $columnorder[end($colname)] = $colposition;
                }
            }
            $properorder = array_merge($columnorder, $ordertosort);
            // If plugin/column disabled unset the proper key.
            $diffkey = array_diff_key($properorder, $ordertosort);
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
        }
        return $ordertosort;
    }
}
