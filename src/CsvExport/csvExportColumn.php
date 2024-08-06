<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\CsvExport;

class csvExportColumn
{
    /**
     * @var string
     */
    protected $column_id = "";

    /**
     * @var string
     */
    protected $column_txt = "";

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @param        $column_id
     * @param string $column_txt
     * @param int    $position
     */
    public function __construct($column_id, $column_txt = "", $position = 0)
    {
        $this->setColumnId($column_id);
        $this->setColumnTxt($column_txt);
        $this->setPosition($position);
    }

    /**
     * @param string $column_id
     */
    public function setColumnId($column_id): void
    {
        $this->column_id = $column_id;
    }

    /**
     * @return string
     */
    public function getColumnId()
    {
        return $this->column_id;
    }

    /**
     * @param string $column_txt
     */
    public function setColumnTxt($column_txt): void
    {
        $this->column_txt = $column_txt;
    }

    /**
     * @return string
     */
    public function getColumnTxt()
    {
        if ($this->column_txt == "") {
            return $this->getColumnId();
        }
        return $this->column_txt;
    }

    /**
     * @param int $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

}
