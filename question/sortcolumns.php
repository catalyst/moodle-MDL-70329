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
 * Question bank settings page class.
 *
 * @package    qbank_settingspage
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_question\local\bank\helper;

require_once(__DIR__ . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/question/sortcolumns.php', ['section' => 'columnsortorder']);
$PAGE->set_title(get_string('qbankcolumnsortorder', 'question'));
$PAGE->set_heading(get_string('qbankcolumnsortorder', 'question'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->js_call_amd('core_question/sort_columns', 'init');
$PAGE->navigation->clear_cache();

$context = [];

$corequestionbankcolumns = helper::get_question_list_columns();
foreach ($corequestionbankcolumns as $columnname) {
    $name = $columnname->name . ' (' . $columnname->colname . ')';
    $names['names'][] = ['name' => $name];
}

$context = $names;
$context['manageqbankurl'] = new moodle_url('/admin/settings.php', ['section' => 'manageqbanks'],
    get_string('manageqbanks', 'admin'));

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('question/qbank_columnsortorder', $context);

echo $OUTPUT->footer();
