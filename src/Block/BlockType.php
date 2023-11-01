<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Block;

interface BlockType
{
    public function setId(int $id);

    public function getId(): int;

    public function setDescription(string $description);

    public function getDescription(): string;

    public function setParentId(int $parent_id);

    public function getParentId(): int;

    public function setPosition(int $position);

    public function getPosition(): int;

    public function setTitle(string $title);

    public function getTitle(): string;
}
