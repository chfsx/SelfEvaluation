<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Block;

use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock;
use ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock;
use ilDBInterface;

class BlockFactory
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(ilDBInterface $db, $self_eval_id)
    {
        $this->db = $db;
        $this->id = $self_eval_id;
    }

    /**
     * @return Block[]
     */
    public function getAllBlocks()
    {
        $blocks = QuestionBlock::_getAllInstancesByParentId($this->db, $this->id);

        $blocks = array_merge($blocks, MetaBlock::_getAllInstancesByParentId($this->db, $this->id));

        $this->sortByPosition($blocks);

        return $blocks;
    }

    public static function _getNextPositionAcrossBlocks(ilDBInterface $db, int $self_eval_id): int
    {
        $block = new QuestionBlock($db);
        $pos = $block->getNextPosition($self_eval_id);
        $block = new MetaBlock($db);
        $pos = max($block->getNextPosition($self_eval_id), $pos);

        return $pos;
    }

    protected function positionSort(Block $a, Block $b): int
    {
        if ($a->getPosition() == $b->getPosition()) {

            return 0; // a and b are equal

        } else {
            if ($a->getPosition() > $b->getPosition()) {

                return 1; // a is after b
            } else {

                return -1; // a is before b
            }
        }
    }

    /**
     * @param Block[] $blocks
     * @return bool
     */
    public function sortByPosition(&$blocks): bool
    {
        return usort($blocks, [get_class(), "positionSort"]);
    }
}
