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
 * Form for moving questions between categories.
 *
 * @package    moodlecore
 * @subpackage questionbank
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');


/**
 * Form for moving questions between categories.
 *
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @deprecated since Moodle 4.0 MDL-71585
 * @see qbank_managecategories\form\question_move_form
 */
class question_move_form extends moodleform {
    protected function definition() {
        debugging('Class question_move_form in \core_question\move_form is deprecated,
        please use qbank_managecategories\form\question_move_form instead.', DEBUG_DEVELOPER);

        $mform = $this->_form;

        $currentcat = $this->_customdata['currentcat'];
        $contexts = $this->_customdata['contexts'];

        $mform->addElement('questioncategory', 'category', get_string('category', 'question'), compact('contexts', 'currentcat'));

        $this->add_action_buttons(true, get_string('categorymoveto', 'question'));

        $mform->addElement('hidden', 'delete', $currentcat);
        $mform->setType('delete', PARAM_INT);
    }
}
