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
 * Discrimination index filter.
 *
 * @module     core/local/filter/filtertypes/discrimination
 * @copyright  2022 Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Autocomplete from 'core/form-autocomplete';
import Range from 'core/local/filter/range';
import Selectors from 'core/local/filter/selectors';
import Templates from 'core/templates';
import {get_string as getString} from 'core/str';

const INTERPRETATION = {
    VALUES: {
        'Very good discrimination': 1,
        'Adequate discrimination': 2,
        'Weak discrimination': 3,
        'Very weak discrimination': 4,
        'Question probably invalid': 5,
    },
    RANGES: {
        '1': '49',
        '2': '30-49',
        '3': '20-29',
        '4': '0-19',
        '5': '0'
    }
};

export default class extends Range {
    constructor(filterType, filterSet, initialValues, filterRange) {
        super(filterType, filterSet, initialValues, filterRange);
        this.interpretation = false;
        this.filterType = filterType;
        this.setUpRangeUi('number', initialValues).then(() => {
            this.displayRadio();
            return;
        }).catch();
    }

    /**
     * Displays radio inputs.
     *
     */
    displayRadio() {
        const filterRange = this.rootNode.querySelector(Selectors.filter.regions.rangeByName(this.filterType));
        const filterInterpretation = document.createElement('div');
        filterInterpretation.setAttribute('data-filterregion', 'interpretation');
        Templates.render('qbank_statistics/discrimination_index_radio', {})
        .then((html) => {
            filterInterpretation.innerHTML = html;
            filterRange.parentNode.insertBefore(filterInterpretation, filterRange);
            return;
        })
        .then(() =>{
            this.discriminationRadioListenner();
            return;
        })
        .catch();
    }

    /**
     * Adds listenner on radio inputs.
     *
     */
    async discriminationRadioListenner() {
        const placeholderOne = await this.placeholderOne;
        const placeholderTwo = await this.placeholderTwo;
        const interpretationPlaceholder = await this.interpretationPlaceholder;
        const filterRange = this.filterRoot.querySelector(Selectors.filter.regions.rangeByName(this.filterType));
        const discriminationSelect = document.getElementById('discriminationselect');
        discriminationSelect.addEventListener('change', (e) => {
            if (e.target.value === 'interpretation') {
                filterRange.setAttribute('class', 'd-none');
                this.interpretation = true;
                const dataSource = this.filterRoot.querySelector('#rangeValue1');
                const inputHide = document.getElementById('rangeValue2');
                if (inputHide !== null) {
                    inputHide.setAttribute('class', 'd-none');
                }

                for (const [key, value] of Object.entries(INTERPRETATION.VALUES)) {
                    let selectedOption;
                    selectedOption = document.createElement('option');
                    selectedOption.value = value;
                    selectedOption.innerHTML = key;
                    dataSource.append(selectedOption);
                }
                Autocomplete.enhance(
                    dataSource,
                    false,
                    null,
                    interpretationPlaceholder,
                    false,
                    true,
                    null,
                    true,
                    {
                        layout: 'core/local/filter/autocomplete_layout',
                        selection: 'core/local/filter/autocomplete_selection',
                    }
                );
            }
            if (e.target.value === 'index') {
                filterRange.removeAttribute('class');
                this.interpretation = false;
                const context = {
                    placeholderone: placeholderOne,
                    type: 'number'
                };
                if (this.rangetype === 2) {
                    context.placeholdertwo = placeholderTwo;
                }
                this.displayRange(context);
            }
        });
    }

    /**
     * Get the placeholder for range value one.
     *
     * @return {Promise} Promise resolving string
     */
    get placeholderOne() {
        return getString('firstplaceholder', 'qbank_statistics');
    }

    /**
     * Get the placeholder for range value two.
     *
     * @return {Promise} Promise resolving string
     */
    get placeholderTwo() {
        return getString('secondplaceholder', 'qbank_statistics');
    }

    /**
     * Get the placeholder for interpretation.
     *
     * @return {Promise} Promise resolving string
     */
    get interpretationPlaceholder() {
        return getString('interpretationplaceholder', 'qbank_statistics');
    }

    /**
     * Get selected option when interpretation selected.
     *
     * @returns {Number}
     */
    get selectedOption() {
        return this.filterRoot.querySelector('div[data-active-value]').dataset.activeValue;
    }

    /**
     * Get ranges when interpretation selected.
     *
     * @returns {Array}
     */
    get interpretationRanges() {
        const range = INTERPRETATION.RANGES[this.selectedOption].split('-');
        return [range[0], range[1]];
    }

    /**
     * Get the list of raw values for this filter type.
     *
     * @returns {Array}
     */
    get rawValues() {
        if (this.interpretation === true) {
            if (this.selectedOption == 1) {
                // After.
                return [this.interpretationRanges[0]];
            }
            if (this.selectedOption == 5) {
                // Before.
                return [this.interpretationRanges[0]];
            }
            return this.interpretationRanges;
        }
        const rangeValue1 = document.getElementById('rangeValue1').value;
        const values = [rangeValue1];
        if (this.rangetype === 2) {
            const rangeValue2 = document.getElementById('rangeValue2').value;
            values.push(rangeValue2);
        }
        return values;
    }

    /**
     * Get the type of range specified.
     *
     * @returns {Number}
     */
    get rangetype() {
        if (this.interpretation === true) {
            if (this.selectedOption == 1) {
                // After.
                return 0;
            }
            if (this.selectedOption == 5) {
                // Before.
                return 1;
            }
        }
        return parseInt(this.filterRoot.querySelector(Selectors.filter.fields.range).value, 10);
    }
}
