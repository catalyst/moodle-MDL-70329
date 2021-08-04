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
 * Accessibility interface for brickfield accessibility tool.
 *
 * @package    core_question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_question\admin\tool;

defined('MOODLE_INTERNAL') || die();

/**
 * Class brickfield to return the question data.
 *
 * @package    core_question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class brickfield {

    /**
     * @var int $qtype
     */
    protected $qtype;

    /**
     * @var string $areafield
     */
    protected $areafield;

    /**
     * @var string $fieldname
     */
    protected $fieldname;

    /**
     * brickfield constructor.
     *
     * @param int $qtype
     * @param string $areafield
     * @param string $fieldname
     */
    public function __construct($qtype, $areafield, $fieldname) {
        $this->qtype = $qtype;
        $this->areafield = $areafield;
        $this->fieldname = $fieldname;
    }

    /**
     * Find the relevant areas of the question.
     *
     * @param string $questiondata
     * @return \moodle_recordset
     */
    public function find_relevant_question_areas($questiondata, $refid): ?\moodle_recordset {
        global $DB;

        $sql = "SELECT {$this->qtype} AS type,
                       ctx.id AS contextid,
                       {$this->areafield}
                       q.id AS itemid,
                       {$questiondata}
                       q.{$this->fieldname} AS content
                  FROM {question} q
            INNER JOIN {question_versions} qv
                    ON qv.questionid = q.id
            INNER JOIN {question_bank_entry} qbe
                    ON qbe.id = qv.questionbankentryid
            INNER JOIN {question_categories} qc
                    ON qc.id = qbe.questioncategoryid
            INNER JOIN {context} ctx 
                    ON ctx.id = qc.contextid
                 WHERE (q.id = :refid)
              ORDER BY q.id";

        $rs = $DB->get_recordset_sql($sql, ['refid' => $refid]);
        return $rs;
    }

    /**
     * Find question course areas.
     *
     * @param int $courseid
     * @param array $param
     * @return \moodle_recordset|null
     */
    public function find_question_course_areas($courseid, $param): ?\moodle_recordset {
        global $DB;

        $sql = "SELECT {$this->qtype} AS type,
                       ctx.id AS contextid,
                       {$this->areafield}
                       q.id AS itemid,
                       {$courseid} AS courseid,
                       null AS categoryid,
                       q.{$this->fieldname} AS content
                  FROM {question} q
            INNER JOIN {question_versions} qv
                    ON qv.questionid = q.id
            INNER JOIN {question_bank_entry} qbe
                    ON qbe.id = qv.questionbankentryid
            INNER JOIN {question_categories} qc
                    ON qc.id = qbe.questioncategoryid
            INNER JOIN {context} ctx 
                    ON ctx.id = qc.contextid
                 WHERE (ctx.contextlevel = :ctxcourse 
                   AND ctx.id = qc.contextid 
                   AND ctx.instanceid = :courseid) 
                    OR (ctx.contextlevel = :module 
                   AND {$DB->sql_like('ctx.path', ':coursecontextpath')})
              ORDER BY q.id ASC";

        return $DB->get_recordset_sql($sql, $param);
    }

    /**
     * Find system question areas.
     *
     * @param $params
     * @return \moodle_recordset
     */
    public function find_system_question_areas($params): ?\moodle_recordset {
        global $DB;

        $sql = "SELECT {$this->qtype} AS type,
                       qc.contextid AS contextid,
                       {$this->areafield}
                       q.id AS itemid,
                       " . SITEID . "  as courseid,
                       cc.id as categoryid,
                       q.{$this->fieldname} AS content
                  FROM {question} q
            INNER JOIN {question_versions} qv
                    ON qv.questionid = q.id
            INNER JOIN {question_bank_entry} qbe
                    ON qbe.id = qv.questionbankentryid
            INNER JOIN {question_categories} qc
                    ON qc.id = qbe.questioncategoryid
            INNER JOIN {context} ctx
                    ON ctx.id = qc.contextid
             LEFT JOIN {course_categories} cc
                    ON cc.id = ctx.instanceid
                   AND ctx.contextlevel = :coursecat
                 WHERE (ctx.contextlevel = :syscontext) 
                    OR (ctx.contextlevel = :coursecat2)
              ORDER BY q.id";

        return $DB->get_recordset_sql($sql, $params);
    }

    /**
     * Find the relevant question answer areas of the question.
     *
     * @param string $questiondata
     * @return \moodle_recordset
     */
    public function find_relevant_question_answer_areas($questiondata, $refid, $reftable): ?\moodle_recordset {
        global $DB;

        $sql = "SELECT {$this->qtype} AS type,
                       ctx.id AS contextid,
                       {$this->areafield}
                       a.id AS itemid,
                       {$reftable}
                       q.id AS refid,    
                       {$questiondata}
                       a.{$this->fieldname} AS content
                  FROM {question} q
            INNER JOIN {question_answers} a 
                    ON a.question = q.id
            INNER JOIN {question_versions} qv
                    ON qv.questionid = q.id
            INNER JOIN {question_bank_entry} qbe
                    ON qbe.id = qv.questionbankentryid
            INNER JOIN {question_categories} qc
                    ON qc.id = qbe.questioncategoryid
            INNER JOIN {context} ctx 
                    ON ctx.id = qc.contextid
                 WHERE (q.id = :refid)
              ORDER BY a.id";

        $rs = $DB->get_recordset_sql($sql, ['refid' => $refid]);
        return $rs;
    }

    /**
     * Find question answer area.
     *
     * @param int $courseid
     * @param array $param
     * @param string $reftable
     * @return \moodle_recordset|null
     */
    public function find_question_answer_area($courseid, $param, $reftable): ?\moodle_recordset {
        global $DB;

        $sql = "SELECT {$this->qtype} AS type,
                       ctx.id AS contextid,
                       {$this->areafield}
                       a.id AS itemid,
                       {$reftable}
                       q.id AS refid,    
                       {$courseid} AS courseid,
                       a.{$this->fieldname} AS content
                  FROM {question} q
            INNER JOIN {question_answers} a 
                    ON a.question = q.id
            INNER JOIN {question_versions} qv
                    ON qv.questionid = q.id
            INNER JOIN {question_bank_entry} qbe
                    ON qbe.id = qv.questionbankentryid
            INNER JOIN {question_categories} qc
                    ON qc.id = qbe.questioncategoryid
            INNER JOIN {context} ctx 
                    ON ctx.id = qc.contextid
                 WHERE (ctx.contextlevel = :ctxcourse 
                   AND ctx.id = qc.contextid 
                   AND ctx.instanceid = :courseid) 
                    OR (ctx.contextlevel = :module 
                   AND {$DB->sql_like('ctx.path', ':coursecontextpath')})
              ORDER BY a.id ASC";

        return $DB->get_recordset_sql($sql, $param);
    }

    /**
     * Find system question answer areas.
     *
     * @param $params
     * @return \moodle_recordset
     */
    public function find_system_question_answer_areas($params, $reftable): ?\moodle_recordset {
        global $DB;

        $sql = "SELECT {$this->qtype} AS type,
                       qc.contextid AS contextid,
                       {$this->areafield}
                       a.id AS itemid,
                       {$reftable}
                       q.id AS refid,
                       " . SITEID . "  as courseid,
                       cc.id as categoryid,
                       q.{$this->fieldname} AS content
                  FROM {question} q
            INNER JOIN {question_answers} a 
                    ON a.question = q.id
            INNER JOIN {question_versions} qv
                    ON qv.questionid = q.id
            INNER JOIN {question_bank_entry} qbe
                    ON qbe.id = qv.questionbankentryid
            INNER JOIN {question_categories} qc
                    ON qc.id = qbe.questioncategoryid
            INNER JOIN {context} ctx
                    ON ctx.id = qc.contextid
             LEFT JOIN {course_categories} cc
                    ON cc.id = ctx.instanceid
                   AND ctx.contextlevel = :coursecat
                 WHERE (ctx.contextlevel = :syscontext) 
                    OR (ctx.contextlevel = :coursecat2)
              ORDER BY a.id";

        return $DB->get_recordset_sql($sql, $params);
    }

    /**
     * Get the editquestion url.
     *
     * @param array $param
     * @return \moodle_url
     */
    public static function get_edit_question_url ($param = []): \moodle_url {
        $url = '/question/question.php';
        if (class_exists('qbank_editquestion\\edit_action_column')) {
            $url = '/question/bank/editquestion/question.php';
        }
        return new \moodle_url($url, $param);
    }

    /**
     * Get the course and category data for the question.
     *
     * @param int $coursemodule
     * @param int $refid
     * @return false|mixed
     */
    public static function get_course_and_category ($coursemodule, $refid) {
        global $DB;

        $sql = 'SELECT ctx.instanceid,
                       cm.course as courseid,
                       ctx.contextlevel
                  FROM {question} q
            INNER JOIN {question_versions} qv
                    ON qv.questionid = q.id
            INNER JOIN {question_bank_entry} qbe
                    ON qbe.id = qv.questionbankentryid
            INNER JOIN {question_categories} qc
                    ON qc.id = qbe.questioncategoryid
            INNER JOIN {context} ctx 
                    ON ctx.id = qc.contextid
             LEFT JOIN {course_modules} cm 
                    ON cm.id = ctx.instanceid 
                   AND ctx.contextlevel = :coursemodule
                 WHERE q.id = :refid';
        $params = [
            'coursemodule' => $coursemodule,
            'refid' => $refid
        ];
        return $DB->get_record_sql($sql, $params);
    }
}
