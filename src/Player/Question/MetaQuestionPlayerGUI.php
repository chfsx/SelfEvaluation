<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Player\Question;

use ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion;
use ilub\plugin\SelfEvaluation\Player\PlayerFormContainer;
use ilSelfEvaluationPlugin;
use ilub\plugin\SelfEvaluation\Question\Meta\Type\MetaTypeFactory;

class MetaQuestionPlayerGUI
{
    protected MetaQuestion $question;
    protected \ilSelfEvaluationPlugin $plugin;

    public function __construct(ilSelfEvaluationPlugin $plugin, MetaQuestion $question)
    {
        $this->question = $question;
        $this->plugin = $plugin;
    }

    public function addItemsToForm(PlayerFormContainer $form): PlayerFormContainer
    {
        $type = (new MetaTypeFactory())->getTypeByTypeId($this->question->getTypeId());

        $inputs = $type->getPresentationInputGUI(
            $this->plugin,
            $this->question->getName(),
            MetaQuestion::POSTVAR_PREFIX . $this->question->getId(),
            $this->question->getValues()
        );

        if (!is_array($inputs)) {
            $inputs = [$inputs];
        }

        foreach ($inputs as $input) {
            if ($this->question->isRequired()) {
                $input->setRequired(true);
            }
            $form->addItem($input);
        }

        return $form;
    }
}
