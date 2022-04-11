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
import ModalFactory from 'core/modal_factory';
import {get_string as getString} from 'core/str';

const SELECTORS = {
    CATEGORY_LIST: '.category_list',
    MODAL_CATEGORY_ITEM: '.modal_category_item[data-categoryid]',
    CATEGORY_RENDERED: '#categoriesrendered',
    ACTIONABLE_ELEMENT: 'a, [role="button"], [role="menuitem"]',
    SHOW_DESCRIPTION_CHECKBOX: '[name="qbshowdescr"]',
    MOVE_CATEGORY_MENU_ITEM: '[role="menuitem"][data-actiontype="move"]',
    DRAGGABLE_ITEM: '[draggable="true"]',
    DROPPABLE_ITEM: '.list_item[data-categoryid]',
};

/**
 * Sets up sortable list in the column sort order page.
 * @param {number} pagecontextid Context id for fragment.
 */
const setupSortableLists = (pagecontextid) => {
    const draggableitems = document.querySelectorAll(SELECTORS.DRAGGABLE_ITEM);
    const droppableitems = document.querySelectorAll(SELECTORS.DROPPABLE_ITEM);

    // Touch events do not have datatranfer property.
    // This variable is used to store id of first element that started the touch events.
    let categoryid;

    /**
     * Get touch target at touch point.
     * The target of all touch events is the first element that has been touched at 'touch start'.
     * So we need to infer the target from touch point for 'touch move' and 'touch end' events.
     *
     * @param {Object} e event
     * @returns {any | Element}
     */
    const getTouchTarget = (e) => {
        const target = document.elementFromPoint(
            e.changedTouches[0].pageX,
            e.changedTouches[0].pageY
        );
        // Check if the target is droppable.
        return target.closest(SELECTORS.DROPPABLE_ITEM);
    };

    /**
     * Handle Drag start
     * @param {Object} e event
     */
    const handleDragStart = (e) => {
        const target = e.target.closest(SELECTORS.DRAGGABLE_ITEM);
        // Return if target is not a draggable item.
        if (!target) {
            return;
        }

        // Save category ID of current moving item.
        // The datatransfer is not used as it is not a property of touch events.
        categoryid = target.dataset?.categoryid;

        // Prevent scrolling when touching on the draggable item.
        if (e.type == 'touchstart' && e.cancelable) {
            e.preventDefault();
        }
    };

    /**
     * Handle Drag move
     * Provide drag effect for touch events.
     *
     * @param {Object} e event
     */
    const handleDrag = (e) => {
        // Remove all highlight.
        droppableitems.forEach(item => {
            item.classList.remove('border-danger');
        });

        let target;
        if (e.type == 'touchmove') {
            target = getTouchTarget(e);
        } else {
            target = e.target.closest(SELECTORS.DROPPABLE_ITEM);
        }

        // Return if target is not a droppable item or there is no sourceid.
        if (!target || !categoryid) {
            return;
        }

        // Highlight the target.
        target.classList.add('border-danger');
    };

    /**
     * Handle Drag end
     * @param {Object} e event
     */
    const handleDragEnd = (e) => {
        let target;
        if (e.type == 'touchend') {
            target = getTouchTarget(e);
        } else {
            target = e.target.closest(SELECTORS.DROPPABLE_ITEM);
        }

        // Return if target is not a droppable item or there is no sourceid.
        if (!target || !categoryid) {
            return;
        }

        // Get list item whose id is the same as current moving category id.
        const source = document.getElementById(`category-${categoryid}`);
        if (!source) {
            return;
        }

        e.preventDefault();

        // Reset sourceid for touch event.
        categoryid = null;

        // Insert the source item after the "target" item.
        target.closest(SELECTORS.CATEGORY_LIST).insertBefore(source, target.nextSibling);

        // Old category.
        const originCategory = source.dataset.categoryid;

        // Insert after this category.
        const insertaftercategory = target.dataset.categoryid;

        // Insert the category after the target category
        setCatOrder(originCategory, insertaftercategory, pagecontextid);
    };

    /**
     * Allow drop
     * This is required to allow drop event to be trigger on an element.
     *
     * @param {Object} e event
     */
    const allowDrop = (e) => {
        e.preventDefault();
    };

    // Disable scrolling (for touch event) on the draggable item.
    draggableitems.forEach(item => {
            item.setAttribute("style", "touch-action: none;");
        }
    );

    // Events for droppable items.
    droppableitems.forEach(item => {
        item.addEventListener('dragenter', handleDrag);
        item.addEventListener('dragover', allowDrop);
        item.addEventListener('drop', handleDragEnd);
    });

    // Add event to draggable items.
    draggableitems.forEach(item => {
        // Touch events.
        item.addEventListener('touchstart', handleDragStart, false);
        item.addEventListener('touchmove', handleDrag, false);
        item.addEventListener('touchend', handleDragEnd, false);

        // Drag events.
        item.addEventListener('dragstart', handleDragStart);
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
        if (!e.target.closest(SELECTORS.CATEGORY_RENDERED)) {
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
                Templates.replaceNode(SELECTORS.CATEGORY_RENDERED, html, js);
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
        const checkbox = e.target.closest(SELECTORS.SHOW_DESCRIPTION_CHECKBOX);
        if (!checkbox) {
            return;
        }
        checkbox.form.submit();
    });
};

/**
 * Sets events listener for move category using dragdrop icon.
 * @param {number} pagecontextid Context id to get all relevant categories.
 */
const setUpMoveMenuItem = (pagecontextid) => {
    document.addEventListener('click', e => {
        // Return if it is not menu item.
        const item = e.target.closest(SELECTORS.MOVE_CATEGORY_MENU_ITEM);
        if (!item) {
            return;
        }
        // Return if it is disabled.
        if (item.getAttribute('aria-disabled')) {
            return;
        }

        // Prevent addition click on the item.
        item.setAttribute('aria-disabled', true);

        // Get categories.
        const call = {
            methodname: 'qbank_managecategories_get_categories_in_a_context',
            args: {
                contextid: pagecontextid,
            }
        };

        Ajax.call([call])[0]
            .then((data) => {
                // Exclude the current moving category from the data.
                data.contexts.forEach(context => {
                    if (context.contextid == item.dataset.contextid) {
                        context.categories.forEach(category => {
                            if (category.id == item.dataset.categoryid) {
                                category.disabled = true;
                                return;
                            }
                        });
                    }
                });
                // Render template with retrieved data.
                return Templates.renderForPromise('qbank_managecategories/move_category', data);
            })
            .then((template) => {
                // Create modal.
                return ModalFactory.create({
                    title: getString('movecategory', 'question'),
                    body: template.html
                });
            })
            .then(modal => {
                // Show modal and add click event for list item.
                modal.show();
                document.querySelector('.modal').addEventListener('click', e => {
                    const target = e.target.closest(SELECTORS.MODAL_CATEGORY_ITEM);
                    if (!target) {
                        return;
                    }
                    setCatOrder(item.dataset.categoryid, target.dataset.categoryid, pagecontextid);
                    modal.destroy();
                });
                item.setAttribute('aria-disabled', false);
                return;
            })
            .catch(Notification.exception);
    });
};

export const init = (pagecontextid) => {
    categoryParentListener(pagecontextid);
    setupSortableLists(pagecontextid);
    setupShowDescriptionCheckbox();
    setUpMoveMenuItem(pagecontextid);
};
