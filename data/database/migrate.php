<?php

require_once "Migration.php";

$migrations = new Migration();
$migrationFiles = $migrations->getMigrationFile();

if (empty($migrationFiles)) {
    echo "Новых миграций не найдено.\n";
} else {
    echo "Начинаем миграцию...\n";
    foreach ($migrationFiles as $file) {
        if ($migrations->migrate($file)) {
            echo basename($file) . " --- ОК.\n";
        } else {
            echo basename($file) . " --- ERROR.\n";
        }
        echo "Миграция завершена.\n";
    }
}
