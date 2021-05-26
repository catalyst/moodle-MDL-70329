<?php

namespace core_question\local\bank;

abstract class column_plugin_base {

    protected $plugincomponent;

    protected $qbank;

    public function __construct($qbank) {
        $this->qbank = $qbank;
    }

    abstract public function get_question_columns();

}
