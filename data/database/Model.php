<?php
require_once "Database.php";
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

        // получаем текущее состояние таблицы
        $queryNameColumns = Database::getColumns($this->tableName);
        $queryNameColumnsRows = $queryNameColumns->fetchAll(PDO::FETCH_ASSOC);

        $queryAllNameColumns = [];
        foreach ($queryNameColumnsRows as $row) {
            $queryAllNameColumns[] = $row['column_name']; // массив с наименованием колонок из базы
        }

        // если таблица существует то добавляем новые колонки
        $checkTable = Database::getTable($this->tableName);
        $valueCheckTable = $checkTable->fetch(PDO::FETCH_ASSOC);
        if ($valueCheckTable['exists']) {
            // формируем массив с наименованием колонок из модели
            $nameColumns = [];
            foreach ($listColumns as $column) {
                $nameColumns[] = $column['name']; // массив с наименованием колонок из модели
            }
            // сравниваем колонки текушей таблицы и модели и получаем разницу
            $addColumns = [];
            if (count($nameColumns) > count($queryAllNameColumns)) {
                // если в модели есть новые столбцы
                $addColumns = array_diff($nameColumns, $queryAllNameColumns);
            } else {
                // если в модели были удалены столбцы
                $addColumns = array_diff($queryAllNameColumns, $nameColumns);
            }
            // если есть разница
            if ($addColumns) {
                // формируем условие по которому определяем добавляем или удаляем колонки из таблицы.
                // если в модели колонок больше, чем в базе, то добавляем...
                if (count($nameColumns) > $queryNameColumns->rowCount()) {
                    $columnRows = [];
                    foreach ($listColumns as $columns) {
                        // добавляем только те колонки которых нет в текущей таблице.
                        if (in_array($columns['name'], $addColumns)) {
                            $columnRows[] = $columns;
                        }
                    }
                    $sql = Database::addColumns($this->tableName, $columnRows);
                    // ...иначе удаляем
                } else if (count($nameColumns) < $queryNameColumns->rowCount()){
                    $columnRows = [];
                    // удаляем колонки которых нет в модели, для этого формируем массив с наименованием столбцов из базы
                    foreach ($addColumns as $column) {
                        $columnRows[] = $column;
                    }
                    $sql = Database::removeColumns($this->tableName, $columnRows);
                }
            } else {
                return null;
            }
        } else {
            $sql = Database::createTable($this->tableName, $listColumns);
        }
        return $sql;
    }
}