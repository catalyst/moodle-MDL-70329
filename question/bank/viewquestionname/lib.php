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
 * Callback and other methods for viewquestionname plugin.
 *
 * @package    qbank_viewquestionname
 * @copyright  2022 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * In place editing callback for question name.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param string $newvalue
 * @return \core\output\inplace_editable|void
 */
function qbank_viewquestionname_inplace_editable($itemtype, $itemid, $newvalue): \core\output\inplace_editable {
    if ($itemtype === 'questionname') {
        global $CFG, $DB;
        require_once($CFG->libdir . '/questionlib.php');
        $record = $DB->get_record('question', ['id' => $itemid], '*', MUST_EXIST);
        \external_api::validate_context(context_system::instance());
        $record->name = $newvalue;
        $DB->update_record('question', $record);
        // Prepare the element for the output:
        $a = new \stdClass();
        $a->name = format_string($record->name);
        return new \core\output\inplace_editable('qbank_viewquestionname', 'questionname', $record->id,
            question_has_capability_on($record, 'edit'), format_string($record->name), $record->name,
            get_string('edit_question_name_hint', 'qbank_viewquestionname'),
            get_string('edit_question_name_label', 'qbank_viewquestionname', $a));
    }

}
