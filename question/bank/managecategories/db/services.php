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


/**
 * qbank_managecategories external functions and service definitions.
 * @package    qbank_managecategories
 * @category   webservice
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     2021, Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'qbank_managecategories_set_category_order' => [
        'classname'   => 'qbank_managecategories_external',
        'methodname'  => 'set_category_order',
        'classpath'   => 'question/bank/managecategories/classes/external/external.php',
        'description' => 'Returns question category order',
        'type'        => 'write',
        'ajax'        => true,
    ],    
    'qbank_managecategories_submit_add_category_form' => [
        'classname'   => 'qbank_managecategories_external',
        'methodname'  => 'submit_add_category_form',
        'classpath'   => 'question/bank/managecategories/classes/external/external.php',
        'description' => 'Adds a new question category',
        'type'        => 'write',
        'ajax'        => true,
    ],
];
