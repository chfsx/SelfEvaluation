<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\DatabaseHelper;

interface hasDBFields extends \Serializable
{
    public function getArrayForDb(): array;
}
