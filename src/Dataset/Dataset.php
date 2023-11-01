<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Dataset;

use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
use ilub\plugin\SelfEvaluation\DatabaseHelper\hasDBFields;
use ilub\plugin\SelfEvaluation\Question\Matrix\Question;
use ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion;
use ilub\plugin\SelfEvaluation\Identity\Identity;
use ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock;
use ilDBInterface;
use Exception;
use ilub\plugin\SelfEvaluation\UIHelper\Scale\Scale;
use ilub\plugin\SelfEvaluation\Block\Block;

/**
 * Class Dataset
 *
 * Note, the naming is not ideal and should be changed to "answers" since "1 dataset" directly corresponds to "1 set of answers"
 * from 1 user to questions from 1 self evaluation.
 */
class Dataset implements hasDBFields
{
    use ArrayForDB;

    public const TABLE_NAME = 'rep_robj_xsev_ds';
    public int $id = 0;
    protected int $identifier_id = 0;
    protected int $creation_date = 0;
    protected int $highest_scale = 0;
    protected bool $complete = false;
    protected array $percentage_per_block = [];
    protected ilDBInterface $db;
    /**
     * @var QuestionBlock[]
     */
    protected array $question_blocks;
    /**
     * @var Data[][]
     */
    protected array $questions_data_for_blocks;
    protected Statistics $statistics;

    public function __construct(ilDBInterface $db, int $id = 0)
    {
        $this->id = $id;
        $this->db = $db;
        if ($id != 0) {
            $this->read();
        }

        $this->statistics = new Statistics();
    }

    public function read()
    {
        $set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));
        while ($rec = $this->db->fetchObject($set)) {
            $this->setObjectValuesFromRecord($this, $rec);
        }
    }

    protected function getNonDbFields(): array
    {
        return ['db', 'percentage_per_block', 'question_blocks','questions_data_for_blocks','highest_scale','statistics'];
    }

    final public function initDB()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $this->db->createTable(self::TABLE_NAME, $this->getArrayForDbWithAttributes());
            $this->db->addPrimaryKey(self::TABLE_NAME, ['id']);
            $this->db->createSequence(self::TABLE_NAME);
        }
    }

    public function create()
    {
        if ($this->getId() != 0) {
            $this->update();

            return;
        }
        $this->setId($this->db->nextId(self::TABLE_NAME));
        $this->db->insert(self::TABLE_NAME, $this->getArrayForDb());
    }

    public function delete(): int
    {
        $this->db->manipulate('DELETE FROM ' . Data::TABLE_NAME . ' WHERE dataset_id = '
            . $this->db->quote($this->getId(), 'integer'));

        return $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));
    }

    public function update()
    {
        if ($this->getId() == 0) {
            $this->create();

            return;
        }
        $this->db->update(self::TABLE_NAME, $this->getArrayForDb(), $this->getIdForDb());
    }

    public function updateValuesByPost(array $post)
    {
        $this->updateValuesByArray($this->getDataFromPost($post));
    }

    /**
     * @param $array array (qid => int, value => string, type => string)
     */
    protected function updateValuesByArray(array $array)
    {
        if ($this->getId() == 0) {
            $this->create();
        }

        foreach ($array as $item) {
            $da = Data::_getInstanceForQuestionId($this->db, $this->getId(), $item['qid'], $item['type']);
            if(is_array($item['value'])) {
                $item['value'] = serialize($item['value']);
            }
            $da->setValue($item['value']);
            $da->setCreationDate(time());
            $da->update();
        }
    }

    protected function getDataFromPost(array $post): array
    {
        $data = [];
        foreach ($post as $k => $v) {
            $type = $this->determineQuestionType($k);
            if ($type !== "") {
                $qid = $this->getQuestionId($type, $k);
                if ($qid !== 0) {
                    $data[] = ['qid' => $qid, 'value' => $v, 'type' => $type];
                }
            }

        }
        return $data;
    }

    protected function determineQuestionType(string $postvar_key): string
    {
        if (str_starts_with($postvar_key, Question::POSTVAR_PREFIX)) {
            return Data::QUESTION_TYPE;
        }
        if (str_starts_with($postvar_key, MetaQuestion::POSTVAR_PREFIX)) {
            return Data::META_QUESTION_TYPE;
        }
        return "";

    }

    protected function getQuestionId(string $question_type, string $postvar_key): int
    {
        if ($question_type == Data::QUESTION_TYPE) {
            return (int) str_replace(Question::POSTVAR_PREFIX, '', $postvar_key);
        }
        if ($question_type == Data::META_QUESTION_TYPE) {
            return (int) str_replace(MetaQuestion::POSTVAR_PREFIX, '', $postvar_key);
        }
        return 0;
    }

    public function getPercentagePerBlock(): array
    {
        if (!$this->percentage_per_block) {
            $this->percentage_per_block = [];
            $highest = $this->getHighestValueFromScale();
            foreach ($this->getQuestionBlocks() as $block) {
                $values = [];
                foreach($this->getQuestionsDataPerBlock($block->getId()) as $data) {
                    $values[] = (int)$data->getValue();
                }
                $fraction =  $this->statistics->arraySumFractionOfMaxSumPossible($values, $highest);
                $this->percentage_per_block[$block->getId()] = $this->statistics->valueToPercentage($fraction);
            }
        }

        return $this->percentage_per_block;
    }


    public function getPercentageForBlock(int $block_id): ?float
    {
        return $this->getPercentagePerBlock()[$block_id];
    }

    public function getMinPercentageBlockAndMin(): array
    {
        [$key, $min] = $this->statistics->getMinKeyAndValueFromArray($this->getPercentagePerBlock());
        return [$this->getBlockById($key),$min];
    }

    public function getMaxPercentageBlockAndMax(): array
    {
        [$key, $max] = $this->statistics->getMaxKeyAndValueFromArray($this->getPercentagePerBlock());
        return [$this->getBlockById($key),$max];
    }

    public function getBlockById(int $block_id): QuestionBlock
    {
        return $this->getQuestionBlocks()[$block_id];
    }

    public function getOverallPercentage(): float
    {
        return $this->statistics->getMeanFromData($this->getPercentagePerBlock());
    }

    public function getOverallPercentageVarianz(): float
    {
        return $this->statistics->getVarianzFromValues($this->getPercentagePerBlock());
    }

    public function getOverallPercentageStandardabweichung(): float
    {
        return $this->statistics->getStandardDeviation($this->getPercentagePerBlock());
    }

    public function getPercentageStandardabweichungPerBlock(): array
    {
        $return = [];
        $highest = $this->getHighestValueFromScale();

        foreach ($this->getQuestionBlocks() as $block) {
            $data_as_percentage = [];
            $answer_data = $this->getQuestionsDataPerBlock($block->getId());
            foreach ($answer_data as $data) {
                $data_as_percentage[] = $this->statistics->percentageOf((int)$data->getValue(), $highest);
            }
            $return[$block->getId()] = $this->statistics->getStandardDeviation($data_as_percentage);
        }
        return $return;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setIdentifierId(int $identifier_id)
    {
        $this->identifier_id = $identifier_id;
    }

    public function getIdentifierId(): int
    {
        return $this->identifier_id;
    }

    public function setCreationDate(int $creation_date)
    {
        $this->creation_date = $creation_date;
    }

    public function getCreationDate(): int
    {
        return $this->creation_date;
    }

    /**
     * @param QuestionBlock[] $blocks
     */
    public function setQuestionBlocks(array $blocks)
    {
        $this->question_blocks = $blocks;
    }

    /**
     * @return bool
     */
    public function isComplete(): bool
    {
        return $this->complete;
    }

    /**
     * @param bool $complete
     */
    public function setComplete(bool $complete): void
    {
        $this->complete = $complete;
    }

    /**
     * @return QuestionBlock[]
     */
    public function getQuestionBlocks(): array
    {
        if (!is_array($this->question_blocks)) {
            foreach (QuestionBlock::_getAllInstancesByIdentifierId($this->db, (string) $this->getIdentifierId()) as $block) {
                $this->question_blocks[$block->getId()] = $block;
            }
        }
        return $this->question_blocks;
    }

    public function setQuestionsDataForBlocks(array $questions_data_for_blocks)
    {
        $this->questions_data_for_blocks = $questions_data_for_blocks;
    }

    /**
     * @param int $block_id
     * @return Data[]
     */
    public function getQuestionsDataPerBlock(int $block_id): array
    {
        if(!is_array($this->questions_data_for_blocks) || ! is_array($this->questions_data_for_blocks[$block_id])) {
            foreach (Question::_getAllInstancesForParentId($this->db, $block_id) as $qst) {
                $data = Data::_getInstanceForQuestionId($this->db, $this->getId(), $qst->getId());
                $this->questions_data_for_blocks[$block_id][$qst->getId()] = $data;
            }
        }

        return $this->questions_data_for_blocks[$block_id];
    }



    /**
     * @param int $highest_scale
     */
    public function setHighestScale(int $highest_scale): void
    {
        $this->highest_scale = $highest_scale;
    }

    public function getHighestValueFromScale()
    {
        if (!$this->highest_scale) {
            $obj_id = Identity::_getObjIdForIdentityId($this->db, (string) $this->getIdentifierId());
            $this->highest_scale = Scale::_getHighestScaleByObjId($this->db, $obj_id);
        }
        return $this->highest_scale;
    }

    public function getSubmitDate(): int
    {
        $latest_entry = Data::_getLatestInstanceByDatasetId($this->db, $this->getId());
        if ($latest_entry) {
            return $latest_entry->getCreationDate();
        } else {
            throw new Exception("Invalid Entry");
        }
    }

    public function getDuration(): int
    {
        $latest_entry = Data::_getLatestInstanceByDatasetId($this->db, $this->getId());
        if ($latest_entry) {
            return $latest_entry->getCreationDate() - $this->getCreationDate();
        } else {
            throw new Exception("Invalid Entry");
        }
    }

    /**
     * @param ilDBInterface $db
     * @param int           $identifier_id
     * @return Dataset[]
     */
    public static function _getAllInstancesByIdentifierId(ilDBInterface $db, int $identifier_id)
    {
        $return = [];
        $set = $db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
            . $identifier_id . ' ORDER BY creation_date ASC');
        while ($rec = $db->fetchObject($set)) {
            $data_set = new Dataset($db);
            $data_set->setObjectValuesFromRecord($data_set, $rec);
            $return[] = $data_set;
        }

        return $return;
    }

    /**
     * @param ilDBInterface $db
     * @param int           $obj_id
     * @param bool          $as_array
     * @param string        $identifier
     * @return Dataset[]
     */
    public static function _getAllInstancesByObjectId(
        ilDBInterface $db,
        int $obj_id,
        bool $as_array = false,
        string $identifier = ""
    ) {
        $return = [];
        if ($identifier == "") {
            $identities = Identity::_getAllInstancesByObjId($db, $obj_id);
        } else {
            $identities = Identity::_getAllInstancesForObjIdAndIdentifier($db, $obj_id, $identifier);
        }

        foreach ($identities as $identity) {
            $set = $db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
                . $db->quote($identity->getId(), 'integer') . ' ORDER BY creation_date ASC');
            while ($rec = $db->fetchObject($set)) {
                if ($as_array) {
                    $return[] = (array) $rec;
                } else {
                    $data_set = new Dataset($db);
                    $data_set->setObjectValuesFromRecord($data_set, $rec);
                    $return[] = $data_set;
                }
            }
        }

        return $return;
    }


    public static function _deleteAllInstancesByObjectId(ilDBInterface $db, int $obj_id): bool
    {
        foreach (self::_getAllInstancesByObjectId($db, $obj_id) as $obj) {
            $obj->delete();
        }

        return true;
    }

    /**
     * @param ilDBInterface $db
     * @param int           $identifier_id
     * @return bool|Dataset
     */
    public static function _getInstanceByIdentifierId(ilDBInterface $db, int $identifier_id)
    {
        $set = $db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
            . $db->quote($identifier_id, 'integer'). " ORDER BY id DESC");
        while ($rec = $db->fetchObject($set)) {
            $data_set = new Dataset($db);
            $data_set->setObjectValuesFromRecord($data_set, $rec);
            return $data_set;
        }

        return false;
    }

    /**
     * @param ilDBInterface $db
     * @param               $identifier_id
     * @return Dataset
     */
    public static function _getNewInstanceForIdentifierId(ilDBInterface $db, int $identifier_id)
    {
        $obj = new self($db);
        $obj->setIdentifierId($identifier_id);

        return $obj;
    }

    public static function _datasetExists(ilDBInterface $db, int $identifier_id): bool
    {
        $set = $db->query('SELECT id FROM ' . self::TABLE_NAME . ' ' . ' WHERE identifier_id = '
            . $db->quote($identifier_id, 'integer'));
        while ($rec = $db->fetchObject($set)) {
            return true;
        }

        return false;
    }
}
