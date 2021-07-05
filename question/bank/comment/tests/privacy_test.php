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
 * Privacy api tests for question comments.
 *
 * @package    qbank_comment
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use qbank_comment\privacy\provider;
use core_privacy\local\request\approved_userlist;

/**
 * Privacy api tests class.
 *
 * @package    qbank_comment
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Safat Shahin <safatshahin@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbank_comment_privacy_testcase extends advanced_testcase {

    /** @var stdClass A teacher who is only enrolled in course1. */
    protected $teacher1;

    /** @var stdClass A teacher who is only enrolled in course2. */
    protected $teacher2;

    /** @var stdClass A teacher who is enrolled in both course1 and course2. */
    protected $teacher3;

    /** @var stdClass A test course. */
    protected $course1;

    /** @var stdClass A test course. */
    protected $course2;

    protected function setUp(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        // Create courses.
        $generator = $this->getDataGenerator();
        $this->course1 = $generator->create_course();
        $this->course2 = $generator->create_course();

        // Create and enrol teachers.
        $this->teacher1 = $generator->create_user();
        $this->teacher2 = $generator->create_user();
        $this->teacher3 = $generator->create_user();

        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $generator->enrol_user($this->teacher1->id,  $this->course1->id, $studentrole->id);
        $generator->enrol_user($this->teacher2->id,  $this->course2->id, $studentrole->id);
        $generator->enrol_user($this->teacher3->id,  $this->course1->id, $studentrole->id);
        $generator->enrol_user($this->teacher3->id,  $this->course2->id, $studentrole->id);
    }

    /**
     * Posts a comment on a given context.
     *
     * @param string $text The comment's text.
     * @param context $context The context on which we want to put the comment.
     */
    protected function add_comment($text, context $context) {
        $args = new stdClass;
        $args->context = $context;
        $args->area = 'core_question';
        $args->itemid = 0;
        $args->component = 'qbank_comment';
        $args->linktext = get_string('commentheader', 'qbank_comment');
        $args->notoggle = true;
        $args->autostart = true;
        $args->displaycancel = false;
        $comment = new comment($args);

        $comment->add($text);
    }

    /**
     * Test for provider::get_metadata().
     */
    public function test_get_metadata() {
        $collection = new collection('qbank_comment');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();
        $this->assertCount(1, $itemcollection);

        $link = reset($itemcollection);

        $this->assertEquals('qbank_comment', $link->get_name());
        $this->assertEmpty($link->get_privacy_fields());
        $this->assertEquals('privacy:metadata:qbank_comment', $link->get_summary());
    }
}
