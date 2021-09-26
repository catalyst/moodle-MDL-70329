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

namespace qbank_bulkmove;

/**
 * Bulk move helper.
 *
 * @package    qbank_bulkmove
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Bulk move questions to a category.
     *
     * @param $movequestionselected
     * @param $tocategory
     */
    public static function bulk_move_questions($movequestionselected, $tocategory): void {
        global $DB;
        if ($questionids = explode(',', $movequestionselected)) {
            list($usql, $params) = $DB->get_in_or_equal($questionids);
            $sql = "SELECT q.*, c.contextid
                      FROM {question} q
                      JOIN {question_categories} c ON c.id = q.category
                     WHERE q.id 
                     {$usql}";
            $questions = $DB->get_records_sql($sql, $params);
            foreach ($questions as $question) {
                question_require_capability_on($question, 'move');
            }
            question_move_questions_to_category($questionids, $tocategory->id);
        }
    }

    /**
     * Get the display data for the move form.
     *
     * @param $addcontexts
     * @param $moveurl
     * @param $returnurl
     * @return array
     */
    public static function get_displaydata($addcontexts, $moveurl, $returnurl) {
        $displaydata = [];
        $displaydata ['categorydropdown'] = \qbank_managecategories\helper::question_category_select_menu($addcontexts,
            false, 0, '', -1, true);
        $displaydata ['moveurl'] = $moveurl;
        $displaydata['returnurl'] = $returnurl;
        return $displaydata;
    }

    /**
     * Process the question came from the form post.
     *
     * @param $rawquestions
     * @return array
     */
    public static function process_question_ids($rawquestions) {
        $questionids = [];
        $questionlist = '';
        foreach ($rawquestions as $key => $value) {
            // Parse input for question ids.
            if (preg_match('!^q([0-9]+)$!', $key, $matches)) {
                $key = $matches[1];
                $questionids[] = $key;
                $questionlist .= $key.',';
            }
        }
        if (!empty($questionlist)) {
            $questionlist = rtrim($questionlist, ',');
        }
        return [$questionids, $questionlist];
    }
}
