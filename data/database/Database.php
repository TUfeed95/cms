<?php
class Database
{
    const DB_USER = 'root';
    const DB_USER_PASSWORD = 'root';
    const DB_NAME = 'cms_db';
    const DB_HOST = 'database';

    /**
     * Подключение к базе данных
     * @return PDO|void
     */
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

    /**
     * Проверяме существование таблицы.
     * @param $tableName string имя таблицы
     * @return bool|PDOStatement
     */
    public static function getTable($tableName): bool|PDOStatement
    {
        $conn = self::connection();
        $query = "SELECT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_NAME='" . self::DB_NAME . "' AND COLUMN_NAME = '" . $tableName . "')";
        $data = $conn->prepare($query);
        $data->execute();
        return $data;
    }
}
