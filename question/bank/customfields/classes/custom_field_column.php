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

namespace qbank_customfields;

use core_question\local\bank\column_base;

/**
 * A column type for the name of the question creator.
 *
 * @package   qbank_customfields
 * @copyright 2009 Tim Hunt
 * @author    2021 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_field_column extends column_base {

    /** @var \core_customfield\field_controller The custom field this column is displaying. */
    protected $field;

    /**
     * Constructor.
     *
     * @param view $qbank the question bank view we are helping to render.
     * @param \core_customfield\field_controller $field The custom field this column is displaying.
     */
    public function __construct(\core_question\local\bank\view $qbank, \core_customfield\field_controller $field) {
        parent::__construct($qbank);
        $this->field = $field;
    }

    public function get_name(): string {
        return 'customfield';
    }

    public function get_column_name() {
        return 'custom_field_column\\' . $this->field->get('shortname');
    }

    protected function get_title(): string {
        return $this->field->get_formatted_name();
    }

    protected function display_content($question, $rowclasses): void {
        global $PAGE;
        $fieldhandler = $this->field->get_handler();
        if ($fieldhandler->can_view($this->field, $question->id)) {
            $fielddata = $fieldhandler->get_field_data($this->field, $question->id);
            echo $fieldhandler->display_custom_field_table($fielddata);
        } else {
            echo '';
        }
    }

}
