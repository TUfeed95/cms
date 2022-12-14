<?php

class Users
{
    public $tableName;

    public function up(): array
    {
        $dbModel = new Model('users');
        $this->tableName = $dbModel->getTableName;
        
        return [
            "id" => $dbModel->serial('serial')->isNotNull()->primaryKey()->column('id'),
            "name" => $dbModel->character('varchar', 40)->isNotNull()->column('name'),
            "name1" => $dbModel->character('varchar', 40)->isNotNull()->column('name1'),
            "name4" => $dbModel->character('varchar', 40)->isNotNull()->column('name4'),
        ];
    }
}
