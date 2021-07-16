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
 * Question usage preview.
 *
 * @package    qbank_usage
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/question/editlib.php');

require_login();
core_question\local\bank\helper::require_plugin_enabled('qbank_usage');

$entryid = required_param('entryid', PARAM_INT);
$returnurl = required_param('returnurl', PARAM_RAW);

list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) =
    question_edit_setup('questions', '/question/bank/usage/usage.php');

$PAGE->set_url('/question/bank/usage/test.php');
$PAGE->set_title('test');
$PAGE->set_heading('test');
$PAGE->set_context(context_system::instance());
//echo $OUTPUT->header();
echo qbank_usage_output_fragment_question_usage(['questionid' => 2]);
//echo $OUTPUT->footer();