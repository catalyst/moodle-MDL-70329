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
 * Javascript module handling creation of Modal form.
 *
 * @package    qbank_managecategories
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

import $ from 'jquery';
import * as Str from 'core/str';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Fragment from 'core/fragment';
import Ajax from 'core/ajax';
import Notification from 'core/notification';

/**
 * Function handling display of moodle form.
 *
 * @param {string} selector Selector to trigger form display on.
 * @param {int} contextid Context id for fragment.
 * @param {int} cmid Cmid for fragment.
 */
const displayModal = (selector, contextid, cmid) => {
    let trigger = $(selector);
    ModalFactory.create({
      type: ModalFactory.types.SAVE_CANCEL,
      title: Str.get_string('addcategory', 'question'),
      body: getBody(contextid, cmid),
      large: true,
    }, trigger)
    .done((modal) => {
      modal.setSaveButtonText(Str.get_string('addcategory', 'question'));
      modal.getRoot().on(ModalEvents.hidden, () => {
        modal.setBody(getBody(contextid, cmid));
      });
      modal.getRoot().on(ModalEvents.shown, () => {
        modal.getRoot().append('<style>[data-fieldtype=submit] { display: none ! important; }</style>');
      });
      modal.getRoot().on(ModalEvents.save, (e) =>{
        submitForm(modal, e);
      });
      modal.getRoot().on('submit', 'form', (e) => {
        submitFormAjax(modal, e)
        .then(() => {
          modal.hide();
          location.reload();
        })
        .catch((error) => error);
      });
    });
};

/**
 * Get body for moodle form from fragment new_category_form.
 *
 * @param {int} contextid Context id for fragment.
 * @param {int} cmid Cmid for fragment.
 * @returns {Promise}
 */
const getBody = (contextid, cmid) => {
    let params = {cmid: JSON.stringify(cmid)};
    let htmlBody = Fragment.loadFragment('qbank_managecategories', 'new_category_form', contextid, params);
    return htmlBody;
};

/**
 * Call external function add_category_form - inserts the newly added category in the question_categories table.
 *
 * @param {Object} modal Object representing form data.
 * @returns {Mixed}
 */
const submitFormAjax = (modal, e) => {
  e.preventDefault();
  let changeEvent = document.createEvent('HTMLEvents');
  changeEvent.initEvent('change', true, true);

  modal.getRoot().find(':input').each((index, element) => {
    element.dispatchEvent(changeEvent);
  });

  let invalid = $.merge(
    modal.getRoot().find('[aria-invalid="true"]'),
    modal.getRoot().find('.error')
  );

  // If we found invalid fields, focus on the first one and do not submit via ajax.
  if (invalid.length) {
      invalid.first().focus();
      return;
  }

  let formData =  modal.getRoot().find('form').serialize();
  return Promise.resolve(
    Ajax.call([{
    methodname: 'qbank_managecategories_submit_add_category_form',
    args: {jsonformdata: JSON.stringify(formData)},
    fail: Notification.exception
  }]));
};

/**
 * This triggers a form submission, so that any mform elements can do final tricks before the form submission is processed.
 *
 * @param {Object} modal representing form data.
 * @param {Event} e Form submission event.
 */
const submitForm = (modal, e) => {
  e.preventDefault();
  modal.getRoot().find('form').submit();
};

export const initModal = (selector, contextid, cmid) => {
  displayModal(selector, contextid, cmid);
};
