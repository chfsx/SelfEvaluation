<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\Dataset;

class Statistics
{
    public function getMeanFromData(array $data): ?float
    {
        $count = count($data);
        if($count) {
            return array_sum($data) / $count;
        }
        return null;
    }

    public function valueToPercentage(float $mean): float
    {
        return abs($mean * 100);
    }

    public function fractionOf(float $value, float $max): float
    {
        if($value == 0) {
            return 0;
        }
        if($max == 0) {
            throw new \Exception("Fractions of zero for values greater than 0 do not exist");
        }
        return $value / $max;
    }

    public function percentageOf(float $value, float $max): float
    {
        return $this->valueToPercentage($this->fractionOf($value, $max));
    }

    public function arraySumFractionOfMaxSumPossible(array $values, $max): float
    {
        $max_possible = count($values) * $max;
        $sum = array_sum($values);
        return $max_possible == 0 ? 0 : $sum / $max_possible;
    }

    public function getMinKeyAndValueFromArray($values): array
    {
        $min_key = array_keys($values, min($values));
        $key = array_pop($min_key);
        return [$key, $values[$key]];
    }

    public function getMaxKeyAndValueFromArray($values): array
    {
        $min_key = array_keys($values, max($values));
        $key = array_pop($min_key);
        return [$key, $values[$key]];
    }

    public function getVarianzFromValues(array $values): float
    {
        $mean = $this->getMeanFromData($values);
        $varianz = 0;
        $nr_values = count($values);
        foreach ($values as $value) {
            $varianz += pow($mean - $value, 2) / $nr_values;
        }

        return $varianz;
    }

    public function getStandardDeviation(array $values): float
    {
        return sqrt($this->getVarianzFromValues($values));
    }
}
