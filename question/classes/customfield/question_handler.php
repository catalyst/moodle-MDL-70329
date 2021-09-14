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
 * question handler for custom fields
 *
 * @package   core_question
 * @copyright 2021 mattp@catalyst-au.net <mattp@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_question\customfield;

defined('MOODLE_INTERNAL') || die;

use core_customfield\api;
use core_customfield\field_controller;

/**
 * Question handler for custom fields.
 *
 * @package core_question
 * @copyright 2021 mattp@catalyst-au.net <mattp@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_handler extends \core_customfield\handler {

    /**
     * @var question_handler
     */
    static protected $singleton;

    /**
     * @var \context
     */
    protected $parentcontext;

    /** @var int Field is displayed in the course listing, visible to everybody */
    const VISIBLETOALL = 2;
    /** @var int Field is displayed in the course listing but only for teachers */
    const VISIBLETOTEACHERS = 1;
    /** @var int Field is not displayed in the course listing */
    const NOTVISIBLE = 0;

    /**
     * Returns a singleton
     *
     * @param int $itemid
     * @return \core_question\customfield\question_handler
     */
    public static function create(int $itemid = 0) : \core_customfield\handler {
        if (static::$singleton === null) {
            self::$singleton = new static(0);
        }
        return self::$singleton;
    }

    /**
     * Run reset code after unit tests to reset the singleton usage.
     */
    public static function reset_caches(): void {
        if (!PHPUNIT_TEST) {
            throw new \coding_exception('This feature is only intended for use in unit tests');
        }

        static::$singleton = null;
    }

    /**
     * The current user can configure custom fields on this component.
     *
     * @return bool true if the current can configure custom fields, false otherwise
     */
    public function can_configure() : bool {
        return has_capability('moodle/question:configurecustomfields', $this->get_configuration_context());
    }

    /**
     * The current user can edit custom fields for the given question.
     *
     * @param field_controller $field
     * @param int $instanceid id of the question to test edit permission
     * @return bool true if the current can edit custom fields, false otherwise
     */
    public function can_edit(field_controller $field, int $instanceid = 0) : bool {
        if ($instanceid) {
            $context = $this->get_instance_context($instanceid);
        } else {
            $context = $this->get_parent_context();
        }

        $returnval = (!$field->get_configdata_property('locked') ||
                has_capability('moodle/question:changelockedcustomfields', $context));

        return $returnval;
    }

    /**
     * The current user can view custom fields on the given course.
     *
     * @param field_controller $field
     * @param int $instanceid id of the question to test edit permission
     * @return bool true if the current can edit custom fields, false otherwise
     */
    public function can_view(field_controller $field, int $instanceid) : bool {
        $visibility = $field->get_configdata_property('visibility');
        if ($visibility == self::NOTVISIBLE) {
            return false;
        } else if ($visibility == self::VISIBLETOTEACHERS) {
            return has_capability('moodle/question:viewhiddencustomfields', $this->get_instance_context($instanceid));
        } else {
            return true;
        }
    }

    /**
     * Sets parent context for the course
     *
     * This may be needed when question is being created, there is no question context but we need to check capabilities
     *
     * @param \context $context
     */
    public function set_parent_context(\context $context) {
        $this->parentcontext = $context;
    }

    /**
     * Returns the parent context for the course
     *
     * @return \context
     */
    protected function get_parent_context() : \context {
        global $PAGE;
        if ($this->parentcontext) {
            return $this->parentcontext;
        } else {
            return \context_system::instance();
        }
    }

    /**
     * Context that should be used for new categories created by this handler
     *
     * @return \context the context for configuration
     */
    public function get_configuration_context() : \context {
        return \context_system::instance();
    }

    /**
     * URL for configuration of the fields on this handler.
     *
     * @return \moodle_url The URL to configure custom fields for this component
     */
    public function get_configuration_url() : \moodle_url {
        return new \moodle_url('/question/customfield.php');
    }

    /**
     * Returns the context for the data associated with the given instanceid.
     *
     * @param int $instanceid id of the record to get the context for
     * @return \context the context for the given record
     */
    public function get_instance_context(int $instanceid = 0) : \context {

        if ($instanceid > 0) {
            $questiondata = question_preload_questions([$instanceid]);
            $contextid = $questiondata[$instanceid]->contextid;
            $context = \context::instance_by_id($contextid);
            return $context;

        } else {
            return \context_system::instance();
        }
    }

    /**
     * Allows adding custom controls to the field configuration form that will be saved.
     *
     * @param \MoodleQuickForm $mform
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        $mform->addElement('header', 'question_handler_header', get_string('customfieldsettings', 'core_question'));
        $mform->setExpanded('question_handler_header', true);

        // If field is locked.
        $mform->addElement('selectyesno', 'configdata[locked]', get_string('customfield_islocked', 'core_question'));
        $mform->addHelpButton('configdata[locked]', 'customfield_islocked', 'core_question');

        // Field data visibility.
        $visibilityoptions = [
                self::VISIBLETOALL => get_string('customfield_visibletoall', 'core_question'),
                self::VISIBLETOTEACHERS => get_string('customfield_visibletoteachers', 'core_question'),
                self::NOTVISIBLE => get_string('customfield_notvisible', 'core_question')
        ];
        $mform->addElement('select', 'configdata[visibility]', get_string('customfield_visibility', 'core_question'),
                $visibilityoptions);
        $mform->addHelpButton('configdata[visibility]', 'customfield_visibility', 'core_question');
    }

    /**
     * Creates or updates custom field data when restoring from a backup.
     *
     * @param \restore_task $task
     * @param array $data
     */
    public function restore_instance_data_from_backup(\restore_task $task, array $data) {

        $editablefields = $this->get_editable_fields($data['newquestion']);
        $records = api::get_instance_fields_data($editablefields, $data['newquestion']);
        $target = $task->get_target();
        $override = ($target != \backup::TARGET_CURRENT_ADDING && $target != \backup::TARGET_EXISTING_ADDING);

        foreach ($records as $d) {
            $field = $d->get_field();
            if ($field->get('shortname') === $data['shortname'] && $field->get('type') === $data['type']) {
                if (!$d->get('id') || $override) {
                    $d->set($d->datafield(), $data['value']);
                    $d->set('value', $data['value']);
                    $d->set('valueformat', $data['valueformat']);
                    $d->set('contextid', $data['fieldcontextid']);
                    $d->save();
                }
                return;
            }
        }
    }
}
