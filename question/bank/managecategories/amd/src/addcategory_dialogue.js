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
 * Function handling display of moodle form.
 *
 * @param {string} selector Selector to trigger form display on.
 * @param {int} contextid Context id for fragment.
 * @param {int} categoryid Category id for edit form and data-action.
 * @param {int} cmid Course module id.
 * @param {int} courseid Course id.
 */
const displayModal = (selector, contextid, categoryid, cmid, courseid) => {
    let title = getString('addcategory', 'question');
    if (categoryid !== null) {
      title = getString('editcategory', 'question');
    }
    const trigger = document.querySelector(selector);
    if (trigger !== null) {
      trigger.addEventListener('click', e => {
        e.preventDefault();
        const element = e.target;
        const modalForm = new ModalForm({
            formClass: "qbank_managecategories\\form\\question_category_edit_form",
            args: {
              contextid: contextid,
              categoryid: categoryid,
              cmid: cmid,
              courseid: courseid
            },
            modalConfig: {
              title: title,
              large: true,
            },
            saveButtonText: title,
            returnFocus: element,
        });
        modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => {
          return getCategoriesFragment(contextid, cmid, courseid).done((html, js) => {
            Templates.replaceNodeContents('#categoriesrendered', html, js);
          });
          // Refresh fragment for category rendering here.
        });
        // Show the form.
        modalForm.show();
      });
    }
};

/**
 * Call category_rendering fragment.
 *
 * @param {int} contextid String containing new ordered categories.
 * @param {int} cmid Course module id.
 * @param {int} courseid Course id.
 * @returns {Promise}
 */
 const getCategoriesFragment = (contextid, cmid, courseid) => {
  let params = {
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

export const initModal = (selector, contextid, categoryid, cmid, courseid) => {
  displayModal(selector, contextid, categoryid, cmid, courseid);
};
