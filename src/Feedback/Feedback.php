<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Feedback;

use ilDBInterface;

use SimpleXMLElement;
use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
use ilub\plugin\SelfEvaluation\DatabaseHelper\hasDBFields;

class Feedback implements hasDBFields
{
    use ArrayForDB;

    public const TABLE_NAME = 'rep_robj_xsev_fb';
    /**
     * @var int
     */
    public $id = 0;
    /**
     * @var int
     */
    protected $parent_id = 0;
    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var string
     */
    protected $description = '';
    /**
     * @var int
     */
    protected $start_value = 0;
    /**
     * @var int
     */
    protected $end_value = 100;
    /**
     * @var string
     */
    protected $feedback_text = '';
    /**
     * @var bool
     */
    protected $parent_type_overall = false;
    /**
     * @var \ilDBInterface
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

    public function cloneTo(int $parent_id)
    {
        $clone = new self($this->db);
        $clone->setParentId($parent_id);
        $clone->setTitle($this->getTitle());
        $clone->setDescription($this->getDescription());
        $clone->setStartValue($this->getStartValue());
        $clone->setEndValue($this->getEndValue());
        $clone->setFeedbackText($this->getFeedbackText());
        $clone->setParentTypeOverall($this->isParentTypeOverall());
        $clone->update();
        return $clone;
    }

    public function toXml(SimpleXMLElement $xml): SimpleXMLElement
    {
        $child_xml = $xml->addChild("feedback");
        $child_xml->addAttribute("parentId", (string) $this->getParentId());
        $child_xml->addAttribute("title", $this->getTitle());
        $child_xml->addAttribute("description", $this->getDescription());
        $child_xml->addAttribute("startValue", (string) $this->getStartValue());
        $child_xml->addAttribute("endValue", (string) $this->getEndValue());
        $child_xml->addAttribute("feedbackText", $this->getFeedbackText());
        $child_xml->addAttribute("parentTypeOverall", (string) $this->isParentTypeOverall());

        return $xml;
    }

    public static function fromXml(ilDBInterface $db, $parent_id, SimpleXMLElement $xml): SimpleXMLElement
    {
        $attributes = $xml->attributes();
        $question = new self($db);
        $question->setParentId($parent_id);
        $question->setTitle($attributes["title"]->__toString());
        $question->setDescription($attributes["description"]->__toString());
        $question->setStartValue((int)$attributes["startValue"]);
        $question->setEndValue((int)$attributes["endValue"]);
        $question->setFeedbackText($attributes["feedbackText"]->__toString());
        $question->setParentTypeOverall((bool)$attributes["parentTypeOverall"]);
        $question->create();
        return $xml;
    }

    public function read()
    {
        $set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));

        $this->setObjectValuesFromRecord($this, $this->db->fetchObject($set));
    }

    final public function initDB()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $this->db->createTable(self::TABLE_NAME, $this->getArrayForDbWithAttributes());
            $this->db->addPrimaryKey(self::TABLE_NAME, ['id']);
            $this->db->createSequence(self::TABLE_NAME);
        }
    }

    final public function updateDB()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $this->initDB();
            return;
        }
        foreach ($this->getArrayForDbWithAttributes() as $property => $attributes) {
            if (!$this->db->tableColumnExists(self::TABLE_NAME, $property)) {
                $this->db->addTableColumn(self::TABLE_NAME, $property, $attributes);
            }
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

    /**
     * @return int
     */
    public function delete()
    {
        return $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE id = ' . $this->getId());
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
     * @param int           $parent_id
     * @param bool          $as_array
     * @param bool          $is_overall
     * @return self[]
     */
    public static function _getAllInstancesForParentId(
        ilDBInterface $db,
        int $parent_id,
        bool $as_array = false,
        bool $is_overall = false
    ) {
        $return = [];
        $q = 'SELECT * FROM ' . self::TABLE_NAME . ' ' .
            ' WHERE parent_id = ' . $db->quote($parent_id, 'integer');

        if ($is_overall) {
            $q .= ' AND parent_type_overall = ' . $db->quote($is_overall, 'integer');
        }
        $q .= ' ORDER BY start_value ASC';

        $set = $db->query($q);

        while ($rec = $db->fetchObject($set)) {
            $feedback = new self($db);
            $feedback->setObjectValuesFromRecord($feedback, $rec);

            if ($as_array) {
                $return[] = (array) $feedback;
            } else {
                $return[] = $feedback;
            }
        }

        return $return;
    }

    /**
     * @param ilDBInterface $db
     * @param bool          $is_overall
     * @return self[]
     */
    public static function _getAllInstances(ilDBInterface $db, bool $is_overall = false)
    {
        $return = [];
        $q = 'SELECT * FROM ' . self::TABLE_NAME . ' ';

        if ($is_overall) {
            $q .= ' WHERE parent_type_overall = ' . $db->quote($is_overall, 'integer');
        }
        $set = $db->query($q);

        while ($rec = $db->fetchObject($set)) {
            $feedback = new self($db);
            $feedback->setObjectValuesFromRecord($feedback, $rec);
            $return[] = $feedback;

        }

        return $return;
    }

    public static function _getFeedbackForPercentage(
        ilDBInterface $db,
        int $parent_id,
        float $percentage,
        bool $is_overall = false
    ): ?Feedback {
        $q = 'SELECT id FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent_id = ' . $db->quote($parent_id, 'integer')
            . ' AND start_value <= ' . $db->quote($percentage, 'float')
            . ' AND end_value >= ' . $db->quote($percentage, 'float');
        if ($is_overall) {
            $q .= ' AND parent_type_overall = ' . $db->quote($is_overall, 'integer');
        }
        $set = $db->query($q);

        while ($rec = $db->fetchObject($set)) {
            $feedback = new self($db, $rec->id);
            return $feedback;
        }
        return null;
    }

    public static function _getNextMinValueForParentId(
        ilDBInterface $db,
        int $parent_id,
        int $value = 0,
        int $ignore = 0,
        bool $is_overall = false
    ): int {
        for ($return = $value; $return < 100; $return++) {
            $q =
                'SELECT id FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent_id = ' . $db->quote($parent_id, 'integer')
                . ' AND start_value <= ' . $db->quote($return, 'integer')
                . ' AND end_value > ' . $db->quote($return, 'integer');
            if ($ignore) {
                $q .= ' AND id != ' . $db->quote($ignore, 'integer');
            }
            if ($is_overall) {
                $q .= ' AND parent_type_overall = ' . $db->quote($is_overall, 'integer');
            }
            $set = $db->query($q);
            $res = $db->fetchObject($set);
            if (!$res->id) {
                return $return;
            }
        }

        return 100;
    }

    public static function _getNextMaxValueForParentId(
        ilDBInterface $db,
        int $parent_id,
        int $value = 0,
        int $ignore = 0,
        bool $is_overall = false
    ): int {
        for ($return = $value + 1; $return <= 100; $return++) {
            $q =
                'SELECT id FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent_id = ' . $db->quote($parent_id, 'integer')
                . ' AND start_value <= ' . $db->quote($return, 'integer')
                . ' AND end_value >= ' . $db->quote($return, 'integer');
            if ($ignore) {
                $q .= ' AND id != ' . $db->quote($ignore, 'integer');
            }
            if ($is_overall) {
                $q .= ' AND parent_type_overall = ' . $db->quote($is_overall, 'integer');
            }
            $set = $db->query($q);
            $res = $db->fetchObject($set);
            if ($res->id) {
                return $return;
            }

        }

        return 100;
    }

    public static function _isComplete(ilDBInterface $db, int $parent_id, bool $is_overall = false): bool
    {
        $min = self::_getNextMinValueForParentId($db, $parent_id, 0, 0, $is_overall);
        $max = self::_getNextMaxValueForParentId($db, $parent_id, $min, 0, $is_overall);

        return ($min == 100 and $max == 100) ? true : false;
    }

    public static function _getNewInstanceByParentId(ilDBInterface $db, int $parent_id, bool $is_overall = false): self
    {
        $obj = new self($db);
        $obj->setParentId($parent_id);
        $obj->setParentTypeOverall($is_overall);

        return $obj;
    }

    public static function _rearangeFeedbackLinear(ilDBInterface $db, int $parent_id, bool $is_overall = false): int
    {
        $obj = new self($db);
        $obj->setParentId($parent_id);

        $feedbacks = self::_getAllInstancesForParentId($db, $parent_id, $as_array = false, $is_overall);
        $nr_feedbacks = count($feedbacks) + 1;
        $range_per_feedback = (int) floor(100 / $nr_feedbacks);
        $remainder = 100 - $range_per_feedback * $nr_feedbacks;

        $start = 0;
        foreach ($feedbacks as $feedback) {
            $range = $range_per_feedback;
            if ($remainder > 0) {
                $range++;
                $remainder -= 1;
            }
            $feedback->setStartValue($start);
            $end = $start + $range;
            $feedback->setEndValue($end);
            $feedback->update();
            $start = $end;
        }

        return $range_per_feedback;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setEndValue(int $end_value)
    {
        $this->end_value = $end_value;
    }

    public function getEndValue(): int
    {
        return $this->end_value;
    }

    public function setFeedbackText(string $feedback_text)
    {
        $this->feedback_text = $feedback_text;
    }

    public function getFeedbackText(): string
    {
        return $this->feedback_text;
    }

    public function setParentId(int $parent_id)
    {
        $this->parent_id = $parent_id;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function setStartValue(int $start_value)
    {
        $this->start_value = $start_value;
    }

    public function getStartValue(): int
    {
        return $this->start_value;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isParentTypeOverall(): bool
    {
        return $this->parent_type_overall;
    }

    public function setParentTypeOverall(bool $parent_type_overall)
    {
        $this->parent_type_overall = $parent_type_overall;
    }
}
