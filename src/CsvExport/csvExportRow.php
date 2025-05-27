<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\CsvExport;

use ilub\plugin\SelfEvaluation\CsvExport\Exceptions\csvExportException;

class csvExportRow
{
    /**
     * @var csvExportValue[]
     */
    protected array $values = [];
    protected ?csvExportColumns $columns = null;

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
    public function getValues(): ?array
    {
        return $this->values;
    }

    public function getValue(csvExportColumn $column): ?csvExportValue
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

    public function getColumns(): ?csvExportColumns
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

    public function getValuesAsArray(): array
    {
        $values = [];
        foreach ($this->getValues() as $value) {
            $values[$value->getColumn()->getColumnId()] = $value->getValue();
        }
        return $values;
    }

}
