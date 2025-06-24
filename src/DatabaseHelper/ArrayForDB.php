<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\DatabaseHelper;

use stdClass;

trait ArrayForDB
{
    protected function getArrayForDbWithAttributes(): array
    {
        $array = [];
        foreach ($this->getArrayForDb() as $property => $value) {
            $type = $value[0];
            $attributes = ['type' => $type];
            switch ($type) {
                case 'integer':
                    $attributes['length'] = 4;
                    break;
                case 'text':
                    $attributes['length'] = 4000;
                    break;
            }
            if ($property == 'id') {
                $attributes['notnull'] = true;
            }
            $array[$property] = $attributes;
        }
        return $array;
    }

    public function getArrayForDb(): array
    {
        $array = [];
        foreach (get_object_vars($this) as $property => $value) {
            if (!in_array($property, $this->getNonDbFields())) {
                if (is_array($value)) {
                    $value = serialize($value);
                }
                $array[$property] = [$this->getDBFieldType($value), $value];
            }
        }
        return $array;
    }

    public function getArray(): array
    {
        $array = [];
        foreach (get_object_vars($this) as $property => $value) {
            if (!in_array($property, $this->getNonDbFields())) {
                $array[$property] = $value;
            }
        }

        return $array;
    }

    public function fromArray(array $array): self
    {
        foreach ($array as $k => $v) {
            $serialized = unserialize($v);
            $this->{$k} = is_array($serialized) ? $serialized : $v;
        }
        return $this;
    }

    protected function getIdForDb(): array
    {
        return ['id' => ['integer', $this->getId()]];
    }

    protected function getNonDbFields(): array
    {
        return ['db'];
    }

    protected function setObjectValuesFromRecord(hasDBFields $data, stdClass $rec): static
    {
        //Problematisch
        foreach (array_keys($data->getArrayForDb()) as $k) {
            try {
                $serialized = unserialize((string) $rec->{$k});
            } catch (\ErrorException) {
                $serialized = "false";
            }
            if (is_array($serialized)) {
                $this->{$k} = $serialized;
            } else {
                $type = gettype($this->{$k});
                switch ($type) {
                    case 'string':
                        $this->{$k} = (string) $rec->{$k};
                        break;
                    case 'boolean':
                        $this->{$k} = (bool) $rec->{$k};
                        break;
                    case 'integer':
                        $this->{$k} = (int) $rec->{$k};
                        break;
                }
            }
        }
        return $this;
    }

    protected function getDBFieldType($var): string
    {
        return match (gettype($var)) {
            'string', 'array', 'object' => 'text',
            'NULL', 'boolean' => 'integer',
            default => gettype($var),
        };
    }

    public function serialize(): string
    {
        return serialize($this->getArray());
    }

    public function unserialize($serialized): self
    {
        return $this->fromArray(unserialize($serialized));
    }
}
