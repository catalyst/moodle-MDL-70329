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
 * A question bank column showing the question name with idnumber and tags.
 *
 * @package   qbank_viewquestionname
 * @copyright 2019 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_viewquestionname;

defined('MOODLE_INTERNAL') || die();

/**
 * A question bank column showing the question name with idnumber and tags.
 *
 * @copyright 2019 The Open University
 * @author    2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_name_idnumber_tags_column extends viewquestionname_column_helper {

    /**
     * Get the internal name for this column. Used as a CSS class name,
     * and to store information about the current sort. Must match PARAM_ALPHA.
     *
     * @return string column name.
     */
    public function get_name(): string {
        return 'qnameidnumbertags';
    }

    /**
     * Output the contents of this column.
     * @param object $question the row from the $question table, augmented with extra information.
     * @param string $rowclasses CSS class names that should be applied to this row of output.
     */
    protected function display_content($question, $rowclasses): void {
        global $OUTPUT, $PAGE;
        $displaydata = array();
        $displaydata['haslabel'] = false;
        $displaydata['labelfor'] = $this->label_for($question);
        if ($displaydata['labelfor']) {
            $displaydata['haslabel'] = true;
        }

        // Question name.
        $displaydata['namecontent'] = $question->name;

        // Question idnumber.
        if ($question->idnumber !== null && $question->idnumber !== '') {
            $displaydata['idnumber'] = s($question->idnumber);
        }

        // Question tags.
        if (!empty($question->tags)) {
            $tags = \core_tag_tag::get_item_tags('core_question', 'question', $question->id);
            $displaydata['tags'] = $OUTPUT->tag_list($tags, null, 'd-inline flex-shrink-1 text-truncate ml-1', 0, null, true);
        }

        echo $PAGE->get_renderer('qbank_viewquestionname')->render_question_name($displaydata);
    }

    /**
     * Use table alias 'q' for the question table, or one of the
     * ones from get_extra_joins. Every field requested must specify a table prefix.
     * @return array fields required.
     */
    public function get_required_fields(): array {
        $fields = parent::get_required_fields();
        $fields[] = 'q.idnumber';
        return $fields;
    }

    /**
     * Can this column be sorted on? You can return either:
     *  + false for no (the default),
     *  + a field name, if sorting this column corresponds to sorting on that datbase field.
     *  + an array of subnames to sort on as follows
     *  return array(
     *      'firstname' => array('field' => 'uc.firstname', 'title' => get_string('firstname')),
     *      'lastname' => array('field' => 'uc.lastname', 'title' => get_string('lastname')),
     *  );
     * As well as field, and field, you can also add 'revers' => 1 if you want the default sort
     * order to be DESC.
     * @return mixed as above.
     */
    public function is_sortable(): array {
        return [
                'name' => ['field' => 'q.name', 'title' => get_string('questionname', 'question')],
                'idnumber' => ['field' => 'q.idnumber', 'title' => get_string('idnumber', 'question')],
        ];
    }

    /**
     * If this column needs extra data (e.g. tags) then load that here.
     *
     * The extra data should be added to the question object in the array.
     * Probably a good idea to check that another column has not already
     * loaded the data you want.
     *
     * @param \stdClass[] $questions the questions that will be displayed.
     */
    public function load_additional_data(array $questions): void {
        parent::load_additional_data($questions);
        parent::load_question_tags($questions);
    }

}

