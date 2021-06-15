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
 * Javascript for report card display and processing.
 *
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
*/

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import SortableList from 'core/sortable_list';
import jQuery from 'jquery';

/**
 * Sets up sortable list in the column sort order page.
 */
const setupSortableLists = () => {
    new SortableList(
        '.list',
        {
            moveHandlerSelector: '.item',
        }
    );

    jQuery('.item').on(SortableList.EVENTS.DROP, () => {
        let columnorder = getColumnOrder();
        setOrder(JSON.stringify(columnorder));
    });
};

/**
 * Call external function set_order - inserts the updated column in the config_plugins table.
 *
 * @param {String} updatedcolumn String that contains column order.
 */
const setOrder = (updatedcolumn) => {
    Ajax.call([{
        methodname: 'core_question_set_columnbank_order',
        args: {columns: updatedcolumn},
        fail: Notification.exception
    }]);
};

/**
 * Gets an array duplicate.
 *
 * @param {Array} columnsDuplicate Array to search duplicates for.
 * @returns {Object}
 */
const findDuplicates = (columnsDuplicate) => {
    return columnsDuplicate.filter((item, index) => columnsDuplicate.indexOf(item) !== index);
};

/**
 * Gets the newly reordered columns to display in the question bank view.
 *
 * @returns {String}
 */
const getColumnOrder = () => {
    let updated = [...document.querySelectorAll('.retrievable')];
    let columns = new Array(updated.length);
    for (let i = 0; i < updated.length; i++) {
        columns[i] = updated[i].innerText.trim();
    }
    if (findDuplicates(columns).length !== 0) {
        columns.pop();
    }
    return columns.join();
};

export const init = () => {
    setupSortableLists();
};
