<?php

namespace database\models;
use Database;

class Model
{


    function __construct(public $tableName)
    {

    }

    /**
     * Формируем маасив с данным для солоки в тадлице
     * @param $columnName
     * @param $typeColumn
     * @param $size
     * @param $primaryKey
     * @param $notNull
     * @return array
     */
    public function column($columnName, $typeColumn, $size=null, $primaryKey=null, $notNull=null): array
    {
        $column = [];
        switch ($typeColumn) {
            case "serial":
                $column = [
                    'name' => $columnName,
                    'type' => $typeColumn,
                    'primaryKey' => $primaryKey,
                ];
                break;
            case "varchar":
                $column = [
                    'name' => $columnName,
                    'type' => $typeColumn,
                    'size' => $size,
                    'notNull' => $notNull,
                ];
                break;
        }
        return $column;
    }

    /**
     * Формируем sql запрос. В зависимости от условий будет формироваться добавление всей таблицы, если она новая,
     * либо определенных колонок, либо удаление колонок.
     * TODO придумать как удалять всю таблицу.
     * @param array $listColumns
     * @return string
     */
    public function createSqlRequest(array $listColumns): string
    {
        $sql = '';

        $nameColumns = [];
        // формируем массив с наименованием колонок из модели
        foreach ($listColumns as $column) {
            $nameColumns += $column['name'];
        }
        // получаем текущее состояние таблицы
        $queryNameColumns = Database::getColumns($this->tableName);
        // сравниваем колонки текушей таблицы и модели
        $addColumns = array_diff((array)$queryNameColumns, $nameColumns);
        if ($addColumns) {
            // формируем условие по которому определяем добавляем или удаляем колонки из таблицы.
            // если в модели колонок больше чем в базе то добавляем иначе удаляем
            if (count($nameColumns) > count((array)$queryNameColumns)) {
                foreach ($listColumns as $columns) {
                    foreach ($columns as $column) {
                        // добавляем только те колонки которых нет в текущей таблице.
                        if (in_array($columns, $addColumns)) {
                            $sql = "ALTER TABLE " . $this->tableName .  " ADD COLUMN " . $column['name'] . " " .
                                $column['type'] . "( " . $column['size'] . " );\n";
                        }
                    }
                }
            } else {
                foreach ($listColumns as $columns) {
                    foreach ($columns as $column) {
                        if (in_array($columns, $addColumns)) {
                            $sql = "ALTER TABLE " . $this->tableName . " DROP COLUMN " . $column['name'] . "\n";
                        }
                    }
                }
            }
        }
        return $sql;
    }
}