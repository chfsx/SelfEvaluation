<?php


use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock;
use ilub\plugin\SelfEvaluation\Question\Matrix\Question;
use ilub\plugin\SelfEvaluation\Dataset\Data;
use ilub\plugin\SelfEvaluation\Dataset\Dataset;

trait DatasetHelperTrait
{
    /**
     * @return QuestionBlock[]|Question[]|Data[]
     */
    protected function getBasics(): array
    {
        return [$this->getBasicBlock(), $this->getBasicQuestion(), $this->getBasicAnswerWithValue1()];
    }

    protected function getBasicBlock(): QuestionBlock
    {
        $block = new QuestionBlock($this->db);
        $block->setId(1);
        return $block;
    }

    protected function getBasicQuestion(): Question
    {
        $question = new Question($this->db);
        $question->setId(1);
        return $question;
    }

    protected function getBasicAnswerWithValue1(): Data
    {
        $answer = new Data($this->db);
        $answer->setValue(1);
        return $answer;
    }

    protected function getBlock1()
    {
        return $this->getBasicBlock();
    }

    protected function getBlock2()
    {
        ($block2 = $this->getBasicBlock())->setId(3);
        return $block2;
    }

    protected function getBlock3()
    {
        ($block3 = $this->getBasicBlock())->setId(4);
        return $block3;
    }

    protected function setUpDatasetWithThreeBlocks(Dataset $dataset): Dataset
    {
        $dataset->setHighestScale($max = 5);
        $block1 = $this->getBlock1();
        $block2 = $this->getBlock2();
        $block3 = $this->getBlock3();
        $dataset->setQuestionBlocks([$block1->getId() => $block1, $block2->getId() => $block2, $block3->getId() => $block3]);

        $dataset->setQuestionsDataForBlocks(
            [$block1->getId() => $this->getDataForBlock1WithThreeQuestionsAndAnswerValuesOneTwoFive(),
             $block2->getId() => $this->getDataForBlock2WithOneQuestionAndAnswerValuesOne(),
             $block3->getId() => $this->getDataForBlock3WithThreeQuestionsAndAnswerValuesOneTwo()
            ]
        );
        return $dataset;
    }

    protected function getDataForBlock1WithThreeQuestionsAndAnswerValuesOneTwoFive(): array
    {

        $question1 = $this->getBasicQuestion();
        ($question2 = $this->getBasicQuestion())->setId(2);
        ($question3 = $this->getBasicQuestion())->setId(3);

        $answer_data_1 = $this->getBasicAnswerWithValue1();
        ($answer_data_2 = $this->getBasicAnswerWithValue1())->setValue(2);
        ($answer_data_3 = $this->getBasicAnswerWithValue1())->setValue(5);

        return [$question1->getId() => $answer_data_1, $question2->getId() => $answer_data_2, $question3->getId() => $answer_data_3];
    }

    protected function getDataForBlock2WithOneQuestionAndAnswerValuesOne(): array
    {

        $question1 = $this->getBasicQuestion();

        $answer_data_1 = $this->getBasicAnswerWithValue1();


        return [$question1->getId() => $answer_data_1];
    }

    protected function getDataForBlock3WithThreeQuestionsAndAnswerValuesOneTwo(): array
    {

        $question1 = $this->getBasicQuestion();
        ($question2 = $this->getBasicQuestion())->setId(2);

        $answer_data_1 = $this->getBasicAnswerWithValue1();
        ($answer_data_2 = $this->getBasicAnswerWithValue1())->setValue(2);

        return [$question1->getId() => $answer_data_1, $question2->getId() => $answer_data_2];
    }

    protected function getBlock1Percentage(): float
    {
        return (1 + 2 + 5) / (3 * 5) * 100;
    }

    protected function getBlock2Percentage(): float
    {
        return 1 / 5 * 100;
    }

    protected function getBlock3Percentage(): float
    {
        return (1 + 2) / (2 * 5) * 100;
    }

    protected function getBlockThreeBlocksPercentages(): array
    {
        return [$this->getBlock1()->getId() => $this->getBlock1Percentage(),
                $this->getBlock2()->getId() => $this->getBlock2Percentage(),
                $this->getBlock3()->getId() => $this->getBlock3Percentage()];
    }

    protected function getOverallPercentage()
    {
        return ($this->getBlock1Percentage() + $this->getBlock2Percentage() + $this->getBlock3Percentage()) / 3;
    }

    protected function getOverallPercentageVarianz()
    {
        $op = ($this->getBlock1Percentage() + $this->getBlock2Percentage() + $this->getBlock3Percentage()) / 3;
        return ($this->getBlock1Percentage() - $op) ** 2 / 3
            + ($this->getBlock2Percentage() - $op) ** 2 / 3
            + ($this->getBlock3Percentage() - $op) ** 2 / 3;
    }

    protected function getSdPerBlock(): array
    {
        $perc1 = $this->getBlock1Percentage();
        $perc2 = $this->getBlock2Percentage();
        $perc3 = $this->getBlock3Percentage();

        $varianz1 = (1 / 5 * 100 - $perc1) ** 2 / 3 +  (2 / 5 * 100 - $perc1) ** 2 / 3 +  (5 / 5 * 100 - $perc1) ** 2 / 3;
        $varianz2 = (1 / 5 * 100 - $perc2) ** 2 / 1;
        $varianz3 = (1 / 5 * 100 - $perc3) ** 2 / 2 +  (2 / 5 * 100 - $perc3) ** 2 / 2;

        return [$this->getBlock1()->getId() => sqrt($varianz1),$this->getBlock2()->getId() => sqrt($varianz2),$this->getBlock3()->getId() => sqrt($varianz3)];
    }
}
