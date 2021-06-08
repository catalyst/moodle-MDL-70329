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
 * Defines the import questions form.
 *
 * @package    qbank_settingspage
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class sort_column_form extends moodleform { 

    public function definition() {
        global $CFG;
 
        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('html', $this->_customdata['html']);
        $mform->addElement('hidden', 'list', $this->_customdata['list']);
        $mform->setType('list', PARAM_RAW);
        $mform->setType('html', PARAM_RAW);
        $this->add_action_buttons();
    }
    
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

    public function custom_element($outp) {
        $mform = $this->_form;
        $mform->addElement('html', $outp);
    }
}