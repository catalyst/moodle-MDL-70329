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
 * @module     qbank_managecategories
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

import $ from 'jquery';
import Ajax from 'core/ajax';
import Fragment from 'core/fragment';
import Notification from 'core/notification';
import SortableList from 'core/sortable_list';
import Templates from 'core/templates';

new SortableList(
    '.category_list',
    {
        moveHandlerSelector: '.list_item [data-drag-type=move]',
    }
);

/**
 * Sets up sortable list in the column sort order page.
 *
 * @param {number} contextid Context id for fragment.
 */
const setupSortableLists = (contextid) => {
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
        const newOrder = getNewOrder(categoryListElements, oldContextId, oldCat);
        // Call external function.
        const newCatOrder = JSON.stringify(newOrder[0]);
        let destinationContext = oldContextId;
        if (newOrder[1] !== undefined) {
            const destination = newOrder[1].split(',');
            destinationContext = destination[1];
        }
        const origin = newOrder[2].split(',');
        const originContext = origin[1];
        const originCategory = origin[0];
        setCatOrder(newCatOrder, originCategory, destinationContext, originContext)
        .then(() => {
            return getCategoriesFragment(contextid).done((html, js) => {
                Templates.replaceNodeContents('#categoriesrendered', html, js);
            });
        })
        .catch((error) => {
            Notification.addNotification({
                message: error.error,
                type: 'error'
            });
            return getCategoriesFragment(contextid).done((html, js) => {
                document.getElementsByClassName('alert-danger')[0].scrollIntoView();
                Templates.replaceNodeContents('#categoriesrendered', html, js);
            });
        });
    });
};

/**
 * Call category_rendering fragment.
 *
 * @param {int} contextid String containing new ordered categories.
 * @returns {Promise}
 */
const getCategoriesFragment = (contextid) => {
    let params = {
        url: location.href,
    };
    return Fragment.loadFragment('qbank_managecategories', 'category_rendering', contextid, params);
};

/**
 * Call external function update_category_order - inserts the updated column in the question_categories table.
 *
 * @param {string} newCatOrder String containing new ordered categories.
 * @param {number} originCategory Category which was dragged.
 * @param {number} destinationContext Context where category was dropped.
 * @param {number} originContext Context from which the category was dragged.
 * @returns {Promise}
 */
const setCatOrder = (newCatOrder, originCategory, destinationContext, originContext) => {
    const promise = new Promise((resolve, reject) => {
        const response = Ajax.call([{
            methodname: 'qbank_managecategories_update_category_order',
            args: {
                neworder: newCatOrder,
                origincategory: originCategory,
                destinationcontext: destinationContext,
                origincontext: originContext,
            },
            fail: Notification.exception
        }]);
        response[0].then((resp) => {
            if (resp.success === true) {
                resolve();
            } else {
                reject(resp);
            }
            return;
        }).catch(() => {
            return;
        });
    });
    return promise;
};

/**
 * Retrieving the order on EVENT.DROP, also gets new parameter.
 *
 * @param {JQuery<HTMLElement>} categoryListElements List of HTML element to parse.
 * @param {number} oldContextId Old context id to change.
 * @param {number} oldCat Old category.
 * @returns {array}
 */
const getNewOrder = (categoryListElements, oldContextId, oldCat) => {
    const oldCtxCat = oldCat + ',' + oldContextId;
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
            const categories = params.get('cat');
            listOrder[j] = categories;
            if (categories === oldCtxCat) {
                destinationCtx.push(listOrder);
            }
        }
        // New category order.
        newCatOrder[i] = listOrder;
    }
    destinationCtx = destinationCtx[0];
    destinationCtx = destinationCtx.filter((ctxId) => ctxId.split(',')[1] != oldContextId);
    return [newCatOrder, destinationCtx[0], oldCtxCat];
};

/**
 * Method to add listenner on category arrow - descendants.
 *
 * @param {number} contextid Context id for fragment.
 */
const categoryParentListenner = (contextid) => {
    const categorycontainer = document.getElementById('categoriesrendered');
    if (categorycontainer) {
        categorycontainer.addEventListener('click', (e) => {
            if (e.target.parentNode.classList.contains('action-icon')) {
                const data = e.target.parentNode.dataset;
                const response = Ajax.call([{
                    methodname: 'qbank_managecategories_update_category_order',
                    args: {
                        origincategory: data.tomove,
                        tocategory: data.tocategory,
                    },
                    fail: Notification.exception
                }]);
                response[0].then(() => {
                    getCategoriesFragment(contextid).done((html, js) => {
                        Templates.replaceNodeContents('#categoriesrendered', html, js);
                    });
                    return;
                }).catch(() => {
                    return;
                });
            }
        });
    }
};

export const init = (contextid) => {
    categoryParentListenner(contextid);
    setupSortableLists(contextid);
};
