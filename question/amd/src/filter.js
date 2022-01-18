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
import Notification from 'core/notification';
import Selectors from 'core/local/filter/selectors';
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
 * @param {int} contextId id of the context
 * @param {string} component name of the component for fragment
 * @param {string} callback name of the callback for the fragment
 * @param {string} extraparams json encoded extra params for the extended apis
 */
export const init = (filterRegionId, defaultcourseid, defaultcategoryid,
                     perpage, recurse, showhidden, qbshowtext,
                     contextId, component, callback, extraparams) => {

    const filterSet = document.querySelector(`#${filterRegionId}`);

    // Default filter params for WS function.
    let wsfilter = {
        // Default value filterset::JOINTYPE_DEFAULT.
        filters: [],
        filteroptions: {
            filterverb: 2,
            recurse: recurse,
            showhidden: showhidden,
        },
        displayoptions: {
            perpage: perpage,
            showtext: qbshowtext,
        },
        sortdata: [
            {
                sortby: 'qbank_viewquestiontype\\question_type_column',
                sortorder: 4,
            }
        ],
        defaultcourseid: defaultcourseid,
        defaultcategoryid: defaultcategoryid,
    };

    // HTML <div> ID of question container.
    const SELECTORS = {
        QUESTION_CONTAINER_ID: '#questionscontainer',
        SORT_LINK: '#questionscontainer div.sorters a',
        PAGINATION_LINK: '#questionscontainer a[href].page-link',
    };

    // Init function with apply callback.
    const coreFilter = new CoreFilter(filterSet, function(filters, pendingPromise) {
        applyFilter(filters, pendingPromise);
    });
    coreFilter.init();

    /**
     * Ajax call to retrieve question via ws functions
     *
     * @param {Object} filter filter object
     * @returns {*}
     */
    const requestQuestions = filter => {
        const request = {methodname: 'core_question_filter', args: filter};
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
            wsfilter.filteroptions.filterverb = parseInt(filterSet.dataset.filterverb, 10);

            // Clean old filter
            wsfilter.filters = [];

            // Retrieve fitter info.
            for (const [key, value] of Object.entries(filterdata)) {
                let filter = {'filtertype': key, 'jointype': value.jointype, 'values': value.values.toString()};
                wsfilter.filters.push(filter);
            }
        }

        // Load questions for first page.
        requestQuestions(wsfilter)
            .then((response) => {
                // Cleans any notifications if not needed.
                let element = document.getElementById('user-notifications');
                while (element.firstChild) {
                    element.removeChild(element.firstChild);
                }
                if (response.warnings[0] !== undefined) {
                    if (response.warnings[0].warningcode === 'nocategoryconditionspecified') {
                        Notification.addNotification({
                            message: response.warnings[0].message,
                            type: 'info'
                          });
                    }
                }
                return renderQuestiondata(response.filtercondition);
            })
            // Render questions for first page and pagination.
            .then((response) => {
                const questionscontainer = document.querySelector(SELECTORS.QUESTION_CONTAINER_ID);
                if (response.questionhtml === undefined) {
                    response.questionhtml = '';
                }
                if (response.jsfooter === undefined) {
                    response.jsfooter = '';
                }
                Templates.replaceNodeContents(questionscontainer, response.questionhtml, response.jsfooter);
                // Resolve filter promise.
                if (pendingPromise) {
                    pendingPromise.resolve();
                }
            })
            .fail(Notification.exception);
    };

    /**
     * Render question data using the fragment.
     * @param {object} filtercondition
     * @return {*}
     */
    const renderQuestiondata = (filtercondition) => {
        // eslint-disable-next-line no-console
        console.log(extraparams);
        const viewData = {
            component: component,
            callback: callback,
            filtercondition: filtercondition,
            contextid: contextId,
            extraparams: extraparams,
        };
        const request = {methodname: 'core_question_view', args: viewData};
        return ajax.call([request])[0];
    };

    // Add listeners for the sorting actions.
    document.addEventListener('click', e => {
        const sortableLink = e.target.closest(SELECTORS.SORT_LINK);
        const paginationLink = e.target.closest(SELECTORS.PAGINATION_LINK);
        if (sortableLink) {
            e.preventDefault();
            let oldsort = wsfilter.sortdata;
            wsfilter.sortdata = [];
            let sortdata = {
                sortby: sortableLink.dataset.sortby,
                sortorder: sortableLink.dataset.sortorder
            };
            wsfilter.sortdata.push(sortdata);
            oldsort.forEach(value => {
                if (value.sortby !== sortableLink.dataset.sortby) {
                    wsfilter.sortdata.push(value);
                }
            });
            wsfilter.displayoptions.page = 0;
            coreFilter.updateTableFromFilter();
        }
        if (paginationLink) {
            e.preventDefault();
            let attr = e.target.getAttribute("href");
            if (attr !== '#') {
                const urlParams = new URLSearchParams(attr);
                wsfilter.displayoptions.page = urlParams.get('qpage');
                coreFilter.updateTableFromFilter();
            }
        }
    });

    // Run apply filter at page load.
    const urlLoadedFilters = coreFilter.loadUrlParams();
    const filter = filterSet.querySelector(Selectors.filter.region);
    if (Object.entries(urlLoadedFilters).length !== 0) {
        for (const urlFilter in urlLoadedFilters) {
            if (urlFilter != 'courseid') {
                coreFilter.addFilter(filter,
                                     urlFilter,
                                     urlLoadedFilters[urlFilter].values,
                                     urlLoadedFilters[urlFilter].jointype,
                                     urlLoadedFilters[urlFilter].rangetype);
            }
        }
    } else {
        coreFilter.addFilter(filter, 'category', [defaultcategoryid]);
    }
    applyFilter(urlLoadedFilters);
};
