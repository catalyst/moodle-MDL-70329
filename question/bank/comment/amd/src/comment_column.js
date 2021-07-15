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

define([
        'jquery',
        'core/fragment',
        'core/str',
        'core/modal_events',
        'core/modal_factory',
        'core/notification',
        'core/custom_interaction_events'
    ],
    function (
        $,
        Fragment,
        Str,
        ModalEvents,
        ModalFactory,
        Notification,
        CustomEvents
    ) {

        /**
         * Event listeners for the module.
         *
         * @method clickEvent
         * @param {object} root
         * @param {string} selector
         */
        var clickEvent = function (root, selector) {
            // Modal for the question comments.
            var modalPromise = ModalFactory.create(
                {
                    type: ModalFactory.types.CANCEL,
                    large: true
                }, [root, selector]
            ).then(function (modal) {
                Str.get_string('commentheader', 'qbank_comment')
                    .then(function (string) {
                        modal.setTitle(string);
                        return string;
                    })
                    .fail(Notification.exception);
                return modal;
            });
            // Event listener.
            root.on(CustomEvents.events.activate, selector, function (e) {
                e.preventDefault();
                var currentTarget = e.target.parentElement;
                var questionId = currentTarget.getAttribute('data-questionid'),
                    courseID = currentTarget.getAttribute('data-courseid'),
                    contextId = currentTarget.getAttribute('data-contextid');
                modalPromise.then(function (modal) {
                    var args = {
                        questionid: questionId,
                        courseid: courseID
                    };
                    var commentFragment = Fragment.loadFragment('qbank_comment', 'question_comment', contextId, args);
                    modal.setBody(commentFragment);
                    modal.getRoot().on(ModalEvents.cancel, function (e) {
                        e.preventDefault();
                        location.reload();
                        modal.hide();
                    });
                    modal.getRoot().on(ModalEvents.hide, function (e) {
                        e.preventDefault();
                        location.reload();
                        modal.hide();
                    });
                    return modal;
                }).fail(Notification.exception);
            });
        };

        return {
            init: function (root, questionSelector) {
                clickEvent($(root), questionSelector);
            }
        };
    });