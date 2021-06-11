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
 * Javascript for report card display and processing.
 *
 * @package    qbank_settingspage
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
*/

import Ajax from 'core/ajax';
import Notification from 'core/notification';

const draggables = document.querySelectorAll('.item');
const containers = document.querySelectorAll('.list');

draggables.forEach(draggable => {
    draggable.addEventListener('dragstart', () => {
        draggable.classList.add('dragging');
        draggable.classList.add('active');
        draggable.style.opacity = '0.5';
    });

    draggable.addEventListener('dragend', () => {
        draggable.classList.remove('dragging');
        draggable.classList.remove('active');
        draggable.style.opacity = null;
        let updatedColumns = getColumnOrder();
        callPhpFunc(JSON.stringify(updatedColumns));
    });
});

containers.forEach(container => {
    container.addEventListener('dragover', e => {
        e.preventDefault();
        let afterElement = getDragAfterElement(container, e.clientY);
        let draggable = document.querySelector('.dragging');
        if (!afterElement) {
            container.appendChild(draggable);

        } else {
            container.insertBefore(draggable, afterElement);
        }
    });
});

const getDragAfterElement = (container, y) => {
const draggableElements  = [...container.querySelectorAll('.item:not(.dragging)')];

return draggableElements.reduce((closest, child) => {
    let box = child.getBoundingClientRect();
    let offset = y - box.top - box.height / 2;
    if (offset < 0 && offset > closest.offset){
        return {offset : offset, element: child};
    } else {
        return closest;
    }
}, {offset: Number.NEGATIVE_INFINITY}).element;
};

const callPhpFunc = (updatedcol) => {
    let ajcall = Ajax.call([{
        methodname: 'core_question_get_order',
        args: { columnarr: updatedcol },
        fail: Notification.exception
    }]);
    ajcall[0].then((response) => console.log(JSON.parse(response)));
};

const getColumnOrder = () => {
    let updated = [...document.querySelectorAll('.item')];
    let columns = new Array(updated.length);
    for (let i = 0; i < updated.length; i++) {
        columns[i] = updated[i].childNodes[1].innerText.trim();
    }
    return columns;
};

export const init = () => window.console.log('we have been started');