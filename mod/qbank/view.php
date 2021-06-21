<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_qbank.
 *
 * @package     mod_qbank
 * @copyright   2021 Catalyst IT Australia Pty Ltd
 * @author      Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot . '/question/editlib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);
$moduleid = optional_param('cmid', 0, PARAM_INT);

// Activity instance id.
$q = optional_param('q', 0, PARAM_INT);

if ($moduleid) {
    $id = $moduleid;
}

if ($id) {
    $cm = get_coursemodule_from_id('qbank', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('qbank', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($q) {
    $moduleinstance = $DB->get_record('qbank', array('id' => $q), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('qbank', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    throw new moodle_exception(get_string('missingidandcmid', 'mod_qbank'));
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);
require_capability('mod/qbank:view', $modulecontext);

$event = \mod_qbank\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('qbank', $moduleinstance);
$event->trigger();

list($thispageurl, $contexts, $cmid, $cm, $module, $pagevars) =
        question_edit_setup('questions', '/mod/qbank/view.php', $cm->id);

$PAGE->set_url(new moodle_url($thispageurl));

$url = new moodle_url($thispageurl);
// Qbank api call.
$questionbank = new core_question\local\bank\view($contexts, $thispageurl, $course, $cm);

$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

// Print horizontal nav if needed.
$renderer = $PAGE->get_renderer('core_question', 'bank');
echo $OUTPUT->header();

$qbankaction = new \core_question\output\qbank_actionbar($url);
echo $renderer->qbank_action_menu($qbankaction);

// Print the question area.
$questionbank->display($pagevars, 'questions');

echo $OUTPUT->footer();
