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
 * Javascript module updating question attempt when regrading question.
 *
 * @module     core_question/regrade_version
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
import Ajax from 'core/ajax';
import Notification from 'core/notification';

const updateAttempt = (questionId, questionVersion, attemptId, versionToRegrade) => {
    return Ajax.call([{
        methodname: 'core_question_update_attempt_regrade',
        args: {
            questionid: questionId,
            questionversion: questionVersion,
            attemptid: attemptId,
            versiontoregrade: versionToRegrade,
        },
        fail: Notification.exception
    }])[0];
};

export const init = () => {
    const attemptForm = document.getElementById('attemptsform');
    if (attemptForm) {
        attemptForm.addEventListener('change', (e) => {
            if (e.target.className.includes('version-regrade')) {
                const questionId = e.target.dataset.questionid;
                const questionVersion = e.target.dataset.questionversion;
                const attemptId = e.target.dataset.attemptid;
                const versionToRegrade = e.target.value;
                updateAttempt(questionId, questionVersion, attemptId, versionToRegrade);
            }
        });
    }
};