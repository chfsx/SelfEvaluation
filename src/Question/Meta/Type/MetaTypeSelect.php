<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question\Meta\Type;

use ilTextWizardInputGUI;
use ilSelfEvaluationPlugin;
use ilPropertyFormGUI;
use ilSelectInputGUI;

class MetaTypeSelect extends MetaQuestionType
{
    public const TYPE_ID = 2;
    public const IGNORE_KEY = 'ilsel_dummy';

    public function getId(): int
    {
        return self::TYPE_ID;
    }

    public function getTypeName(): string
    {
        return 'MetaTypeSelect';
    }

    public function getValueDefinitionInputGUI(ilSelfEvaluationPlugin $plugin, MetaTypeOption $option)
    {
        $ty_se_mu = new ilTextWizardInputGUI($plugin->txt('value'), 'value_' . $this->getId());
        $ty_se_mu->setRequired(true);
        $ty_se_mu->setSize(32);
        $ty_se_mu->setMaxLength(128);
        $ty_se_mu->setValues(['']);
        $option->addSubItem($ty_se_mu);

        return $option;
    }

    public function setValues(MetaTypeOption $item, array $values = [])
    {
        foreach ($item->getSubItems() as $sub_item) {
            if ($sub_item instanceof ilTextWizardInputGUI and $sub_item->getPostVar() == 'value_' . $this->getId()) {
                $sub_item->setValue($values);
            }
        }
    }

    public function getValues(ilPropertyFormGUI $form)
    {
        return $form->getInput('value_' . $this->getId());
    }

    public function getPresentationInputGUI(ilSelfEvaluationPlugin $plugin, string $title, string $postvar, array $values)
    {
        $select = new ilSelectInputGUI($title, $postvar);

        $options = [null => $plugin->txt('select_one')];

        foreach ($values as $key => $value) {
            $options[$key] = $value;
        }
        $select->setOptions($options);

        return $select;
    }
}
