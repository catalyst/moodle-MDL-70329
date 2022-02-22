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

namespace core_question\external;

require_once($CFG->dirroot . '/question/editlib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

/**
 * Class qbank_view is the external service to load the question html via ajax call.
 *
 * @package    core_question
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbank_view extends external_api {

    /**
     * Describes the parameters for fetching the table html.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'component' => new external_value(PARAM_TEXT, 'The name of the component'),
            'callback' => new external_value(PARAM_TEXT, 'The name of the callback'),
            'filtercondition' => new external_value(PARAM_RAW, 'The filter conditions'),
            'contextid' => new external_value(PARAM_INT, 'The context of the api'),
            'extraparams' => new external_value(PARAM_RAW, 'The extra parameters for extended apis', VALUE_DEFAULT, ''),
        ]);
    }

    public static function execute($component, $callback, $filtercondition, $contextid, $extraparams = '') {
        global $OUTPUT;
        // Parameter validation.
        $params = self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'callback' => $callback,
            'filtercondition' => $filtercondition,
            'contextid' => $contextid,
            'extraparams' => $extraparams
        ]);
        $argument [] = json_encode([
            'filtercondition' => $params['filtercondition'],
            'extraparams' => $params['extraparams']
        ]);
        $context = \context::instance_by_id($contextid);
        self::validate_context($context);
        $OUTPUT->header();
        list ($questionhtml, $jsfooter) = component_callback($params['component'],
            'output_fragment_' . $params['callback'], $argument);
        return [
            'questionhtml' => $questionhtml,
            'jsfooter' => $jsfooter
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'questionhtml' => new external_value(PARAM_RAW, 'Question html to render'),
            'jsfooter' => new external_value(PARAM_RAW, 'Question js to readd')
        ]);
    }
}
