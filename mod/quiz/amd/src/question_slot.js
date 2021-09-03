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
 * Render the question slot template for each question in the quiz edit view.
 *
 * @package    mod_quiz
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


import Ajax from 'core/ajax';
import Notification from 'core/notification';
import Templates from 'core/templates';

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
 * @param {int} slotId The slot id.
 */
const setSelectors = (slotId) => {
    let slotIdClass = '';
    if (slotId) {
        slotIdClass = '-' + slotId;
    }

    SELECTORS = {
        VERSION_LIST: '#version' + slotIdClass,
        SLOT_ID: '#mod-indent-outer-slot' + slotIdClass,
    };
};

/**
 * Helper ajax function.
 *
 * @param {object} request The request to be made.
 * @param {function} done The function to be executed once ajax call is done.
 * @returns {Promise}
 */
const ajax = (request, done) => {
    // ...toggleLoading();

    return Ajax.call(request)[0].done(function(response) {
        // ...toggleLoading();
        done(response);
    }).fail(Notification.exception);
};

/**
 * Toggle loading spinner.
 */
const toggleLoading = () => {
    let loadingBlock = document.querySelector(SELECTORS.LOADING_BLOCK);
    let loadingSpinner = loadingBlock.querySelector(SELECTORS.LOADING_BLOCK_ICON);

    loadingBlock.classList.toggle('hidden');
    loadingSpinner.classList.toggle('hidden');
};

/**
 * Replace the container with a new version.
 *
 * @param {int} slotId
 * @param {int} slot
 * @param {int} quizId
 */
const changeVersion = (slotId, slot, quizId) => {
    const selectElement = document.querySelector(SELECTORS.VERSION_LIST);
    selectElement.addEventListener('change', () => {
        let versionIdSelected = parseInt(selectElement.value);
        setSelectors(slotId);
        let mainContainer = document.querySelector(SELECTORS.SLOT_ID);
        let request = [{
            methodname: 'mod_quiz_get_question_slot',
            args: {
                slotid: slotId,
                slot: slot,
                quizid: quizId,
                newversionid: versionIdSelected,
            }
        }];
        ajax(request, function(response) {
            let dataToRender = {
                slotid: response.slotid,
                canbeedited: response.canbeedited,
                checkbox: response.checkbox,
                questionnumber: response.questionnumber,
                questionname: response.questionname,
                questionicons: response.questionicons,
                questiondependencyicon: response.questiondependencyicon,
                versionoption: JSON.parse(response.versionoption)
            };
            Templates.render('mod_quiz/question_slot', dataToRender).then(function(html) {
                mainContainer.innerHTML = html;
                return html;
            }).catch(Notification.exception);
        });
    });
};

/**
 * Entrypoint of the js.
 *
 * @param {int} slotId
 * @param {int} slot
 * @param {int} quizId
 */
export const init = (slotId, slot, quizId) => {
    setSelectors(slotId);
    changeVersion(slotId, slot, quizId);
};
