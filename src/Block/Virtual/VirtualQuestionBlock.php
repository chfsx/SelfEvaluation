<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Block\Virtual;

use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlockInterface;
use ilub\plugin\SelfEvaluation\Question\Matrix\Question;
use ilub\plugin\SelfEvaluation\Block\BlockType;

class VirtualQuestionBlock implements QuestionBlockInterface, BlockType
{
    /**
     * @var int
     */
    public $id = 0;
    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var string
     */
    protected $description = '';
    /**
     * @var int
     */
    protected $position = 99;
    /**
     * @var int
     */
    protected $parent_id = 0;
    /**
     * @var string
     */
    protected $abbreviation = '';
    /**
     * @var Question[]
     */
    protected $questions = [];

    public function __construct(int $parent_id = 0)
    {
        $this->setParentId($parent_id);
    }

    public function setAbbreviation(string $abbreviation)
    {
        $this->abbreviation = $abbreviation;
    }

    public function getAbbreviation(): string
    {
        return $this->abbreviation;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setParentId(int $parent_id)
    {
        $this->parent_id = $parent_id;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param Question $question
     */
    public function addQuestion(Question $question)
    {
        $this->questions[$question->getId()] = $question;
    }

    /**
     * @return Question[]
     */
    public function getQuestions()
    {
        return $this->questions;
    }

}
