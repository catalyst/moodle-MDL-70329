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

global $CFG, $OUTPUT, $PAGE, $USER;
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');

core_question\local\bank\helper::require_plugin_enabled('qbank_usage');

$questionid = required_param('questionid', PARAM_INT);
$returnurl = required_param('returnurl', PARAM_RAW);
$cmid = optional_param('cmid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
}

if ($cmid) {
    list($module, $cm) = get_module_from_cmid($cmid);
    require_login($cm->course, false, $cm);
    $thiscontext = context_module::instance($cmid);
} else if ($courseid) {
    require_login($courseid, false);
    $thiscontext = context_course::instance($courseid);
} else {
    throw new moodle_exception('missingcourseorcmid', 'question');
}

$contexts = new \core_question\lib\question_edit_contexts($thiscontext);
$url = new moodle_url('/question/bank/usage/usage.php', ['questionid' => $questionid, 'returnurl' => $returnurl]);
$PAGE->set_url($url);
$streditingquestions = get_string('usageheader', 'qbank_usage');
$PAGE->set_title($streditingquestions);
$PAGE->set_heading($streditingquestions);
$context = $contexts->lowest();
$PAGE->set_context($context);
$PAGE->navbar->add(get_string('question'), new moodle_url($returnurl));
$PAGE->navbar->add($streditingquestions, $url);

$displaydata = [];
$question = question_bank::load_question($questionid);
$quba = question_engine::make_questions_usage_by_activity('core_question_preview', context_user::instance($USER->id));
$options = new \qbank_previewquestion\question_preview_options($question);
$options->load_user_defaults();
$options->set_from_request();
$quba->set_preferred_behaviour($options->behaviour);
$slot = $quba->add_question($question, $options->maxmark);
$quba->start_question($slot, $options->variant);
$displaydata['question'] = $quba->render_question($slot, $options, '1');
$questionusagetable = new \qbank_usage\tables\question_usage_table('question_usage_table', $question);
$displaydata['tablesql'] = $questionusagetable->export_for_fragment();

echo $OUTPUT->header();
echo $PAGE->get_renderer('qbank_usage')->render_usage_fragment($displaydata);
echo $OUTPUT->footer();
