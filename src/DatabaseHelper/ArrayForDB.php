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
                if(is_array($value)) {
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
            if(is_array($serialized)) {
                $this->{$k} = $serialized;
            } else {
                $this->{$k} = $v;
            }
        }
        return $this;
    }

    protected function getIdForDb(): array
    {
        return ['id' => ['integer', $this->getId()]];
    }

    /**
     * @return array
     */
    protected function getNonDbFields()
    {
        return ['db'];
    }

    protected function setObjectValuesFromRecord(hasDBFields $data, stdClass $rec)
    {
        //Problematisch
        foreach ($data->getArrayForDb() as $k => $v) {
            try {
                $serialized = unserialize((string) $rec->{$k});
            } catch(\ErrorException $e) {
                $serialized = "false";
            }
            if(is_array($serialized)) {
                $this->{$k} = $serialized;
            } else {
                $type = getType($this->$k);
                switch($type) {
                    case 'string':
                        $this->{$k} = (string) $rec->{$k};
                        break;
                    case 'bool':
                        $this->{$k} = (bool) $rec->{$k};
                        break;
                    case 'integer':
                        $this->{$k} = (int) $rec->{$k};
                        break;
                    default:
                        $this->{$k} = (int) $rec->{$k};
                        break;
                }
            }

        }
        return $this;
    }

    protected function getDBFieldType($var): string
    {
        switch (gettype($var)) {
            case 'string':
            case 'array':
            case 'object':
                return 'text';
            case 'NULL':
            case 'boolean':
                return 'integer';
            default:
                return gettype($var);
        }
    }

    public function serialize()
    {
        return serialize($this->getArray());
    }

    public function unserialize($serialized)
    {
        return $this->fromArray(unserialize($serialized));
    }
}
