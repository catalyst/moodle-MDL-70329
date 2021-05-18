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
 * Library functions used by question/preview.php.
 *
 * @package    qbank_exportquestions
 * @copyright  2010 The Open University
 * @author     2021 Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qbank_exportquestions;

use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Class exportquestions_helper contains all the library functions.
 *
 * @package qbank_exportquestions
 */
class exportquestions_helper {

    /**
     * Create url for question export.
     *
     * @param int $contextid Current context.
     * @param int $categoryid Category id.
     * @param string $format
     * @param string $withcategories
     * @param $withcontexts
     * @param $filename
     * @return moodle_url
     */
    public static function question_make_export_url($contextid, $categoryid, $format, $withcategories,
                                      $withcontexts, $filename) {
        global $CFG;
        $urlbase = "$CFG->wwwroot/pluginfile.php";
        return moodle_url::make_file_url($urlbase,
            "/$contextid/question/export/{$categoryid}/{$format}/{$withcategories}" .
            "/{$withcontexts}/{$filename}", true);
    }
}
