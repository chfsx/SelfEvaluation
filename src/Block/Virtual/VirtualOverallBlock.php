<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Block\Virtual;

use ilSelfEvaluationPlugin;

class VirtualOverallBlock extends VirtualQuestionBlock
{
    public function __construct(int $parent_obj_id, ilSelfEvaluationPlugin $plugin)
    {
        parent::__construct($parent_obj_id);
        $this->setId($parent_obj_id);
        $this->setTitle($plugin->txt("overall_feedback_block"));
        $this->setDescription($plugin->txt("overall_feedback_block_description"));

    }
}
