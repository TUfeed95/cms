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
        $email1 = $dbModel->column('email1','varchar', 255, false, 'NOT NULL');
        $email2 = $dbModel->column('email2','varchar', 255, false, 'NOT NULL');
        $email3 = $dbModel->column('email3','varchar', 255, false, 'NOT NULL');
        $listColumns = [$id, $name, $login, $email, $email1, $email2, $email3];
        return $dbModel->createQuery($listColumns);
    }
}