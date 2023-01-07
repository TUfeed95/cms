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
     * Получаем наименование типа колонки 
     */
    public static function getTypeFromString($typeFromString)
    {
        $pattern = '[\((0-9)+\)]';
        return mb_ereg_replace($pattern, '', $typeFromString);
    }

    /**
     * Существует ли таблица.
     * Если таблица не существует то возвращает пустое значение (в прямом смысле этого слова).
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
    /**
     * Список всех таблиц в базе
     */
    public static function showTables()
    {
        $conn = self::connection();
        $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $tables;
    }

    /**
     * Получаем колонки переданной таблицы
     * @param $tableName
     * @return bool|PDOStatement
     */
    public static function getColumns($tableName): bool|PDOStatement
    {
        $conn = self::connection();
        $query = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $tableName . "'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Создаем таблицу (sql запрос)
     * @param $tableName наименование таблицы
     * @param $columns колонки
     * @return string
     */
    public static function createTable(string $tableName, array $columns): string
    {
        $sql = "CREATE TABLE " . $tableName . " (";
        foreach ($columns as $column) {
            if ($column != end($columns)) {
                $sql .= $column . ", ";
            } else {
                $sql .= $column;
            }
        }
        $sql .=  ");";
        return $sql;
    }

    /**
     * Добавляем колонки (sql запрос)
     * @param $tableName string наименование таблицы
     * @param $columns array колонки
     * @return string
     */
    public static function addColumns(string $tableName, array $columns): string
    {
        $sql = "ALTER TABLE "  . $tableName;
        foreach ($columns as $column) {
            if ($column != end($columns)) {
                $sql .= " ADD COLUMN " . $column . ", ";
            } else {
                $sql .= " ADD COLUMN " . $column;
            }
        }
        $sql .=  ";";
        return $sql;
    }

    /**
     * Удаляем колонки (sql запрос)
     * @param $tableName string наименование таблицы
     * @param $columns array колонки
     * @return string
     */
    public static function removeColumns(string $tableName, array $columns): string
    {
        $sql = "ALTER TABLE " . $tableName;
        foreach ($columns as $column) {
            if ($column != end($columns)) {
                $sql .= " DROP COLUMN " . $column . ", ";
            } else {
                $sql .= " DROP COLUMN " . $column;
            }
        }
        return $sql;
    }

    public static function removeTable($table)
    {
        $sql = "DROP TABLE " . $table;
        return $sql;
    }

}
