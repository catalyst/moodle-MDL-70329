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
 * @package    qbank_managecategories
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_managecategories\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');


/**
 * Form for displaying category descriptions.
 *
 */
class question_category_checkbox_form extends moodleform {

    /**
     * Build the form definition.
     *
     * This adds all the form fields that the question move feature needs.
     * @throws \coding_exception
     */
    protected function definition() {
        $mform = $this->_form;

        $checked = (int) $this->_customdata['checked'];
        $cmid = $this->_customdata['cmid'];

        $mform->addElement('checkbox', 'qbshowdescr', '', get_string('showcategorydescription', 'qbank_managecategories'));
        $mform->setDefault('qbshowdescr', $checked);
        $mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);
    }
}
