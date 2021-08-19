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

namespace qbank_history;

use core_question\local\bank\view;

/**
 * Custom view class.
 *
 * @package    qbank_history
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_history_view extends view {

    /**
     * Entry id to get the versions
     *
     * @var int $entryid
     */
    protected $entryid;

    /**
     * Base url for the return.
     *
     * @var \moodle_url $basereturnurl
     */
    protected $basereturnurl;

    /**
     * Constructor.
     * @param \core_question\lib\question_edit_contexts $contexts
     * @param \moodle_url $pageurl
     * @param \stdClass $course course settings
     * @param \stdClass $cm activity settings.
     * @param \stdClass $entryid quiz settings.
     */
    public function __construct($contexts, $pageurl, $course, $entryid, $returnurl, $cm = null) {
        parent::__construct($contexts, $pageurl, $course, $cm);
        $this->entryid = $entryid;
        $this->basereturnurl = $returnurl;
    }

    protected function get_question_bank_plugins(): array {
        $questionbankclasscolumns = [];
        $corequestionbankcolumns = [
                'checkbox_column',
                'question_type_column',
                'question_name_idnumber_tags_column',
                'edit_menu_column',
                'edit_action_column',
                'tags_action_column',
                'preview_action_column',
                'delete_action_column',
                'export_xml_action_column',
                'question_status_column',
                'version_number_column',
                'creator_name_column',
                'modifier_name_column',
                'comment_count_column'
        ];
        if (question_get_display_preference('qbshowtext', 0, PARAM_BOOL, new \moodle_url(''))) {
            $corequestionbankcolumns[] = 'question_text_row';
        }

        foreach ($corequestionbankcolumns as $fullname) {
            $shortname = $fullname;
            if (class_exists('qbank_history\\' . $fullname)) {
                $fullname = 'qbank_history\\' . $fullname;
                $questionbankclasscolumns[$shortname] = new $fullname($this);
            } else if (class_exists('core_question\\local\\bank\\' . $fullname)) {
                $fullname = 'core_question\\local\\bank\\' . $fullname;
                $questionbankclasscolumns[$shortname] = new $fullname($this);
            } else {
                $questionbankclasscolumns[$shortname] = '';
            }
        }
        $plugins = \core_component::get_plugin_list_with_class('qbank', 'plugin_feature', 'plugin_feature.php');
        foreach ($plugins as $componentname => $plugin) {
            $pluginentrypointobject = new $plugin();
            $pluginobjects = $pluginentrypointobject->get_question_columns($this);
            // Don't need the plugins without column objects.
            if (empty($pluginobjects)) {
                unset($plugins[$componentname]);
                continue;
            }
            foreach ($pluginobjects as $pluginobject) {
                $classname = new \ReflectionClass(get_class($pluginobject));
                foreach ($corequestionbankcolumns as $key => $corequestionbankcolumn) {
                    if (!\core\plugininfo\qbank::is_plugin_enabled($componentname)) {
                        unset($questionbankclasscolumns[$classname->getShortName()]);
                        continue;
                    }
                    // Check if it has custom preference selector to view/hide.
                    if ($pluginobject->has_preference()) {
                        if (!$pluginobject->get_preference()) {
                            continue;
                        }
                    }
                    if ($corequestionbankcolumn === $classname->getShortName()) {
                        $questionbankclasscolumns[$classname->getShortName()] = $pluginobject;
                    }
                }
            }
        }

        return $questionbankclasscolumns;
    }

    public function wanted_filters($cat, $tagids, $showhidden, $recurse, $editcontexts, $showquestiontext): void {
        global $CFG;
        list(, $contextid) = explode(',', $cat);
        $catcontext = \context::instance_by_id($contextid);
        $thiscontext = $this->get_most_specific_context();
        $this->display_question_bank_header();

        // Display tag filter if usetags setting is enabled/enablefilters is true.
        if ($this->enablefilters) {
            if (is_array($this->customfilterobjects)) {
                foreach ($this->customfilterobjects as $filterobjects) {
                    $this->searchconditions[] = $filterobjects;
                }
            } else {
                if ($CFG->usetags) {
                    array_unshift($this->searchconditions,
                            new \core_question\bank\search\tag_condition([$catcontext, $thiscontext], $tagids));
                }

                array_unshift($this->searchconditions, new \core_question\bank\search\hidden_condition(!$showhidden));
            }
        }
        $this->display_options_form($showquestiontext);
    }

    protected function display_advanced_search_form($advancedsearch): void {
        foreach ($advancedsearch as $searchcondition) {
            echo $searchcondition->display_options_adv();
        }
    }

    protected function create_new_question_form($category, $canadd): void {
        // As we dont want to create questions in this page.
    }

    protected function build_query(): void {
        // Get the required tables and fields.
        $joins = [];
        $fields = ['qv.status', 'qc.id', 'qv.version', 'qv.id as versionid', 'qbe.id as questionbankentryid'];
        if (!empty($this->requiredcolumns)) {
            foreach ($this->requiredcolumns as $column) {
                $extrajoins = $column->get_extra_joins();
                foreach ($extrajoins as $prefix => $join) {
                    if (isset($joins[$prefix]) && $joins[$prefix] != $join) {
                        throw new \coding_exception('Join ' . $join . ' conflicts with previous join ' . $joins[$prefix]);
                    }
                    $joins[$prefix] = $join;
                }
                $fields = array_merge($fields, $column->get_required_fields());
            }
        }
        $fields = array_unique($fields);

        // Build the order by clause.
        $sorts = [];
        foreach ($this->sort as $sort => $order) {
            list($colname, $subsort) = $this->parse_subsort($sort);
            $sorts[] = $this->requiredcolumns[$colname]->sort_expression($order < 0, $subsort);
        }

        // Build the where clause.
        $entryid = "qbe.id = $this->entryid";
        // Changes done here to get the questions only for the passed entryid.
        $tests = ['q.parent = 0', $entryid];
        $this->sqlparams = [];
        foreach ($this->searchconditions as $searchcondition) {
            if ($searchcondition->where()) {
                $tests[] = '((' . $searchcondition->where() .'))';
            }
            if ($searchcondition->params()) {
                $this->sqlparams = array_merge($this->sqlparams, $searchcondition->params());
            }
        }
        // Build the SQL.
        $sql = ' FROM {question} q ' . implode(' ', $joins);
        $sql .= ' WHERE ' . implode(' AND ', $tests);
        $this->countsql = 'SELECT count(1)' . $sql;
        $this->loadsql = 'SELECT ' . implode(', ', $fields) . $sql . ' ORDER BY ' . implode(', ', $sorts);
    }

    protected function display_question_bank_header(): void {
        global $PAGE, $DB;
        $sql = 'SELECT q.*
                 FROM {question} q
                 JOIN {question_versions} qv ON qv.questionid = q.id
                 JOIN {question_bank_entry} qbe ON qbe.id = qv.questionbankentryid
                WHERE qv.version  = (SELECT MAX(v.version)
                                       FROM {question_versions} v
                                       JOIN {question_bank_entry} be 
                                         ON be.id = v.questionbankentryid
                                      WHERE be.id = qbe.id)
                  AND qbe.id = ?';
        $latestquestiondata = $DB->get_record_sql($sql, [$this->entryid]);
        $historydata = [
            'questionname' => $latestquestiondata->name,
            'returnurl' => $this->basereturnurl,
            'questionicon' => print_question_icon($latestquestiondata)
        ];
        // Header for the page before the actual form from the api.
        echo $PAGE->get_renderer('qbank_history')->render_history_header($historydata);
    }

}
