<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace qbank_usage\tables;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/tablelib.php');

use moodle_url;
use qbank_usage\helper;
use table_sql;

/**
 * Class question_usage_table.
 * An extension of regular Moodle table.
 *
 * @package    qbank_usage
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_usage_table extends table_sql {

    /**
     * Search string.
     *
     * @var string $search
     */
    public $search = '';

    /**
     * Question id.
     *
     * @var \question_definition $question
     */
    public \question_definition $question;

    /**
     * constructor.
     * Sets the SQL for the table and the pagination.
     *
     * @param string $uniqueid
     * @param \question_definition $question
     */
    public function __construct(string $uniqueid, \question_definition $question) {
        global $PAGE;
        parent::__construct($uniqueid);
        $this->question = $question;
        $columns = ['modulename', 'coursename', 'versions', 'attempts'];
        $headers = [
            get_string('modulename', 'qbank_usage'),
            get_string('coursename', 'qbank_usage'),
            get_string('versions', 'qbank_usage'),
            get_string('attempts', 'qbank_usage')
        ];
        $this->is_collapsible = false;
        $this->no_sorting('modulename');
        $this->no_sorting('coursename');
        $this->no_sorting('versions');
        $this->no_sorting('attempts');
        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->define_baseurl($PAGE->url);
    }

    public function query_db($pagesize, $useinitialsbar = true) {
        $param['bankentryid'] = $this->question->questionbankentryid;
        if (isset($this->sql->params)) {
            $this->sql->params = array_merge($this->sql->params, $param);
        } else {
            $this->sql = new \stdClass();
            $this->sql->params = $param;
        }
        $this->rawdata = helper::get_question_entry_usage_data($this->question->questionbankentryid);
    }

    public function col_modulename(\stdClass $values): string {
        $params = [
            'href' => new moodle_url('/mod/quiz/view.php', ['q' => $values->quizid])
        ];
        return \html_writer::tag('a', $values->modulename, $params);
    }

    public function col_coursename(\stdClass $values): string {
        $course = get_course($values->courseid);
        $params = [
            'href' => new moodle_url('/course/view.php', ['id' => $values->courseid])
        ];
        return \html_writer::tag('a', $course->fullname, $params);
    }

    public function col_versions(\stdClass $values): string {
        if ($values->version === null) {
            return get_string('alwayslatest', 'quiz');
        }
        return get_string('questionusageversion', 'qbank_usage', $values->version);
    }

    public function col_attempts(\stdClass $values): string {
        return helper::get_question_attempts_count_in_quiz($this->question->id, $values->quizid);
    }

    /**
     * Export this data so it can be used as the context for a mustache template/fragment.
     *
     * @return string
     */
    public function export_for_fragment(): string {
        ob_start();
        $this->out(20, true);
        $tablehtml = ob_get_contents();
        ob_end_clean();
        return $tablehtml;
    }

}
