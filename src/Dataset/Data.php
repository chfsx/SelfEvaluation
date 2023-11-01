<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Dataset;

use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
use ilDBInterface;
use ilub\plugin\SelfEvaluation\DatabaseHelper\hasDBFields;

/**
 * Class Data
 *
 * Note, the naming is not ideal and should be changed to "answer" sinde "1 data" directly corresponds to "1 answer"
 * from 1 user to 1 question (meta or matrix).
 */
class Data implements hasDBFields
{
    use ArrayForDB;

    public const TABLE_NAME = 'rep_robj_xsev_d';
    public const QUESTION_TYPE = 'qst';
    public const META_QUESTION_TYPE = 'mqst';
    /**
     * @var int
     */
    protected $id = 0;
    /**
     * @var int
     */
    protected $dataset_id = 0;
    /**
     * @var int
     */
    protected $question_id = 0;
    /**
     * @var string
     */
    protected $question_type = '';
    /**
     * @var int
     */
    protected $creation_date = 0;
    /**
     * @var string
     */
    protected $value = '';

    /**
     * @var ilDBInterface
     */
    protected $db;


    public function __construct(ilDBInterface $db, int $id = 0)
    {
        $this->id = $id;
        $this->db = $db;
        if ($id != 0) {
            $this->read();
        }
    }

    public function read()
    {
        $set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));
        while ($rec = $this->db->fetchObject($set)) {
            $this->setObjectValuesFromRecord($this, $rec);
        }
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
        $this->setId($this->db->nextID(self::TABLE_NAME));

        $this->db->insert(self::TABLE_NAME, $this->getArrayForDb());
    }

    public function delete(): int
    {
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



    /**
     * @param ilDBInterface $db
     * @param int           $dataset_id
     * @return Data[]
     */
    public static function _getAllInstancesByDatasetId(ilDBInterface $db, int $dataset_id)
    {
        $return = [];
        $set = $db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE dataset_id = ' .$dataset_id);
        while ($rec = $db->fetchObject($set)) {
            $data = new self($db);
            $data->setObjectValuesFromRecord($data, $rec);
            $return[] = $data;
        }

        return $return;
    }

    /**
     * @param ilDBInterface $db
     * @param $dataset_id
     * @return Data|null
     */
    public static function _getLatestInstanceByDatasetId(ilDBInterface $db, int $dataset_id)
    {
        $set = $db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE dataset_id = '.$dataset_id. ' ORDER BY creation_date DESC LIMIT 1');
        while ($rec = $db->fetchObject($set)) {
            $data = new Data($db);
            return $data->setObjectValuesFromRecord($data, $rec);
        }

        return null;
    }

    public static function _getInstanceForQuestionId(
        ilDBInterface $db,
        int $dataset_id,
        int $question_id,
        string $question_type = Data::QUESTION_TYPE
    ): Data {

        $stmt = $db->prepare('SELECT * FROM ' . self::TABLE_NAME .
            ' WHERE dataset_id = ? AND question_id = ? AND question_type = ?;', ['integer', 'integer', 'text']);
        $db->execute($stmt, [$dataset_id, $question_id, $question_type]);

        while ($rec = $db->fetchObject($stmt)) {
            $data = new self($db);
            $data->setObjectValuesFromRecord($data, $rec);

            return $data;
        }
        $obj = new self($db);
        $obj->setQuestionId($question_id);
        $obj->setDatasetId($dataset_id);
        $obj->setQuestionType($question_type);

        return $obj;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setDatasetId(int $dataset_id)
    {
        $this->dataset_id = $dataset_id;
    }

    public function getDatasetId(): int
    {
        return $this->dataset_id;
    }

    public function setQuestionId(int $question_id)
    {
        $this->question_id = $question_id;
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function setQuestionType(string $question_type)
    {
        $this->question_type = $question_type;
    }

    public function getQuestionType(): string
    {
        return $this->question_type;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        if(is_string($this->value)) {
            $unserialized = unserialize($this->value);
            if ($unserialized !== false) {
                return $unserialized;
            }
        }

        return $this->value;
    }

    public function setCreationDate(int $creation_date)
    {
        $this->creation_date = $creation_date;
    }

    public function getCreationDate(): int
    {
        return $this->creation_date;
    }
}
