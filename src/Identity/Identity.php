<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Identity;

use ilDBInterface;
use ilub\plugin\SelfEvaluation\DatabaseHelper\ArrayForDB;
use ilub\plugin\SelfEvaluation\DatabaseHelper\hasDBFields;

class Identity implements hasDBFields
{
    use ArrayForDB;

    public const TABLE_NAME = 'rep_robj_xsev_uid';
    public const LENGTH = 6;
    public const TYPE_LOGIN = 1;
    public const TYPE_EXTERNAL = 2;

    protected int $id = 0;
    protected int $identifier = 0;
    protected int $obj_id = 0;
    protected int $type = self::TYPE_LOGIN;
    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db, int $id = 0)
    {
        global $DIC;

        $this->id = $id;
        $this->db = $DIC->database();
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

    final public function updateDB()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $this->initDB();

            return;
        }
        foreach ($this->getArrayForDbWithAttributes() as $property => $field) {
            if (!$this->db->tableColumnExists(self::TABLE_NAME, $property)) {
                $this->db->addTableColumn(self::TABLE_NAME, $property, $field);
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
        return $this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE id = '.$this->getId());
    }

    public function update()
    {
        $this->db->update(self::TABLE_NAME, $this->getArrayForDb(), $this->getIdForDb());
    }


    //
    // Static
    //
    /**
     * @param int           $obj_id
     * @param ilDBInterface $db
     * @param string        $identifier
     * @return Identity[]
     */
    public static function _getAllInstancesByObjId(ilDBInterface $db, int $obj_id, string $identifier = ""): array
    {
        $return = [];
        if ($identifier != "") {
            $set = $db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE obj_id = '
                . $obj_id . ' AND identifier = ' . $db->quote($identifier, 'text'));
        } else {
            $set = $db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE obj_id = '
                . $db->quote($obj_id, 'integer'));
        }

        while ($rec = $db->fetchObject($set)) {
            $return[] = new self($db, $rec->id);
        }

        return $return;
    }

    public static function _getInstanceForObjIdAndIdentifier(ilDBInterface $db, int $obj_id, int $identifier): Identity
    {
        $set = $db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE obj_id = '
            . $obj_id . ' AND identifier = ' . $db->quote($identifier, 'text'));

        while ($rec = $db->fetchObject($set)) {
            return new self($db, $rec->id);
        }
        return self::_getNewInstanceForObjIdAndUserId($db, $obj_id, $identifier);
    }

    /**
     * @param int           $obj_id
     * @param string        $identifier
     * @param ilDBInterface $db
     * @return array
     */
    public static function _getAllInstancesForObjIdAndIdentifier(ilDBInterface $db, int $obj_id, string $identifier): array
    {
        return self::_getAllInstancesByObjId($db, $obj_id, $identifier);
    }

    public static function _getNewHashInstanceForObjId(ilDBInterface $db, int $obj_id): Identity
    {
        do {
            $identifier = strtoupper(substr(md5((string)rand(1, 99999)), 0, self::LENGTH));
        } while (self::_identityExists($db, $obj_id, $identifier));

        $obj = new self($db);
        $obj->setObjId($obj_id);
        $obj->setIdentifier((int) $identifier);
        $obj->setType(self::TYPE_EXTERNAL);
        $obj->create();

        return $obj;
    }

    public static function _getNewInstanceForObjIdAndUserId(ilDBInterface $db, int $obj_id, int $user_id): Identity
    {
        $obj = new self($db);
        $obj->setObjId($obj_id);
        $obj->setIdentifier($user_id);
        $obj->create();

        return $obj;
    }

    public static function _getNewInstanceForObjId(ilDBInterface $db, int $obj_id): Identity
    {
        $obj = new self($db);
        $obj->setObjId($obj_id);

        return $obj;
    }

    public static function _identityExists(ilDBInterface $db, int $obj_id, string $identifier): bool
    {
        $set = $db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE obj_id = '
            . $obj_id . ' AND identifier = ' . $db->quote($identifier, 'text'));
        while ($rec = $db->fetchObject($set)) {
            return true;
        }

        return false;
    }

    public static function _getObjIdForIdentityId(ilDBInterface $db, string $identity_id): int
    {
        $set = $db->query('SELECT obj_id FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '.$identity_id);
        while ($rec = $db->fetchObject($set)) {
            return $rec->obj_id;
        }

        return 0;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $obj_id
     */
    public function setObjId($obj_id)
    {
        $this->obj_id = $obj_id;
    }

    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    public function setIdentifier(int $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier(): int
    {
        return $this->identifier;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}
