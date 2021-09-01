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
 * Main javascript for the block
 *
 * @package
 * @author
 * @copyright   2021 Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/ajax',
    'core/notification',
    'core/templates',
], function(
    Ajax,
    Notification,
    Templates,
) {

    /**
     *
     * Initialize CSS selectors.
     *
     * @type {object}
     */
    let SELECTORS = {};

    /**
     * Set CSS selector.
     * Add slot id to class if is not null.
     *
     * @param {int} slotid The slot id.
     */
    var setSelectors = function(slotid) {
        let slotIdClass = '';
        if (slotid) {
            slotIdClass = '-' + slotid;
        }

        SELECTORS = {
            VERSION_LIST: '#version' + slotIdClass,
            SLOT_ID: '#slot' + slotIdClass,
        };
    };

    /**
     * Helper ajax function.
     *
     * @param {object} request The request to be made.
     * @param {function} done The function to be executed once ajax call is done.
     */
    var ajax = function (request, done) {
        //toggleLoading();

        return Ajax.call(request)[0].done(function (response) {
            //toggleLoading();
            done(response);
        }).fail(Notification.exception);
    };

    /**
     * Toggle loading spinner.
     *
     */
    var toggleLoading = function() {
        var loadingBlock = document.querySelector(SELECTORS.LOADING_BLOCK);
        var loadingSpinner = loadingBlock.querySelector(SELECTORS.LOADING_BLOCK_ICON);

        loadingBlock.classList.toggle('hidden');
        loadingSpinner.classList.toggle('hidden');
    };


    var changeVersion = function(slotId) {
        const selectElement = document.querySelector(SELECTORS.VERSION_LIST);

        selectElement.addEventListener('change', () => {
            setSelectors(slotId);

            var mainContainer = document.querySelector(SELECTORS.SLOT_ID);
            var request = [{
                methodname: 'mod_quiz_get_question_slot',
                args: {
                    slotid: slotId,
                }
            }];
            ajax(request, function(response) {
                Templates.render('mod_quiz/main', response).then(function(html) {
                    mainContainer.innerHTML = html;
                });
            });
        });
    };

    var init = function(slotId) {
        setSelectors(slotId);
        changeVersion(slotId);
    };

    return {
        init: init
    };
});