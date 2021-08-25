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
 * A column type for the name of the question last modifier.
 *
 * @package   qbank_viewcreator
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_viewcreator;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\column_base;

/**
 * A column type for the name of the question last modifier.
 *
 * @copyright 2009 Tim Hunt
 * @author    2021 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class history_column extends column_base {

    public function get_name(): string {
        return 'history';
    }

    protected function get_title(): string {
        return get_string('history', 'qbank_viewcreator');
    }

    protected function display_content($question, $rowclasses): void {
        global $PAGE;
        $displaydata = [];

        $displaydata['datecreated'] = userdate($question->timecreated, get_string('strftimedatetimeshort', 'qbank_viewcreator'));
        $displaydata['datemodified'] = userdate($question->timemodified, get_string('strftimedatetimeshort', 'qbank_viewcreator'));
        echo $PAGE->get_renderer('qbank_viewcreator')->render_history($displaydata);
    }

    public function get_extra_joins(): array {
        return [];
    }

    public function get_required_fields(): array {
        $requiredfields[] = 'q.timecreated';
        $requiredfields[] = 'q.timemodified';
        return $requiredfields;
    }

    public function is_sortable(): array {
        return [
            'timecreated' => ['field' => 'q.timecreated', 'title' => get_string('created', 'qbank_viewcreator')],
            'timemodified' => ['field' => 'q.timemodified', 'title' => get_string('lastmodified', 'qbank_viewcreator')]
        ];
    }

}

