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
 * Question history view.
 *
 * @package    qbank_history
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
     * Constructor.
     * @param \core_question\lib\question_edit_contexts $contexts
     * @param \moodle_url $pageurl
     * @param \stdClass $course course settings
     * @param \stdClass $cm activity settings.
     * @param \stdClass $entryid quiz settings.
     */
    public function __construct($contexts, $pageurl, $course, $cm, $entryid) {
        parent::__construct($contexts, $pageurl, $course, $cm);
        $this->entryid = $entryid;
    }

    protected function get_question_bank_plugins(): array {
        $questionbankclasscolumns = [];
        $newpluginclasscolumns = [];
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
                $fullname = 'cqbank_history\\' . $fullname;
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
                    } else {
                        // Any community plugin for column/action.
                        $newpluginclasscolumns[$classname->getShortName()] = $pluginobject;
                    }
                }
            }
        }

        // New plugins added at the end of the array, will change in sorting feature.
        foreach ($newpluginclasscolumns as $key => $newpluginclasscolumn) {
            $questionbankclasscolumns[$key] = $newpluginclasscolumn;
        }

        return $questionbankclasscolumns;
    }

    public function wanted_filters($cat, $tagids, $showhidden, $recurse, $editcontexts, $showquestiontext): void {
        // To replace the filter.
    }

    protected function create_new_question_form($category, $canadd): void {
        // To replace with empty form.
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

    protected function print_table($questions): void {
        echo \html_writer::tag('h1', 'test');
        parent::print_table($questions); // TODO: Change the autogenerated stub
    }

}
