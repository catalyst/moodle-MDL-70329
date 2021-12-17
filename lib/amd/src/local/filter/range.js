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
 * @copyright  2021 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Selectors from 'core/local/filter/selectors';
import Templates from 'core/templates';
export default class {
    constructor(filterType, filterSet) {
        this.filterType = filterType;
        this.rootNode = filterSet;
        this.setUpRangeUi();
        this.rangeListenner();
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
     * Adds listenner on filter range region.
     *
     */
    rangeListenner() {
        this.filterRoot.querySelector(Selectors.filter.regions.range).addEventListener('change', () => {
            const context = {
                placeholderone: this.placeholderOne
            };
            if (this.rangetype === 2) {
                context.placeholdertwo = this.placeholderTwo;
            }
            Templates.render('core/local/filter/range', context)
            .then((rangeUi, js) => {
                Templates.replaceNodeContents(this.getFilterValueNode(), rangeUi, js);
                return;
            }).fail();
        });
    }

    /**
     * Sets up base range UI.
     *
     */
    setUpRangeUi() {
        const context = {
            placeholderone: this.placeholderOne,
            placeholdertwo: this.placeholderTwo
        };
        Templates.render('core/filter_range', {})
        .then((html, js) => {
            Templates.appendNodeContents(Selectors.filter.regions.range, html, js);
            return;
        }).fail();
        Templates.render('core/local/filter/range', context)
        .then((rangeUi, js) => {
            Templates.appendNodeContents(this.getFilterValueNode(), rangeUi, js);
            return;
        }).fail();
    }

    /**
     * Get the HTMLElement which contains the value selector.
     *
     * @returns {HTMLElement}
     */
    getFilterValueNode() {
        return this.filterRoot.querySelector(Selectors.filter.regions.values);
    }

    /**
     * Get the placeholder for range value one.
     *
     * @return {String} String
     */
    get placeholderOne() {
        return 'First range value';
    }

    /**
     * Get the placeholder for range value two.
     *
     * @return {String} String
     */
    get placeholderTwo() {
        return 'Second range value';
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
