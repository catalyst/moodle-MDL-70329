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
 * A column type for the name of the question creator.
 *
 * @package    qbank_viewcreator
 * @copyright  2009 Tim Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_viewcreator;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\column_base;

/**
 * A column type for the name of the question creator.
 *
 * @copyright 2009 Tim Hunt
 * @author    2021 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class versioncontrol_column extends column_base {

    public function get_name(): string {
        return 'versioncontrol';
    }

    protected function get_title(): string {
        return get_string('versioncontrol', 'qbank_viewcreator');
    }

    protected function display_content($question, $rowclasses): void {
        global $PAGE;
        $displaydata = [];

        $displaydata['version'] = $question->version;
        $displaydata['creator'] = $question->creatorfirstname . ' ' . $question->creatorlastname;
        $displaydata['lastmodifier'] = $question->modifierfirstname . ' ' . $question->modifierlastname;
        echo $PAGE->get_renderer('qbank_viewcreator')->render_version_control($displaydata);
    }

    public function get_extra_joins(): array {
        return ['uc' => 'LEFT JOIN {user} uc ON uc.id = q.createdby',
                'um' => 'JOIN {user} um ON um.id = q.modifiedby'
                ];
    }

    public function get_required_fields(): array {
        $allnames = \core_user\fields::get_name_fields();
        $requiredfields = [];
        foreach ($allnames as $allname) {
            $requiredfields[] = 'uc.' . $allname . ' AS creator' . $allname;
            $requiredfields[] = 'um.' . $allname . ' AS modifier' . $allname;
        }
        $requiredfields[] = 'qv.version';
        return $requiredfields;
    }

    public function is_sortable(): array {
        return [
            'creator' => ['field' => 'uc.firstname', 'title' => get_string('creator', 'qbank_viewcreator')],
            'lastmodifier' => ['field' => 'um.firstname', 'title' => get_string('lastmodifier', 'qbank_viewcreator')],
            'Version' => ['field' => 'qv.version', 'title' => get_string('version', 'qbank_viewcreator')],
        ];
    }

}

