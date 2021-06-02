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
 * Column selector js.
 *
 * @package    qbank_comment
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Fragment from 'core/fragment';
import * as Str from 'core/str';
import ModalEvents from 'core/modal_events';
import ModalFactory from 'core/modal_factory';
import Notification from 'core/notification';

/**
 * Event listeners for the module.
 *
 * @method clickEvent
 * @param {int} questionId
 * @param {int} courseID
 * @param {int} contextId
 */
const commentEvent = (questionId, courseID, contextId) => {
    let args = {
        questionid: questionId,
        courseid: courseID
    };
    let commentFragment = Fragment.loadFragment('qbank_comment', 'question_comment', contextId, args);
    ModalFactory.create({
        type: ModalFactory.types.ALERT,
        title: Str.get_string('commentheader', 'qbank_comment'),
        body: commentFragment,
        large: true,
    }).then((modal) => {
        let root = modal.getRoot();
        root.on(ModalEvents.cancel, function() {
            location.reload();
            modal.hide();
        });
        root.on('click', 'button[data-action="hide"]', () => {
            location.reload();
            modal.hide();
        });
        modal.show();
        return modal;
    }).fail(Notification.exception);
};

/**
 * Entrypoint of the js.
 *
 * @method init
 * @param {string} questionSelector the question comment identifier.
 */
export const init = (questionSelector) => {
    let target = document.querySelector(questionSelector);
    let contextId = 1;
    let questionId = target.getAttribute('data-questionid'),
        courseID = target.getAttribute('data-courseid');
    target.addEventListener('click', () => {
        // Call for the event listener to listed for clicks in any comment count row.
        commentEvent(questionId, courseID, contextId);
    });
};
