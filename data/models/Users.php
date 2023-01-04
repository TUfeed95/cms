<?php

class Users extends Model
{
    public string $tableName;
    public function columns(): array
    {
        $dbModel = new Model();
        $this->tableName = $dbModel->tableName = 'users';
        return [
            "id" => $dbModel->column('id', 'serial', null, 'PRIMARY KEY'),
            "name" => $dbModel->column('name', 'varchar', 255, false, 'NOT NULL'),
            "name" => $dbModel->column('name', self::real(true), 255, false, 'NOT NULL'),
        ];
    }
}
