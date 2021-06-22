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
 * Javascript module for addition or edition of category as a modal form.
 * Clicking "Add category" or "Edit > Edit settings" will trigger this modal.
 *
 * @module     qbank_managecategories
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

import {get_string as getString} from 'core/str';
import Fragment from 'core/fragment';
import ModalForm from 'core_form/modalform';
import Templates from 'core/templates';

/**
 * Return modal title
 *
 * @param {boolean} isEdit is 'add' or 'edit' form
 * @returns {String} title string
 */
const getTitle = isEdit => getString(isEdit ? 'editcategory' : 'addcategory', 'question');

/**
 * Function handling display of moodle form.
 *
 */
export const initModal = () => {
    document.addEventListener('click', e => {
        const addEditButton = e.target.closest('[data-action="addeditcategory"]');

        // Return if it is not 'addeditcategory' button.
        if (!addEditButton) {
            return;
        }

        // Return if the action type is not specified.
        if (!addEditButton.dataset.actiontype) {
            return;
        }

        e.preventDefault();
        // Data for the modal.
        const title = getTitle(addEditButton.dataset.actiontype === 'edit');
        const contextid = addEditButton.dataset.contextid;
        const categoryid = addEditButton.dataset.categoryid;
        const cmid = addEditButton.dataset.cmid;
        const courseid = addEditButton.dataset.courseid;

        // Page context.
        const pagecontextid = document.getElementById('categoriesrendered')?.dataset.contextid ?? contextid;

        // Call the modal.
        const modalForm = new ModalForm({
            formClass: "qbank_managecategories\\form\\question_category_edit_form",
            args: {
                contextid,
                categoryid,
                cmid,
                courseid
            },
            modalConfig: {
                title: title,
                large: true,
            },
            saveButtonText: title,
            returnFocus: addEditButton,
        });
        modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => {
            return getCategoriesFragment(pagecontextid, cmid, courseid)
                .then((html, js) => {
                    Templates.replaceNode('#categoriesrendered', html, js);
                    return;
                });
        });
        // Show the form.
        modalForm.show();
    });
};

/**
 * Call category_rendering fragment.
 *
 * @param {number} contextid String containing new ordered categories.
 * @param {number} cmid Course module id.
 * @param {number} courseid Course id.
 * @returns {Promise}
 */
const getCategoriesFragment = (contextid, cmid, courseid) => {
    const params = {
        url: location.href,
    };
    if (cmid !== undefined) {
        params.cmid = cmid;
    }
    if (courseid !== undefined) {
        params.courseid = courseid;
    }
    return Fragment.loadFragment('qbank_managecategories', 'category_rendering', contextid, params);
};
