<?php
class Database
{
    const DB_USER = 'root';
    const DB_USER_PASSWORD = 'root';
    const DB_NAME = 'cms_db';
    const DB_HOST = 'database';
    public static function connection()
    {
        try {
            $dns = sprintf("pgsql:host='%s';port=5432;dbname='%s';user='%s';password='%s'", self::DB_HOST, self::DB_NAME, self::DB_USER, self::DB_USER_PASSWORD);
            return new PDO($dns);
        } catch (PDOException $e)
        {
            print "Ошибка подключения к базе данных: " . $e->getMessage();
            die();
        }
    }

    public static function getTable()
    {
        $conn = self::connection();
        $query = "SELECT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_NAME='" . Database::DB_NAME . "' AND COLUMN_NAME = '" . DB_MIGRATE_VERSIONS . "')";
    }
}
