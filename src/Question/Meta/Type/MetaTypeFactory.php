<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question\Meta\Type;

class MetaTypeFactory
{
    /**
     * @param int $type_id
     * @return MetaQuestionType|null
     */
    public function getTypeByTypeId(int $type_id): ?MetaQuestionType
    {
        switch($type_id) {
            case MetaTypeMatrix::TYPE_ID:
                return new MetaTypeMatrix();
            case MetaTypeSelect::TYPE_ID:
                return new MetaTypeSelect();
            case MetaTypeSingleChoice::TYPE_ID:
                return new MetaTypeSingleChoice();
            case MetaTypeText::TYPE_ID:
                return new MetaTypeText();
        }
        return null;
    }

    public function getTypes()
    {
        $type = new MetaTypeText();
        $types[$type->getId()] = $type;
        $type = new MetaTypeSelect();
        $types[$type->getId()] = $type;
        $type = new MetaTypeSingleChoice();
        $types[$type->getId()] = $type;
        $type = new MetaTypeMatrix();
        $types[$type->getId()] = $type;

        return $types;
    }
}
