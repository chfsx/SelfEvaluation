<?php


use PHPUnit\Framework\TestCase;
use ilub\plugin\SelfEvaluation\Block\Block;
use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock;

class QuestionBlockTest extends TestCase
{
    /**
     * @var QuestionBlock
     */
    protected $block;

    /**
     * @var ilDBInterface
     */
    protected $db;

    public function setUp(): void
    {
        $this->db = \Mockery::mock("\ilDBInterface");
        $this->block = new QuestionBlock($this->db);
    }

    public function testConstruct()
    {
        self::assertInstanceOf(Block::class, $this->block);
        self::assertInstanceOf(QuestionBlock::class, $this->block);
    }

    public function testIdAfterConstruct()
    {
        self::assertEquals(0, $this->block->getId());
    }

    public function testSetId()
    {
        $this->block->setId(1);
        self::assertEquals(1, $this->block->getId());
    }

    public function testGetArrayForDBOnEmpty()
    {
        self::assertEquals(
            ['id' => ['integer', 0],
                            'abbreviation' => ['text', ""],
                            'title' => ['text', ""],
                            'description' => ['text', ""],
                            'position' => ['integer', 99],
                            'parent_id' => ['integer', 0]],
            $this->block->getArrayForDb()
        );
    }
}
