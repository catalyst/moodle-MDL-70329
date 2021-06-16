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
 * A column to show the number of comments.
 *
 * @package    qbank_previewquestion
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_previewquestion;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\column_base;

/**
 * A column type for the name of the question creator.
 *
 * @package    qbank_previewquestion
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class comment_count_column extends column_base {

    /**
     * Get the internal name for this column. Used as a CSS class name,
     * and to store information about the current sort. Must match PARAM_ALPHA.
     *
     * @return string column name.
     */
    public function get_name(): string {
        return 'commentcount';
    }


    /**
     * Not used if is_sortable returns an array.
     * @return string Title for this column
     */
    protected function get_title(): string {
        return get_string('commentplural', 'qbank_previewquestion');
    }

    /**
     * Output the contents of this column.
     * @param object $question the row from the $question table, augmented with extra information.
     * @param string $rowclasses CSS class names that should be applied to this row of output.
     */
    protected function display_content($question, $rowclasses): void {
        global $DB, $OUTPUT;
        $args = array (
                'component' => 'qbank_previewquestion',
                'commentarea' => 'core_question',
                'itemid' => $question->id
        );
        $attr = array();
        $commentcount = $DB->count_records('comments', $args);
        if (question_has_capability_on($question, 'comment')) {

            $context = $this->qbank->get_most_specific_context();
            $url = previewquestion_helper::question_preview_url($question->id, null,
                    null, null, null, $context);
            $attr['href'] = $url;
            $link = new \action_menu_link_secondary($url, null,
                    $commentcount, ['target' => 'questionpreview']);

            echo $OUTPUT->render($link);
        } else {
            echo \html_writer::tag('a', $commentcount);
        }
    }
}
