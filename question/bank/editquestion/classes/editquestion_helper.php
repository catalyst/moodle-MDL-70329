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
 * Helper class for adding/editing a question.
 *
 * This code is based on question/editlib.php by Martin Dougiamas.
 *
 * @package    qbank_editquestion
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qbank_editquestion;

defined('MOODLE_INTERNAL') || die();

/**
 * Class editquestion_helper for methods related to add/edit/copy
 *
 * @package    qbank_editquestion
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 */
class editquestion_helper {

    /**
     * Print a form to let the user choose which question type to add.
     * When the form is submitted, it goes to the question.php script.
     * @param $hiddenparams hidden parameters to add to the form, in addition to
     *      the qtype radio buttons.
     * @param $allowedqtypes optional list of qtypes that are allowed. If given, only
     *      those qtypes will be shown. Example value array('description', 'multichoice').
     */
    public static function print_choose_qtype_to_add_form($hiddenparams, array $allowedqtypes = null, $enablejs = true) {
        global $CFG, $PAGE, $OUTPUT;

        $chooser = \qbank_editquestion\qbank_chooser::get($PAGE->course, $hiddenparams, $allowedqtypes);
        $renderer = $PAGE->get_renderer('qbank_editquestion');

        return $renderer->render($chooser);
    }

}
