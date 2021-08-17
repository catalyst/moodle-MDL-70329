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

/**
 * Manage usage table of qbank_usage.
 *
 * @package    qbank_usage
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_usage\tables;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/tablelib.php');

use DateTime;
use moodle_url;
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
     * @var string
     */
    public $search = '';

    /**
     * constructor.
     * Sets the SQL for the table and the pagination.
     *
     * @param string $uniqueid
     * @param int $questionid
     */
    public function __construct(string $uniqueid, int $questionid) {
        global $PAGE;
        parent::__construct($uniqueid);

        $columns = ['modulename', 'coursename', 'state', 'versions', 'attempts', 'lastused'];
        $headers = [
            get_string('modulename', 'qbank_usage'),
            get_string('coursename', 'qbank_usage'),
            get_string('state', 'qbank_usage'),
            get_string('versions', 'qbank_usage'),
            get_string('attempts', 'qbank_usage'),
            get_string('lastused', 'qbank_usage')
        ];
        $this->is_collapsible = false;
        $this->define_columns($columns);
        $this->define_headers($headers);
        $params = [];
        $sql = '';
        $this->set_count_sql($sql, $params);
        $this->define_baseurl($PAGE->url);
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
