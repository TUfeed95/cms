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
        $queryAllNameColumns = [];
        $checkTable = Database::getTable($this->tableName);
        $valueCheckTable = $checkTable->fetch(PDO::FETCH_ASSOC);
        // получаем текущее состояние таблицы
        $queryNameColumns = Database::getColumns($this->tableName);
        $queryNameColumnsRows = $queryNameColumns->fetchAll(PDO::FETCH_ASSOC);
        foreach ($queryNameColumnsRows as $row) {
            $queryAllNameColumns[] = $row['column_name'];
        }
        // если таблица существует то добавляем новые колонки
        if ($valueCheckTable['exists']) {
            // формируем массив с наименованием колонок из модели
            foreach ($listColumns as $column) {
                $nameColumns[] = $column['name'];
            }
            // сравниваем колонки текушей таблицы и модели и получаем разницу
            $addColumns = [];
            if (count($nameColumns) > count($queryAllNameColumns)) {
                $addColumns = array_diff($nameColumns, $queryAllNameColumns);
            } else {
                $addColumns = array_diff($queryAllNameColumns, $nameColumns);
            }

            print_r($nameColumns);
            print_r($queryAllNameColumns);
            print_r($addColumns);
            if ($addColumns) {
                // формируем условие по которому определяем добавляем или удаляем колонки из таблицы.
                // если в модели колонок больше чем в базе то добавляем, иначе удаляем
                if (count($nameColumns) > $queryNameColumns->rowCount()) {
                    $columnRows = [];
                    foreach ($listColumns as $columns) {
                        // добавляем только те колонки которых нет в текущей таблице.
                        if (in_array($columns['name'], $addColumns)) {
                            $columnRows[] = $columns;
                        }
                    }
                    $sql = Database::buildingQuery($columnRows, $this->tableName, ALTER_TABLE_ADD);
                } else if (count($nameColumns) < $queryNameColumns->rowCount()){
                    $columnRows = [];
                    foreach ($addColumns as $column) {
                        $columnRows[] = $column;
                    }
                    $sql = Database::buildingQuery($columnRows, $this->tableName, ALTER_TABLE_DROP);
                }
            } else {
                return null;
            }
        } else {
            $sql = Database::buildingQuery($listColumns, $this->tableName, CREATE_TABLE);
        }
        return $sql;
    }
}