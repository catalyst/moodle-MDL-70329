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
 * For rendering qbank filters on the edit qbank page.
 *
 * @package    qbank_editquestion
 * @copyright  2020 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qbank_editquestion;

use renderer_base;
use stdClass;
use qbank_managecategories\helper;

/**
 * Class for rendering qbank filters on the qbank filter page.
 *
 * @copyright  2021 Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbank_filter extends \core\output\filter {

    /** @var array $searchconditions Searchconditions for the filter. */
    protected $searchconditions = array();

    /**
     * Set searchcondition.
     *
     * @param array $searchconditions
     * @return self
     */
    public function set_searchconditions(array $searchconditions): self {
        $this->searchconditions = $searchconditions;
        return $this;
    }

    /**
     * Get data for all filter types.
     *
     * @return array
     */
    protected function get_filtertypes(): array {

        $filtertypes = [];

        if ($filtertype = $this->get_category_filter()) {
            $filtertypes[] = $filtertype;
        }

        if ($filtertype = $this->get_tag_filter()) {
            $filtertypes[] = $filtertype;
        }

        return $filtertypes;
    }

    /**
     * Get data for the tag filter.
     *
     * @return stdClass|null
     */
    protected function get_tag_filter(): ?stdClass {
        global $CFG;
        if (!$CFG->usetags) {
            return null;
        }

        $tagcondition = $this->searchconditions['tag'];
        return $this->get_filter_object(
            'tag',
            get_string('tag', 'tag'),
            true,
            true,
            null,
            [
                (object) [
                    'value' => ENROL_USER_ACTIVE,
                    'title' => get_string('active'),
                ],
                (object) [
                    'value' => ENROL_USER_SUSPENDED,
                    'title'  => get_string('inactive'),
                ],
            ]
        );
    }

    /**
     * Get data for the category filter.
     *
     * @return stdClass|null
     */
    protected function get_category_filter(): ?stdClass {
        $categorycondition = $this->searchconditions['category'];
        $filteroptions = $categorycondition->get_filter_options();
        return $this->get_filter_object(
            'category',
            get_string('category', 'core_question'),
            true,
            false,
            null,
            $filteroptions,
            true,
        );
    }

    /**
     * Export the renderer data in a mustache template friendly format.
     *
     * @param renderer_base $output Unused.
     * @return stdClass Data in a format compatible with a mustache template.
     */
    public function export_for_template(renderer_base $output): stdClass {
        $defaultcategory = $this->searchconditions['category']->get_default_category();
        return (object) [
            'tableregionid' => $this->tableregionid,
            'courseid' => $this->context->instanceid,
            'filtertypes' => $this->get_filtertypes(),
            'selected' => 'category',
            'rownumber' => 1,
            'defaultcategoryid' => $defaultcategory->id,
        ];
    }
}
