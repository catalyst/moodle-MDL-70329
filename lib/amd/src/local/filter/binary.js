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
 * Base filter for binary selector ie: (Yes / No).
 *
 * @module     core/local/filter/binary
 * @author     2022 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @copyright  2022 Catalyst IT Australia Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Selectors from 'core/local/filter/selectors';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';

const binaryOptions = {
    '0': 'optionone',
    '1': 'optiontwo'
};
export default class {
    /**
     * Constructor for binary base filter.
     *
     * @param {String} filterType The type of filter that this relates to
     * @param {HTMLElement} filterSet The root node for the participants filterset
     * @param {Array} initialValues The initial values for the selector
     */
    constructor(filterType, filterSet, initialValues) {
        this.filterType = filterType;
        this.rootNode = filterSet;
        this.initialValues = initialValues;
        this.getTextValues().then(() => {
            this.displayBinarySelection();
            return;
        }).catch();
    }

    /**
     * Perform any tear-down for this filter type.
     */
    tearDown() {
        // eslint-disable-line no-empty-function
    }

    /**
     * Allows alternate text values to be passed in array for select element.
     *
     * @param {Array} optionalValues Optional array precising select values.
     */
    async getTextValues(optionalValues) {
        if (optionalValues === undefined) {
            this.optionOne = await getString('yes');
            this.optionTwo = await getString('no');
        } else {
            this.optionOne = optionalValues[0];
            this.optionTwo = optionalValues[1];
        }
    }

    /**
     * Renders yes/no select input with proper selection.
     *
     */
    displayBinarySelection() {
        // We specify a specific filterset in case there are multiple filtering condition - avoiding glitches.
        const specificFilterSet = this.rootNode.querySelector(Selectors.filter.byName(this.filterType));
        const context = {filtertype: this.filterType, textvalueone: this.optionOne, textvaluetwo: this.optionTwo};
        // Default selection.
        context[binaryOptions[1]] = true;
        // Load any URL parameter.
        if (this.initialValues !== undefined) {
            context[binaryOptions[1]] = false;
            context[binaryOptions[this.initialValues[0]]] = true;
        }
        Templates.render('core/local/filter/binary_selector', context)
        .then((binaryUi, js) => {
            Templates.replaceNodeContents(specificFilterSet.querySelector(Selectors.filter.regions.values), binaryUi, js);
            return;
        }).fail();
    }

    /**
     * Get the root node for this filter.
     *
     * @returns {HTMLElement}
     */
    get filterRoot() {
        return this.rootNode.querySelector(Selectors.filter.byName(this.filterType));
    }

    /**
     * Get the name of this filter.
     *
     * @returns {String}
     */
    get name() {
        return this.filterType;
    }

    /**
     * Get the type of join specified.
     *
     * @returns {Number}
     */
    get jointype() {
        return parseInt(this.filterRoot.querySelector(Selectors.filter.fields.join).value, 10);
    }

    /**
     * Get the list of raw values for this filter type.
     *
     * @returns {Array}
     */
    get values() {
        return this.filterRoot.querySelector(`[data-filterfield="${this.name}"]`).value;
    }

    /**
     * Get the composed value for this filter.
     *
     * @returns {Object}
     */
    get filterValue() {
        return {
            name: this.name,
            jointype: this.jointype,
            values: this.values,
        };
    }
}
