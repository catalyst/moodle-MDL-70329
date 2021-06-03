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
 * A column type for the name of the question last modifier.
 *
 * @package   qbank_viewcreator
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_viewcreator;
defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\column_base;

/**
 * A column type for the name of the question last modifier.
 *
 * @copyright 2009 Tim Hunt
 * @author    2021 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class modifier_name_column extends column_base {
    /**
     * Get the internal name for this column. Used as a CSS class name,
     * and to store information about the current sort. Must match PARAM_ALPHA.
     *
     * @return string column name.
     */
    public function get_name(): string {
        return 'modifiername';
    }

    /**
     * Not used if is_sortable returns an array.
     * @return string Title for this column
     */
    protected function get_title(): string {
        return get_string('lastmodifiedby', 'question');
    }

    /**
     * Output the contents of this column.
     * @param object $question the row from the $question table, augmented with extra information.
     * @param string $rowclasses CSS class names that should be applied to this row of output.
     */
    protected function display_content($question, $rowclasses): void {
        global $PAGE;
        $displaydata = array();

        if (!empty($question->modifierfirstname) && !empty($question->modifierlastname)) {
            $u = new \stdClass();
            $u = username_load_fields_from_object($u, $question, 'modifier');
            $displaydata['date'] = userdate($question->timemodified, get_string('strftimedatetime', 'langconfig'));
            $displaydata['modifier'] = fullname($u);
            echo $PAGE->get_renderer('qbank_viewcreator')->render_modifier_name($displaydata);
        }
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
     * It is important that your join simply adds additional data (or NULLs) to the
     * existing rows of the query. It must not cause additional rows.
     *
     * @return array 'table_alias' => 'JOIN clause'
     */
    public function get_extra_joins(): array {
        return array('um' => 'LEFT JOIN {user} um ON um.id = q.modifiedby');
    }

    /**
     * Use table alias 'q' for the question table, or one of the
     * ones from get_extra_joins. Every field requested must specify a table prefix.
     * @return array fields required.
     */
    public function get_required_fields(): array {
        $allnames = \core_user\fields::get_name_fields();
        $requiredfields = array();
        foreach ($allnames as $allname) {
            $requiredfields[] = 'um.' . $allname . ' AS modifier' . $allname;
        }
        $requiredfields[] = 'q.timemodified';
        return $requiredfields;
    }

    /**
     * Can this column be sorted on? You can return either:
     *  + false for no (the default),
     *  + a field name, if sorting this column corresponds to sorting on that datbase field.
     *  + an array of subnames to sort on as follows
     *  return array(
     *      'firstname' => array('field' => 'uc.firstname', 'title' => get_string('firstname')),
     *      'lastname' => array('field' => 'uc.lastname', 'title' => get_string('lastname')),
     *      'timemodified' => array('field' => 'q.timemodified', 'title' => get_string('date'))
     *  );
     * As well as field, and field, you can also add 'revers' => 1 if you want the default sort
     * order to be DESC.
     * @return mixed as above.
     */
    public function is_sortable(): array {
        return array(
            'firstname' => array('field' => 'um.firstname', 'title' => get_string('firstname')),
            'lastname' => array('field' => 'um.lastname', 'title' => get_string('lastname')),
            'timemodified' => array('field' => 'q.timemodified', 'title' => get_string('date'))
        );
    }
}

