<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\CsvExport;

use ilub\plugin\SelfEvaluation\CsvExport\Exceptions\csvExportException;

class csvExportColumns
{
    /**
     * @var csvExportColumn[]
     */
    protected $columns = [];

    /**
     * @param csvExportColumn[] $columns
     */
    public function __construct($columns = [])
    {
        $this->columns = $columns;
    }

    /**
     * @param $columns
     */
    public function setColumns($columns = [])
    {
        $this->columns = $columns;
    }

    /**
     * @return array|csvExportColumn[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param csvExportColumns $columns
     */
    public function addColumns(csvExportColumns $columns)
    {
        foreach ($columns->getColumns() as $column) {
            $this->addColumn($column);
        }
    }

    /**
     * @param csvExportColumn $column
     */
    public function addColumn(csvExportColumn $column)
    {
        if (!$this->columnExists($column)) {
            $this->columns[$column->getColumnId()] = $column;
        }
    }

    /**
     * @param csvExportColumn $column
     * @return bool
     */
    public function columnExists(csvExportColumn $column)
    {
        return $this->columnIdExists($column->getColumnId());
    }

    /**
     * @param string $column_id
     * @return bool
     */
    public function columnIdExists($column_id = "")
    {
        return array_key_exists($column_id, $this->getColumns());
    }

    public function reset()
    {
        $this->setColumns(null);
    }

    /**
     * @param $columns
     * @throws csvExportException
     */
    public function addColumnsFromArray($columns)
    {
        foreach ($columns as $column) {
            if (is_array($column) && array_key_exists("position", $column) && array_key_exists("name", $column)) {
                $this->addColumn(new csvExportColumn($column["name"], $column["position"]));
            } elseif (is_array($column) && array_key_exists("name", $column)) {
                $this->addColumn(new csvExportColumn($column["name"]));
            } elseif (is_array($column)) {
                throw new csvExportException(csvExportException::INVALID_ARRAY);
            } else {
                $this->addColumn(new csvExportColumn($column));
            }

        }
    }

    /**
     * @return array
     */
    public function getColumnNamesAsArray()
    {
        $column_names = [];
        foreach ($this->getColumns() as $column) {
            $column_names[$column->getColumnId()] = $column->getColumnTxt();
        }
        return $column_names;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->columns);
    }

    public function sortColumns()
    {
        uasort($this->columns, function (csvExportColumn $column_a, csvExportColumn $column_b) {
            if ($column_a->getPosition() == $column_b->getPosition()) {
                return strcmp($column_a->getColumnId(), $column_b->getColumnId());
            }
            return $column_a->getPosition() > $column_b->getPosition();
        });
    }

    /**
     * @param string $id
     * @return csvExportColumn
     * @throws csvExportException
     */
    public function getColumnById($id = "")
    {
        if (array_key_exists($id, $this->getColumns())) {
            return $this->columns[$id];
        } else {
            throw new csvExportException(csvExportException::COLUMN_DOES_NOT_EXIST);
        }

    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->getColumns());
    }
}
