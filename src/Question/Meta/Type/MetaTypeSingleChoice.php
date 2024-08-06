<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question\Meta\Type;

use ilRadioGroupInputGUI;
use ilRadioOption;
use ilSelfEvaluationPlugin;

class MetaTypeSingleChoice extends MetaTypeSelect
{
    public const TYPE_ID = 3;

    public function getId(): int
    {
        return self::TYPE_ID;
    }

    public function getTypeName(): string
    {
        return 'MetaTypeSingleChoice';
    }

    public function getPresentationInputGUI(ilSelfEvaluationPlugin $plugin, string $title, string $postvar, array $values): \ilRadioGroupInputGUI
    {
        $select = new ilRadioGroupInputGUI($title, $postvar);

        foreach ($values as $key => $value) {

            $select->addOption(new ilRadioOption($value, (string)$key));
        }

        return $select;
    }
}
