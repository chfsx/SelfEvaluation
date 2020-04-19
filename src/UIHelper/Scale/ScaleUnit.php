<?php
namespace ilub\plugin\SelfEvaluation\UIHelper\Scale;

use ilDBInterface;
use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
use ilub\plugin\SelfEvaluation\DatabaseHelper\hasDBFields;
use SimpleXMLElement;

class ScaleUnit implements hasDBFields
{
    use ArrayForDB;

    const TABLE_NAME = 'rep_robj_xsev_scale_u';
    /**
     * @var int
     */
    protected $id = 0;
    /**
     * @var string
     */
    protected $title = 'Standartitle';
    /**
     * @var int
     */
    protected $value = 10;
    /**
     * @var int
     */
    protected $parent_id = 0;
    /**
     * @var int
     */
    protected $position = 99;
    /**
     * @var \ilDBInterface
     */
    protected $db;

    function __construct(ilDBInterface $db, $id = 0)
    {
        $this->db = $db;

        $this->id = $id;
        if ($id != 0) {
            $this->read();
        }
    }

    public function cloneTo(int $parent_id) : self
    {
        $clone = new self($this->db);
        $clone->setParentId($parent_id);
        $clone->setTitle($this->getTitle());
        $clone->setValue($this->getValue());
        $clone->setPosition($this->getPosition());
        $clone->update();
        return $clone;
    }


    public function toXml(int $parent_id, SimpleXMLElement $xml) : SimpleXMLElement
    {
        $child_xml = $xml->addChild("scaleUnit");
        $child_xml->addAttribute("parentId", $parent_id);
        $child_xml->addAttribute("title", $this->getTitle());
        $child_xml->addAttribute("value", $this->getValue());
        $child_xml->addAttribute("position", $this->getPosition());
        return $xml;
    }

    public static function fromXml(ilDBInterface $db, int $parent_id, SimpleXMLElement $xml) : SimpleXMLElement
    {
        $attributes = $xml->attributes();
        $unit = new self($db);
        $unit->setParentId($parent_id);
        $unit->setTitle($attributes["title"]);
        $unit->setValue((int)$attributes["value"]);
        $unit->setPosition((int)$attributes["position"]);
        $unit->create();
        return $xml;
    }

    public function read()
    {
        $set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
            . $this->db->quote($this->getId(), 'integer'));

        $this->setObjectValuesFromRecord($this,$this->db->fetchObject($set));
    }


    final function initDB()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $this->db->createTable(self::TABLE_NAME, $this->getArrayForDbWithAttributes());
            $this->db->addPrimaryKey(self::TABLE_NAME, ['id']);
            $this->db->createSequence(self::TABLE_NAME);
        }
    }

    final function updateDB()
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
     * @return self[]
     */
    public static function _getAllInstancesByParentId(ilDBInterface $db, int $parent_id) : self
    {
        $return = [];
        $set = $db->query('SELECT * FROM '.self::TABLE_NAME.' '.' WHERE parent_id = '.$parent_id.' ORDER BY position ASC');
        while ($rec = $db->fetchObject($set)) {
            $return[] = new self($db, $rec->id);
        }

        return $return;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setParentId(int $parent_id)
    {
        $this->parent_id = $parent_id;
    }

    public function getParentId() : int
    {
        return $this->parent_id;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setValue(int $value)
    {
        $this->value = $value;
    }

    public function getValue() : int
    {
        return $this->value;
    }

    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    public function getPosition() : int
    {
        return $this->position;
    }
}

