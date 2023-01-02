<?php
class Model
{
    public string $tableName;

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

    public function tableName(): string
    {
        return $this->tableName;
    }
}