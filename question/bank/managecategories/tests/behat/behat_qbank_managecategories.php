<?php
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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * Behat steps definitions for question category UI.
 *
 * @package   qbank_managecategories
 * @category  test
 * @copyright 2021 Catalyst IT Australia Pty Ltd
 * @author    2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_qbank_managecategories extends behat_base {
    /**
     * Generic click action. Click on the edit menu specified item.
     *
     * @When /^I click on "(?P<element_string>(?:[^"]|\\")*)" edit menu in the question category list$/
     * @param string $element Element we look for
     */
    public function i_click_on_edit_menu_question_category_list($element) {
        // Gets the node based on the requested selector type and locator.
        $selectortype = 'xpath_element';
        $element = "//a[contains(text(), '{$element}')]/../../div[@class='float-right']/div[@class='float-left']";
        $node = $this->get_selected_node($selectortype, $element);
        $this->ensure_node_is_visible($node);
        $node->click();
    }

    /**
     * Drags and drops the specified element in the question category list.
     *
     * @Given /^I drag "(?P<element_string>(?:[^"]|\\")*)" and I drop it in "(?P<container_element_string>(?:[^"]|\\")*)" in the question category list$/
     * @param string $source
     * @param string $target
     */
    public function i_drag_and_i_drop_it_in_question_category_list($source, $target) {
        $source = "//a[contains(text(), '{$source}')]/../../div[@class='float-right']/div[@class='float-right']/span";
        $sourcetype = 'xpath_element';
        $targettype = 'text';
        $generalcontext = behat_context_helper::get('behat_general');
        $generalcontext->i_drag_and_i_drop_it_in($source, $sourcetype, $target, $targettype);
    }

    /**
     * Click on specific button.
     *
     * @Given /^I click on "(?P<element_string>(?:[^"]|\\")*)" button$/
     * @param string $element
     */
    public function i_click_on_button($element) {
        $selectortype = 'xpath_element';
        $element = "//button[contains(text(), '$element')]";
        $node = $this->get_selected_node($selectortype, $element);
        $this->ensure_node_is_visible($node);
        $node->click();
    }
}
