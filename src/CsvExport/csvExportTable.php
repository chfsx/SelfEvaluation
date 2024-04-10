<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\CsvExport;

use ilDBInterface;

class csvExportTable
{
    /**
     * @var csvExportRow[]
     */
    protected $rows = null;

    /**
     * @var csvExportColumns
     */
    protected $columns = null;

    /**
     * @var csvExportColumn
     */
    protected $sort_column;

    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
        $this->setRows($rows);
    }

    /**
     * @param csvExportRow[] $rows
     */
    protected function addColumnsFromRows($rows)
    {
        $this->columns = new csvExportColumns();

        if ($rows) {
            foreach ($rows as $row) {
                $this->addColumnsFromRow($row);
            }
        }
    }

    /**
     * @param csvExportRow $row
     */
    protected function addColumnsFromRow(csvExportRow $row)
    {
        $this->getColumns()->addColumns($row->getColumns());
    }

    /**
     * @param csvExportRow[] $rows
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
        $this->columns = null;

        $this->addColumnsFromRows($rows);
    }

    /**
     * @return csvExportRow[]|null
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param csvExportRow $row
     * @param bool         $containsNewColumns
     */
    public function addRow(csvExportRow $row, $containsNewColumns = true)
    {
        $this->rows[] = $row;
        if ($containsNewColumns || ($this->getColumns()->isEmpty())) {
            $this->addColumnsFromRow($row);
        }
    }

    public function addDBTable(ilDBInterface $db, $table_name)
    {
        $this->addDBCustom($db, "SELECT * FROM " . $table_name);
    }

    public function addDBCustom(ilDBInterface $db, $query)
    {
        $set = $db->query($query);

        while ($record = $db->fetchAssoc($set)) {
            $row = new csvExportRow();
            $row->addValuesFromPairedArray($record);
            $this->addRow($row);
        }
    }

    protected function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    protected function getColumnsArray()
    {
        return $this->columns->getColumns();
    }

    public function addColumn(csvExportColumn $column)
    {
        $this->getColumns()->addColumn($column);
    }

    public function addColumnsFromArray(array $columns)
    {
        $this->getColumns()->addColumnsFromArray($columns);
    }

    public function addValuesFromArray(array $rows_of_values = [])
    {
        $first = true;
        foreach ($rows_of_values as $row_of_values) {
            $row = new csvExportRow();
            $row->addValuesFromArray($this->getColumns()->getColumnNamesAsArray(), $row_of_values);
            $this->addRow($row, $first);
            $first = false;
        }
    }

    public function addColumnsAndValuesFromArrays($columns, $rows_of_values)
    {
        $first = true;
        foreach ($rows_of_values as $row_of_values) {
            $row = new csvExportRow();
            $row->addValuesFromArray($columns, $row_of_values);
            $this->addRow($row, $first);
            $first = false;
        };
    }

    public function getRowsValuesAsArray(): array
    {
        $values = [];
        foreach ($this->getRows() as $row) {
            $values[] = $row->getValuesAsArray();
        }
        return $values;
    }

    public function setPositionOfColumn(string $id, int $posititon)
    {
        $this->getColumns()->getColumnById($id)->setPosition($posititon);
    }

    public function getTableAsArray()
    {
        $values = [];
        $this->getColumns()->sortColumns();
        $this->sortRows();
        $values[0] = $this->getColumns()->getColumnNamesAsArray();
        foreach ($this->getRowsValuesAsArray() as $row_id => $row_array) {
            $values[1 + $row_id] = [];
            foreach ($this->getColumnsArray() as $column) {
                if (array_key_exists($column->getColumnId(), $row_array)) {
                    $values[1 + $row_id][] = $row_array[$column->getColumnId()];
                } else {
                    $values[1 + $row_id][] = "";
                }

            }
        }
        return $values;
    }

    /**
     *
     */
    public function sortRows()
    {
        if ($this->getSortColumn() && $this->getColumns()->columnExists($this->getSortColumn())) {
            $sort_column = $this->getSortColumn();
            uasort($this->rows, function (csvExportRow $row_a, csvExportRow $row_b) use ($sort_column) {
                if (is_string($row_a->getValue($sort_column))) {
                    return strcmp($row_a->getValue($sort_column)->getValue(), $row_b->getValue($sort_column)->getValue());
                } else {
                    return $row_a->getValue($sort_column)->getValue() > $row_b->getValue($sort_column)->getValue()? 1:-1;
                }
            });
        }
    }

    /**
     * @param $sort_column
     */
    public function setSortColumn($sort_column)
    {
        $this->sort_column = new csvExportColumn($sort_column);
    }

    /**
     * @return csvExportColumn
     */
    public function getSortColumn()
    {
        return $this->sort_column;
    }

    public function count()
    {
        return count($this->getRows());
    }

}
