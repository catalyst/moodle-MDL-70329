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
 * Javascript module handling ordering of categories.
 *
 * @package    qbank_managecategories
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import SortableList from 'core/sortable_list';
import CheckboxParam from 'qbank_managecategories/checkbox_param';
import $ from 'jquery';

class OrderCategories {
    /**
     * Sets up sortable list in the column sort order page.
     *
     */
    setupSortableLists = () => {
        new SortableList(
            '.category_list',
            {
                moveHandlerSelector: '.list_item [data-drag-type=move]',
            }
        );
        $('.list_item').on(SortableList.EVENTS.DROP, (evt) => {
            evt.stopPropagation();
            const categoryListElements = $('.list_item').parent();
            // Get moved list item href URL.
            const href = evt.currentTarget.getElementsByTagName('a')[0].href;
            // Get query string for that URL.
            const queryString = href.substr(href.search('\\?'));
            const params = new URLSearchParams(queryString);
            const cat = params.get('cat');
            // Get old context and category id.
            const oldContextId = cat.substr(cat.search(',') + 1);
            const oldCat = cat.substr(0, cat.search(','));
            // Remove proxy created by sortable list.
            $('li.list_item[style]').remove();
            const newOrder = this.getNewOrder(categoryListElements, oldContextId, oldCat);
            // Call external function.
            this.setCatOrder(JSON.stringify(newOrder))
            .then(() => location.reload())
            .catch(() => location.reload());
        });
    };

    /**
     * Call external function set_category_order - inserts the updated column in the question_categories table.
     *
     * @param {string} updatedCategories String containing new sortorder.
     * @returns {Promise}
     */
    setCatOrder = (updatedCategories) => {
        const promise = new Promise((resolve, reject) => {
            const response = Ajax.call([{
                methodname: 'qbank_managecategories_set_category_order',
                args: {categories: updatedCategories},
                fail: Notification.exception
            }]);
            response[0].then((resp) => {
                if (JSON.parse(resp) === false) {
                    reject();
                } else {
                    resolve();
                }
            });
        });
        return promise;
    };

    /**
     * Retrieving the order on EVENT.DROP, also gets new parameter
     *
     * @param {JQuery<HTMLElement>} categoryListElements List of HTML element to parse.
     * @param {int} oldContextId Old context id to change.
     * @param {int} oldCat Old category.
     * @returns {array}
     */
    getNewOrder = (categoryListElements, oldContextId, oldCat) => {
        const oldCtxCat = oldContextId + ' ' + oldCat;
        const newCatOrder = [];
        let destinationCtx = [];
        for (let i = 0; i < categoryListElements.length; i++) {
            const listItems = categoryListElements[i].querySelectorAll('li.list_item');
            const listOrder = [];
            for (let j = 0; j < listItems.length; j++) {
                // Get href parameters.
                const href = listItems[j].getElementsByTagName('a')[0].href;
                const queryString = href.substr(href.search('\\?'));
                const params = new URLSearchParams(queryString);
                // Parameters.
                const cat = params.get('cat');
                const contextId = cat.substr(cat.search(',') + 1);
                cat = cat.substr(0, cat.search(','));
                listOrder[j] = contextId + ' ' + cat;
                if (listOrder[j] === oldCtxCat) {
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
}

export const init = () => {
    const checkboxParam = new CheckboxParam();
    checkboxParam.setEventListenner();
    const orderCat = new OrderCategories();
    orderCat.setupSortableLists();
};
