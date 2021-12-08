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

namespace qbank_tagquestion;

use core_question\local\bank\condition;

/**
 * Question bank search class to allow searching/filtering by tags on a question.
 *
 * @copyright 2018 Ryan Wyllie <ryan@moodle.com>
 * @author    2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag_condition extends condition {

    /** @var string SQL fragment to add to the where clause. */
    protected $where;

    /** @var array Contexts to be used. */
    protected $contexts = [];

    /** @var array List of IDs for tags that have been selected in the form. */
    protected $selectedtagids;

    /** @var array named params for the where clause. */
    protected $params;

    /**
     * Tag condition constructor. It uses the qbank object and initialises all the its required information
     * to be passed as a part of condition to get the questions.
     *
     */
    public function __construct($qbank) {
        global $DB;
        $cat = $qbank->get_pagevars('cat');
        if (is_array($cat)) {
            foreach ($cat as $value) {
                list($category, $contextid) = explode(',', $value);
                $catcontext = \context::instance_by_id($contextid);
                $this->contexts[] = $catcontext;
            }
        } else {
            list($category, $contextid) = explode(',', $qbank->get_pagevars('cat'));
            $catcontext = \context::instance_by_id($contextid);
            $this->contexts[] = $catcontext;
        }
        $thiscontext = $qbank->get_most_specific_context();
        $this->contexts[] = $thiscontext;
        $filters = $qbank->get_pagevars('filters');
        $selectedtagids = $filters['qtagids']['values'] ?? [];
        $filterverb = $filters['qtagids']['jointype'] ?? self::JOINTYPE_DEFAULT;
        // If some tags have been selected then we need to filter
        // the question list by the selected tags.
        if ($selectedtagids) {
            // We treat each additional tag as an AND condition rather than
            // an OR condition.
            //
            // For example, if the user filters by the tags "foo" and "bar" then
            // we reduce the question list to questions that are tagged with both
            // "foo" AND "bar". Any question that does not have ALL of the specified
            // tags will be omitted.
            $equal = !($filterverb === self::JOINTYPE_NONE);
            list($tagsql, $tagparams) = $DB->get_in_or_equal($selectedtagids, SQL_PARAMS_NAMED, 'param', $equal);
            $tagparams['tagcount'] = count($selectedtagids);
            $tagparams['questionitemtype'] = 'question';
            $tagparams['questioncomponent'] = 'core_question';
            $this->selectedtagids = $selectedtagids;
            $this->params = $tagparams;
            $this->where = "q.id IN (SELECT ti.itemid
                                       FROM {tag_instance} ti
                                      WHERE ti.itemtype = :questionitemtype
                                            AND ti.component = :questioncomponent
                                            AND ti.tagid {$tagsql}
                                   GROUP BY ti.itemid ";
            if ($filterverb === self::JOINTYPE_ALL) {
                $this->where .= "HAVING COUNT(itemid) = :tagcount ";
            }
            $this->where .= ") ";

        } else {
            $this->selectedtagids = [];
            $this->params = [];
            $this->where = '';
        }
    }

    public function get_condition_key() {
        return 'tag';
    }

    public function where() {
        return $this->where;
    }

    /**
     * Named SQL params to be used with the SQL WHERE snippet.
     *
     * @return array
     */
    public function params() {
        return $this->params;
    }

    /**
     * Print HTML to display the list of tags to filter by.
     */
    public function display_options() {
        global $PAGE;

        $tags = \core_tag_tag::get_tags_by_area_in_contexts('core_question', 'question', $this->contexts);
        $tagoptions = array_map(function($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'selected' => in_array($tag->id, $this->selectedtagids)
            ];
        }, array_values($tags));
        $context = [
            'tagoptions' => $tagoptions
        ];

        return $PAGE->get_renderer('qbank_tagquestion')->render_tag_condition($context);
    }

    /**
     * Get options for filter.
     *
     * @return array
     */
    public function get_filter_options(): array {
        $tags = \core_tag_tag::get_tags_by_area_in_contexts('core_question', 'question', $this->contexts);
        $values = [];
        foreach ($tags as $tag) {
            $values[] = [
                'value' => $tag->id,
                'title' => html_entity_decode($tag->name),
                'selected' => in_array($tag->id, $this->selectedtagids)
            ];
        }
        $filteroptions = [
            'name' => 'qtagids',
            'title' => get_string('tag', 'tag'),
            'custom' => false,
            'multiple' => true,
            'filterclass' => null,
            'values' => $values,
            'allowempty' => true,
        ];
        return $filteroptions;
    }
}
