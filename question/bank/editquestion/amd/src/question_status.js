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
 * Status column selector js.
 *
 * @module     qbank_editquestion/question_status
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Fragment from 'core/fragment';
import * as Str from 'core/str';
import ModalFactory from 'core/modal_factory';
import Notification from 'core/notification';
import ModalEvents from 'core/modal_events';
import Ajax from 'core/ajax';

/**
 * Get the fragment.
 *
 * @method getFragment
 * @param {{questioned: int}} args
 * @param {int} contextId
 * @return {string}
 */
const getFragment = (args, contextId) => {
    return Fragment.loadFragment('qbank_editquestion', 'question_status', contextId, args);
};

/**
 * Save the status.
 *
 * @method getFragment
 * @param {object} modal
 * @param {int} questionId
 * @param {object} target
 */
const save = (modal, questionId, target) => {
    let formData = modal.getBody().find('form').serialize();
    let responseAjax = Ajax.call([{
        methodname: 'qbank_editquestion_set_status',
        args: {
            questionid: questionId,
            formdata: formData
        }
    }]);
    responseAjax[0].done(function(result) {
        if (result.status) {
            target.innerText = result.statusname;
        }
    }).fail(Notification.exception);
    return true;
};

/**
 * Event listeners for the module.
 *
 * @method clickEvent
 * @param {int} questionId
 * @param {int} contextId
 * @param {object} target
 */
export const statusEvent = (questionId, contextId, target) => {
    let args = {
        questionid: questionId
    };
    ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: Str.get_string('questionstatusheader', 'qbank_editquestion'),
        body: getFragment(args, contextId),
        large: false,
    }).done((modal) => {
        modal.show();
        let root = modal.getRoot();
        root.on(ModalEvents.save, function(e) {
            e.preventDefault();
            e.stopPropagation();
            save(modal, questionId, target);
            modal.hide();
        });
        return modal;
    }).fail(Notification.exception);
};
