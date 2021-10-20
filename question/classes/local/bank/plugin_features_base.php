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
 * Base class class for qbank plugins.
 *
 * Every qbank plugin must extent this class.
 *
 * @package    core_question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_question\local\bank;

use core_question\bank\search\condition;

/**
 * Class plugin_features_base is the base class for qbank plugins.
 *
 * @package    core_question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_features_base {

    public static function get_qbank_plugin_list(): array {
        return \core_component::get_plugin_list_with_class('qbank', 'plugin_feature', 'plugin_feature.php');
    }

    /**
     * This method will return the array of objects to be rendered as a prt of question bank columns/actions.
     *
     * @param view $qbank
     * @return array
     */
    public function get_question_columns(view $qbank): ?array {
        return [];
    }

    /**
     * This method will return the object for the navigation node.
     *
     * @return null|object
     */
    public function get_navigation_node(): ?object {
        return null;
    }

    /**
     * Return search conditions for the plugin.
     *
     * @param view $qbank
     * @return condition[]
     */
    public function get_question_bank_search_conditions(view $qbank): array {
        return [];
    }

    public function get_external_function_parameters(): array {
        return [];
    }

}
