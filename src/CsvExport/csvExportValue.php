<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\CsvExport;

/**
 * Class csvExportValue
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class csvExportValue
{
    protected csvExportColumn $column;
    protected string $value;

    public function __construct(string $column_name, string $value)
    {
        $this->column = new csvExportColumn($column_name);
        $this->value = $value;
    }

    public function setColumn(csvExportColumn $column): void
    {
        $this->column = $column;
    }

    public function getColumn(): csvExportColumn
    {
        return $this->column;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
