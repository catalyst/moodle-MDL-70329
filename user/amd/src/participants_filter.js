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
 * Participants filter management.
 *
 * @module     core_user/participants_filter
 * @copyright  2021 Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as CoreFilter from 'core/filter';
import * as DynamicTable from 'core_table/dynamic';
import Selectors from 'core/local/filter/selectors';
import Notification from 'core/notification';

/**
 * Initialise the participants filter on the element with the given id.
 *
 * @param {String} filterRegionId The id for the filter element.
 */
export const init = filterRegionId => {
    CoreFilter.init(filterRegionId,  function(filters, filterSet, pendingPromise) {
        console.log(filters);
        console.log(filterSet);
        console.log(pendingPromise);
        DynamicTable.setFilters(
            DynamicTable.getTableFromId(filterSet.dataset.tableRegion),
            {
                jointype: parseInt(filterSet.querySelector(Selectors.filterset.fields.join).value, 10),
                filters,
            }
        )
            .then(result => {
                pendingPromise.resolve();

                return result;
            })
            .catch(Notification.exception);
    });
};

