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

namespace core_question\local\bank;

/**
 * An abstract class for filtering/searching questions.
 *
 * @package    core_question
 * @copyright  2013 Ray Morris
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class condition {

    /** @var int The default filter type (ALL) */
    const JOINTYPE_DEFAULT = 2;

    /** @var int None of the following match */
    const JOINTYPE_NONE = 0;

    /** @var int Any of the following match */
    const JOINTYPE_ANY = 1;

    /** @var int All of the following match */
    const JOINTYPE_ALL = 2;

    /** @var int The default filter type (BETWEEN) */
    const RANGETYPE_DEFAULT = 2;

    /** @var int After specified date */
    const RANGETYPE_AFTER = 0;

    /** @var int Before specified date */
    const RANGETYPE_BEFORE = 1;

    /** @var int Between specified dates */
    const RANGETYPE_BETWEEN = 2;

    /**
     * Return an SQL fragment to be ANDed into the WHERE clause to filter which questions are shown.
     * @return string SQL fragment. Must use named parameters.
     */
    abstract public function where();

    /**
     * Each condition will need a unique key to be identified and sequenced by the api.
     * Use a unique string for the condition identifier, use string directly, dont need to use language pack.
     * Using language pack might break the filter object for multilingual support.
     *
     * @return string
     */
    public function get_condition_key() {
        return '';
    }

    /**
     * Return parameters to be bound to the above WHERE clause fragment.
     * @return array parameter name => value.
     */
    public function params() {
        return [];
    }

    /**
     * Display GUI for selecting criteria for this condition. Displayed when Show More is open.
     *
     * Compare display_options(), which displays always, whether Show More is open or not.
     * @return bool|string HTML form fragment
     */
    public function display_options_adv() {
        return false;
    }

    /**
     * Display GUI for selecting criteria for this condition. Displayed always, whether Show More is open or not.
     *
     * Compare display_options_adv(), which displays when Show More is open.
     * @return bool|string HTML form fragment
     */
    public function display_options() {
        return false;
    }

    /**
     * Get options for filter.
     *
     * @return array
     */
    public function get_filter_options(): array {
        return [];
    }

    /**
     * Get the list of available joins for the filter.
     *
     * @return array
     */
    public function get_join_list(): array {
        return [
            self::JOINTYPE_NONE => get_string('none'),
            self::JOINTYPE_ANY => get_string('any'),
            self::JOINTYPE_ALL => get_string('all'),
        ];
    }
}
