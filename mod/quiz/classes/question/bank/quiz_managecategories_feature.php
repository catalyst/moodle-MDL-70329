<?php

namespace mod_quiz\question\bank;

use core_question\local\bank\view;
use mod_quiz\question\bank\filter\custom_category_condition;
use qbank_managecategories\subcategories_condition;

class quiz_managecategories_feature extends \qbank_managecategories\plugin_feature {

    public function get_question_filters(view $qbank): array {
        return [
            new custom_category_condition($qbank),
            new subcategories_condition($qbank),
        ];
    }
}
