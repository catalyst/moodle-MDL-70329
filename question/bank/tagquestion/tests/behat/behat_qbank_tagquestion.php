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
 * @package    qbank_tagquestion
 * @category   test
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_qbank_tagquestion extends behat_base {
    /**
     * Add / Append query to current url and visit it for tag filter.
     *
     * @param string $tag tag name to get proper question category id.
     * @param string $course course name to get proper course id.
     * @param string $jointype join type for query.
     *
     * @Given /^I add "(?P<tag>[^"]*)" tag and "(?P<course>[^"]*)" with "(?P<join_type>[^"]*)" join type parameters to url and visit it$/
     */
    public function i_add_tagg_and_with_join_type_parameters_to_url_and_visit_it($tag, $course, $jointype) {
        global $DB;
        $jointypes = [
            'None' => 0,
            'Any' => 1,
            'All' => 2
        ];
        $courseid = $DB->get_field('course', 'id', ['fullname' => $course]);
        $tagid = $DB->get_field('tag', 'id', ['name' => $tag]);
        $querystring = "courseid%3Dname%253Dcourseid%2526jointype%253D1%2526values%253D". $courseid ."%25253D2%26qtagids%3Dname%253Dqtagids%2526jointype%253D" . $jointypes[$jointype] . "%2526values%253D0%25253D" . $tagid;
        $querystring = urldecode($querystring);
        $url = new moodle_url($this->getSession()->getCurrentUrl(), ['filter' => $querystring]);
        $this->execute('behat_general::i_visit', [$url->out(false)]);
    }
}
