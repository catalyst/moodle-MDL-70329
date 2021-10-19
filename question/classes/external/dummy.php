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
 * Question external API.
 *
 * @package    core_question
 * @category   external
 * @copyright  2021 Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core_question\external;

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;


/**
 * Core question external functions.
 *
 * @copyright  2021 Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dummy extends external_api {

    /**
     * Describes the parameters for fetching the table html.
     *
     * @return external_function_parameters
     */
    public static function get_questions_parameters(): external_function_parameters {
        return new external_function_parameters ([
            'filterverb' => new external_value(
                PARAM_INT,
                'Main join types',
                VALUE_DEFAULT,
                2
            ),
            'filters' => new external_multiple_structure (
                new external_single_structure(
                    array(
                        'filtertype' => new external_value(PARAM_ALPHANUM,'Filter type'),
                        'jointype' => new external_value(PARAM_INT, 'Join type'),
                        'values' => new external_value(PARAM_RAW, 'list of ids'),
                    )
                ), 'Filter params', VALUE_DEFAULT, array()
            ),
            'defaultcourseid' => new external_value(
                PARAM_INT,
                'Course ID',
                VALUE_REQUIRED,
            ),
            'defaultcategoryid' => new external_value(
                PARAM_INT,
                'default question category ID',
                VALUE_REQUIRED,
            ),
            'qperpage' => new external_value(
                PARAM_INT,
                'The number of records per page',
                VALUE_DEFAULT,
                false,
            ),
            'qpage' => new external_value(
                PARAM_INT,
                'The page number',
                VALUE_DEFAULT,
                0
            ),
            'qbshowtext' => new external_value(
                PARAM_BOOL,
                'Flag to show question text',
                VALUE_DEFAULT,
                false,
            ),
            'recurse' => new external_value(
                PARAM_BOOL,
                'Type of join to join all filters together',
                VALUE_DEFAULT,
                false,
            ),
            'showhidden' => new external_value(
                PARAM_BOOL,
                'Flag to show question text',
                VALUE_DEFAULT,
                false,
            ),
        ]);
    }

    /**
     * External function to get the table view content.
     *
     * @param int $defaultcourseid
     * @param int $defaultcategoryid
     * @param int $qperpage
     * @param int $qpage
     * @param bool $qbshowtext
     * @param bool $recurse
     * @param bool $showhidden
     * @return array
     */
    public static function get_questions(
        int $filterverb,
        array $filters = [],
        int $defaultcourseid,
        int $defaultcategoryid,
        int $qperpage = 0,
        int $qpage = 0,
        bool $qbshowtext = false,
        bool $recurse = false,
        bool $showhidden = false
    ): array {
        global $PAGE;

        $params = self::validate_parameters(self::get_questions_parameters(), [
            'filterverb' => $filterverb,
            'filters' => $filters,
            'defaultcourseid' => $defaultcourseid,
            'defaultcategoryid' => $defaultcategoryid,
            'qperpage' => $qperpage,
            'qpage' => $qpage,
            'qbshowtext' => $qbshowtext,
            'recurse' => $recurse,
            'showhidden' => $showhidden,
        ]);

        $tablehtml = '<table id="categoryquestions"><thead><tr><th class="header checkbox" scope="col">
            <span title="Select questions for bulk actions"><input id="qbheadercheckbox" name="qbheadercheckbox" type="checkbox" value="1" data-action="toggle" data-toggle="master" data-togglegroup="qbank" data-toggle-selectall="Select all" data-toggle-deselectall="Deselect all">
    <label for="qbheadercheckbox" class="accesshide">Select all</label></span>
</th><th class="header qtype" scope="col">
        <div class="sorters">
            <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0&amp;qbs1=-qbank_viewquestiontype%5Cquestion_type_column&amp;qbs2=qbank_viewquestionname%5Cquestion_name_idnumber_tags_column-name" title="Sort by Question type descending">
    T<i class="icon fa fa-sort-asc fa-fw iconsort" title="Ascending" aria-label="Ascending"></i>
</a>
        </div>
</th><th class="header qnameidnumbertags" scope="col">
        <div class="title">Question</div>
        <div class="sorters">
            <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0&amp;qbs1=qbank_viewquestionname%5Cquestion_name_idnumber_tags_column-name&amp;qbs2=qbank_viewquestiontype%5Cquestion_type_column" title="Sort by Question name ascending">
    Question name
</a> / <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0&amp;qbs1=qbank_viewquestionname%5Cquestion_name_idnumber_tags_column-idnumber&amp;qbs2=qbank_viewquestiontype%5Cquestion_type_column&amp;qbs3=qbank_viewquestionname%5Cquestion_name_idnumber_tags_column-name" title="Sort by ID number ascending">
    ID number
</a>
        </div>
</th><th class="header editmenu" scope="col">
            Actions
</th><th class="header creatorname" scope="col">
        <div class="title">Created by</div>
        <div class="sorters">
            <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0&amp;qbs1=qbank_viewcreator%5Ccreator_name_column-firstname&amp;qbs2=qbank_viewquestiontype%5Cquestion_type_column&amp;qbs3=qbank_viewquestionname%5Cquestion_name_idnumber_tags_column-name" title="Sort by First name ascending">
    First name
</a> / <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0&amp;qbs1=qbank_viewcreator%5Ccreator_name_column-lastname&amp;qbs2=qbank_viewquestiontype%5Cquestion_type_column&amp;qbs3=qbank_viewquestionname%5Cquestion_name_idnumber_tags_column-name" title="Sort by Surname ascending">
    Surname
</a> / <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0&amp;qbs1=qbank_viewcreator%5Ccreator_name_column-timecreated&amp;qbs2=qbank_viewquestiontype%5Cquestion_type_column&amp;qbs3=qbank_viewquestionname%5Cquestion_name_idnumber_tags_column-name" title="Sort by Date ascending">
    Date
</a>
        </div>
</th><th class="header modifiername" scope="col">
        <div class="title">Last modified by</div>
        <div class="sorters">
            <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0&amp;qbs1=qbank_viewcreator%5Cmodifier_name_column-firstname&amp;qbs2=qbank_viewquestiontype%5Cquestion_type_column&amp;qbs3=qbank_viewquestionname%5Cquestion_name_idnumber_tags_column-name" title="Sort by First name ascending">
    First name
</a> / <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0&amp;qbs1=qbank_viewcreator%5Cmodifier_name_column-lastname&amp;qbs2=qbank_viewquestiontype%5Cquestion_type_column&amp;qbs3=qbank_viewquestionname%5Cquestion_name_idnumber_tags_column-name" title="Sort by Surname ascending">
    Surname
</a> / <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0&amp;qbs1=qbank_viewcreator%5Cmodifier_name_column-timemodified&amp;qbs2=qbank_viewquestiontype%5Cquestion_type_column&amp;qbs3=qbank_viewquestionname%5Cquestion_name_idnumber_tags_column-name" title="Sort by Date ascending">
    Date
</a>
        </div>
</th></tr></thead><tbody><tr class="r0"><td class="checkbox"><input id="checkq1" name="q1" type="checkbox" value="1" data-action="toggle" data-toggle="slave" data-togglegroup="qbank">
    <label for="checkq1" class="accesshide">Select</label></td><td class="qtype"><img class="icon " title="Multiple choice" alt="Multiple choice" src="http://localhost/theme/image.php/boost/qtype_multichoice/1633669926/icon"></td><td class="qnameidnumbertags"><label for="checkq1" class="d-inline-flex flex-nowrap overflow-hidden w-100"><span class="questionname flex-grow-1 flex-shrink-1 text-truncate">Multiple choice Question</span><div class="tag_list hideoverlimit d-inline flex-shrink-1 text-truncate ml-1">
        <b class="accesshide">Tags:</b>
    <ul class="inline-list">
            <li>
                <a href="http://localhost/tag/index.php?tc=1&amp;tag=ZZZ&amp;from=137" class="badge badge-info standardtag">
                    ZZZ</a>
            </li>
    </ul>
    </div></label></td><td class="editmenu"><div class="action-menu moodle-actionmenu" id="action-menu-2" data-enhance="moodle-core-actionmenu">

        <div class="menubar d-flex " id="action-menu-2-menubar" role="menubar">

            


                <div class="action-menu-trigger">
                    <div class="dropdown">
                        <a href="#" tabindex="0" class=" dropdown-toggle icon-no-margin" id="action-menu-toggle-2" aria-label="Edit" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" aria-controls="action-menu-2-menu" data-display="static">
                            
                            Edit
                                
                            <b class="caret"></b>
                        </a>
                            <div class="dropdown-menu dropdown-menu-right menu  align-tl-bl" id="action-menu-2-menu" data-rel="menu-content" aria-labelledby="action-menu-toggle-2" role="menu" data-align="tl-bl">
                                                                <a href="http://localhost/question/bank/editquestion/question.php?returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0&amp;courseid=3&amp;id=1" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-1">
                                <i class="icon fa fa-cog fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-1">Edit question</span>
                        </a>
                                                                <a href="http://localhost/question/bank/editquestion/question.php?returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0&amp;courseid=3&amp;id=1&amp;makecopy=1" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-2">
                                <i class="icon fa fa-copy fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-2">Duplicate</span>
                        </a>
                                                                <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0" class="dropdown-item menu-action" data-action="edittags" data-cantag="1" data-contextid="137" data-questionid="1" role="menuitem" aria-labelledby="actionmenuaction-3">
                                <i class="icon fa fa-tags fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-3">Manage tags</span>
                        </a>
                                                                <a href="http://localhost/question/bank/previewquestion/preview.php?id=1&amp;courseid=3&amp;returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-4">
                                <i class="icon fa fa-search-plus fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-4">Preview</span>
                        </a>
                                                                <a href="http://localhost/question/bank/deletequestion/delete.php?deleteselected=1&amp;q1=1&amp;sesskey=yaFHdOQeG6&amp;courseid=3&amp;returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-5">
                                <i class="icon fa fa-trash fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-5">Delete</span>
                        </a>
                                                                <a href="http://localhost/question/bank/exporttoxml/exportone.php?id=1&amp;sesskey=yaFHdOQeG6&amp;courseid=3" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-6">
                                <i class="icon fa fa-download fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-6">Export as Moodle XML</span>
                        </a>
                            </div>
                    </div>
                </div>

        </div>

</div></td><td class="creatorname"><span class="qbank-creator-name">
    Admin User
</span>
<br>
<span class="date">
    17 September 2021, 3:37 AM
</span></td><td class="modifiername"><span class="qbank-creator-name">
    Admin User
</span>
<br>
<span class="date">
    17 September 2021, 3:37 AM
</span></td></tr><tr class="highlight text-dark r1"><td class="checkbox"><input id="checkq8025" name="q8025" type="checkbox" value="1" data-action="toggle" data-toggle="slave" data-togglegroup="qbank">
    <label for="checkq8025" class="accesshide">Select</label></td><td class="qtype"><img class="icon " title="Short answer" alt="Short answer" src="http://localhost/theme/image.php/boost/qtype_shortanswer/1633669926/icon"></td><td class="qnameidnumbertags"><label for="checkq8025" class="d-inline-flex flex-nowrap overflow-hidden w-100"><span class="questionname flex-grow-1 flex-shrink-1 text-truncate">Short answer</span></label></td><td class="editmenu"><div class="action-menu moodle-actionmenu" id="action-menu-3" data-enhance="moodle-core-actionmenu">

        <div class="menubar d-flex " id="action-menu-3-menubar" role="menubar">

            


                <div class="action-menu-trigger">
                    <div class="dropdown">
                        <a href="#" tabindex="0" class=" dropdown-toggle icon-no-margin" id="action-menu-toggle-3" aria-label="Edit" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" aria-controls="action-menu-3-menu" data-display="static">
                            
                            Edit
                                
                            <b class="caret"></b>
                        </a>
                            <div class="dropdown-menu dropdown-menu-right menu  align-tl-bl" id="action-menu-3-menu" data-rel="menu-content" aria-labelledby="action-menu-toggle-3" role="menu" data-align="tl-bl">
                                                                <a href="http://localhost/question/bank/editquestion/question.php?returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0&amp;courseid=3&amp;id=8025" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-7">
                                <i class="icon fa fa-cog fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-7">Edit question</span>
                        </a>
                                                                <a href="http://localhost/question/bank/editquestion/question.php?returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0&amp;courseid=3&amp;id=8025&amp;makecopy=1" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-8">
                                <i class="icon fa fa-copy fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-8">Duplicate</span>
                        </a>
                                                                <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0" class="dropdown-item menu-action" data-action="edittags" data-cantag="1" data-contextid="137" data-questionid="8025" role="menuitem" aria-labelledby="actionmenuaction-9">
                                <i class="icon fa fa-tags fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-9">Manage tags</span>
                        </a>
                                                                <a href="http://localhost/question/bank/previewquestion/preview.php?id=8025&amp;courseid=3&amp;returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-10">
                                <i class="icon fa fa-search-plus fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-10">Preview</span>
                        </a>
                                                                <a href="http://localhost/question/bank/deletequestion/delete.php?deleteselected=8025&amp;q8025=1&amp;sesskey=yaFHdOQeG6&amp;courseid=3&amp;returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-11">
                                <i class="icon fa fa-trash fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-11">Delete</span>
                        </a>
                                                                <a href="http://localhost/question/bank/exporttoxml/exportone.php?id=8025&amp;sesskey=yaFHdOQeG6&amp;courseid=3" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-12">
                                <i class="icon fa fa-download fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-12">Export as Moodle XML</span>
                        </a>
                            </div>
                    </div>
                </div>

        </div>

</div></td><td class="creatorname"><span class="qbank-creator-name">
    Admin User
</span>
<br>
<span class="date">
    8 October 2021, 6:33 AM
</span></td><td class="modifiername"><span class="qbank-creator-name">
    Admin User
</span>
<br>
<span class="date">
    8 October 2021, 6:33 AM
</span></td></tr><tr class="r0"><td class="checkbox"><input id="checkq4" name="q4" type="checkbox" value="1" data-action="toggle" data-toggle="slave" data-togglegroup="qbank">
    <label for="checkq4" class="accesshide">Select</label></td><td class="qtype"><img class="icon " title="True/False" alt="True/False" src="http://localhost/theme/image.php/boost/qtype_truefalse/1633669926/icon"></td><td class="qnameidnumbertags"><label for="checkq4" class="d-inline-flex flex-nowrap overflow-hidden w-100"><span class="questionname flex-grow-1 flex-shrink-1 text-truncate">TF System for course</span><div class="tag_list hideoverlimit d-inline flex-shrink-1 text-truncate ml-1">
        <b class="accesshide">Tags:</b>
    <ul class="inline-list">
            <li>
                <a href="http://localhost/tag/index.php?tc=1&amp;tag=ZZZ&amp;from=137" class="badge badge-info standardtag">
                    ZZZ</a>
            </li>
    </ul>
    </div></label></td><td class="editmenu"><div class="action-menu moodle-actionmenu" id="action-menu-4" data-enhance="moodle-core-actionmenu">

        <div class="menubar d-flex " id="action-menu-4-menubar" role="menubar">

            


                <div class="action-menu-trigger">
                    <div class="dropdown">
                        <a href="#" tabindex="0" class=" dropdown-toggle icon-no-margin" id="action-menu-toggle-4" aria-label="Edit" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" aria-controls="action-menu-4-menu" data-display="static">
                            
                            Edit
                                
                            <b class="caret"></b>
                        </a>
                            <div class="dropdown-menu dropdown-menu-right menu  align-tl-bl" id="action-menu-4-menu" data-rel="menu-content" aria-labelledby="action-menu-toggle-4" role="menu" data-align="tl-bl">
                                                                <a href="http://localhost/question/bank/editquestion/question.php?returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0&amp;courseid=3&amp;id=4" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-13">
                                <i class="icon fa fa-cog fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-13">Edit question</span>
                        </a>
                                                                <a href="http://localhost/question/bank/editquestion/question.php?returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0&amp;courseid=3&amp;id=4&amp;makecopy=1" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-14">
                                <i class="icon fa fa-copy fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-14">Duplicate</span>
                        </a>
                                                                <a href="http://localhost/question/edit.php?courseid=3&amp;cat=4%2C137&amp;qpage=0" class="dropdown-item menu-action" data-action="edittags" data-cantag="1" data-contextid="137" data-questionid="4" role="menuitem" aria-labelledby="actionmenuaction-15">
                                <i class="icon fa fa-tags fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-15">Manage tags</span>
                        </a>
                                                                <a href="http://localhost/question/bank/previewquestion/preview.php?id=4&amp;courseid=3&amp;returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-16">
                                <i class="icon fa fa-search-plus fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-16">Preview</span>
                        </a>
                                                                <a href="http://localhost/question/bank/deletequestion/delete.php?deleteselected=4&amp;q4=1&amp;sesskey=yaFHdOQeG6&amp;courseid=3&amp;returnurl=%2Fquestion%2Fedit.php%3Fcourseid%3D3%26cat%3D4%252C137%26qpage%3D0" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-17">
                                <i class="icon fa fa-trash fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-17">Delete</span>
                        </a>
                                                                <a href="http://localhost/question/bank/exporttoxml/exportone.php?id=4&amp;sesskey=yaFHdOQeG6&amp;courseid=3" class="dropdown-item menu-action" role="menuitem" aria-labelledby="actionmenuaction-18">
                                <i class="icon fa fa-download fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-18">Export as Moodle XML</span>
                        </a>
                            </div>
                    </div>
                </div>

        </div>

</div></td><td class="creatorname"><span class="qbank-creator-name">
    Admin User
</span>
<br>
<span class="date">
    17 September 2021, 3:39 AM
</span></td><td class="modifiername"><span class="qbank-creator-name">
    Admin User
</span>
<br>
<span class="date">
    17 September 2021, 3:39 AM
</span></td></tr></tbody></table>';
        // TODO build query with join types: filterset::JOINTYPE_DEFAULT, filterset::JOINTYPE_NONE, filterset::JOINTYPE_ALL

        // Todo count $numberofrecords.
        $totalquestions = 100;

        return [
            'html' => $tablehtml,
            'totalquestions' => $totalquestions,
            'warnings' => []
        ];
    }

    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function get_questions_returns(): external_single_structure {
        return new external_single_structure([
            'html' => new external_value(PARAM_RAW, 'The raw html of the requested table.'),
            'totalquestions' => new external_value(PARAM_INT, 'Total number of questions'),
            'warnings' => new external_warnings()
        ]);
    }
}
