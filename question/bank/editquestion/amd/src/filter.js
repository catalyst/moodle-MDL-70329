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
 * Question bank filter managemnet.
 *
 * @module     qbank_editquestion/filter
 * @copyright  2021 Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as CoreFilter from 'core/filter';
import ajax from 'core/ajax';

/**
 * Initialise the question bank filter on the element with the given id.
 *
 * @param {String} filterRegionId
 * @param {String} courseid
 * @param {String} defaultcategoryid
 */
export const init = (filterRegionId, defaultcourseid, defaultcategoryid) => {
    CoreFilter.init(filterRegionId, 'QbankTable', function(filterdata, pendingPromise) {
        applyFilter(filterdata, pendingPromise);
    });

    /**
     * Retrieve table data.
     *
     * @param {Object} filter data
     * @param {Promise} filter pending promise
     */
    const applyFilter = (filterdata, pendingPromise) => {
        if (filterdata) {
            var courseid = filterdata['courseid'].values.toString();
            var categories = filterdata['category'] ? filterdata['category'].values.toString() : '';
            var qtagids = filterdata['tag'] ? filterdata['tag'].values.toString() : '';
        } else {
            var courseid = defaultcourseid;
            var categories = defaultcategoryid;
            var qtagids = '';
        }

        var qperpage = 10;
        var qbshowtext = false;
        var recurse = false;
        var showhidden = false;

        var promises = ajax.call([{
            methodname: 'core_qbank_dummy', args: {
                    courseid: courseid,
                    category: categories,
                     qtagids: qtagids,
                    qperpage: qperpage,
                  qbshowtext: qbshowtext,
                     recurse: recurse,
                  showhidden: showhidden
                }
            }
        ]);

        promises[0].done(function(response) {
            var questionscontainer = document.getElementById('questionscontainer');
            var html = '<div className="categoryquestionscontainer" id="questionscontainer">' + response.html + '</div>';
            questionscontainer.innerHTML = html;
            if (pendingPromise) {
                pendingPromise.resolve();
            }
        });

    };

    applyFilter();
};

