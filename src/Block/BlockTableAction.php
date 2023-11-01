<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Block;

class BlockTableAction
{
    protected string $title;
    protected string $cmd;
    protected string $link;
    protected int  $position = 0;

    public function __construct(string $title, string $cmd, string $link, int $position = 0)
    {
        $this->title = $title;
        $this->cmd = $cmd;
        $this->link = $link;
        $this->setPosition($position);
    }

    public function getCmd(): string
    {
        return $this->cmd;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setPosition(int  $position)
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

}
