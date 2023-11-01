<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\CsvExport;

use ilub\plugin\SelfEvaluation\CsvExport\Exceptions\csvExportException;

class csvExportRow
{
    /**
     * @var csvExportValue[]
     */
    protected $values = [];

    /**
     * @var csvExportColumns
     */
    protected $columns = null;

    public function __construct(array $values = [])
    {
        $this->columns = new csvExportColumns();
        $this->values = $values;
        foreach ($values as $value) {
            $this->addValue($value);
        }
    }

    public function setValues(array $values)
    {
        unset($this->values);
        $this->getColumns()->reset();
        foreach ($values as $value) {
            $this->addValue($value);
        }
    }

    /**
     * @return csvExportValue[]|null
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param csvExportColumn $column
     * @return csvExportValue
     */
    public function getValue(csvExportColumn $column)
    {
        if (array_key_exists($column->getColumnId(), $this->values)) {
            return $this->values[$column->getColumnId()];
        } else {
            return null;
        }
    }

    public function addValue(csvExportValue $value)
    {
        if ($this->getColumns()->columnExists($value->getColumn())) {
            throw new csvExportException(csvExportException::COLUMN_DOES_ALREADY_EXISTS_IN_ROW);
        }
        $this->columns->addColumn($value->getColumn());
        $this->values[$value->getColumn()->getColumnId()] = $value;
    }

    public function getColumns()
    {
        return $this->columns;
    }


    public function addValuesFromArray(array $column_names, array $values)
    {
        foreach ($values as $value) {
            $this->addValue(new csvExportValue(array_shift($column_names), $value));
        }
    }

    public function addValuesFromPairedArray(array $values)
    {
        foreach ($values as $column_name => $value) {
            $this->addValue(new csvExportValue($column_name, $value));
        }
    }

    public function getValuesAsArray()
    {
        $values = [];
        foreach ($this->getValues() as $value) {
            $values[$value->getColumn()->getColumnId()] = $value->getValue();
        }
        return $values;
    }

}
