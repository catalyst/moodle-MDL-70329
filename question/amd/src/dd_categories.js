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
            moveHandlerSelector: '.list_item',
        }
    );

    jQuery('.list_item').on(SortableList.EVENTS.DROP, () => {
        let categoryListElements = jQuery('.list_item').parent();
        jQuery('li.list_item[style]').remove();
        let val = getParentCategoryList(categoryListElements);
        setCatOrder(JSON.stringify(val));
    });
};

/**
 * Call external function set_order - inserts the updated column in the question_categories table.
 *
 * @returns {Void}
 */
 const setCatOrder = (updatedCategories) => {
    const val = Ajax.call([{
        methodname: 'core_question_set_category_order',
        args: {categories: updatedCategories},
        fail: Notification.exception
    }]);
    val[0].then((response) => console.log(JSON.parse(response)));
};

/**
 * Retrieving the the order on EVENT.DROP, also gets new parameter
 *
 * @returns {Array}
 */
const getParentCategoryList = (categoryListElements) => {
    let newcatorder = [];
    for (let i = 0; i < categoryListElements.length; i++) {
        let listItems = categoryListElements[i].querySelectorAll('li.list_item');
        let listOrder = [];
        for (let j = 0; j < listItems.length; j++) {
            // Get href parameters.
            let href = listItems[j].children[0].children[0].href;
            let queryString = href.substr(href.search('\\?'));
            const params = new URLSearchParams(queryString);
            // Parameters.
            let cmid = params.get('cmid');
            let cat = params.get('cat');
            let contextid = cat.substr(cat.search(',')+1);
            cat = cat.substr(0, cat.search(','));
            console.log(cmid, contextid, cat);
            let categories = listItems[j].firstChild.innerText;
            let propercatname = categories.substr(0, categories.lastIndexOf('('));
            //listOrder[j] = propercatname;
            listOrder[j] = [cmid, cat, contextid];
        }
    newcatorder[i] = listOrder;
    }
    return newcatorder;
};

export const init = () => {
    setupSortableLists();
};