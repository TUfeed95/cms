<?php
class Model
{
    /**
     * Наименование колонки
     */
    private $getColumnName = '';
    /**
     * Наименование таблицы
     */
    public $getTableName = '';
    /**
     * Тип поля в таблице
     */
    private $getTypeColumn = '';
    /**
     * Не нулевое значение поля
     */
    private $getNotNull = '';
    /**
     * Уникальные значения поля
     */
    private $getPrimaryKey = '';

    /**
     * @param string $tableName Наименование таблицы
     */
    function __construct($tableName)
    {
        $this->getTableName = $tableName;
    }

    private function clearParams()
    {
        $this->getPrimaryKey = '';
        $this->getNotNull = '';
        $this->getTableName = '';
        $this->getTypeColumn = '';
    }

    /**
     * Формируем массив с данными о колонках
     * @param $columnName Наименование колонки
     * @return string
     */
    public function column($columnName)
    {
        $this->getColumnName = $columnName;
        $add = sprintf('%s %s %s %s', $this->getColumnName, $this->getTypeColumn, $this->getNotNull, $this->getPrimaryKey);
        self::clearParams();

        return $add;
        /** 
        *return [
        *    'name' => $this->getColumnName,
        *    'type' => $this->getTypeColumn,
        *    'primaryKey' => $this->getPrimaryKey,
        *   'notNull' => $this->getNotNull,
        *]; 
        */
    }

    /**
     * Целочисленный тип данных.
     * 
     * Типы smallint, integer, и bigint хранят целые числа, то есть числа без дробных частей, различных диапазонов. 
     * Попытки сохранить значения за пределами допустимого диапазона приведут к ошибке.
     * По умолчанию INTEGER
     * 
     * @param string $type Тип целочисленных данных
     */
    public function integer($type = 'INTEGER')
    {
        switch (strtoupper($type)) {
            case 'SMALLINT':
            case 'BIGINT':
            case 'INTEGER':
                $this->getTypeColumn = strtoupper($type);
                return $this;
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
            return $this->getTypeColumn =  sprintf('NUMERIC(%s, %s)', $precision, $scale);
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
            $this->getTypeColumn = 'REAL';
            return $this;
        } else {
            $this->getTypeColumn ='DOUBLE PRECISION';
            return $this;
        }
    }

    /**
     * Типы данных small serial, serial и bigserial.
     * 
     * @param string $type Тип данных
     */
    public function serial($type)
    {
        switch (strtoupper($type)) {
            case 'SMALSERIAL':
            case 'SERIAL':
            case 'BIGSERIAL':
                $this->getTypeColumn = strtoupper($type);
                return $this;
        }
    }

    /**
     * Символьный тип. Принимает псевдоним типа: varchar, char и text.
     * 
     * @param string $type Псевдоним типа: varchar, char и text.
     * @param integer $size Ограничение по кол-ву символов (по умолчанию 255). Игнорируется при $type = 'text'
     */
    public function character($type, $size = 255)
    {
        if (!is_null($size) && strtoupper($type) !== 'TEXT'){
            switch (strtoupper($type)) {
                case 'VARCHAR':
                case 'CHAR':
                    $this->getTypeColumn = sprintf('%s(%s)', strtoupper($type), $size);
                    return $this;
            }
        } else {
            $this->getTypeColumn = sprintf('TEXT');
            return $this;
        }
    }

    /**
     * Уникальное значение
     */
    public function primaryKey()
    {
        $this->getPrimaryKey = 'PRIMARY KEY';
        return $this;
    }
    /**
     * Не нулевое значение
     */
    public function isNotNull() 
    {
        $this->getNotNull = 'NOT NULL';
        return $this;
    }
}