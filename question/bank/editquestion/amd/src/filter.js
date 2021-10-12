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
import $ from 'jquery';
import Templates from 'core/templates';
import PagedContentFactory from 'core/paged_content_factory';

/**
 * Initialise the question bank filter on the element with the given id.
 *
 * @param {String} filterRegionId
 * @param {String} defaultcourseid
 * @param {String} defaultcategoryid
 * @param {int} perpage
 */
export const init = (filterRegionId, defaultcourseid, defaultcategoryid, perpage) => {
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

        let qperpage = perpage;
        let qpage = 0;
        let qbshowtext = false;
        let recurse = false;
        let showhidden = false;

        let promises = ajax.call([{
            methodname: 'core_qbank_dummy', args: {
                    courseid: courseid,
                    category: categories,
                     qtagids: qtagids,
                    qperpage: qperpage,
                       qpage: qpage,
                  qbshowtext: qbshowtext,
                     recurse: recurse,
                  showhidden: showhidden
                }
            }
        ]);

        promises[0].done(function(response) {
            let questionscontainer = document.getElementById('questionscontainer');
            let totalpage = response.totalpage;
            let pagination = renderPagination(qpage, qperpage, totalpage);
            let html = '<div className="categoryquestionscontainer" id="questionscontainer">' +
                pagination +
                response.html +
                '</div>';
            // questionscontainer.innerHTML = html;

            if (pendingPromise) {
                pendingPromise.resolve();
            }
        });

    };
    /**
     * Render pagination
     *
     * @param {int} page
     * @param {int} perpage
     * @param {int} totalpage
     */
    const renderPagination = (page, perpage, totalpage) => {


        // Some container for your paged content.
        var container = $('#questionscontainer');
        PagedContentFactory.createWithLimit(
            // Show 10 items per page.
            10,
            // Callback to load and render the items as the user clicks on the pages.
            function(pagesData, actions) {
                return pagesData.map(function(pageData) {
                    // Your function to load the data for the given limit and offset.
                    // actions.allItemsLoaded(1);
                    console.log(pageData);
                    // return loadData(pageData.limit, pageData.offset)
                        // .then(function(data) {
                        //     // You criteria for when all of the data has been loaded.
                        //     if (data.length > 100) {
                        //         // Tell the page content code everything has been loaded now.
                        //         actions.allItemsLoaded(pageData.pageNumber);
                        //     }
                        //
                        //     // Your function to render the data you've loaded.
                        //     // return renderData(data);
                        // });
                });
            },
            // Config to set up the paged content.
            {
                controlPlacementBottom: true,
                eventNamespace: 'example-paged-content',
                persistentLimitKey: 'example-paged-content-limit-key'
            }
        ).then(function(html, js) {
            // Add the paged content into the page.
            console.log(html);
            Templates.replaceNodeContents(container, html, js);
        });

        return '<div> Total page:' + totalpage + ' </div>';

    };

    applyFilter();
};

