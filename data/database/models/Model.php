<?php

namespace database\models;
use Database;
use PDO;
const CREATE_TABLE = 'createTable';
const DELETE_TABLE = 'deleteTable';
const ALTER_TABLE_ADD = 'alterTableAdd';
const ALTER_TABLE_DROP = 'alterTableDrop';
class Model
{


    function __construct(public $tableName)
    {

    }

    /**
     * Формируем массив с данными о колонках
     * @param $columnName
     * @param $typeColumn
     * @param $size
     * @param $primaryKey
     * @param $notNull
     * @return array
     */
    public function column($columnName, $typeColumn, $size=null, $primaryKey=null, $notNull=null): array
    {
        return [
            'name' => $columnName,
            'type' => $typeColumn,
            'size' => $size,
            'primaryKey' => $primaryKey,
            'notNull' => $notNull,
        ];
    }

    /**
     * В зависимости от условий будет формироваться добавление всей таблицы, если она новая,
     * либо определенных колонок, либо удаление колонок.
     * TODO придумать как удалять всю таблицу.
     * @param array $listColumns
     * @return string|null
     */
    public function createQuery(array $listColumns): ?string
    {
        $sql = '';
        $nameColumns = [];
        $checkTable = Database::getTable($this->tableName);
        $valueCheckTable = $checkTable->fetch(PDO::FETCH_ASSOC);
        // получаем текущее состояние таблицы
        $queryNameColumns = Database::getColumns($this->tableName);
        // сравниваем колонки текушей таблицы и модели и получаем разницу
        $addColumns = array_diff((array)$queryNameColumns, $nameColumns);
        // если таблица существует то добавляем новые колонки
        if ($valueCheckTable['exists']) {
            // формируем массив с наименованием колонок из модели
            foreach ($listColumns as $column) {
                $nameColumns += $column['name'];
            }
            if ($addColumns) {
                // формируем условие по которому определяем добавляем или удаляем колонки из таблицы.
                // если в модели колонок больше чем в базе то добавляем, иначе удаляем
                if (count($nameColumns) > count((array)$queryNameColumns)) {
                    foreach ($listColumns as $columns) {
                        // добавляем только те колонки которых нет в текущей таблице.
                        if (in_array($columns, $addColumns)) {
                            $sql = Database::buildingQuery($columns, $this->tableName, ALTER_TABLE_ADD);
                        }
                    }
                } else if (count($nameColumns) < count((array)$queryNameColumns)){
                    foreach ($listColumns as $columns) {
                        if (in_array($columns, $addColumns)) {
                            $sql = Database::buildingQuery($columns, $this->tableName, ALTER_TABLE_DROP);
                        }
                    }
                }
            }
        } else {
            foreach ($listColumns as $columns) {
                if (in_array($columns, $addColumns)) {
                    $sql = Database::buildingQuery($columns, $this->tableName, CREATE_TABLE);
                }
            }
        }
        return $sql;
    }
}