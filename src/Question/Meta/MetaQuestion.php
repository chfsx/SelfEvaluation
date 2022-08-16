<?php
namespace ilub\plugin\SelfEvaluation\Question\Meta;

use ilub\plugin\SelfEvaluation\Question\Question as BaseQuestion;
use SimpleXMLElement;
use ilDBInterface;

class MetaQuestion extends BaseQuestion
{
    const TABLE_NAME = 'rep_robj_xsev_mqst';

    const POSTVAR_PREFIX = 'mqst_';

    /**
     * @var string
     */
    const PRIMARY_KEY = 'id';

    /**
     * @var int
     */
    protected $parent_id;

    /**
     * @var string
     */
    protected $name = "";

    /**
     * @var string
     */
    protected $short_title = "";

    /**
     * @var int
     */
    protected $type_id;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var bool
     */
    protected $required;


    public function cloneTo(int $parent_id): BaseQuestion
    {
        $clone = new self($this->db);
        $clone->setName($this->getName());
        $clone->setShortTitle($this->getShortTitle());
        $clone->setTypeId($this->getTypeId());
        $clone->setValues($this->getValues());
        $clone->enableRequired($this->isRequired());
        $clone->setPosition($this->getPosition());
        $clone->setParentId($parent_id);
        $clone->update();
        return $clone;
    }

    public function toXml(SimpleXMLElement $xml): SimpleXMLElement
    {
        $child_xml = $xml->addChild("metaQuestion");
        $child_xml->addAttribute("containerId", $this->getParentId());
        $child_xml->addAttribute("name", $this->getName());
        $child_xml->addAttribute("shortTitle", $this->getShortTitle());
        $child_xml->addAttribute("typeId", $this->getTypeId());
        $child_xml->addAttribute("values", serialize($this->getValues()));
        $child_xml->addAttribute("enableRequired", $this->isRequired());
        $child_xml->addAttribute("position", $this->getPosition());
        return $xml;
    }

    public static function fromXml(ilDBInterface $db, int $parent_id, SimpleXMLElement $xml): SimpleXMLElement
    {
        $attributes = $xml->attributes();
        $question = new self($db);
        $question->setParentId($parent_id);
        $question->setName($attributes["name"]);
        $question->setShortTitle($attributes["shortTitle"]);
        $question->setTypeId((int) $attributes["typeId"]);
        $question->setValues(unserialize($attributes["values"]));
        $question->enableRequired($attributes["enableRequired"] == "1" ? true : false);
        $question->setPosition((int) $attributes["position"]);
        $question->update();
        return $xml;
    }

    public function getTypeId(): int
    {
        return $this->type_id;
    }

    public function setTypeId(int $type)
    {
        $this->type_id = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->getName();
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getShortTitle(): string
    {
        return $this->short_title;
    }

    public function setShortTitle(string $short_title)
    {
        $this->short_title = $short_title;
    }

    public function getValues(): array
    {
        return $this->values ? $this->values : [];
    }

    public function setValues(array $values)
    {
        $this->values = $values;
    }

    public function isRequired():bool
    {
        return $this->required;
    }

    public function enableRequired(bool $status)
    {
        $this->required = $status;
    }

    /**
     * @param ilDBInterface $db
     * @param int           $parent_id
     * @return MetaQuestion[]
     */
    public static function _getAllInstancesForParentId(ilDBInterface $db, int $parent_id) : array
    {
        $questions = [];
        $stmt = self::_getAllInstancesForParentIdQuery($db, $parent_id);
        while ($rec = $db->fetchObject($stmt)) {
            $question = new self($db);
            $question->setId((int)$rec->field_id);
            $question->setParentId($rec->parent_id);
            $question->setName((string)$rec->name);
            $question->setShortTitle((string)$rec->short_title);
            $question->setTypeId((int)$rec->type_id);
            $question->setValues((array)unserialize($rec->values));
            $question->enableRequired((bool)$rec->required);
            $question->setPosition((int)$rec->position);
            $questions[$question->getId()] = $question;
        }
        return $questions;
    }

    public static function _getAllInstancesForParentIdAsArray(ilDBInterface $db, int $parent_id) : array{
        $questions = [];

        foreach (self::_getAllInstancesForParentId( $db,  $parent_id) as $question){
            $questions[] = $question->getArray();
        }
        return $questions;
    }
}