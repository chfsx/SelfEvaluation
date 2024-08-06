<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question\Meta\Type;

use ilSelfEvaluationPlugin;
use ilTextWizardInputGUI;
use ilPropertyFormGUI;
use ilub\plugin\SelfEvaluation\UIHelper\MatrixHeaderGUI;
use ilub\plugin\SelfEvaluation\UIHelper\MatrixFieldInputGUI;

class MetaTypeMatrix extends MetaQuestionType
{
    public const TYPE_ID = 4;

    public function getId(): int
    {
        return self::TYPE_ID;
    }

    public function getTypeName(): string
    {
        return 'MatrixQuestion';
    }

    public function getValueDefinitionInputGUI(ilSelfEvaluationPlugin $plugin, MetaTypeOption $option): MetaTypeOption
    {

        $ty_se_mu = new ilTextWizardInputGUI($plugin->txt("matrix_scale"), 'scale_' . $this->getId());
        $ty_se_mu->setRequired(true);
        $ty_se_mu->setSize(32);
        $ty_se_mu->setMaxLength(128);
        $ty_se_mu->setValues(['']);
        $ty_se_mu->setInfo($plugin->txt("matrix_scale_description"));
        $option->addSubItem($ty_se_mu);

        $ty_se_mu = new ilTextWizardInputGUI($plugin->txt("matrix_question"), 'question_' .
            $this->getId());
        $ty_se_mu->setRequired(true);
        $ty_se_mu->setSize(64);
        $ty_se_mu->setMaxLength(4096);
        $ty_se_mu->setValues(['']);
        $ty_se_mu->setInfo($plugin->txt("matrix_scale_question_description"));
        $option->addSubItem($ty_se_mu);

        return $option;
    }

    public function setValues(MetaTypeOption $item, array $values = []): void
    {
        $scale_values = self::getScaleFromArray($values);
        $question_values = self::getQuestionsFromArray($values);

        foreach ($item->getSubItems() as $sub_item) {
            if ($sub_item instanceof ilTextWizardInputGUI && $sub_item->getPostVar() === 'scale_' . $this->getId()) {
                $sub_item->setValue($scale_values);
            } elseif ($sub_item instanceof ilTextWizardInputGUI && $sub_item->getPostVar() === 'question_' . $this->getId()) {
                $sub_item->setValue($question_values);
            }
        }
    }

    public static function getQuestionsFromArray($data): array
    {
        $questions = [];

        foreach ($data as $key => $value) {
            if (strpos((string)$key, 'question_') !== false) {
                $questions[$key] = $value;
            }
        }
        return $questions;
    }

    public static function getScaleFromArray($data): array
    {
        $scale = [];

        foreach ($data as $key => $value) {
            if (strpos((string)$key, 'scale_') !== false) {
                $scale[$key] = $value;
            }
        }
        return $scale;
    }

    public function getValues(ilPropertyFormGUI $form): array
    {
        /**
         * @var array $scale
         * @var array $questions
         */
        $scale = $form->getInput('scale_' . $this->getId());
        $questions = $form->getInput('question_' . $this->getId());

        foreach ($scale as $key => $item) {
            $correctd_key = "scale_" . $key;
            $scale[$correctd_key] = $item;
        }

        foreach ($questions as $key => $item) {
            $correctd_key = "question_" . $key;
            $questions[$correctd_key] = $item;
        }
        return array_merge($scale, $questions);
    }

    public function getPresentationInputGUI(ilSelfEvaluationPlugin $plugin, string $title, string $postvar, array $values): array
    {
        $scale_values = self::getScaleFromArray($values);
        $question_values = self::getQuestionsFromArray($values);

        $matrix_items = [];

        $header = new MatrixHeaderGUI($plugin, $title);
        $header->setScale($scale_values);
        $matrix_items[] = $header;

        foreach ($question_values as $key => $question_value) {
            $input_item = new MatrixFieldInputGUI(
                $plugin,
                $question_value,
                "" . $postvar . "[" . $key . "]"
            );
            $input_item->setScale($scale_values);
            $matrix_items[] = $input_item;
        }

        return $matrix_items;
    }
}
