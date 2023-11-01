<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Player\Block;

use ilub\plugin\SelfEvaluation\Player\Question\MetaQuestionPlayerGUI;
use ilub\plugin\SelfEvaluation\Player\PlayerFormContainer;
use ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock;

class MetaBlockPlayerGUI extends BlockPlayerGUI
{
    /**
     * @var MetaBlock
     */
    protected $block;

    public function getBlockForm(PlayerFormContainer $parent_form = null): PlayerFormContainer
    {
        $form = parent::getBlockForm($parent_form);

        $questions = $this->block->getQuestions();

        foreach ($questions as $question) {
            $question_gui = new MetaQuestionPlayerGUI($this->plugin, $question);
            $question_gui->addItemsToForm($form);
        }

        return $form;
    }
}
