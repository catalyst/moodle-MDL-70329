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
 * Filter last used question display.
 *
 * @module     core/local/filter/filtertypes/lastused
 * @author     2022 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @copyright  2022 Catalyst IT Australia Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Binary from 'core/local/filter/binary';
import {get_strings as getStrings} from 'core/str';
export default class extends Binary {
    constructor(filterType, filterSet, initialValues) {
        super(filterType, filterSet, initialValues);
        this.getTextValues().then(() => {
            this.displayBinarySelection();
        });
    }

    /**
     * Text values for select element.
     *
     * @returns {Promise}
     */
    getTextValues() {
        return getStrings([
            {key: 'lastused', component: 'qbank_usage'},
            {key: 'notused', component: 'qbank_usage'},
        ]).then((strings) => {
            this.optionOne = strings[0];
            this.optionTwo = strings[1];
        });
    }
}
