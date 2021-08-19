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

namespace qbank_history;

/**
 * Helper class.
 *
 * @package    qbank_history
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Get the question history url.
     *
     * @param int $entryid
     * @param \moodle_url $returnrul
     * @param int $courseid
     * @return \moodle_url
     */
    public static function question_history_url($entryid, $returnrul, $courseid): \moodle_url {
        $params = [
            'entryid' => $entryid,
            'returnurl' => $returnrul,
            'courseid' => $courseid
        ];

        return new \moodle_url('/question/bank/history/history.php', $params);
    }

}
