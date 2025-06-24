<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\CsvExport;

class csvExportColumn
{
    protected string $column_id = "";
    protected string $column_txt = "";
    protected int $position = 0;

    public function __construct(string $column_id, string $column_txt = "", int $position = 0)
    {
        $this->setColumnId($column_id);
        $this->setColumnTxt($column_txt);
        $this->setPosition($position);
    }

    public function setColumnId(string $column_id): void
    {
        $this->column_id = $column_id;
    }

    public function getColumnId(): string
    {
        return $this->column_id;
    }

    public function setColumnTxt(string $column_txt): void
    {
        $this->column_txt = $column_txt;
    }

    public function getColumnTxt(): string
    {
        if ($this->column_txt === "") {
            return $this->getColumnId();
        }
        return $this->column_txt;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

}
