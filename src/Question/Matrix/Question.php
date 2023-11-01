<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question\Matrix;

use ilub\plugin\SelfEvaluation\Question\Question as BaseQuestion;

use SimpleXMLElement;
use ilDBInterface;

class Question extends BaseQuestion
{
    public const TABLE_NAME = 'rep_robj_xsev_qst';

    public const POSTVAR_PREFIX = 'qst_';

    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var string
     */
    protected $question_body = '';
    /**
     * @var bool
     */
    protected $is_inverse = false;
    /**
     * @var int
     */
    protected $parent_id = 0;

    /**
     * @var array
     */
    protected static $instances_for_parent_id_array = [];

    /**
     * @var array
     */
    protected static $instances_for_parent_id = [];

    /**
     * @var int
     */
    protected $position;

    public function cloneTo(int $parent_id): BaseQuestion
    {
        $clone = new self($this->db);
        $clone->setParentId($parent_id);
        $clone->setTitle($this->getTitle());
        $clone->setQuestionBody($this->getQuestionBody());
        $clone->setPosition($this->getPosition());
        $clone->setIsInverse($this->getIsInverse());
        $clone->update();
        return $clone;
    }

    public function toXml(SimpleXMLElement $xml): SimpleXMLElement
    {
        $child_xml = $xml->addChild("question");
        $child_xml->addAttribute("parentId", (string)$this->getParentId());
        $child_xml->addAttribute("title", $this->getTitle());
        $child_xml->addAttribute("questionBody", $this->getQuestionBody());
        $child_xml->addAttribute("position", (string)$this->getPosition());
        $child_xml->addAttribute("inverse", (string)$this->getIsInverse());
        return $xml;
    }

    public static function fromXml(ilDBInterface $db, int $parent_id, SimpleXMLElement $xml): SimpleXMLElement
    {
        $attributes = $xml->attributes();
        $question = new self($db);
        $question->setParentId($parent_id);
        $question->setTitle((string) $attributes["title"]);
        $question->setQuestionBody((string) $attributes["questionBody"]);
        $question->setIsInverse((bool)$attributes["inverse"]);
        $question->create();
        $question->setPosition((int)$attributes["position"]);
        $question->update();
        return $xml;
    }

    /**
     * @param ilDBInterface $db
     * @param int           $parent_id
     * @return Question[]
     */
    public static function _getAllInstancesForParentId(ilDBInterface $db, int $parent_id): array
    {
        if (!self::$instances_for_parent_id[$parent_id]) {
            self::$instances_for_parent_id[$parent_id] = [];
            $stmt = self::_getAllInstancesForParentIdQuery($db, $parent_id);

            while ($rec = $db->fetchObject($stmt)) {
                $question = new self($db);
                $question->setId($rec->id);
                $question->setParentId($parent_id);
                $question->setTitle((string)$rec->title);
                $question->setQuestionBody((string)$rec->question_body);
                $question->setIsInverse($rec->is_inverse);
                $question->setPosition((int)$rec->position);
                self::$instances_for_parent_id[$parent_id][$rec->id] = $question;
            }
        }
        return self::$instances_for_parent_id[$parent_id];
    }

    public static function _getAllInstancesForParentIdAsArray(ilDBInterface $db, int $parent_id): array
    {
        if (!self::$instances_for_parent_id_array[$parent_id]) {
            self::$instances_for_parent_id_array[$parent_id] = [];
            foreach (self::_getAllInstancesForParentId($db, $parent_id) as $question) {
                self::$instances_for_parent_id_array[$parent_id][$question->getId()] = $question->getArray();
            }
        }
        return self::$instances_for_parent_id_array[$parent_id];
    }

    public function setIsInverse(bool $is_inverse)
    {
        $this->is_inverse = $is_inverse;
    }

    public function getIsInverse(): bool
    {
        return $this->is_inverse;
    }


    public function setQuestionBody(string $question_body)
    {
        $this->question_body = $question_body;
    }

    public function getQuestionBody(): string
    {
        return $this->question_body;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
