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
 * Javascript module handling category parent update.
 *
 * @module     qbank_managecategories
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

import Ajax from 'core/ajax';
import Fragment from 'core/fragment';
import Notification from 'core/notification';
import Templates from 'core/templates';

/**
 * Call category_rendering fragment.
 *
 * @param {int} contextid String containing new ordered categories.
 * @returns {Promise}
 */
 const getCategoriesFragment = (contextid) => {
    let params = {
        url: location.href,
    };
    return Fragment.loadFragment('qbank_managecategories', 'category_rendering', contextid, params);
};

export const init = (contextid) => {
    const categorycontainer = document.getElementById('categoriesrendered');
    if (categorycontainer) {
        categorycontainer.addEventListener('click', (e) => {
            if (e.target.parentNode.classList.contains('action-icon')) {
                const data = e.target.parentNode.dataset;
                const response = Ajax.call([{
                    methodname: 'qbank_managecategories_update_category_parent',
                    args: {
                        tomove: data.tomove,
                        tocategory: data.tocategory,
                    },
                    fail: Notification.exception
                }]);
                response[0].then(() => {
                    getCategoriesFragment(contextid).done((html, js) => {
                        Templates.replaceNodeContents('#categoriesrendered', html, js);
                    });
                    return;
                }).catch(() => {
                    return;
                });
            }
        });
    }
};
