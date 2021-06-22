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
 * This page displays a preview of a question
 *
 * The preview uses the option settings from the activity within which the question
 * is previewed or the default settings if no activity is specified. The question session
 * information is stored in the session as an array of subsequent states rather
 * than in the database.
 *
 * @package    qbank_previewquestion
 * @copyright  Alex Smith {@link http://maths.york.ac.uk/serving_maths} and
 *      numerous contributors.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/questionlib.php');

use qbank_previewquestion\form\preview_options_form;
use qbank_previewquestion\question_preview_options;
use qbank_previewquestion\previewquestion_helper;

/**
 * The maximum number of variants previewable. If there are more variants than this for a question
 * then we only allow the selection of the first x variants.
 * @var integer
 */
define('QUESTION_PREVIEW_MAX_VARIANTS', 100);

\core_question\local\bank\helper::check_qbank_status('qbank_previewquestion');

// Get and validate question id.
$id = required_param('id', PARAM_INT);
$question = question_bank::load_question($id);

// Were we given a particular context to run the question in?
// This affects things like filter settings, or forced theme or language.
if ($cmid = optional_param('cmid', 0, PARAM_INT)) {
    $cm = get_coursemodule_from_id(false, $cmid);
    require_login($cm->course, false, $cm);
    $context = context_module::instance($cmid);

} else if ($courseid = optional_param('courseid', 0, PARAM_INT)) {
    require_login($courseid);
    $context = context_course::instance($courseid);

} else {
    require_login();
    $category = $DB->get_record('question_categories',
            array('id' => $question->category), '*', MUST_EXIST);
    $context = context::instance_by_id($category->contextid);
    $PAGE->set_context($context);
    // Note that in the other cases, require_login will set the correct page context.
}
question_require_capability_on($question, 'use');
$PAGE->set_pagelayout('popup');

// Get and validate display options.
$maxvariant = min($question->get_num_variants(), QUESTION_PREVIEW_MAX_VARIANTS);
$options = new question_preview_options($question);
$options->load_user_defaults();
$options->set_from_request();
$PAGE->set_url(previewquestion_helper::question_preview_url($id, $options->behaviour, $options->maxmark,
        $options, $options->variant, $context));

// Get and validate existing preview, or start a new one.
$previewid = optional_param('previewid', 0, PARAM_INT);

if ($previewid) {
    try {
        $quba = question_engine::load_questions_usage_by_activity($previewid);

    } catch (Exception $e) {
        // This may not seem like the right error message to display, but
        // actually from the user point of view, it makes sense.
        throw new moodle_exception('submissionoutofsequencefriendlymessage', 'question',
                previewquestion_helper::question_preview_url($question->id, $options->behaviour,
                $options->maxmark, $options, $options->variant, $context), null, $e);
    }

    if ($quba->get_owning_context()->instanceid != $USER->id) {
        throw new moodle_exception('notyourpreview', 'question');
    }

    $slot = $quba->get_first_question_number();
    $usedquestion = $quba->get_question($slot, false);
    if ($usedquestion->id != $question->id) {
        throw new moodle_exception('questionidmismatch', 'question');
    }
    $question = $usedquestion;
    $options->variant = $quba->get_variant($slot);

} else {
    $quba = question_engine::make_questions_usage_by_activity(
            'core_question_preview', context_user::instance($USER->id));
    $quba->set_preferred_behaviour($options->behaviour);
    $slot = $quba->add_question($question, $options->maxmark);

    if ($options->variant) {
        $options->variant = min($maxvariant, max(1, $options->variant));
    } else {
        $options->variant = rand(1, $maxvariant);
    }

    $quba->start_question($slot, $options->variant);

    $transaction = $DB->start_delegated_transaction();
    question_engine::save_questions_usage_by_activity($quba);
    $transaction->allow_commit();
}
$options->behaviour = $quba->get_preferred_behaviour();
$options->maxmark = $quba->get_question_max_mark($slot);

// Create the settings form, and initialise the fields.
$optionsform = new preview_options_form(previewquestion_helper::question_preview_form_url($question->id, $context, $previewid),
        array('quba' => $quba, 'maxvariant' => $maxvariant));
$optionsform->set_data($options);

// Process change of settings, if that was requested.
if ($newoptions = $optionsform->get_submitted_data()) {
    // Set user preferences.
    $options->save_user_preview_options($newoptions);
    if (!isset($newoptions->variant)) {
        $newoptions->variant = $options->variant;
    }
    if (isset($newoptions->saverestart)) {
        previewquestion_helper::restart_preview($previewid, $question->id, $newoptions, $context);
    }
}

// Prepare a URL that is used in various places.
$actionurl = previewquestion_helper::question_preview_action_url($question->id, $quba->get_id(), $options, $context);

// Process any actions from the buttons at the bottom of the form.
if (data_submitted() && confirm_sesskey()) {

    try {

        if (optional_param('restart', false, PARAM_BOOL)) {
            previewquestion_helper::restart_preview($previewid, $question->id, $options, $context);

        } else if (optional_param('fill', null, PARAM_BOOL)) {
            $correctresponse = $quba->get_correct_response($slot);
            if (!is_null($correctresponse)) {
                $quba->process_action($slot, $correctresponse);

                $transaction = $DB->start_delegated_transaction();
                question_engine::save_questions_usage_by_activity($quba);
                $transaction->allow_commit();
            }
            redirect($actionurl);

        } else if (optional_param('finish', null, PARAM_BOOL)) {
            $quba->process_all_actions();
            $quba->finish_all_questions();

            $transaction = $DB->start_delegated_transaction();
            question_engine::save_questions_usage_by_activity($quba);
            $transaction->allow_commit();
            redirect($actionurl);

        } else {
            $quba->process_all_actions();

            $transaction = $DB->start_delegated_transaction();
            question_engine::save_questions_usage_by_activity($quba);
            $transaction->allow_commit();

            $scrollpos = optional_param('scrollpos', '', PARAM_RAW);
            if ($scrollpos !== '') {
                $actionurl->param('scrollpos', (int) $scrollpos);
            }
            redirect($actionurl);
        }

    } catch (question_out_of_sequence_exception $e) {
        throw new moodle_exception('submissionoutofsequencefriendlymessage', 'question', $actionurl);

    } catch (Exception $e) {
        // This sucks, if we display our own custom error message, there is no way
        // to display the original stack trace.
        $debuginfo = '';
        if (!empty($e->debuginfo)) {
            $debuginfo = $e->debuginfo;
        }
        throw new moodle_exception('errorprocessingresponses', 'question', $actionurl,
                $e->getMessage(), $debuginfo);
    }
}

if ($question->length) {
    $displaynumber = '1';
} else {
    $displaynumber = 'i';
}
$restartdisabled = array();
$finishdisabled = array();
$filldisabled = array();
if ($quba->get_question_state($slot)->is_finished()) {
    $finishdisabled = array('disabled' => 'disabled');
    $filldisabled = array('disabled' => 'disabled');
}
// If question type cannot give us a correct response, disable this button.
if (is_null($quba->get_correct_response($slot))) {
    $filldisabled = array('disabled' => 'disabled');
}
if (!$previewid) {
    $restartdisabled = array('disabled' => 'disabled');
}

// Prepare technical info to be output.
$qa = $quba->get_question_attempt($slot);
$technical = array();
$technical[] = get_string('behaviourbeingused', 'question',
        question_engine::get_behaviour_name($qa->get_behaviour_name()));
$technical[] = get_string('technicalinfominfraction',     'question', $qa->get_min_fraction());
$technical[] = get_string('technicalinfomaxfraction',     'question', $qa->get_max_fraction());
$technical[] = get_string('technicalinfovariant',         'question', $qa->get_variant());
$technical[] = get_string('technicalinfoquestionsummary', 'question', s($qa->get_question_summary()));
$technical[] = get_string('technicalinforightsummary',    'question', s($qa->get_right_answer_summary()));
$technical[] = get_string('technicalinforesponsesummary', 'question', s($qa->get_response_summary()));
$technical[] = get_string('technicalinfostate',           'question', '' . $qa->get_state());

// Start output.
$title = get_string('previewquestion', 'question', format_string($question->name));
$headtags = question_engine::initialise_js() . $quba->render_question_head_html($slot);
$PAGE->set_title($title);
$PAGE->set_heading($title);
echo $OUTPUT->header();

$previewdata = array();
$previewdata['actionurl'] = $actionurl;
$previewdata['session'] = sesskey();
$previewdata['slot'] = $slot;
// Output of the question.
$previewdata['question'] = $quba->render_question($slot, $options, $displaynumber);
$previewdata['restartdisabled'] = html_writer::attributes($restartdisabled);
$previewdata['finishdisabled'] = html_writer::attributes($finishdisabled);
$previewdata['filldisabled'] = html_writer::attributes($filldisabled);
// Output the technical info.
$previewdata['techinfo'] = print_collapsible_region_start('', 'techinfo', get_string('technicalinfo', 'question'),
        'core_question_preview_techinfo_collapsed', true, true, $OUTPUT->help_icon('technicalinfo', 'question'));
foreach ($technical as $info) {
    $previewdata['techinfo'] .= html_writer::tag('p', $info, array('class' => 'notifytiny'));
}
$previewdata['techinfo'] .= print_collapsible_region_end(true);

echo $PAGE->get_renderer('qbank_previewquestion')->render_preview_page($previewdata);

// Output a link to export this single question.
if (question_has_capability_on($question, 'view')) {
    if (class_exists('qbank_exporttoxml\\exporttoxml_helper')) {
        if (\core\plugininfo\qbank::check_qbank_status('qbank_exporttoxml')) {
            $exportfunction = '\\qbank_exporttoxml\\exporttoxml_helper::question_get_export_single_question_url';
            echo html_writer::link($exportfunction($question),
                    get_string('exportonequestion', 'question'));
        }
    } else {
        $exportfunction = 'question_get_export_single_question_url';
        echo html_writer::link($exportfunction($question),
                get_string('exportonequestion', 'question'));
    }
}

// Log the preview of this question.
$event = \core\event\question_viewed::create_from_question_instance($question, $context);
$event->trigger();

// Display the settings form.
$optionsform->display();

$PAGE->requires->js_module('core_question_engine');
$PAGE->requires->strings_for_js(array(
    'closepreview',
), 'question');
$PAGE->requires->yui_module('moodle-qbank_previewquestion-preview', 'M.question.preview.init');
echo $OUTPUT->footer();
