<?php

namespace database\models;

class Model
{


    function __construct(public $tableName)
    {
        $this->tableName = $tableName;
    }

    public function column($columnName, $typeColumn, $notNull=false, $primaryKey=false): ?string
    {
        switch ($typeColumn)
        {
            case 'serial':
                if ($primaryKey && !$notNull)
                {
                    return $columnName . " " . $typeColumn . " PRIMARY KEY";
                }
                break;
            case 'varchar(255)':
                if ($notNull) {
                    return $columnName . " " . $typeColumn . " NOT NULL";
                } else {
                    return $columnName . " " . $typeColumn;
                }
        }
        return null;
    }

    public function createSqlRequest(array $columns): string
    {
        $sql = "CREATE TABLE " . $this->tableName . " (";
        // обходим массив и добавляем колонки в sql запрос
        foreach ($columns as $column) {
            // если элемент в массиве не последний то ставим запятую, иначе не ставим
            if ($column != end($columns)) {
                $sql .= $column . ",";
            } else {
                $sql .= $column;
            }
        }
        $sql .= ");";

        return $sql;
    }
}