<?php

namespace models;
use Model;
require_once "../database/Model.php";

class Users
{
    public static function add(): ?string
    {
        $dbModel = new Model('users');

        $id = $dbModel->column('id', 'serial', null, 'PRIMARY KEY');
        $name = $dbModel->column('name', 'varchar', 255, false, 'NOT NULL');
        $login = $dbModel->column('login', 'varchar', 255, false, 'NOT NULL');
        $email = $dbModel->column('email', 'varchar', 255, false, 'NOT NULL');
        $password = $dbModel->column('password', 'varchar', 255, false, 'NOT NULL');
        $listColumns = [$id, $name, $login, $email, $password];
        return $dbModel->createQuery($listColumns);
    }
}