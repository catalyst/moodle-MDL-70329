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
 * Question bank columns for the preview action icon.
 *
 * @package   qbank_previewquestion
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_previewquestion;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\menu_action_column_base;

/**
 * Question bank columns for the preview action icon.
 *
 * @copyright 2009 Tim Hunt
 * @author    2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class preview_action_column extends menu_action_column_base {
    /**
     * @var string store this lang string for performance.
     */
    protected $strpreview;

    /**
     * A chance for subclasses to initialise themselves, for example to load lang strings,
     * without having to override the constructor.
     */
    public function init(): void {
        parent::init();
        $this->strpreview = get_string('preview');
    }

    /**
     * Get the internal name for this column. Used as a CSS class name,
     * and to store information about the current sort. Must match PARAM_ALPHA.
     *
     * @return string column name.
     */
    public function get_name(): string {
        return 'previewaction';
    }

    /**
     * Get the information required to display this action either as a menu item or a separate action column.
     *
     * If this action cannot apply to this question (e.g. because the user does not have
     * permission, then return [null, null, null].
     *
     * @param \stdClass $question the row from the $question table, augmented with extra information.
     * @return array with three elements.
     *      $url - the URL to perform the action.
     *      $icon - the icon for this action. E.g. 't/delete'.
     *      $label - text label to display in the UI (either in the menu, or as a tool-tip on the icon)
     */
    protected function get_url_icon_and_label(\stdClass $question): array {
        if (!\question_bank::is_qtype_installed($question->qtype)) {
            // It sometimes happens that people end up with junk questions
            // in their question bank of a type that is no longer installed.
            // We cannot do most actions on them, because that leads to errors.
            return [null, null, null];
        }

        if (question_has_capability_on($question, 'use')) {
            $context = $this->qbank->get_most_specific_context();
            $url = previewquestion_helper::question_preview_url($question->id, null, null, null, null, $context);
            return [$url, 't/preview', $this->strpreview];
        } else {
            return [null, null, null];
        }
    }

    /**
     * Output the contents of this column.
     * @param object $question the row from the $question table, augmented with extra information.
     * @param string $rowclasses CSS class names that should be applied to this row of output.
     */
    protected function display_content($question, $rowclasses): void {
        global $PAGE;

        if (!\question_bank::is_qtype_installed($question->qtype)) {
            // It sometimes happens that people end up with junk questions
            // in their question bank of a type that is no longer installed.
            // We cannot do most actions on them, because that leads to errors.
            return;
        }

        if (question_has_capability_on($question, 'use')) {
            echo $PAGE->get_renderer('qbank_previewquestion')->question_preview_link(
                    $question->id, $this->qbank->get_most_specific_context(), false);
        }
    }

    /**
     * Return the appropriate action menu link, or null if it does not apply to this question.
     *
     * @param \stdClass $question data about the question being displayed in this row.
     * @return \action_menu_link|null the action, if applicable to this question.
     */
    public function get_action_menu_link(\stdClass $question): ?\action_menu_link {
        if (!\question_bank::is_qtype_installed($question->qtype)) {
            // It sometimes happens that people end up with junk questions
            // in their question bank of a type that is no longer installed.
            // We cannot do most actions on them, because that leads to errors.
            return null;
        }

        if (!question_has_capability_on($question, 'use')) {
            return null;
        }

        $context = $this->qbank->get_most_specific_context();
        $url = previewquestion_helper::question_preview_url($question->id, null, null, null, null, $context);
        return new \action_menu_link_secondary($url, new \pix_icon('t/preview', ''),
                $this->strpreview, ['target' => 'questionpreview']);
    }
}
