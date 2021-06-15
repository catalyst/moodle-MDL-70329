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
 * Helper class for question bank and its plugins.
 *
 * All the functions which has a potential to be used by different features or
 * plugins, should go here.
 *
 * @package    core_question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_question\local\bank;

defined('MOODLE_INTERNAL') || die();

/**
 * Class helper
 *
 * @package    core_question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Check the status of a plugin and throw exception if not enabled and called manually.
     *
     * Any action plugin having a php script, should call this function for a safer enable/disable implementation.
     *
     * @param string $pluginname
     * @return void
     */
    public static function require_plugin_enabled(string $pluginname): void {
        if (!\core\plugininfo\qbank::is_plugin_enabled($pluginname)) {
            throw new \moodle_exception('The following plugin is either disabled or missing from disk: ' . $pluginname);
        }
    }

    /**
     * Get the columns of the question list.
     *
     * @return array
     */
    public static function get_question_list_columns(): array {
        $result = [];
        $course = new \stdClass();
        $course->id = 0;
        $contexts = \context_system::instance();
        // Dummy call to get the objects without error.
        $questionbank = new view($contexts, new \moodle_url('/question/dummyurl.php'), $course, null);
        foreach ($questionbank->visiblecolumns as $key => $column) {
            if ($column->get_name() === 'checkbox') {
                continue;
            }
            $element = new \stdClass();
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
}
