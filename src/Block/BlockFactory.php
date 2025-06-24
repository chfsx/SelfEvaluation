<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Block;

use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock;
use ilub\plugin\SelfEvaluation\Block\Meta\MetaBlock;
use ilDBInterface;

class BlockFactory
{
    public function __construct(protected ilDBInterface $db, protected int $id)
    {
    }

    /**
     * @return Block[]
     */
    public function getAllBlocks(): array
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
        return max($block->getNextPosition($self_eval_id), $pos);
    }

    protected function positionSort(Block $a, Block $b): int
    {
        if ($a->getPosition() === $b->getPosition()) {
            return 0;
            // a and b are equal
        } elseif ($a->getPosition() > $b->getPosition()) {
            return 1;
            // a is after b
        } else {
            return -1; // a is before b
        }
    }

    /**
     * @param Block[] $blocks
     */
    public function sortByPosition(array &$blocks): bool
    {
        return usort($blocks, [self::class, "positionSort"]);
    }
}
