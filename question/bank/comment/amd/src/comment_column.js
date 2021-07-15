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

        let selector = '[data-target="questioncommentpreview"]';

        /**
         * Event listeners for the module.
         */
        var clickEvent = function (root) {
            var modalPromise = ModalFactory.create(
                {
                    type: ModalFactory.types.CANCEL,
                    large: true
                }, [root, selector]
            ).then(function (modal) {
                // All of this code only executes once, when the modal is
                // first created. This allows us to add any code that should
                // only be run once, such as adding event handlers to the modal.
                Str.get_string('commentheader', 'qbank_comment')
                    .then(function (string) {
                        modal.setTitle(string);
                        return string;
                    })
                    .fail(Notification.exception);
                return modal;
            });

            // We need to add an event handler to the tags link because there are
            // multiple links on the page and without adding a listener we don't know
            // which one the user clicked on the show the modal.
            root.on(CustomEvents.events.activate, selector, function (e) {
                e.preventDefault();
                var currentTarget = e.target.parentElement;

                var questionId = currentTarget.getAttribute('data-questionid'),
                    courseID = currentTarget.getAttribute('data-courseid'),
                    contextId = currentTarget.getAttribute('data-contextid');

                // This code gets called each time the user clicks the tag link
                // so we can use it to reload the contents of the tag modal.
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

                    return modal;
                }).fail(Notification.exception);


            });
        };

        return {
            init: function (root) {
                clickEvent($(root));
            }
        };
    });