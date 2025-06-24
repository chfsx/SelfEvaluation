<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\CsvExport;

use ilDBInterface;

class csvExportTable
{
    protected ?csvExportColumns $columns = null;
    protected ?csvExportColumn $sort_column = null;

    public function __construct(
        /**
         * @var csvExportRow[]|null
         */
        protected ?array $rows = []
    ) {
        $this->setRows($this->rows);
    }

    /**
     * @param csvExportRow[] $rows
     */
    protected function addColumnsFromRows(array $rows)
    {
        $this->columns = new csvExportColumns();

        foreach ($rows as $row) {
            $this->addColumnsFromRow($row);
        }
    }

    protected function addColumnsFromRow(csvExportRow $row)
    {
        $this->getColumns()->addColumns($row->getColumns());
    }

    /**
     * @param csvExportRow[] $rows
     */
    public function setRows(array $rows): void
    {
        $this->rows = $rows;
        $this->columns = null;

        $this->addColumnsFromRows($rows);
    }

    /**
     * @return csvExportRow[]|null
     */
    public function getRows(): ?array
    {
        return $this->rows;
    }

    public function addRow(csvExportRow $row, bool $containsNewColumns = true): void
    {
        $this->rows[] = $row;
        if ($containsNewColumns || ($this->getColumns()->isEmpty())) {
            $this->addColumnsFromRow($row);
        }
    }

    public function addDBTable(ilDBInterface $db, string $table_name): void
    {
        $this->addDBCustom($db, "SELECT * FROM " . $table_name);
    }

    public function addDBCustom(ilDBInterface $db, string $query): void
    {
        $set = $db->query($query);

        while ($record = $db->fetchAssoc($set)) {
            $row = new csvExportRow();
            $row->addValuesFromPairedArray($record);
            $this->addRow($row);
        }
    }

    protected function setColumns(csvExportColumns $columns)
    {
        $this->columns = $columns;
    }

    public function getColumns(): ?csvExportColumns
    {
        return $this->columns;
    }

    protected function getColumnsArray(): array
    {
        return $this->columns->getColumns();
    }

    public function addColumn(csvExportColumn $column): void
    {
        $this->getColumns()->addColumn($column);
    }

    public function addColumnsFromArray(array $columns): void
    {
        $this->getColumns()->addColumnsFromArray($columns);
    }

    public function addValuesFromArray(array $rows_of_values = []): void
    {
        $first = true;
        foreach ($rows_of_values as $row_of_values) {
            $row = new csvExportRow();
            $row->addValuesFromArray($this->getColumns()->getColumnNamesAsArray(), $row_of_values);
            $this->addRow($row, $first);
            $first = false;
        }
    }

    public function addColumnsAndValuesFromArrays(array $columns, array $rows_of_values): void
    {
        $first = true;
        foreach ($rows_of_values as $row_of_values) {
            $row = new csvExportRow();
            $row->addValuesFromArray($columns, $row_of_values);
            $this->addRow($row, $first);
            $first = false;
        }
    }

    public function getRowsValuesAsArray(): array
    {
        $values = [];
        foreach ($this->getRows() as $row) {
            $values[] = $row->getValuesAsArray();
        }
        return $values;
    }

    public function setPositionOfColumn(string $id, int $posititon): void
    {
        $this->getColumns()->getColumnById($id)->setPosition($posititon);
    }

    public function getTableAsArray(): array
    {
        $values = [];
        $this->getColumns()->sortColumns();
        $this->sortRows();
        $values[0] = $this->getColumns()->getColumnNamesAsArray();
        foreach ($this->getRowsValuesAsArray() as $row_id => $row_array) {
            $values[1 + $row_id] = [];
            foreach ($this->getColumnsArray() as $column) {
                $values[1 + $row_id][] = array_key_exists(
                    $column->getColumnId(),
                    $row_array
                ) ? $row_array[$column->getColumnId()] : "";
            }
        }
        return $values;
    }

    /**
     *
     */
    public function sortRows(): void
    {
        $sort_column = $this->getSortColumn();
        if ($sort_column && $this->getColumns()->columnExists($sort_column)) {
            uasort($this->rows, function (csvExportRow $row_a, csvExportRow $row_b) use ($sort_column): int {
                if ($row_a->getValue($sort_column) === null) {
                    return -1;
                }
                if ($row_b->getValue($sort_column) === null) {
                    return 1;
                }
                if (is_string($row_a->getValue($sort_column))) {
                    return strcmp(
                        $row_a->getValue($sort_column)->getValue(),
                        $row_b->getValue($sort_column)->getValue()
                    );
                }
                return $row_a->getValue($sort_column)->getValue() > $row_b->getValue($sort_column)->getValue() ? 1 : -1;
            });
        }
    }

    /**
     * @param $sort_column
     */
    public function setSortColumn($sort_column): void
    {
        $this->sort_column = new csvExportColumn($sort_column);
    }

    public function getSortColumn(): ?csvExportColumn
    {
        return $this->sort_column;
    }

    public function count(): int
    {
        return count($this->getRows());
    }

}
