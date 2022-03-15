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

namespace qbank_editquestion;

use core_question\local\bank\condition;

use core_question\local\bank\question_version_status;

/**
 * This class controls what question status are being displayed.
 *
 * @package    qbank_editquestion
 * @copyright  2022 Catalyst IT Australia Pty Ltd
 * @author     2022 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_status_condition extends condition {
    /** @var bool Status of a question, either read or draft. */
    protected $status;

    /** @var string SQL fragment to add to the where clause. */
    protected $where;

    /**
     * Constructor to initialize the question status condition for qbank.
     */
    public function __construct($qbank) {
        $filters = $qbank->get_pagevars('filters');
        if (isset($filters['questionstatus']['values'][0])) {
            $this->status = (int)$filters['questionstatus']['values'][0];
            if ($this->status === 0) {
                $this->where = "qv.status = '" . question_version_status::QUESTION_STATUS_READY . "'";
            }
            if ($this->status === 1) {
                $this->where = "qv.status = '" . question_version_status::QUESTION_STATUS_DRAFT . "'";
            }
        }
    }

    public function get_condition_key() {
        return 'questionstatus';
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
     * Get options for filter.
     *
     * @return array
     */
    public function get_filter_options(): array {
        return [
            'name' => 'questionstatus',
            'title' => get_string('status', 'qbank_editquestion'),
            'custom' => true,
            'multiple' => true,
            'filterclass' => 'core/local/filter/filtertypes/questionstatus',
            'values' => [],
            'allowempty' => true,
        ];
    }
}
