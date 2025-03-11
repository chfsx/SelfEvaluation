<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\DatabaseHelper;

interface hasDBFields
{
    public function getArrayForDb(): array;
}
