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
 * The main mod_qbank configuration form.
 *
 * @package     mod_qbank
 * @copyright   2021 Catalyst IT Australia Pty Ltd
 * @author      Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_qbank
 * @copyright   2021 Catalyst IT Australia Pty Ltd
 * @author      Safat Shahin <safatshahin@catalyst-au.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_qbank_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('qbankname', 'mod_qbank'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'qbankname', 'mod_qbank');

        $this->standard_intro_elements();

        // Add standard elements using the overridden method for this module.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Overridden standard_coursemodule_elements method from the parent class
     * It will remove all the unnecessary elements for qbank module
     */
    protected function standard_coursemodule_elements() {
        global $COURSE, $CFG;
        $mform =& $this->_form;

        $mform->addElement('header', 'modstandardelshdr', get_string('modstandardels', 'form'));

        $mform->addElement('hidden', 'visible', 1);
        $mform->setType('visible', PARAM_INT);

        if ($this->_features->idnumber) {
            $mform->addElement('text', 'cmidnumber', get_string('idnumbermod'));
            $mform->setType('cmidnumber', PARAM_RAW);
            $mform->addHelpButton('cmidnumber', 'idnumbermod');
        }

        if (!empty($CFG->enableavailability)) {
            // Add special button to end of previous section if groups/groupings
            // are enabled.

            $availabilityplugins = \core\plugininfo\availability::get_enabled_plugins();
            $groupavailability = $this->_features->groups && array_key_exists('group', $availabilityplugins);
            $groupingavailability = $this->_features->groupings && array_key_exists('grouping', $availabilityplugins);

            if ($groupavailability || $groupingavailability) {
                // When creating the button, we need to set type=button to prevent it behaving as a submit.
                $mform->addElement('static', 'restrictgroupbutton', '',
                        html_writer::tag('button', get_string('restrictbygroup', 'availability'), [
                                'id' => 'restrictbygroup',
                                'type' => 'button',
                                'disabled' => 'disabled',
                                'class' => 'btn btn-secondary',
                                'data-groupavailability' => $groupavailability,
                                'data-groupingavailability' => $groupingavailability
                        ])
                );
            }

            // Availability field. This is just a textarea; the user interface
            // interaction is all implemented in JavaScript.
            $mform->addElement('header', 'availabilityconditionsheader',
                    get_string('restrictaccess', 'availability'));
            // Note: This field cannot be named 'availability' because that
            // conflicts with fields in existing modules (such as assign).
            // So it uses a long name that will not conflict.
            $mform->addElement('textarea', 'availabilityconditionsjson',
                    get_string('accessrestrictions', 'availability'));
            // The _cm variable may not be a proper cm_info, so get one from modinfo.
            if ($this->_cm) {
                $modinfo = get_fast_modinfo($COURSE);
                $cm = $modinfo->get_cm($this->_cm->id);
            } else {
                $cm = null;
            }
            \core_availability\frontend::include_all_javascript($COURSE, $cm);
        }

        // Conditional activities: completion tracking section.
        if (!isset($completion)) {
            $completion = new completion_info($COURSE);
        }
        if ($completion->is_enabled()) {
            $mform->addElement('hidden', 'completionunlocked', 0);
            $mform->setType('completionunlocked', PARAM_INT);
        }

        // Populate module tags.
        if (core_tag_tag::is_enabled('core', 'course_modules')) {
            $mform->addElement('header', 'tagshdr', get_string('tags', 'tag'));
            $mform->addElement('tags', 'tags', get_string('tags'), array('itemtype' => 'course_modules', 'component' => 'core'));
            if ($this->_cm) {
                $tags = core_tag_tag::get_item_tags_array('core', 'course_modules', $this->_cm->id);
                $mform->setDefault('tags', $tags);
            }
        }

        $this->standard_hidden_coursemodule_elements();
    }
}
