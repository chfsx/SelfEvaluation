<?php
namespace ilub\plugin\SelfEvaluation\UIHelper\Scale;

use ilDBInterface;
use ilub\plugin\SelfEvaluation\DatabaseHelper\hasDBFields;
use SimpleXMLElement;
use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;

class Scale implements hasDBFields
{
    use ArrayForDB;

    const TABLE_NAME = 'rep_robj_xsev_scale';
    /**
     * @var int
     */
    protected $id = 0;
    /**
     * @var int
     */
    protected $parent_id = 0;

    /**
     * @var ScaleUnit[]
     */
    protected $units;

    /**
     * @var ilDBInterface
     */
    protected $db;

    function __construct(ilDBInterface $db, int $id = 0)
    {
        $this->db = $db;

        $this->id = $id;
        if ($id != 0) {
            $this->read();
        }
        $this->units = ScaleUnit::_getAllInstancesByParentId($this->db,$this->getId());
    }

    public function cloneTo($parent_obj_id)
    {
        $clone = new self($this->db);
        $clone->setParentId($parent_obj_id);
        $clone->update();
        $old_units = ScaleUnit::_getAllInstancesByParentId($this->db,$this->getId());
        $new_units = [];

        foreach ($old_units as $old_unit) {
            $new_units[] = $old_unit->cloneTo($clone->getId());
        }
        $clone->units = $new_units;

        return $clone;
    }

    public function toXml(SimpleXMLElement $xml) : SimpleXMLElement
    {
        $child_xml = $xml->addChild("scale");
        $units = ScaleUnit::_getAllInstancesByParentId($this->db,$this->getId());

        foreach ($units as $unit) {
            $child_xml = $unit->toXml($this->getId(), $child_xml);
        }

        return $xml;
    }

    static function fromXml(ilDBInterface $db,int $parent_id, SimpleXMLElement $xml) : SimpleXMLElement
    {
        $scale = new self($db);
        $scale->setParentId($parent_id);
        $scale->create();

        foreach ($xml->scaleUnit as $unit) {
            ScaleUnit::fromXML($db,$scale->getId(), $unit);
        }

        return $xml;
    }

    /**
     * @param bool $flipped
     * @return array (unit value => unit title)
     */
    public function getUnitsAsArray($flipped = false)
    {
        $return = [];
        foreach ($this->units as $k => $u) {
            if ($flipped) {
                $return[$this->units[count($this->units) - $k - 1]->getValue()] = $u->getTitle();
            } else {
                $return[$u->getValue()] = $u->getTitle();
            }
        }

        return $return;
    }

    public function hasUnits()
    {
        return count($this->units) > 0;
    }

    /**
     * @return array
     */
    public function getUnitsAsRelativeArray()
    {
        $return = [];
        $min_max = $this->getMinMaxValue();
        $max = $min_max['max'];

        foreach ($this->units as $k => $u) {
            $return[$u->getValue() * 100 / $max] = $u->getTitle() . " (" . $u->getValue() . ")";
        }

        return $return;
    }

    /**
     * @return ScaleUnit[]
     */
    public function getUnits() : array
    {
        return $this->units;
    }

    /**
     * @return array
     */
    public function getMinMaxValue()
    {
        $min = 999999;
        $max = 0;
        foreach ($this->units as $k => $u) {
            if ($u->getValue() > $max) {
                $max = $u->getValue();
            }
            if ($u->getValue() < $min) {
                $min = $u->getValue();
            }
        }

        return ['min' => $min, 'max' => $max];
    }

    public function read()
    {
        $set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '.$this->getId());
        $this->setObjectValuesFromRecord($this,$this->db->fetchObject($set));
    }

    /**
     * @return array
     */
    protected function getNonDbFields()
    {
        return ['db','units'];
    }

    final function initDB()
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

    public function delete()
    {
        $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE id = '.$this->getId());
    }

    public function update()
    {
        if ($this->getId() == 0) {
            $this->create();
        }
        $this->db->update(self::TABLE_NAME, $this->getArrayForDb(),$this->getIdForDb());
    }

    public static function _getInstanceByObjId(ilDBInterface $db, int $parent_obj_id) : self
    {
        $set = $db->query("SELECT * FROM " . self::TABLE_NAME . " " . " WHERE parent_id = ".$parent_obj_id);
        while ($rec = $db->fetchObject($set)) {
            return new self($db,$rec->id);
        }
        $obj = new self($db);
        $obj->setParentId($parent_obj_id);

        return $obj;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getAmount() : int
    {
        return count($this->units);
    }

    public function setParentId(int $parent_id)
    {
        $this->parent_id = $parent_id;
    }

    public function getParentId() : int
    {
        return $this->parent_id;
    }
}


