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
 * Backup steps for mod_qbank are defined here.
 *
 * @package     mod_qbank
 * @category    backup
 * @copyright   2021 Catalyst IT Australia Pty Ltd
 * @author      Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete qbank structure for backup, with file and id annotations.
 */
class backup_qbank_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $qbank = new backup_nested_element('qbank', ['id'],
            [
                'name',
                'timecreated',
                'timemodified',
                'intro',
                'introformat',
            ]
        );

        // Build the tree
        // (No tree).

        // Define sources
        $qbank->set_source_table('qbank', ['id' => backup::VAR_ACTIVITYID]);

        // Define id annotations
        // (none).

        // Define file annotations.
        $qbank->annotate_files('mod_qbank', 'intro', null); // This file area does not have an itemid.

        // Return the root element (qbank), wrapped into standard activity structure.
        return $this->prepare_activity_structure($qbank);
    }
}
