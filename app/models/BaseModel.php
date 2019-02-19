<?php

namespace Models;


abstract class BaseModel
{
//    DB connection instance
    protected $pdo;

//    DB connection options
    private $options = [];

    /**
     * BaseModel constructor.
     */
    final public function __construct()
    {
        $this->fetchOptions();
        $this->setDBConnection();
    }

    private function setDBConnection()
    {
        $o = $this->options;
        try {
            $this->pdo = new \PDO($o['dboptions'], $o['user'], $o['password']);

//            Preparing statement without emulation
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }

    }

    private function fetchOptions()
    {
        $this->options = [
            'dboptions' => "{$GLOBALS['dboptions']['driver']}:dbname={$GLOBALS['dboptions']['dbname']};host={$GLOBALS['dboptions']['host']}",
            'user' => $GLOBALS['dboptions']['user'],
            'password' => $GLOBALS['dboptions']['password'],
        ];
    }
}