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
 * A column with a checkbox for each question with name q{questionid}.
 *
 * @package   qbank_viewcheckbox
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qbank_viewcheckbox;
defined('MOODLE_INTERNAL') || die();

use core\output\checkbox_toggleall;
use core_question\local\bank\column_base;

/**
 * A column with a checkbox for each question with name q{questionid}.
 * @package   qbank_viewcheckbox
 * @copyright 2009 Tim Hunt
 * @author    2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class viewcheckbox_column extends column_base {

    /**
     * Get the internal name for this column. Used as a CSS class name,
     * and to store information about the current sort. Must match PARAM_ALPHA.
     *
     * @return string column name.
     */
    public function get_name(): string {
        return 'checkbox';
    }

    /**
     * Title for this column. Not used if is_sortable returns an array.
     */
    protected function get_title() {
        global $OUTPUT;

        $mastercheckbox = new checkbox_toggleall('qbank', true, [
                'id' => 'qbheadercheckbox',
                'name' => 'qbheadercheckbox',
                'value' => '1',
                'label' => get_string('selectall'),
                'labelclasses' => 'accesshide',
        ]);

        return $OUTPUT->render($mastercheckbox);
    }

    /**
     * Use this when get_title() returns
     * something very short, and you want a longer version as a tool tip.
     * @return string a fuller version of the name.
     */
    protected function get_title_tip() {
        return get_string('selectquestionsforbulk', 'question');
    }

    /**
     * Output the contents of this column.
     * @param object $question the row from the $question table, augmented with extra information.
     * @param string $rowclasses CSS class names that should be applied to this row of output.
     */
    protected function display_content($question, $rowclasses): void {
        global $OUTPUT;

        $checkbox = new checkbox_toggleall('qbank', false, [
                'id' => "checkq{$question->id}",
                'name' => "q{$question->id}",
                'value' => '1',
                'label' => get_string('select'),
                'labelclasses' => 'accesshide',
        ]);

        echo $OUTPUT->render($checkbox);
    }

    /**
     * Use table alias 'q' for the question table, or one of the
     * ones from get_extra_joins. Every field requested must specify a table prefix.
     * @return array fields required.
     */
    public function get_required_fields(): array {
        return array('q.id');
    }
}
