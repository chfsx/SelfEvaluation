<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Block\Virtual;

use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlockInterface;
use ilub\plugin\SelfEvaluation\Question\Matrix\Question;
use ilub\plugin\SelfEvaluation\Block\BlockType;

class VirtualQuestionBlock implements QuestionBlockInterface, BlockType
{
    public int $id = 0;
    protected string $title = '';
    protected string $description = '';
    protected int $position = 99;
    protected int $parent_id = 0;
    protected string $abbreviation = '';
    /**
     * @var Question[]
     */
    protected array $questions = [];

    public function __construct(int $parent_id = 0)
    {
        $this->setParentId($parent_id);
    }

    public function setAbbreviation(string $abbreviation): void
    {
        $this->abbreviation = $abbreviation;
    }

    public function getAbbreviation(): string
    {
        return $this->abbreviation;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setParentId(int $parent_id): void
    {
        $this->parent_id = $parent_id;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function addQuestion(Question $question): void
    {
        $this->questions[$question->getId()] = $question;
    }

    /**
     * @return Question[]
     */
    public function getQuestions(): array
    {
        return $this->questions;
    }

}
