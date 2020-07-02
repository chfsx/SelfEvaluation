<?php
namespace ilub\plugin\SelfEvaluation\Player\Question;

use ilub\plugin\SelfEvaluation\Question\Matrix\Question;
use ilFormPropertyGUI;
use ilub\plugin\SelfEvaluation\UIHelper\Scale\Scale;
use ilSelfEvaluationPlugin;
use ilub\plugin\SelfEvaluation\UIHelper\MatrixFieldInputGUI;

class QuestionPlayerGUI
{
    /**
     * @var Question
     */
    protected $question;
    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;

    function __construct(ilSelfEvaluationPlugin $plugin, Question $question)
    {
        $this->question = $question;
        $this->plugin = $plugin;
    }

    public function getQuestionFormItem(Scale $scale) : ilFormPropertyGUI
    {
        $te = new MatrixFieldInputGUI($this->plugin,$this->question->getQuestionBody(),
            Question::POSTVAR_PREFIX . $this->question->getId());

        $te->setScale($scale->getUnitsAsArray($this->question->getIsInverse()));
        $te->setRequired(true);
        return $te;
    }
}

