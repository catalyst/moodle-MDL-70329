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
 * Plugin strings are defined here.
 *
 * @package     mod_qbank
 * @category    string
 * @copyright   2021 Catalyst IT Australia Pty Ltd
 * @author      Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['course_summary'] = 'This course has been created as part of the upgrade to Moodle 4.0.
The question bank in this course category has been migrated to a question bank activity in this course.
All the questions that were previously located in this course categories question bank have been migrated to the question bank activity in this course.';
$string['coursenamebydefault'] = '{$a->context} Question bank: {$a->shortdescription}';
$string['module_summary'] = 'This activity has been created as part of the upgrade to Moodle 4.0.
The question bank has been migrated to this question bank activity.
All the questions that were previously located in this course have been migrated to this question bank activity.';
$string['modulename'] = 'Question bank';
$string['modulename_help'] = 'This activity allows a teacher to create, preview, and edit questions in a database of question categories.

These questions are then used by the quiz activity, or by other plugins.

Questions are given version control and statistics once they have been used, and other parameters.';
$string['noqbankinstances'] = 'There are no Question bank in this course.

See the <a href="https://docs.moodle.org/40/en/Questionbank_activity">Question bank activity</a> for more information about how to create a Question bank correctly.';
$string['modulename_link'] = 'mod/qbank/view';
$string['modulenamebydefault'] = '{$a->context} Question bank: {$a->instanceid} - {$a->shortdescription}';
$string['modulenameplural'] = 'Question banks';
$string['pluginadministration'] = 'Question bank administration';
$string['pluginname'] = 'Question bank';
$string['privacy:metadata'] = 'The Question bank plugin does not store any personal data, for now.';
$string['qbankname'] = 'Question bank name';
$string['qbankname_help'] = 'Enter the Question bank name';
$string['qbank:addinstance'] = 'Add a new Question bank';
$string['qbank:view'] = 'View Question bank';
$string['qbank:viewhidden'] = 'View hidden Question bank';
