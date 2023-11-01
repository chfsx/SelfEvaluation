<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question\Meta\Type;

use ilPropertyFormGUI;
use ilSelfEvaluationPlugin;

abstract class MetaQuestionType
{
    abstract public function getId(): int;

    abstract public function getTypeName(): string;

    /**
     * @param ilSelfEvaluationPlugin $plugin
     * @param MetaTypeOption         $option
     * @return self
     */
    abstract public function getValueDefinitionInputGUI(ilSelfEvaluationPlugin $plugin, MetaTypeOption $option);

    abstract public function setValues(MetaTypeOption $item, array $values = []);

    abstract public function getValues(ilPropertyFormGUI $form);

    abstract public function getPresentationInputGUI(ilSelfEvaluationPlugin $plugin, string $title, string $postvar, array $values);

    public function __toString(): string
    {
        return 'type_id=' . $this->getId();
    }
}
