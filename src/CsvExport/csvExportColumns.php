<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\CsvExport;

use ilub\plugin\SelfEvaluation\CsvExport\Exceptions\csvExportException;

class csvExportColumns
{
    /**
     * @param csvExportColumn[] $columns
     */
    public function __construct(protected array $columns = [])
    {
    }

    public function setColumns(array $columns = []): void
    {
        $this->columns = $columns;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function addColumns(csvExportColumns $columns): void
    {
        foreach ($columns->getColumns() as $column) {
            $this->addColumn($column);
        }
    }

    public function addColumn(csvExportColumn $column): void
    {
        if (!$this->columnExists($column)) {
            $this->columns[$column->getColumnId()] = $column;
        }
    }

    public function columnExists(csvExportColumn $column): bool
    {
        return $this->columnIdExists($column->getColumnId());
    }

    public function columnIdExists(string $column_id = ""): bool
    {
        return array_key_exists($column_id, $this->getColumns());
    }

    public function reset(): void
    {
        $this->setColumns(null);
    }

    /**
     * @throws csvExportException
     */
    public function addColumnsFromArray(array $columns): void
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

    public function getColumnNamesAsArray(): array
    {
        $column_names = [];
        foreach ($this->getColumns() as $column) {
            $column_names[$column->getColumnId()] = $column->getColumnTxt();
        }
        return $column_names;
    }

    public function isEmpty(): bool
    {
        return empty($this->columns);
    }

    public function sortColumns(): void
    {
        uasort($this->columns, function (csvExportColumn $column_a, csvExportColumn $column_b): int {
            if ($column_a->getPosition() === $column_b->getPosition()) {
                return strcmp($column_a->getColumnId(), $column_b->getColumnId());
            }
            return $column_a->getPosition() > $column_b->getPosition() ? 1 : -1;
        });
    }

    /**
     * @throws csvExportException
     */
    public function getColumnById(string $id = ""): csvExportColumn
    {
        if (array_key_exists($id, $this->getColumns())) {
            return $this->columns[$id];
        }
        throw new csvExportException(csvExportException::COLUMN_DOES_NOT_EXIST);
    }

    public function count(): int
    {
        return count($this->getColumns());
    }
}
