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
 * The main mod_test configuration form.
 *
 * @package    core_course
 * @category   test
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace fixtures;

use moodleform_mod;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form for testing.
 *
 * @package    core_course
 * @category   test
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_test_mod_form extends moodleform_mod {

    /** @var bool The availability feature */
    protected $availability = false;

    /** @var bool The restrictions feature */
    protected $restrictions = false;

    /** @var bool The completion feature */
    protected $completion = false;

    /**
     * The module form constructor.
     *
     * @param object $current
     * @param int $section
     * @param object $cm
     * @param object $course
     * @param bool $availability
     * @param bool $restrictions
     * @param bool $completion
     */
    public function __construct(object $current, int $section, object $cm, object $course,
                                bool $availability, bool $restrictions, bool $completion) {
        $this->_modname = 'assign';
        $this->availability = $availability;
        $this->restrictions = $restrictions;
        $this->completion = $completion;
        parent::__construct($current, $section, $cm, $course);
    }

    /**
     * Called to define this moodle form
     */
    public function definition(): void {

        // Disable features by default.
        $this->_features = new stdClass();
        $this->_features->outcomes = false;
        $this->_features->rating = false;
        $this->_features->idnumber = false;
        $this->_features->groups = false;
        $this->_features->groupings = false;
        $this->_features->defaultcompletion = false;

        // Enable features to test.
        $this->_features->availability = $this->availability;
        $this->_features->restrictions = $this->restrictions;
        $this->_features->completion = $this->completion;

        // Add standard elements.
        $this->standard_coursemodule_elements();
    }
}
