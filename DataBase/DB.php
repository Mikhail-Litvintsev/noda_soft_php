<?php

namespace DataBase;

class DB
{
    private static ?\PDO $instance = null;

    protected function __construct()
    {
        self::getInstance();
    }

    public static function getInstance(): \PDO
    {
        if (is_null(self::$instance)) {
            /** Предполагается реализация, автозагрузки, конфиг файлов и т.д. */
            $dbType = $_ENV['DB_TYPE']; // mysql
            $dbName = $_ENV['DB_NAME'];
            $dbHost = $_ENV['DB_HOST'];
            $dsn = "$dbType:dbname=$dbName;host=$dbHost";
            $user = $_ENV['DB_USER'];
            $password = $_ENV['DB_PASSWORD'];
            self::$instance = new \PDO($dsn, $user, $password);
        }
        return self::$instance;
    }
}