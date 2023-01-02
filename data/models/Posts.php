<?php

class Posts
{
    public string $tableName;
    public function columns(): array
    {
        $dbModel = new Model();
        $this->tableName = $dbModel->tableName = 'posts';
        return [
            "id" => $dbModel->column('id', 'serial', null, 'PRIMARY KEY'),
            "name" => $dbModel->column('name', 'varchar', 255, false, 'NOT NULL'),
            "test" => $dbModel->column('test', 'varchar', 255, false, 'NOT NULL'),
            "test2" => $dbModel->column('test2', 'varchar', 255, false, 'NOT NULL'),
            //"test3" => $dbModel->column('test3', 'varchar', 255, false, 'NOT NULL'),
        ];
    }
}