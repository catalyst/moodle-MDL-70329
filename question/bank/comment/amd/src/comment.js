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

import $ from 'jquery';
import Fragment from 'core/fragment';
import * as Str from 'core/str';
import ModalEvents from 'core/modal_events';
import ModalFactory from 'core/modal_factory';
import Notification from 'core/notification';
import CustomEvents from 'core/custom_interaction_events';

/**
 * Event listeners for the module.
 *
 * @method clickEvent
 * @param {object} root
 * @param {string} selector
 */
const clickEvent = (root, selector) => {
    // Modal for the question comments.
    let modalPromise = ModalFactory.create(
        {
            type: ModalFactory.types.CANCEL,
            title: Str.get_string('commentheader', 'qbank_comment'),
            large: true
        }, [root, selector]
    );
    // Event listener.
    root.on(CustomEvents.events.activate, selector, (e) => {
        e.preventDefault();
        let currentTarget = e.target.parentElement;
        // Get the required data for the selected row.
        let questionId = currentTarget.getAttribute('data-questionid'),
            courseID = currentTarget.getAttribute('data-courseid'),
            contextId = currentTarget.getAttribute('data-contextid');
        modalPromise.then((modal) => {
            let args = {
                questionid: questionId,
                courseid: courseID
            };
            let commentFragment = Fragment.loadFragment('qbank_comment', 'question_comment', contextId, args);
            modal.setBody(commentFragment);
            // Because we need to reload the page after adding or removing comments to update the count.
            modal.getRoot().on(ModalEvents.cancel, (e) => {
                e.preventDefault();
                location.reload();
                modal.hide();
            });
            // Listed for the x button in modal in case user uses this instead of close.
            $('button[data-action="hide"]').click(function() {
                e.preventDefault();
                location.reload();
                modal.hide();
            });
            return modal;
        }).fail(Notification.exception);
    });
};

/**
 * Entrypoint of the js.
 *
 * @method init
 * @param {string} root the root element selector for the table.
 * @param {string} questionSelector the question comment identifier.
 */
export const init = (root, questionSelector) => {
    // Call for the event listener to listed for clicks in any comment count row.
    clickEvent($(root), questionSelector);
};
