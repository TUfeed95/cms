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
     * Строим sql запросы
     * @param array $columns
     * @param $tableName
     * @param $typeOfAction
     * @return string
     */
    public static function buildingQuery(array $columns, $tableName, $typeOfAction): string
    {
        $sql = '';
        switch ($typeOfAction) {
            case 'createTable':
                $sql = "CREATE TABLE " . $tableName . "( ";
                foreach ($columns as $column) {
                    if ($column != end($columns)){
                        switch ($column['type']) {
                            case 'serial':
                                $sql .= $column['name'] . " " . $column['type'] . " " . $column['primaryKey'];
                                break;
                            case 'varchar':
                                $sql .= $column['name'] . " " . $column['type'] . "(". $column['size'] .")" . " " . $column['notNull'];
                                break;
                        }
                    }
                }
                $sql .= ");";
                break;
            case 'deleteTable':
                $sql = "DELETE FROM " . $tableName;
                break;
            case 'alterTableAdd':
                foreach ($columns as $column) {
                    $sql = "ALTER TABLE " . $tableName . " ADD COLUMN " . $column['name'] . " " .
                        $column['type'] . "( " . $column['size'] . " );\n";
                }
                break;
            case 'alterTableDrop':
                foreach ($columns as $column) {
                    $sql = "ALTER TABLE " . $tableName . " DROP COLUMN " . $column['name'] . "\n";
                }
                break;
        }

        return $sql;
    }
}
