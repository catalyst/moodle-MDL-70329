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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * Steps definitions related with the drag and drop into text question type.
 * @package    qbank_statistics
 * @category   test
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_qbank_statistics extends behat_base {
    /**
     * Add / Append query to current url and visit it for discrimination index filter.
     *
     * @param string $discrimination discrimination value(s) - multiple values can be passed ie: "20-50".
     * @param string $course course name to get proper course id.
     * @param string $jointype join type for query.
     * @param string $rangetype range type for query.
     *
     * @Given /^I add "(?P<discrimination_value>[^"]*)" discrimination values and "(?P<course>[^"]*)" with "(?P<join_type>[^"]*)" join type and "(?P<range_type>[^"]*)" range type and parameters to url and visit it$/
     */
    public function i_add_discrimination_values_and_with_join_type_and_range_type_and_parameters_to_url_and_visit_it($discrimination, $course, $jointype, $rangetype) {
        global $DB;
        $jointypes = [
            'None' => 0,
            'Any' => 1,
            'All' => 2
        ];
        $rangetypes = [
            'After' => 0,
            'Before' => 1,
            'Between' => 2
        ];
        $valueone = $discrimination;
        $valuetwo = null;
        if (strpos($discrimination, '-') !== false) {
            $valueone = explode('-', $discrimination)[0];
            $valuetwo = explode('-', $discrimination)[1];
        }
        $courseid = $DB->get_field('course', 'id', ['fullname' => $course]);
        $querystring = "courseid%3Dname%253Dcourseid%2526jointype%253D1%2526values%253D" . $courseid ."%25253D2%26discrimination%3Dname%253Ddiscrimination%2526jointype%253D" .
            $jointypes[$jointype] . "%2526rangetype%253D" . $rangetypes[$rangetype] . "%2526values%253D0%25253D" . $valueone;
        if (strpos($discrimination, '-') !== false) {
            $querystring .= "%2525261%25253D" . $valuetwo;
        }
        $querystring = urldecode($querystring);
        $url = new moodle_url($this->getSession()->getCurrentUrl(), ['filter' => $querystring]);
        $this->execute('behat_general::i_visit', [$url->out(false)]);
    }
}
