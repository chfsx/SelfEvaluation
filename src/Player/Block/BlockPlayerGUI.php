<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Player\Block;

use ilub\plugin\SelfEvaluation\Block\Block;
use ilObjSelfEvaluationGUI;
use ilub\plugin\SelfEvaluation\UIHelper\FormSectionHeaderGUIFixed;
use ilub\plugin\SelfEvaluation\Player\PlayerFormContainer;
use ilDBInterface;
use ilSelfEvaluationPlugin;

abstract class BlockPlayerGUI
{
    public function __construct(
        protected ilDBInterface $db,
        protected ilSelfEvaluationPlugin $plugin,
        protected ilObjSelfEvaluationGUI $parent,
        protected Block $block
    ) {
    }

    public function getBlockForm(PlayerFormContainer $parent_form): PlayerFormContainer
    {
        $h = new FormSectionHeaderGUIFixed();

        if ($this->parent->object->isShowBlockTitlesDuringEvaluation()) {
            $h->setTitle($this->block->getTitle());
        } else {
            $h->setTitle(''); // set an empty title to keep the optical separation of blocks
        }
        if ($this->parent->object->isShowBlockDescriptionsDuringEvaluation()) {
            $h->setInfo($this->block->getDescription());
        }
        $parent_form->addItem($h);

        return $parent_form;
    }
}
