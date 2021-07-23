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
 * Javascript for report card display and processing.
 *
 * @package    qbank_managecategories
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
*/

import $ from 'jquery';
import * as Str from 'core/str';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Fragment from 'core/fragment';
import Ajax from'core/ajax';
import Y from 'core/yui';

const displayModal = (selector, contextid, cmid) => {
    let trigger = $(selector);
    ModalFactory.create({
      title: Str.get_string('addcategory', 'question'),
      body: getBody(contextid, cmid),
      footer: 'test footer content',
    }, trigger)
    .done(function(modal) {
      // Do what you want with your new modal.
    });
};

const getBody = (contextid, cmid) => {
    let params = {cmid: JSON.stringify(cmid)};
    let htmlBody = Fragment.loadFragment('qbank_managecategories', 'new_category_form', contextid, params);
    return htmlBody;
};

export const initModal = (selector, contextid, cmid) => {
    displayModal(selector, contextid, cmid);
};