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
 * Javascript module handling event listenner for the description checkbox in the managecategories page.
 *
 * @module     qbank_managecategories
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
 * Sets events listenner for checkbox ticking change.
 */
 const setEventListenner = () => {
    let checkbox = document.getElementsByName('qbshowdescr')[0];
    let checkboxform = document.getElementById('qbshowdescr-form');
    if (checkbox !== undefined) {
        checkbox.addEventListener('click', (e) => {
            e.preventDefault();
            checkboxform.submit();
        });
    }
};

export const init = () => {
    setEventListenner();
};
