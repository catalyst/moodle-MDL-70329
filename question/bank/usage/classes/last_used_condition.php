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

namespace qbank_usage;

use core_question\local\bank\condition;

use core_question\local\bank\question_version_status;

/**
 * This class controls last used condition display.
 *
 * @package    qbank_usage
 * @copyright  2022 Catalyst IT Australia Pty Ltd
 * @author     2022 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class last_used_condition extends condition {

    /** @var string SQL fragment to add to the where clause. */
    protected $where;

    /**
     * Constructor to initialize the last used condition for qbank.
     */
    public function __construct($qbank) {
        $used = $qbank->get_pagevars('filters')['lastused']['values'][0] ?? null;
        if (isset($used) && (int)$used == 0) {
            $this->where = 'qre.questionbankentryid IN
                            (SELECT qre.questionbankentryid
                               FROM {question} q
                               JOIN {question_versions} qv ON qv.questionid = q.id
                               JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                               JOIN {question_references} qre ON qre.questionbankentryid = qbe.id
                           GROUP BY qre.id, qre.questionbankentryid
                             HAVING COUNT(qre.questionbankentryid) > 0)';
        }
        if (isset($used) && (int)$used == 1) {
            $this->where = 'qbe.id NOT IN (SELECT questionbankentryid FROM {question_references})';
        }
    }

    public function get_condition_key() {
        return 'lastused';
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
            'name' => 'lastused',
            'title' => get_string('questionusage', 'qbank_usage'),
            'custom' => true,
            'multiple' => true,
            'filterclass' => 'core/local/filter/filtertypes/lastused',
            'values' => [],
            'allowempty' => true,
        ];
    }
}
