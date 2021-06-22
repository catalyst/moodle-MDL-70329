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
 * @package    qbank_managecategories
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
 *
 * @returns {Void}
 */
const setupSortableLists = () => {
    new SortableList(
        '.list',
        {
            moveHandlerSelector: '.catitem',
        }
    );

    jQuery('.catitem').on(SortableList.EVENTS.DROP, () => {
        console.log('dropped');
        setCatOrder(JSON.stringify('testing'));
    });
};

/**
 * Call external function set_order - inserts the updated column in the config_plugins table.
 *
 * @returns {Void}
 */
 const setCatOrder = (updatedCat) => {
    Ajax.call([{
        methodname: 'core_question_set_category_order',
        args: { categories: updatedCat },
        fail: Notification.exception
    }]);
};

export const init = () => {
    window.console.log('we have been started');
    setupSortableLists();
};