<?php

class Tests
{
    public $tableName;

    public function up(): array
    {
        $dbModel = new Model('tests');
        $this->tableName = $dbModel->getTableName;
        
        return [
            "id" => $dbModel->serial('serial')->isNotNull()->primaryKey()->column('id'),
            "test" => $dbModel->character('varchar', 40)->isNotNull()->column('test'),
        ];
    }
}