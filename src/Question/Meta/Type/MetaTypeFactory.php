<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Question\Meta\Type;

class MetaTypeFactory
{
    public function getTypeByTypeId(int $type_id): ?MetaQuestionType
    {
        return match ($type_id) {
            MetaTypeMatrix::TYPE_ID => new MetaTypeMatrix(),
            MetaTypeSelect::TYPE_ID => new MetaTypeSelect(),
            MetaTypeSingleChoice::TYPE_ID => new MetaTypeSingleChoice(),
            MetaTypeText::TYPE_ID => new MetaTypeText(),
            default => null,
        };
    }

    public function getTypes(): array
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
