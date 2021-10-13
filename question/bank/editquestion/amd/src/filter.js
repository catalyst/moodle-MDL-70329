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
import Templates from 'core/templates';
import Notification from 'core/notification';
import PagedContentFactory from 'core/paged_content_factory';

/**
 * Initialise the question bank filter on the element with the given id.
 *
 * @param {String} filterRegionId
 */
export const init = (filterRegionId) => {
    CoreFilter.init(filterRegionId, 'QbankTable');
 * @param {String} defaultcourseid
 * @param {String} defaultcategoryid
 * @param {int} perpage
 */
export const init = (filterRegionId, defaultcourseid, defaultcategoryid, perpage) => {
    var courseid;
    var categories;
    var qtagids;
    var qbshowtext = false;
    var recurse = 0;
    var showhidden = false;

    var TEMPLATE_NAME = 'qbank_editquestion/qbank_questions';

    CoreFilter.init(filterRegionId, 'QbankTable', function(filterdata, pendingPromise) {
        applyFilter(filterdata, pendingPromise);
    });

    /**
     * Ajax call to retrieve question via ws functions
     *
     * @param {String} courseid course id
     * @param {String} categories sequence of categories
     * @param {String} qtagids sequence of tags
     * @param {int} qperpage number of questions perpage
     * @param {int} qpage current page
     * @param {int} qbshowtext
     * @param {int} recurse
     * @param {int} showhidden
     * @returns {*}
     */
    var requestQuestions = function(courseid, categories, qtagids, qperpage, qpage, qbshowtext, recurse, showhidden) {
        var request = {
            methodname: 'core_qbank_dummy',
            args: {
                courseid: courseid,
                category: categories,
                qtagids: qtagids,
                qperpage: qperpage,
                qpage: qpage,
                qbshowtext: qbshowtext,
                recurse: recurse,
                showhidden: showhidden
            }
        };

        return ajax.call([request])[0];
    };

    /**
     * Retrieve table data.
     *
     * @param {Object} filter data
     * @param {Promise} filter pending promise
     */
    const applyFilter = (filterdata, pendingPromise) => {
        if (filterdata) {
            courseid = filterdata['courseid'].values.toString();
            categories = filterdata['category'] ? filterdata['category'].values.toString() : '';
            qtagids = filterdata['tag'] ? filterdata['tag'].values.toString() : '';
        } else {
            courseid = defaultcourseid;
            categories = defaultcategoryid;
            qtagids = '';
        }

        // Load first page.
        var qpage = 0;

        requestQuestions(courseid, categories, qtagids, perpage, qpage, qbshowtext, recurse, showhidden)
            .then(function(response) {
                let totalquestions = response.totalquestions;
                let firstpagehtml = response.html;
                return renderPagination(perpage, totalquestions, firstpagehtml);
            })
            .then(function(html, js) {
                let questionscontainer = document.getElementById('questionscontainer');
                Templates.replaceNodeContents(questionscontainer, html, js);
                if (pendingPromise) {
                    pendingPromise.resolve();
                }
                return;
            })
            .fail(Notification.exception);
    };

    /**
     * Render table and pagination.
     *
     * @param {int} perpage
     * @param {int} totalquestions
     * @param {string} firstpagehtml
     */
    const renderPagination = (perpage, totalquestions, firstpagehtml) => {
        return PagedContentFactory.createFromAjax(
            totalquestions,
            perpage,
            function(pagesData) {
                return pagesData.map(function(pageData) {
                    let pageNumber = pageData.pageNumber;
                    // Page number start at 1.
                    let qpage = pageNumber - 1;

                    // Render first page
                    if (qpage == 0) {
                        return Templates.render(TEMPLATE_NAME, {html: firstpagehtml});
                    } else {
                        // Load data for selected page.
                        return requestQuestions(courseid, categories, qtagids, perpage, qpage, qbshowtext, recurse, showhidden)
                        .then(function(response) {
                            return Templates.render(TEMPLATE_NAME, {html: response.html});
                        })
                        .fail(Notification.exception);
                    }
                });
            }
        );
    };

    // Run apply filter at page load.
    applyFilter();
};

