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

namespace qbank_viewquestiontext;

use core_question\local\bank\condition;

/**
 * This class controls from which category questions are listed.
 *
 * @package    qbank_viewquestiontext
 * @copyright  2022 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class questiontext_condition extends condition {
    /** @var string SQL fragment to add to the where clause. */
    protected $where;

    /** @var array query param used in where. */
    protected $params;

    /**
     * Constructor to initialize the question text filter condition.
     */
    public function __construct($qbank) {
    }

    public function get_condition_key() {
        return 'showtext';
    }

    /**
     * SQL fragment to add to the where clause.
     *
     * @return string
     */
    public function where() {
        return  $this->where;
    }

    /**
     * Return parameters to be bound to the above WHERE clause fragment.
     * @return array parameter name => value.
     */
    public function params() {
        return $this->params;
    }

    /**
     * Get options for filter.
     *
     * @return array
     */
    public function get_filter_options(): array {
        return [
            'name' => 'showtext',
            'title' => get_string('showquestiontext', 'core_question'),
            'custom' => true,
            'multiple' => true,
            'filterclass' => 'core/local/filter/filtertypes/showtext',
            'values' => [],
            'allowempty' => true,
        ];
    }
}
