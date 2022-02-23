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
 * Range filter.
 *
 * @module     core/local/filter/range
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Selectors from 'core/local/filter/selectors';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';

const filterRangeOptions = {
    '0': 'optionone',
    '1': 'optiontwo',
    '2': 'optionthree'
};
export default class {
    /**
     * Constructor for a new filter.
     *
     * @param {String} filterType The type of filter that this relates to
     * @param {HTMLElement} filterSet The root node for the participants filterset
     * @param {Array} initialValues The initial values for the selector
     * @param {Number} filterRange
     */
    constructor(filterType, filterSet, initialValues, filterRange) {
        this.filterType = filterType;
        this.rootNode = filterSet;

        // If URL parameters are loaded following variable are set.
        this.initialValues = initialValues;
        this.filterRange = filterRange;

        this.setUpRangeUi('text');
    }

    /**
     * Perform any tear-down for this filter type.
     */
    tearDown() {
        // eslint-disable-line no-empty-function
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
     * Renders one or two input based on given context.
     *
     * @param {Object} context Context for mustache containing one or two placeholder.
     */
    async displayRange(context) {
        Templates.render('core/local/filter/range', context)
        .then((rangeUi, js) => {
            Templates.replaceNodeContents(Selectors.filter.regions.values, rangeUi, js);
            return;
        }).fail();
    }

    /**
     * Adds listenner on filter range region.
     *
     * @param {string} type Type of input desired.
     */
    async rangeListenner(type) {
        const placeholderone = await this.placeholderOne;
        const placeholdertwo = await this.placeholderTwo;
        this.filterRoot.querySelector(Selectors.filter.fields.range).addEventListener('change', () => {
            const context = {
                placeholderone: placeholderone,
                type: type
            };
            if (this.rangetype === 2) {
                context.placeholdertwo = placeholdertwo;
            }
            this.displayRange(context);
        });
    }

    /**
     * Sets up base range UI.
     *
     * @param {string} type Type of input desired.
     * @param {Array} initialValues Initial values.
     */
    async setUpRangeUi(type, initialValues) {
        const placeholderone = await this.placeholderOne;
        const placeholdertwo = await this.placeholderTwo;
        const context = {
            placeholderone: placeholderone,
            placeholdertwo: placeholdertwo,
            type: type
        };

        const filterRangeContext = {};
        // Default filter range value.
        filterRangeContext[filterRangeOptions[2]] = true;
        if (this.filterRange !== undefined) {
            filterRangeContext[filterRangeOptions[2]] = false;
            filterRangeContext[filterRangeOptions[this.filterRange]] = true;
        }
        // When url parameters loaded supplied - display setup accordingly.
        if (initialValues !== undefined) {
            context.initialvalueone = initialValues[0];
            // Do not display a second range value if initial value is not between.
            context.placeholdertwo = null;
            if (initialValues.length > 1) {
                context.initialvaluetwo = initialValues[1];
                // If multiple values are supplied - display two range inputs.
                context.placeholdertwo = placeholdertwo;
            }
        }
        Templates.render('core/filter_range', filterRangeContext)
        .then((html, js) => {
            Templates.replaceNodeContents(Selectors.filter.regions.range, html, js);
            this.displayRange(context)
            .then(() => {
                this.rangeListenner(type);
                return;
            }).catch();
            return;
        })
        .catch();
    }

    /**
     * Get the placeholder for range value one.
     *
     * @return {String} String
     */
    get placeholderOne() {
        return getString('rangestart', 'core_question');
    }

    /**
     * Get the placeholder for range value two.
     *
     * @return {String} String
     */
    get placeholderTwo() {
        return getString('rangeend', 'core_question');
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
     * Get the type of range specified.
     *
     * @returns {Number}
     */
    get rangetype() {
        return parseInt(this.filterRoot.querySelector(Selectors.filter.fields.range).value, 10);
    }

    /**
     * Get the list of raw values for this filter type.
     *
     * @returns {Array}
     */
    get rawValues() {
        const rangeValue1 = document.getElementById('rangeValue1').value;
        const values = [rangeValue1];
        if (this.rangetype === 2) {
            const rangeValue2 = document.getElementById('rangeValue2').value;
            values.push(rangeValue2);
        }
        return values;
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
            rangetype: this.rangetype,
            values: this.rawValues,
        };
    }
}
