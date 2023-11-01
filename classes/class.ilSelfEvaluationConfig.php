<?php

declare(strict_types=1);

class ilSelfEvaluationConfig
{
    /**
     * @var string
     */
    protected $table_name = '';
    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @param $table_name
     */
    public function __construct($table_name)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->table_name = $table_name;
    }

    /**
     * @param string $table_name
     */
    public function setTableName($table_name)
    {
        $this->table_name = $table_name;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * @param $method
     * @param $params
     * @return bool|null
     */
    public function __call($method, $params)
    {
        if (substr($method, 0, 3) == 'get') {
            return $this->getValue(self::_fromCamelCase(substr($method, 3)));
        } else {
            if (substr($method, 0, 3) == 'set') {
                $this->setValue(self::_fromCamelCase(substr($method, 3)), $params[0]);

                return true;
            } else {
                return null;
            }
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function setValue($key, $value)
    {
        if (!is_string($this->getValue($key))) {
            $this->db->insert($this->getTableName(), [
                "config_key" => [
                    "text",
                    $key
                ],
                "config_value" => [
                    "text",
                    $value
                ]
            ]);
        } else {
            $this->db->update($this->getTableName(), [
                "config_key" => [
                    "text",
                    $key
                ],
                "config_value" => [
                    "text",
                    $value
                ]
            ], [
                "config_key" => [
                    "text",
                    $key
                ]
            ]);
        }
    }

    /**
     * @param $key
     * @return bool|string
     */
    public function getValue($key): string
    {
        $result = $this->db->query("SELECT config_value FROM " . $this->getTableName() . " WHERE config_key = "
            . $this->db->quote($key, "text"));
        if ($result->numRows() == 0) {
            return '';
        }
        $record = $this->db->fetchAssoc($result);

        return (string) $record['config_value'];
    }

    /**
     * @return int
     */
    public function getContainer()
    {
        $key = $this->getValue('container');
        if ($key == '' or $key == 0) {
            return 1;
        } else {
            return $key;
        }
    }

    /**
     * @return bool
     */
    public function initDB()
    {
        if (!$this->db->tableExists($this->getTableName())) {
            $fields = [
                'config_key' => [
                    'type' => 'text',
                    'length' => 128,
                    'notnull' => true
                ],
                'config_value' => [
                    'type' => 'clob',
                    'notnull' => false
                ],
            ];
            $this->db->createTable($this->getTableName(), $fields);
            $this->db->addPrimaryKey($this->getTableName(), ["config_key"]);
        }

        return true;
    }


    //
    // Helper
    //
    /**
     * @param string $str
     * @return string
     */
    public static function _fromCamelCase($str)
    {
        $str[0] = strtolower($str[0]);

        return preg_replace_callback(
            '/([A-Z])/',
            function ($c) {
                return "_" . strtolower($c[1]);
            },
            $str
        );
    }

    /**
     * @param string $str
     * @param bool   $capitalise_first_char
     * @return string
     */
    public static function _toCamelCase($str, $capitalise_first_char = false)
    {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }

        return preg_replace_callback(
            '/-([a-z])/',
            function ($c) {
                return strtoupper($c[1]);
            },
            $str
        );
    }
}
