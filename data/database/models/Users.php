<?php

namespace database\models;
require_once "Model.php";

class Users
{
    public static function add(): ?string
    {
        $dbModel = new Model("users");

        $id = $dbModel->column('id','serial', null,'PRIMARY KEY');
        $name = $dbModel->column('name','varchar', 255, false, 'NOT NULL');
        $login = $dbModel->column('login','varchar', 255, false, 'NOT NULL');
        $email = $dbModel->column('email','varchar', 255, false, 'NOT NULL');
        $listColumns = [$id, $name, $login, $email];
        return $dbModel->createQuery($listColumns);
    }
}