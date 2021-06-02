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
 * A column type for the name of the question name.
 *
 * @package   qbank_viewquestiontext
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_viewquestiontext;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\row_base;

/**
 * A column type for the name of the question name.
 *
 * @copyright 2009 Tim Hunt
 * @author    2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_text_row extends row_base {

    /**
     * To initialise subclasses
     * @var $formatoptions
     */
    protected $formatoptions;

    /**
     * A chance for subclasses to initialise themselves, for example to load lang strings,
     * without having to override the constructor.
     */
    protected function init(): void {
        $this->formatoptions = new \stdClass();
        $this->formatoptions->noclean = true;
        $this->formatoptions->para = false;
    }

    /**
     * Get the internal name for this column. Used as a CSS class name,
     * and to store information about the current sort. Must match PARAM_ALPHA.
     *
     * @return string column name.
     */
    public function get_name(): string {
        return 'questiontext';
    }

    /**
     * Title for this column. Not used if is_sortable returns an array.
     */
    protected function get_title(): string {
        return get_string('questiontext', 'question');
    }

    /**
     * Output the contents of this column.
     * @param object $question the row from the $question table, augmented with extra information.
     * @param string $rowclasses CSS class names that should be applied to this row of output.
     */
    protected function display_content($question, $rowclasses): void {
        $text = question_rewrite_question_preview_urls($question->questiontext, $question->id,
                $question->contextid, 'question', 'questiontext', $question->id,
                $question->contextid, 'core_question');
        $text = format_text($text, $question->questiontextformat,
                $this->formatoptions);
        if ($text == '') {
            $text = '&#160;';
        }
        echo $text;
    }

    /**
     * Return an array 'table_alias' => 'JOIN clause' to bring in any data that
     * this column required.
     *
     * The return values for all the columns will be checked. It is OK if two
     * columns join in the same table with the same alias and identical JOIN clauses.
     * If to columns try to use the same alias with different joins, you get an error.
     * The only table included by default is the question table, which is aliased to 'q'.
     *
     * It is importnat that your join simply adds additional data (or NULLs) to the
     * existing rows of the query. It must not cause additional rows.
     *
     * @return array 'table_alias' => 'JOIN clause'
     */
    public function get_extra_joins(): array {
        return array('qc' => 'JOIN {question_categories} qc ON qc.id = q.category');
    }

    /**
     * Use table alias 'q' for the question table, or one of the
     * ones from get_extra_joins. Every field requested must specify a table prefix.
     * @return array fields required.
     */
    public function get_required_fields(): array {
        return array('q.id', 'q.questiontext', 'q.questiontextformat', 'qc.contextid');
    }

    /**
     * Check if the row has an extra preference to view/hide.
     */
    public function has_preference(): bool {
        return true;
    }

    /**
     * Get if the preference key of the row.
     */
    public function get_preference_key(): string {
        return 'qbshowtext';
    }

    /**
     * Get if the preference of the row.
     */
    public function get_preference(): bool {
        return question_get_display_preference($this->get_preference_key(), 0, PARAM_BOOL, new \moodle_url(''));
    }

}

