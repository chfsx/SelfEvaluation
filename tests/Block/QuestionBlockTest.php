<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ilub\plugin\SelfEvaluation\Block\Block;
use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock;

class QuestionBlockTest extends TestCase
{
    protected QuestionBlock $block;
    protected ilDBInterface $db;

    public function setUp(): void
    {
        $this->db = Mockery::mock("\ilDBInterface");
        $this->block = new QuestionBlock($this->db);
    }

    public function testConstruct(): void
    {
        self::assertInstanceOf(Block::class, $this->block);
        self::assertInstanceOf(QuestionBlock::class, $this->block);
    }

    public function testIdAfterConstruct(): void
    {
        self::assertEquals(0, $this->block->getId());
    }

    public function testSetId(): void
    {
        $this->block->setId(1);
        self::assertEquals(1, $this->block->getId());
    }

    public function testGetArrayForDBOnEmpty(): void
    {
        self::assertEquals(
            [
                'id' => ['integer', 0],
                'abbreviation' => ['text', ""],
                'title' => ['text', ""],
                'description' => ['text', ""],
                'position' => ['integer', 99],
                'parent_id' => ['integer', 0]
            ],
            $this->block->getArrayForDb()
        );
    }
}
