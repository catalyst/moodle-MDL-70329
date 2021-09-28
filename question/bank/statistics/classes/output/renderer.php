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

namespace qbank_statistics\output;

use qbank_statistics\helper;
/**
 * Description
 *
 * @package    qbank_statistics
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {

    /**
     * Render facility index column.
     *
     * @param float $facility facility index
     * @return string
     */
    public function render_facility_index($facility): string {
        $displaydata['facility_index'] = helper::format_percentage($facility);
        return $this->render_from_template('qbank_statistics/facility_index', $displaydata);
    }

    /**
     * Render discriminative_efficiency column.
     *
     * @param float $discriminative_efficiency discriminative efficiency
     * @return string
     */
    public function render_discriminative_efficiency($discriminative_efficiency): string {
        $displaydata['discriminative_efficiency'] = helper::format_percentage($discriminative_efficiency, false);
        return $this->render_from_template('qbank_statistics/discriminative_efficiency', $displaydata);
    }
}
