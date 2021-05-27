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
 * Base class class for column plugins.
 *
 * Every qbank plugin wants to implement a column/action element, must extent this class.
 *
 * @package    core_question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_question\local\bank;

defined('MOODLE_INTERNAL') || die();

/**
 * Class column_plugin_base is the base class for column plugins.
 *
 * @package core_question
 */
abstract class column_plugin_base {

    /**
     * @var view $qbank the question bank view we are helping to render.
     */
    protected $qbank;

    /**
     * Class column_plugin_base constructor.
     *
     * this constructor requires the view object to be passed.
     * @param view $qbank
     */
    public function __construct($qbank) {
        $this->qbank = $qbank;
    }

    /**
     * This method will return the array of objects to be rendered as a prt of question bank columns/actions.
     * @return array
     */
    abstract public function get_question_columns();

}
