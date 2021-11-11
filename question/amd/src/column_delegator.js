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
 * The purpose of this module is to centralize delegators related to columns.
 *
 * @module     core_question/column_delegator
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {statusEvent} from 'qbank_editquestion/question_status';
import {usageEvent} from 'qbank_usage/usage';
import {commentEvent} from 'qbank_comment/comment';

export const init = () => {
    let delegator = document.getElementById('categoryquestions');
    const eventMethods = {
        'questionstatus': statusEvent,
        'questionusage': usageEvent,
        'commentcount': commentEvent,
    };
    delegator.addEventListener('click', (e) => {
        if (e.target.getAttribute('data-questionid')) {
            let target = e.target;
            let className = e.target.parentNode.className.split(' ')[0];
            let questionId = e.target.getAttribute('data-questionid');
            let contextId = 1;
            if (className === 'commentcount') {
                let courseId = target.getAttribute('data-courseid');
                // Last parameter is courseId for commentEvent.
                target = courseId;
            }
            if (eventMethods.hasOwnProperty(className)) {
                eventMethods[className](questionId, contextId, target);
            }
        }
    });
};
