<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Player\Block;

use ilPropertyFormGUI;
use ilub\plugin\SelfEvaluation\Block\Block;
use ilObjSelfEvaluationGUI;
use ilub\plugin\SelfEvaluation\UIHelper\FormSectionHeaderGUIFixed;
use ilub\plugin\SelfEvaluation\Player\PlayerFormContainer;
use ilDBInterface;
use ilSelfEvaluationPlugin;
use ilub\plugin\SelfEvaluation\Block\BlockType;

abstract class BlockPlayerGUI
{
    /**
     * @var Block
     */
    protected BlockType $block;

    protected \ilObjSelfEvaluationGUI $parent;

    protected \ilDBInterface $db;

    protected \ilSelfEvaluationPlugin $plugin;

    public function __construct(ilDBInterface $db, ilSelfEvaluationPlugin $plugin, ilObjSelfEvaluationGUI $parent, BlockType $block)
    {
        $this->db = $db;
        $this->block = $block;
        $this->parent = $parent;
        $this->plugin = $plugin;
    }

    public function getBlockForm(PlayerFormContainer $parent_form): PlayerFormContainer
    {
        $form = $parent_form ?: new ilPropertyFormGUI();

        $h = new FormSectionHeaderGUIFixed();

        if ($this->parent->object->isShowBlockTitlesDuringEvaluation()) {
            $h->setTitle($this->block->getTitle());
        } else {
            $h->setTitle(''); // set an empty title to keep the optical separation of blocks
        }
        if ($this->parent->object->isShowBlockDescriptionsDuringEvaluation()) {
            $h->setInfo($this->block->getDescription());
        }
        $form->addItem($h);

        return $form;
    }
}
