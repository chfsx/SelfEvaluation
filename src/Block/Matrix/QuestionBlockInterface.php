<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Block\Matrix;

interface QuestionBlockInterface
{
    public function getId(): int;

    public function getDescription(): string;

    public function getParentId(): int;

    public function getPosition(): int;

    public function getTitle(): string;

    public function getAbbreviation(): string;

    public function getQuestions();
}
