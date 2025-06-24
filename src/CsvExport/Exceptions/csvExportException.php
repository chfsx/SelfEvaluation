<?php

declare(strict_types=1);

namespace ilub\plugin\SelfEvaluation\CsvExport\Exceptions;

use ILIAS\DI\Exceptions\Exception;

class csvExportException extends Exception
{
    public const UNKNONWN_EXCEPTION = -1;
    public const COLUMN_DOES_NOT_EXIST = 1001;
    public const COLUMN_DOES_ALREADY_EXISTS_IN_ROW = 1002;
    public const INVALID_ARRAY = 2001;

    protected static array $message_strings = [
        self::UNKNONWN_EXCEPTION => 'Unknown Exception',
        self::COLUMN_DOES_NOT_EXIST => 'Column does not exist:',
        self::COLUMN_DOES_ALREADY_EXISTS_IN_ROW => 'Column does already exist in row:',
        self::INVALID_ARRAY => 'Invalid array: '
    ];

    public function __construct(int $exception_code = self::UNKNONWN_EXCEPTION, protected string $additional_info = '')
    {
        $this->code = $exception_code;
        $this->assignMessageToCode();
        parent::__construct($this->message, $this->code);
    }

    protected function assignMessageToCode()
    {
        $this->message = 'ActiveRecord Exeption: ' . self::$message_strings[$this->code] . $this->additional_info;
    }

    public function __toString(): string
    {
        return implode('<br>', [static::class, $this->message]);
    }
}
