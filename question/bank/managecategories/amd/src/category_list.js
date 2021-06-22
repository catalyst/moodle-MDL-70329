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

import Ajax from 'core/ajax';
import Fragment from 'core/fragment';
import Notification from 'core/notification';
import Templates from 'core/templates';


const SELECTORS = {
    CATEGORY_ITEM: '.category_list .list_item',
    BUTTON: '[role=button]',
};

/**
 * Sets up sortable list in the column sort order page.
 * @param {number} pagecontextid Context id for fragment.
 */
const setupSortableLists = (pagecontextid) => {
    const listitems = document.querySelectorAll(SELECTORS.CATEGORY_ITEM);

    let sourceid;

    /**
     * Get touch target at touch point.
     * @param {Object} e event
     * @returns {any | Element}
     */
    const getTouchTarget = (e) => {
        const target = document.elementFromPoint(
            e.changedTouches[0].pageX,
            e.changedTouches[0].pageY
        );
        // Check if the target is the list item.
        return target.closest(SELECTORS.CATEGORY_ITEM);
    };

    /**
     * Handle Drag start
     * @param {Object} e event
     */
    const handleDragStart = (e) => {
        // Return if it is a button.
        if (e.target.closest(SELECTORS.BUTTON)) {
            return;
        }

        // Identify the event target.
        let target;

        if (e.type == 'touchstart') {
            target = getTouchTarget(e);
        } else {
            target = e.target.closest(SELECTORS.CATEGORY_ITEM);
        }

        if (e.cancelable) {
            e.preventDefault();
        }

        // Save current category id.
        sourceid = target.id;
    };

    /**
     * Handle Drag move
     * @param {Object} e event
     */
    const handleDrag = (e) => {
        // Return if it is a button.
        if (e.target.closest(SELECTORS.BUTTON)) {
            return;
        }

        // Identify the event target.
        let target;
        if (e.type == 'touchmove') {
            target = getTouchTarget(e);
        } else {
            target = e.target.closest(SELECTORS.CATEGORY_ITEM);
        }

        // Return if target not category list item.
        if (!target) {
            return;
        }

        // Return if sourceid is not set.
        if (!sourceid) {
            return;
        }

        if (e.cancelable) {
            e.preventDefault();
        }

        // Highlight the target.
        listitems.forEach(item => {
            item.classList.remove('border-danger');
        });
        target.classList.add('border-danger');
    };

    /**
     * Handle Drag end
     * @param {Object} e event
     */
    const handleDragEnd = (e) => {
        // Return if it is a button.
        if (e.target.closest(SELECTORS.BUTTON)) {
            return;
        }

        // Identify the event target.
        let target;
        if (e.type == 'touchend') {
            target = getTouchTarget(e);
        } else {
            target = e.target.closest(SELECTORS.CATEGORY_ITEM);
        }

        if (!target) {
            return;
        }

        if (e.cancelable) {
            e.preventDefault();
        }

        // This is not a drag and drop event or drop on the same element.
        if (!sourceid || sourceid == target.id) {
            return;
        }

        // Source item.
        const source = document.getElementById(sourceid);
        if (!source) {
            return;
        }

        // Reset sourceid.
        sourceid = null;

        // Insert the source item after the "target" item.
        target.closest('.category_list').insertBefore(source, target.nextSibling);

        // Old category.
        const originCategory = source.dataset.categoryid;

        // Insert after this category.
        const insertaftercategory = target.dataset.categoryid;

        // Insert the category after the target category
        setCatOrder(originCategory, insertaftercategory, pagecontextid);
    };

    // Add event to list item.
    listitems.forEach(item => {
        // Touch events.
        item.addEventListener('touchstart', handleDragStart);
        item.addEventListener('touchmove', handleDrag);
        item.addEventListener('touchend', handleDragEnd);

        // Mouse events.
        item.addEventListener('mousedown', handleDragStart);
        item.addEventListener('mousemove', handleDrag);
        item.addEventListener('mouseup', handleDragEnd);
    });
};

/**
 * Call category_rendering fragment.
 *
 * @param {number} contextid String containing new ordered categories.
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
 * @param {number} origincategory Category which was dragged.
 * @param {number} insertaftercategory Context where category was dropped.
 * @param {number} pagecontextid Context from which the category was dragged.
 */
const setCatOrder = (origincategory, insertaftercategory, pagecontextid) => {
    const call = {
        methodname: 'qbank_managecategories_update_category_order',
        args: {
            origincategory: origincategory,
            insertaftercategory: insertaftercategory,
        }
    };
    Ajax.call([call])[0]
        .then(() => {
            return getCategoriesFragment(pagecontextid);
        })
        .catch(error => {
            Notification.addNotification({
                message: error.message,
                type: 'error',
            });
            document.getElementsByClassName('alert-danger')[0]?.scrollIntoView();
            return getCategoriesFragment(pagecontextid);
        })
        .then((html, js) => {
            Templates.replaceNode('#categoriesrendered', html, js);
            return;
        })
        .catch(Notification.exception);
};


/**
 * Method to add listener on category arrow - descendants.
 *
 * @param {number} pagecontextid Context id for fragment.
 */
const categoryParentListener = (pagecontextid) => {
    document.addEventListener('click', e => {

        // Ignore if there is no categories containers.
        if (!e.target.closest('#categoriesrendered')) {
            return;
        }

        // Ignore if there is no action icon.
        const actionIcon = e.target.closest('.action-icon');
        if (!actionIcon) {
            return;
        }

        // Retrieve data from action icon.
        const data = actionIcon.dataset;

        // Move category.
        const call = {
            methodname: 'qbank_managecategories_update_category_order',
            args: {
                origincategory: data.tomove,
                newparentcategory: data.tocategory,
            }
        };

        Ajax.call([call])[0]
            .then(() => getCategoriesFragment(pagecontextid))
            .then((html, js) => {
                Templates.replaceNode('#categoriesrendered', html, js);
                return;
            })
            .catch(Notification.exception);
    });
};

/**
 * Sets events listener for checkbox ticking change.
 */
const setupShowDescriptionCheckbox = () => {
    document.addEventListener('click', e => {
        const checkbox = e.target.closest('[name="qbshowdescr"]');
        if (!checkbox) {
            return;
        }
        checkbox.form.submit();
    });
};

export const init = (pagecontextid) => {
    categoryParentListener(pagecontextid);
    setupSortableLists(pagecontextid);
    setupShowDescriptionCheckbox();
};
