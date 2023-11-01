<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question\Meta\Type;

use ilPropertyFormGUI;
use ilTextInputGUI;
use ilSelfEvaluationPlugin;

class MetaTypeText extends MetaQuestionType
{
    public const TYPE_ID = 1;

    public function getId(): int
    {
        return self::TYPE_ID;
    }

    public function getTypeName(): string
    {
        return 'MetaTypeText';
    }

    public function getValueDefinitionInputGUI(ilSelfEvaluationPlugin $plugin, MetaTypeOption $option)
    {
        return $option;
    }

    public function setValues(MetaTypeOption $item, $values = [])
    {
    }

    public function getValues(ilPropertyFormGUI $form)
    {
        return [];
    }

    public function getPresentationInputGUI(ilSelfEvaluationPlugin $plugin, string $title, string $postvar, array $values)
    {
        $text = new ilTextInputGUI($title, $postvar);
        $text->setSize(32);
        $text->setMaxLength(255);

        return $text;
    }
}
