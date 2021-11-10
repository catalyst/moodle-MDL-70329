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
 * @module     mod_quiz/question_slot
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Guillermo Gomez Arias <guillermogomez@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';

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
        QUESTION_NAME: '#questionname' + slotIdClass,
        QUESTION_TEXT: '#questiontext' + slotIdClass,
    };
};

/**
 * Helper ajax function.
 *
 * @param {object} request The request to be made.
 * @returns {Promise}
 */
const ajax = (request) => {
    return Ajax.call(request)[0].done((response) => {
        if (response.result === true) {
            document.querySelector(SELECTORS.QUESTION_NAME).innerHTML = response.name;
            document.querySelector(SELECTORS.QUESTION_TEXT).innerHTML = response.questiontext;
            let previewtag = document.querySelector(SELECTORS.SLOT_ID + ' > .actions > .preview');
            let tempo = document.createElement('div');
            tempo.innerHTML = response.previewtag;
            previewtag.replaceWith(tempo.firstChild);
            let editurl = document.querySelector(SELECTORS.SLOT_ID + ' > .activityinstance > a').href;
            editurl = new URL(editurl);
            let searchparams = editurl.searchParams;
            searchparams.set('id', response.questionid);
            editurl.search = searchparams.toString();
            editurl = editurl.toString();
            document.querySelector(SELECTORS.SLOT_ID + ' > .activityinstance > a').href = editurl;
            // let previewurl = document.querySelector(SELECTORS.SLOT_ID + ' > .actions > .preview');
            // console.log(previewurl);
            // let previewurl = document.querySelector(SELECTORS.SLOT_ID + ' > .actions > .preview').href;
            // previewurl = new URL(previewurl);
            // let searchPreviewParam = previewurl.searchParams;
            // searchPreviewParam.set('id', response.questionid);
            // previewurl.search = searchPreviewParam.toString();
            // previewurl = previewurl.toString();
            // document.querySelector(SELECTORS.SLOT_ID + ' > .actions > .preview').href = previewurl;
        }
    }).fail(Notification.exception);
};

/**
 * Replace the container with a new version.
 *
 * @param {int} slotId
 */
const changeVersion = (slotId) => {
    const selectElement = document.querySelector(SELECTORS.VERSION_LIST);
    selectElement.addEventListener('change', () => {
        let versionSelected = parseInt(selectElement.value);
        let previewurl = document.querySelector(SELECTORS.SLOT_ID + ' > .actions > .preview').href;
        setSelectors(slotId);
        let request = [{
            methodname: 'mod_quiz_set_question_version',
            args: {
                slotid: slotId,
                newversion: versionSelected,
                previewurl: previewurl,
            }
        }];
        ajax(request);
    });
};

/**
 * Entrypoint of the js.
 *
 * @param {int} slotId
 */
export const init = (slotId) => {
    setSelectors(slotId);
    changeVersion(slotId);
};
