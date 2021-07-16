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
 * Usage column selector js.
 *
 * @package    qbank_usage
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Fragment from 'core/fragment';
import * as Str from 'core/str';
import ModalFactory from 'core/modal_factory';
import Notification from 'core/notification';

/**
 * Event listeners for the module.
 *
 * @method clickEvent
 * @param {int} questionId
 * @param {int} contextId
 */
const usageEvent = (questionId, contextId) => {
    let args = {
        questionid: questionId
    };
    let usageFragment = Fragment.loadFragment('qbank_usage', 'question_usage', contextId, args);
    ModalFactory.create({
        type: ModalFactory.types.CANCEL,
        title: Str.get_string('usageheader', 'qbank_usage'),
        body: usageFragment,
        large: true,
    }).then((modal) => {
        modal.show();
        return modal;
    }).fail(Notification.exception);
};

/**
 * Entrypoint of the js.
 *
 * @method init
 * @param {string} questionSelector the question usage identifier.
 */
export const init = (questionSelector) => {
    let target = document.querySelector(questionSelector);
    let contextId = 1;
    let questionId = target.getAttribute('data-questionid');
    target.addEventListener('click', () => {
        // Call for the event listener to listed for clicks in any usage count row.
        usageEvent(questionId, contextId);
    });
};
