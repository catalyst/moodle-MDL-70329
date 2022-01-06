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
 * Date filter.
 *
 * @module     core/local/filter/filtertypes/date
 * @copyright  2022 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Range from 'core/local/filter/range';

 export default class extends Range {
    constructor(filterType, filterSet) {
        super(filterType, filterSet);
        super.setUpRangeUi('date');
    }

    /**
     * Get unix timestamps from provided raw dates.
     *
     * @return {Array}
     */
    get datesFromRaw() {
        const dateOne = new Date(this.rawValues[0]);
        const timeStamps = [dateOne.getTime() / 1000];
        if (this.rawValues[1] !== '' && this.rawValues[1] !== undefined) {
            const dateTwo = new Date(this.rawValues[1]);
            timeStamps.push(dateTwo.getTime() / 1000);
        }
        return timeStamps;
    }

    /**
     * Get the composed value for this filter.
     *
     * @returns {Object}
     */
    get filterValue() {
        return {
            name: this.name,
            jointype: this.jointype,
            rangetype: this.rangetype,
            values: this.datesFromRaw,
        };
    }
 }
