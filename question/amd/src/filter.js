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
 * @module     core_question/filter
 * @copyright  2021 Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ajax from 'core/ajax';
import CoreFilter from 'core/filter';
import Fragment from 'core/fragment';
import Notification from 'core/notification';
import PagedContentFactory from 'core/paged_content_factory';
import Templates from 'core/templates';

/**
 * Initialise the question bank filter on the element with the given id.
 *
 * @param {String} filterRegionId id of the filter region
 * @param {String} defaultcourseid default course id
 * @param {String} defaultcategoryid default category id
 * @param {int} perpage number of question per page
 * @param {boolean} recurse if loading sub categories
 * @param {boolean} showhidden if loading hidden question
 * @param {boolean} qbshowtext if loading question text
 * @param {int} contextId contextId
 */
export const init = (filterRegionId, defaultcourseid, defaultcategoryid,
                     perpage, recurse, showhidden, qbshowtext, contextId) => {

    const filterSet = document.querySelector(`#${filterRegionId}`);

    // Default filter params for WS function.
    let wsfilter = {
        // Default value filterset::JOINTYPE_DEFAULT.
        filterverb: 2,
        filters: [],
        defaultcourseid: defaultcourseid,
        defaultcategoryid: defaultcategoryid,
        qperpage: perpage,
        qpage: 0,
        qbshowtext: qbshowtext,
        recurse: recurse,
        showhidden: showhidden,
    };

    // HTML <div> ID of question container.
    const SELECTORS = {
        QUESTION_CONTAINER_ID: 'questionscontainer',
    };

    // Default Pagination config.
    const DEFAULT_PAGED_CONTENT_CONFIG = {
        ignoreControlWhileLoading: true,
        controlPlacementBottom: false,
    };

    // Template to render return value from ws function.
    const TEMPLATE_NAME = 'core_question/qbank_questions';

    // Init function with apply callback.
    const coreFilter = new CoreFilter(filterSet, function(filters, pendingPromise) {
        applyFilter(filters, pendingPromise);
    });
    coreFilter.init();

    /**
     * Ajax call to retrieve question via ws functions
     *
     * @returns {*}
     * @param {Object} filter filter object
     */
    const requestQuestions = filter => {
        const request = {methodname: 'core_qbank_get_questions', args: filter};
        return ajax.call([request])[0];
    };

    /**
     * Retrieve table data.
     *
     * @param {Object} filterdata data
     * @param {Promise} pendingPromise pending promise
     */
    const applyFilter = (filterdata, pendingPromise) => {
        // Getting filter data.
        // Otherwise, the ws function should retrieves question based on default courseid and cateogryid.
        if (filterdata) {
            // Main join types.
            wsfilter['filterverb'] = parseInt(filterSet.dataset.filterverb, 10);

            // Clean old filter
            wsfilter['filters'] = [];

            // Retrieve fitter info.
            for (const [key, value] of Object.entries(filterdata)) {
                let filter = {'filtertype': key, 'jointype': value.jointype, 'values': value.values.toString()};
                wsfilter['filters'].push(filter);
            }
        }

        // Load questions for first page.
        requestQuestions(wsfilter)
            .then(response => {
                const totalquestions = response.totalquestions;
                const firstpagequestions = {questions: JSON.stringify(response.questions)};
                return renderPagination(wsfilter, totalquestions, firstpagequestions);
            })
            // Render questions for first page and pagination.
            .then((html, js) => {
                const questionscontainer = document.getElementById(SELECTORS.QUESTION_CONTAINER_ID);
                Templates.replaceNodeContents(questionscontainer, html, js);
                // Resolve filter promise.
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
     * @param {Object} filter params
     * @param {int} totalquestions
     * @param {string} firstpagequestions
     */
    const renderPagination = (filter, totalquestions, firstpagequestions) => {
        return PagedContentFactory.createFromAjax(
            totalquestions,
            perpage,
            pagesData => {
                return pagesData.map(pageData => {
                    let pageNumber = pageData.pageNumber;
                    // Page number start at 1.
                    let qpage = pageNumber - 1;

                    // Render first page
                    if (qpage == 0) {
                        return Fragment.loadFragment('qbank_viewlist', 'question_list', contextId, firstpagequestions)
                            .then(questionshtml => {
                                return Templates.render(TEMPLATE_NAME, {html: questionshtml});
                            });
                    } else {
                        // Load data for selected page.
                        filter['qpage'] = qpage;
                        return requestQuestions(filter)
                            .then(response => {
                                const pagequestions = {questions: JSON.stringify(response.questions)};
                                return Fragment.loadFragment('qbank_viewlist', 'question_list', contextId, pagequestions)
                                    .then(questionshtml => {
                                        return Templates.render(TEMPLATE_NAME, {html: questionshtml});
                                    });
                            })
                        .fail(Notification.exception);
                    }
                });
            },
            DEFAULT_PAGED_CONTENT_CONFIG
        );
    };

    // Run apply filter at page load.
    applyFilter();
};
