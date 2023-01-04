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

    /**
     * Целочисленный тип данных.
     * 
     * Типы smallint, integer, и bigint хранят целые числа, то есть числа без дробных частей, различных диапазонов. 
     * Попытки сохранить значения за пределами допустимого диапазона приведут к ошибке.
     * 
     * @param string $type Тип целочисленных данных
     * @return string
     */
    public function integer($type = null): string
    {
        switch ($type) {
            case 'smallint':
                return $type;
            case 'bigint':
                return $type;
            default:
                return 'integer';
        }
    }

    /**
     * Тип numeric. Может хранить числа с очень большим количеством цифр.
     * Типы decimal и numeric эквивалентны. Оба типа являются частью стандарта SQL.
     * 
     * @param integer $precision Общее количество значащих цифр в целом числе, то есть количество цифр по обе стороны от десятичной точки. По умолчанию 0.
     * @param integer $scale Количество десятичных цифр в дробной части справа от десятичной точки. По умолчанию 0.
     */
    public function numeric($precision = 0, $scale = 0) 
    {
        if ($precision >= 0 && $scale >= 0) {
            return sprintf('numeric(%s, %s)', $precision, $scale);
        }
    }

    /**
     * Типы данных real и double precision являются неточными числовыми типами переменной точности.
     * 
     * @param boolean $doublePrecision Двойная точность, по умолчанию false.
     */
    public function real($doublePrecision = false) 
    {
        if (!$doublePrecision) {
            return 'real';
        } else {
            return 'double precision';
        }
    }
}