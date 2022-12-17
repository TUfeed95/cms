<?php

namespace database\models;
require_once "Model.php";

class Users
{
    public static function add(): string
    {
        $dbModel = new Model("users");
        $id = $dbModel->column('id','serial', false,true);
        $name = $dbModel->column('name','varchar(255)', true);
        $login = $dbModel->column('login','varchar(255)', true);
        return $dbModel->createSqlRequest(array($id, $name, $login));
    }
}