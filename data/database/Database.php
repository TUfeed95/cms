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

    /**
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
     * Создаем таблицу
     * @param $name string наименование таблицы
     * @param $columns array колонки
     * @return string
     */
    public static function createTable(string $name, array $columns): string
    {
        $sql = "CREATE TABLE " . $name . "( ";
        foreach ($columns as $column) {
            switch ($column['type']) {
                case 'serial':
                    if ($column != end($columns)) {
                        $sql .= $column['name'] . " " . $column['type'] . " " . $column['primaryKey'] . ", ";
                    } else {
                        $sql .= $column['name'] . " " . $column['type'] . " " . $column['primaryKey'] . " ";
                    }
                    break;
                case 'varchar':
                    if ($column != end($columns)){
                        $sql .= $column['name'] . " " . $column['type'] . "(". $column['size'] .")" . " " . $column['notNull'] . ", ";
                    } else {
                        $sql .= $column['name'] . " " . $column['type'] . "(". $column['size'] .")" . " " . $column['notNull'] . " ";
                    }
                    break;
            }
        }
        $sql .=  ");";
        return $sql;
    }

    /**
     * Добавляем колонки
     * @param $tableName string наименование таблицы
     * @param $columns array колонки
     * @return string
     */
    public static function addColumns(string $tableName, array $columns): string
    {
        $sql = "ALTER TABLE "  . $tableName;
        foreach ($columns as $column) {
            if ($column != end($columns)) {
                $sql .= " ADD COLUMN " . $column['name'] . " " .
                    $column['type'] . "(" . $column['size'] . "), ";
            } else {
                $sql .= " ADD COLUMN " . $column['name'] . " " .
                    $column['type'] . "(" . $column['size'] . "); ";
            }
        }
        return $sql;
    }

    /**
     * Удаляем колонки
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

}
