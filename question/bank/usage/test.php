<?php


require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/question/bank/usage/lib.php');
$url = new moodle_url('/question/bank/usage/test.php');
$PAGE->set_url($url);
echo 'sagat';
qbank_usage_output_fragment_question_usage(['questionid' => 1]);