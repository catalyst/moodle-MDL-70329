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
 * Renderers for outputting parts of the question bank.
 *
 * @package    core_question
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * This renderer outputs parts of the question bank.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_question_bank_renderer extends plugin_renderer_base {

    /**
     * Display additional navigation if needed.
     *
     * @param string $active
     * @return string
     */
    public function extra_horizontal_navigation($active = null) {
        // Horizontal navigation for question bank.
        if ($questionnode = $this->page->settingsnav->find("questionbank", \navigation_node::TYPE_CONTAINER)) {
            if ($children = $questionnode->children) {
                $tabs = [];
                foreach ($children as $key => $node) {
                    $tabs[] = new \tabobject($node->key, $node->action, $node->text);
                }
                if (empty($active) && $questionnode->find_active_node()) {
                    $active = $questionnode->find_active_node()->key;
                }
                return \html_writer::div(print_tabs([$tabs], $active, null, null, true),
                        'questionbank-navigation');
            }
        }
        return '';
    }

    /**
     * Output the icon for a question type.
     *
     * @param string $qtype the question type.
     * @return string HTML fragment.
     */
    public function qtype_icon($qtype) {
        $qtype = question_bank::get_qtype($qtype, false);
        $namestr = $qtype->local_name();

        return $this->image_icon('icon', $namestr, $qtype->plugin_name(), array('title' => $namestr));
    }

    /**
     * Render the column headers.
     *
     * @param array $qbankheaderdata
     * @return bool|string
     */
    public function render_column_header($qbankheaderdata) {
        return $this->render_from_template('core_question/column_header', $qbankheaderdata);
    }

    /**
     * Render the column sort elements.
     *
     * @param array $sortdata
     * @return bool|string
     */
    public function render_column_sort($sortdata) {
        return $this->render_from_template('core_question/column_sort', $sortdata);
    }

    /**
     * Render a qbank_chooser.
     *
     * @param renderable $qbankchooser The chooser.
     * @return string
     * @deprecated since Moodle 4.0
     * @see \qbank_editquestion\output\renderer
     * @todo Final deprecation on Moodle 4.4 MDL-72438
     */
    public function render_qbank_chooser(renderable $qbankchooser) {
        debugging('Function render_qbank_chooser is deprecated,
         please use qbank_editquestion renderer instead.', DEBUG_DEVELOPER);
        return $this->render_from_template('core_question/qbank_chooser', $qbankchooser->export_for_template($this));
    }

    /**
     * Render category condition.
     *
     * @param array $displaydata
     * @return bool|string
     * @todo Final deprecation on Moodle 4.4 MDL-72438
     */
    public function render_category_condition($displaydata) {
        debugging('Function render_category_condition()
         has been deprecated and moved to qbank_managecategories plugin,
         Please use qbank_managecategories\output\renderer::render_category_condition() instead.',
            DEBUG_DEVELOPER);
        return $this->render_from_template('core_question/category_condition', $displaydata);
    }

    /**
     * Render category condition advanced.
     *
     * @param array $displaydata
     * @return bool|string
     * @todo Final deprecation on Moodle 4.4 MDL-72438
     */
    public function render_category_condition_advanced($displaydata) {
        debugging('Function render_category_condition_advanced()
         has been deprecated and moved to qbank_managecategories plugin,
         Please use qbank_managecategories\output\renderer::render_category_condition_advanced() instead.',
            DEBUG_DEVELOPER);
        return $this->render_from_template('core_question/category_condition_advanced', $displaydata);
    }

    /**
     * Render hidden condition advanced.
     *
     * @param array $displaydata
     * @return bool|string
     * @deprecated since Moodle 4.0 MDL-72321
     * @see qbank_deletequestion\output\renderer
     * @todo Final deprecation on Moodle 4.4 MDL-72438
     */
    public function render_hidden_condition_advanced($displaydata) {
        debugging('Function render_hidden_condition_advanced()
         has been deprecated and moved to qbank_deletequestion plugin,
         Please use qbank_deletequestion\output\renderer::render_hidden_condition_advanced() instead.',
            DEBUG_DEVELOPER);
        return $this->render_from_template('core_question/hidden_condition_advanced', $displaydata);
    }

    /**
     * Render question pagination.
     *
     * @param array $displaydata
     * @return bool|string
     */
    public function render_question_pagination($displaydata) {
        return $this->render_from_template('core_question/question_pagination', $displaydata);
    }

    /**
     * Render question showtext checkbox.
     *
     * @param array $displaydata
     * @return bool|string
     */
    public function render_showtext_checkbox($displaydata) {
        return $this->render_from_template('core_question/showtext_checkbox', $displaydata);
    }

    /**
     * Render bulk actions ui.
     *
     * @param array $displaydata
     * @return bool|string
     */
    public function render_bulk_actions_ui($displaydata) {
        return $this->render_from_template('core_question/bulk_actions_ui', $displaydata);
    }

    /**
     * Build the HTML for the question chooser javascript popup.
     *
     * @param array $real A set of real question types
     * @param array $fake A set of fake question types
     * @param object $course The course that will be displayed
     * @param array $hiddenparams Any hidden parameters to add to the form
     * @return string The composed HTML for the questionbank chooser
     * @todo Final deprecation on Moodle 4.4 MDL-72438
     */
    public function qbank_chooser($real, $fake, $course, $hiddenparams) {
        debugging('Method core_question_bank_renderer::qbank_chooser() is deprecated, ' .
                'see core_question_bank_renderer::render_qbank_chooser().', DEBUG_DEVELOPER);
        return '';
    }

    /**
     * Get the rendered HTML for the action area in Question bank.
     *
     * @param \core_question\output\qbank_actionbar $qbankactionbar qbankactionbar object.
     * @return string rendered HTML string from template.
     */
    public function qbank_action_menu(\core_question\output\qbank_actionbar $qbankactionbar): string {
        return $this->render_from_template('core_question/qbank_action_menu',
                $qbankactionbar->export_for_template($this));
    }

    /**
     * Build the HTML for a specified set of question types.
     *
     * @param array $types A set of question types as used by the qbank_chooser_module function
     * @return string The composed HTML for the module
     * @todo Final deprecation on Moodle 4.4 MDL-72438
     */
    protected function qbank_chooser_types($types) {
        debugging('Method core_question_bank_renderer::qbank_chooser_types() is deprecated, ' .
                'see core_question_bank_renderer::render_qbank_chooser().', DEBUG_DEVELOPER);
        return '';
    }

    /**
     * Return the HTML for the specified question type, adding any required classes.
     *
     * @param object $qtype An object containing the title, and link. An icon, and help text may optionally be specified.
     * If the module contains subtypes in the types option, then these will also be displayed.
     * @param array $classes Additional classes to add to the encompassing div element
     * @return string The composed HTML for the question type
     * @todo Final deprecation on Moodle 4.4 MDL-72438
     */
    protected function qbank_chooser_qtype($qtype, $classes = array()) {
        debugging('Method core_question_bank_renderer::qbank_chooser_qtype() is deprecated, ' .
                'see core_question_bank_renderer::render_qbank_chooser().', DEBUG_DEVELOPER);
        return '';
    }

    /**
     * Return the title for the question bank chooser.
     *
     * @param string $title The language string identifier
     * @param string $identifier The component identifier
     * @return string The composed HTML for the title
     * @todo Final deprecation on Moodle 4.4 MDL-72438
     */
    protected function qbank_chooser_title($title, $identifier = null) {
        debugging('Method core_question_bank_renderer::qbank_chooser_title() is deprecated, ' .
                'see core_question_bank_renderer::render_qbank_chooser().', DEBUG_DEVELOPER);
        return '';
    }

    /**
     * Render the data required for the questionbank filter on the questionbank edit page.
     *
     * @param \context $context The context of the course being displayed
     * @param array $searchconditions The context of the course being displayed
     * @param array $additionalparams Additional filter parameters
     * @param string $component the component for the fragment
     * @param string $callback the callback for the fragment
     * @param array $exraparams extra pamams for the extended apis
     * @return string
     */
    public function render_questionbank_filter(\context $context, array $searchconditions, array $additionalparams,
                                                $component, $callback, $exraparams): string {
        global $PAGE;
        $filter = new \core_question\local\bank\qbank_filter($context, 'qbank-table');
        $filter->set_searchconditions($searchconditions, $additionalparams);
        $templatecontext = $filter->export_for_template($this->output);
        $renderedtemplate = $this->render_from_template('core_question/qbank_filter', $templatecontext);

        // Building params for filter js.
        $mustache = $this->get_mustache();
        $uniqidhelper = $mustache->getHelper('uniqid');
        $params = [
            'filterRegionId' => "core-filter-$uniqidhelper",
            'defaultcourseid' => $templatecontext->courseid,
            'defaultcategoryid' => $templatecontext->defaultcategoryid,
            'perpage' => $templatecontext->perpage,
            'contextid' => $context->id,
            'component' => $component,
            'callback' => $callback,
            'extraparams' => json_encode($exraparams),
        ];
        $PAGE->requires->js_call_amd('core_question/filter', 'init', $params);
        return $renderedtemplate;
    }
}
