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
 * Unit tests for {@mod_form core_course\tests\fixture}.
 *
 * @package    core_course
 * @category   test
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use fixtures\mod_test_mod_form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/course/tests/fixtures/mod_form.php');

class moodleform_mod_test extends \advanced_testcase {

    /**
     * Test moodleform_mod features control. If feature is enable, the elements will be displayed in the form.
     */
    public function test_moodleform_mod_features() {
        global $CFG, $COURSE;

        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a mod_assign to test its form.
        $assign = self::getDataGenerator()->create_module('assign', ['course' => $COURSE->id]);
        $assigncm = get_coursemodule_from_id('assign', $assign->cmid);
        $assign->instance = $assign->id;
        $assign->coursemodule = $assigncm;

        // Test availability feature enabled in moodleform_mod.
        $form = new mod_test_mod_form($assign, 0, $assigncm, $COURSE, true, false, false);
        $testavailability = '';
        foreach ((array)$form as $key => $formfeature) {
            if ($key === "\000*\000_form") {
                $testavailability = $formfeature;
            }
        }
        $results = $testavailability->_elements[1]->_helpbutton;
        $results = html_entity_decode($results,ENT_QUOTES,'UTF-8');
        $this->assertStringContainsString(substr(get_string('modvisible_help'), 0, 157), $results);

        // Test availability feature disabled in moodleform_mod.
        $form = new mod_test_mod_form($assign, 0, $assigncm, $COURSE, false, false, false);
        $testavailability = '';
        foreach ((array)$form as $key => $formfeature) {
            if ($key === "\000*\000_form") {
                $testavailability = $formfeature;
            }
        }
        $results = $testavailability->_elements[1]->_helpbutton;
        $results = html_entity_decode($results,ENT_QUOTES,'UTF-8');
        $this->assertStringContainsString('', $results);

        // Test restrictions feature enabled in moodleform_mod.
        $CFG->enableavailability = true;
        $form = new mod_test_mod_form($assign, 0, $assigncm, $COURSE, false, true, false);
        $testrestrictions = '';
        foreach ((array)$form as $key => $formfeature) {
            if ($key === "\000*\000_form") {
                $testrestrictions = $formfeature;
            }
        }
        $results = $testrestrictions->_elements[1]->_text;
        $this->assertStringContainsString(get_string('restrictaccess', 'availability'), $results);

        // Test completion feature enabled in moodleform_mod.
        $CFG->enablecompletion = COMPLETION_ENABLED;
        $COURSE->enablecompletion = COMPLETION_ENABLED;
        $form = new mod_test_mod_form($assign, 0, $assigncm, $COURSE, false, false, true);

        $testcompletion = '';
        foreach ((array)$form as $key => $formfeature) {
            if ($key === "\000*\000_form") {
                $testcompletion = $formfeature;
            }
        }
        $results = $testcompletion->_elements[1]->_text;
        $this->assertStringContainsString(get_string('activitycompletion', 'completion'), $results);
    }
}
