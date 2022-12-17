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
            $dsn = sprintf("pgsql:host='%s';port=5432;dbname='%s';user='%s';password='%s'", self::DB_HOST, self::DB_NAME, self::DB_USER, self::DB_USER_PASSWORD);
            return new PDO($dsn);
        } catch (PDOException $e)
        {
            echo "Ошибка подключения к базе данных: " . $e->getMessage();
            die();
        }
    }

    /**
     * Существует ли таблица.
     * Если таблица не существует то возвращает пустое значение (в прямом смылсе этого слова).
     * Иначе возвращает 1.
     * @param string $tableName Имя таблицы
     * @return bool|PDOStatement
     */
    public static function getTable(string $tableName): bool|PDOStatement
    {
        $conn = self::connection();
        $query = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE TABLE_NAME = '" . $tableName . "')";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
