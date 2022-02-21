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
 * The task that provides all the steps to perform a complete backup is defined here.
 *
 * @package     mod_qbank
 * @category    backup
 * @copyright   2021 Catalyst IT Australia Pty Ltd
 * @author      Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/qbank/backup/moodle2/backup_qbank_stepslib.php');

/**
 * choice backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_qbank_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Qbank only has one structure step
        $this->add_step(new backup_qbank_activity_structure_step('qbank_structure', 'qbank.xml'));

        // Process all the annotated questions to calculate the question
        // categories needing to be included in backup for this activity
        // plus the categories belonging to the activity context itself.
        $this->add_step(new backup_calculate_question_categories('activity_question_categories'));

        // Clean backup_temp_ids table from questions. We already
        // have used them to detect question_categories and aren't
        // needed anymore.
        $this->add_step(new backup_delete_temp_questions('clean_temp_questions'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        // Link to the list of qbanks.
        $search = "/(".$base."\/mod\/qbank\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@QBANKINDEX*$2@$', $content);

        // Link to qbank view by moduleid.
        $search = "/(".$base."\/mod\/qbank\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@QBANKVIEWBYID*$2@$', $content);

        return $content;
    }
}
