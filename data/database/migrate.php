<?php

require_once "Migration.php";

$migrations = new Migration();

// создаем файл загрузки моделей
$classNames = $migrations->getFiles('/models', '*.php', dirname(__DIR__));
$migrations->createLoadClassesFile($classNames);

try {
    $migrations->createMigrationFiles();
} catch (ReflectionException $e) {
    echo "  => Произошла ошибка при создании файлов миграции: " . $e;
}
try {
    $migrationFiles = $migrations->getMigrationFile();
} catch (ReflectionException $e) {
    echo "  => Произошла ошибка при получении файлов миграции: " . $e;
}

if (empty($migrationFiles)) {
    echo "  => Новых миграций не найдено.\n";
} else {
    echo "  => Начинаем миграцию...\n";
    foreach ($migrationFiles as $file) {
        if ($migrations->migrate($file)) {
            echo "  => " . basename($file) . " ---> \033[32m ОК \033[0m \n";
        } else {
            echo "  => " . basename($file) . " ---> \033[31m ERROR \033[0m \n";
        }
    }
    echo "Миграция завершена.\n";
}
