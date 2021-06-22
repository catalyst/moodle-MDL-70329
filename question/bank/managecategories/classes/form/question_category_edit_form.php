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

namespace qbank_managecategories\form;

use context;
use context_module;
use context_course;
use qbank_managecategories\helper;
use moodle_url;
use question_edit_contexts;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/questionlib.php');

/**
 * Defines the form for editing question categories.
 *
 * Form for editing questions categories (name, description, etc.)
 *
 * @package    qbank_managecategories
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_category_edit_form extends \core_form\dynamic_form {

    /**
     * Build the form definition.
     *
     * This adds all the form fields that the manage categories feature needs.
     * @throws \coding_exception
     */
    protected function definition() {
        $mform = $this->_form;

        $contexts = $this->_customdata['contexts'] ?? null;
        $currentcat = $this->_customdata['currentcat'] ?? 0;
        $categoryid = isset($this->_ajaxformdata['categoryid']) ? (int)$this->_ajaxformdata['categoryid'] : 0;
        // Contexts when ajaxformdata is being used.
        if (!$contexts) {
            $cmid = isset($this->_ajaxformdata['cmid']) ? (int)$this->_ajaxformdata['cmid'] : 0;
            $courseid = isset($this->_ajaxformdata['courseid']) ? (int)$this->_ajaxformdata['courseid'] : 0;
            if ($cmid !== 0) {
                $thiscontext = context_module::instance($cmid);
            }

            if ($courseid !== 0) {
                $thiscontext = context_course::instance($courseid);
            }

            if ($courseid === 0 && $cmid === 0) {
                $parentcontext = (int)explode(',', $this->_ajaxformdata['parent'])[1];
                $contextid = $parentcontext === 0 ? $this->_ajaxformdata['contextid'] : (int)$parentcontext;
                $thiscontext = context::instance_by_id($contextid);
            }

            if ($thiscontext) {
                $contexts = new question_edit_contexts($thiscontext);
                $contexts = $contexts->all();
            }
        }
        $categoryheadercaption = get_string('editcategory', 'question');
        if ($categoryid === 0) {
            $categoryheadercaption = get_string('addcategory', 'question');
        }

        $mform->addElement('header', 'categoryheader', $categoryheadercaption);
        $mform->addElement('questioncategory', 'parent', get_string('parentcategory', 'question'),
                ['contexts' => $contexts, 'top' => true, 'currentcat' => $currentcat, 'nochildrenof' => $currentcat]);
        $mform->setType('parent', PARAM_SEQUENCE);
        if (helper::question_is_only_child_of_top_category_in_context($currentcat)) {
            $mform->hardFreeze('parent');
        }
        $mform->addHelpButton('parent', 'parentcategory', 'question');

        $mform->addElement('text', 'name', get_string('name'), 'maxlength="254" size="50"');
        $mform->setDefault('name', '');
        $mform->addRule('name', get_string('categorynamecantbeblank', 'question'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('editor', 'info', get_string('categoryinfo', 'question'));
        $mform->setDefault('info', '');
        $mform->setType('info', PARAM_RAW);

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'question'), 'maxlength="100"  size="10"');
        $mform->addHelpButton('idnumber', 'idnumber', 'question');
        $mform->setType('idnumber', PARAM_RAW);

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array the errors that were found
     * @throws \dml_exception|\coding_exception
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        $currentrec = $DB->get_record('question_categories', ['id' => $data['id']]);
        // Add field validation check for duplicate idnumber.
        list($parentid, $contextid) = explode(',', $data['parent']);
        if ($currentrec) {
            $currentparent = $currentrec->parent . ',' . $currentrec->contextid;
            $lastcategoryinthiscontext = helper::question_is_only_child_of_top_category_in_context($data['id']);
            if ($lastcategoryinthiscontext && $currentparent !== $data['parent']) {
                if ($parentid !== $this->_ajaxformdata['id']) {
                    $errors['parent'] = get_string('lastcategoryinthiscontext', 'qbank_managecategories');
                }
            }
            // Cannot move category in same category.
            if ($currentrec->id === $parentid && $currentrec->contextid === $contextid) {
                $errors['parent'] = get_string('categoryincategory', 'qbank_managecategories');
            }
        }
        if (((string) $data['idnumber'] !== '') && !empty($contextid)) {
            $conditions = 'contextid = ? AND idnumber = ?';
            $params = [$contextid, $data['idnumber']];
            if (!empty($data['id'])) {
                $conditions .= ' AND id <> ?';
                $params[] = $data['id'];
            }
            if ($DB->record_exists_select('question_categories', $conditions, $params)) {
                $errors['idnumber'] = get_string('idnumbertaken', 'error');
            }
        }

        return $errors;
    }

    protected function get_context_for_dynamic_submission(): context {
        $contextid = $this->optional_param('contextid', 0, PARAM_INT);
        if ($contextid === 0) {
            $contextid = (int)explode(',', $this->_ajaxformdata['parent'])[1];
        }
        return context::instance_by_id($contextid);
    }

    protected function check_access_for_dynamic_submission(): void {
        require_capability('moodle/category:manage', $this->get_context_for_dynamic_submission());
    }

    public function process_dynamic_submission() {
        global $DB;

        $values = $this->get_data();

        $parentid = (int)explode(',', $values->parent)[0];
        $contextid = (int)explode(',', $values->parent)[1];
        $newcategory = $values->name;
        $newinfo = format_text($values->info['text'], (int)$values->info['format'], ['noclean' => false]);
        $idnumber = $values->idnumber;

        if ((string) $idnumber === '') {
            $idnumber = null;
        }

        $cat = (object) [
            'parent' => $parentid,
            'contextid' => $contextid,
            'name' => $newcategory,
            'info' => $newinfo,
            'infoformat' => (int)$values->info['format'],
            'stamp' => make_unique_id_code(),
            'idnumber' => $idnumber
        ];

        if ($values->id !== 0) {
            $cat->id = $values->id;
            if ($idnumber) {
                $exists = helper::idnumber_exists($idnumber, $contextid);
                if ($exists && $exists !== $values->id) {
                    throw new moodle_exception('idnumberexists', 'qbank_managecategories');
                }
            }
            $DB->update_record('question_categories', $cat);
        } else {
            $cat->sortorder = 999;
            if ($idnumber) {
                $exists = helper::idnumber_exists($idnumber, $contextid);
                if ($exists) {
                    throw new moodle_exception('idnumberexists', 'qbank_managecategories');
                }
            }
            $categoryid = $DB->insert_record('question_categories', $cat);
        }
    }

    public function set_data_for_dynamic_submission(): void {
        $categoryid = isset($this->_ajaxformdata['categoryid']) ? (int)$this->_ajaxformdata['categoryid'] : 0;
        if ($categoryid !== 0) {
            global $DB;
            $cattoset = $DB->get_record('question_categories', ['id' => $categoryid]);
            $this->set_data((object) [
                'id' => (int)$cattoset->id,
                'name' => $cattoset->name,
                'contextid' => (int)$cattoset->contextid,
                'info' => [
                    'format' => FORMAT_HTML,
                    'text' => $cattoset->info
                ],
                'infoformat' => (int)$cattoset->infoformat,
                'parent' => (int)$cattoset->parent,
                'idnumber' => $cattoset->idnumber
            ]);
        }

    }

    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $params = [];
        $cmid = isset($this->_ajaxformdata['cmid']) ? (int)$this->_ajaxformdata['cmid'] : 0;
        $courseid = isset($this->_ajaxformdata['courseid']) ? (int)$this->_ajaxformdata['courseid'] : 0;
        if ($cmid !== 0) {
            $params['cmid'] = $cmid;
        }

        if ($courseid !== 0) {
            $params['courseid'] = $courseid;
        }
        return new moodle_url('/question/bank/managecategories/category.php', $params);
    }
}
