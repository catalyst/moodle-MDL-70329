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
 * Class bulk_action_base is the base class for bulk actions ui.
 *
 * @package    core_question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class bulk_action_base {

    /**
     * Title of the bulk action.
     *
     * @return string
     */
    abstract public function get_bulk_action_title(): string;

    /**
     * A unique key for the bulk action, this will be used in the api to identify the action data.
     *
     * @return string
     */
    abstract public function get_bulk_action_key(): string;

    /**
     * URL of the bulk action redirect page.
     *
     * @return \moodle_url
     */
    abstract public function get_bulk_action_url(): \moodle_url;

    /**
     * Get the capabilities for the bulk action.
     *
     * @return array|null
     */
    public function get_bulk_action_capabilities(): ?array {
        return null;
    }
}
