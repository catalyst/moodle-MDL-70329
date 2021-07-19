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
        '.category_list',
        {
            moveHandlerSelector: '.list_item [data-drag-type=move]',
        }
    );
    jQuery('.list_item').on(SortableList.EVENTS.DROP, (evt) => {
        evt.stopPropagation();
        let categoryListElements = jQuery('.list_item').parent();
        // Get moved list item href URL.
        let href = jQuery('li.list_item[style]')[0].children[0].children[0].href;
        // Get query string for that URL.
        let queryString = href.substr(href.search('\\?'));
        const params = new URLSearchParams(queryString);
        let cat = params.get('cat');
        // Get old context and category id.
        let oldContextId = cat.substr(cat.search(',')+1);
        let oldCat = cat.substr(0, cat.search(','));
        // Remove proxy created by sortable list.
        jQuery('li.list_item[style]').remove();
        let newOrder = getNewOrder(categoryListElements, oldContextId, oldCat);
        // Call external function.
        setCatOrder(JSON.stringify(newOrder));
        setTimeout(location.reload(), 30000);
    });
};

/**
 * Call external function set_category_order - inserts the updated column in the question_categories table.
 *
 * @param {String} updatedCategories String containing new sortorder.
 * @returns {Void}
 */
 const setCatOrder = (updatedCategories) => {
    Ajax.call([{
        methodname: 'qbank_managecategories_set_category_order',
        args: {categories: updatedCategories},
        fail: Notification.exception
    }]);
};

/**
 * Retrieving the the order on EVENT.DROP, also gets new parameter
 *
 * @param {JQuery<HTMLElement>} categoryListElements List of HTML element to parse.
 * @param {Number}              oldContextId Old context id to change.
 * @returns {Array}
 */
const getNewOrder = (categoryListElements, oldContextId, oldCat) => {
    let oldCtxCat = oldContextId + ' ' + oldCat;
    let newCatOrder = [];
    let destinationCtx = [];
    for (let i = 0; i < categoryListElements.length; i++) {
        let listItems = categoryListElements[i].querySelectorAll('li.list_item');
        let listOrder = [];
        for (let j = 0; j < listItems.length; j++) {
            // Get href parameters.
            let href = listItems[j].children[0].children[0].href;
            let queryString = href.substr(href.search('\\?'));
            const params = new URLSearchParams(queryString);
            // Parameters.
            let cat = params.get('cat');
            let contextId = cat.substr(cat.search(',')+1);
            cat = cat.substr(0, cat.search(','));
            listOrder[j] = contextId + ' ' + cat;
            if (listOrder[j] == oldCtxCat){
                destinationCtx.push(listOrder);
            }
        }
        // New category order.
        newCatOrder[i] = listOrder;
    }
    destinationCtx = destinationCtx[0];
    destinationCtx = destinationCtx.filter((ctxId) => ctxId !== oldCtxCat);
    return [newCatOrder, destinationCtx[0], oldCtxCat];
};

export const init = () => {
    setupSortableLists();
};