<?php
require_once '../Database.php';
require_once '../models/Users.php';

use database\models\Users;

$connection = Database::connection();
const DB_MIGRATE_VERSIONS = 'migrate_versions';

function init(): void
{
    $dateTime = new DateTime();
    $query = Users::add();
    if ($query) {
        $nameFileMigration = $dateTime->format('Y_m_d_his') . ".sql";
        echo "Инициализация миграций.\n";
        try {
            file_put_contents($nameFileMigration, $query);
            echo "Создан файл миграции: " . $nameFileMigration . "\n";
        } catch (Exception $exception) {
            echo "Ошибка при создании фала миграции: " . $exception  . "\n";
        }
    }




}

/**
 * @param $connection PDO|null
 * @return bool|array
 */
function getMigrationFile(?PDO $connection): bool|array
{
    $sqlFolder = str_replace('\\', '/', realpath(dirname(__FILE__)) . '/');
    // файлы миграций
    $allFiles = glob($sqlFolder . '*.sql');
    // проверяем наличие таблицы DB_MIGRATE_VERSIONS
    $migrateVersionsTable = Database::getTable(DB_MIGRATE_VERSIONS);
    // получаем сторку в виде массива проиндексированного по имени столбца
    $migrateVersionsTableRow = $migrateVersionsTable->fetch(PDO::FETCH_ASSOC);

    if (!$migrateVersionsTableRow['exists']) {
        echo "Создаем таблицу " . DB_MIGRATE_VERSIONS . "\n";
        $query = "CREATE TABLE " . DB_MIGRATE_VERSIONS . " (name varchar(255) NOT NULL)";
        $stmt = $connection->prepare($query);
        $stmt->execute();
    }
    // если таблица пустая то возвращем все файлы миграций
    if (!$migrateVersionsTable->rowCount()) {
        return $allFiles;
    }

    $versionsFile = array();

    $query = "select name from " . DB_MIGRATE_VERSIONS;
    $data = $connection->prepare($query);
    $data->execute();
    $rows = $data->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $versionsFile[] = $sqlFolder . $row['name'];
    }
    // сравниваем миграции в таблицы и в директории
    return array_diff($allFiles, $versionsFile);
}

function migrate($connection, $file): bool
{
    $sqlMigrate = file_get_contents($file);
    try {
        $migrate = $connection->prepare($sqlMigrate);
        $migrate->execute();
    } catch (PDOException $exception) {
        echo "Во время миграции произошла ошибка: " . $exception . "\n";
        return false;
    }

    $baseName = basename($file);
    $query = "insert into " . DB_MIGRATE_VERSIONS . " (name) values ('" . $baseName . "')";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    return true;
}

init();
$migrationFiles = getMigrationFile($connection);

if (empty($migrationFiles)) {
    echo "Новых миграций не найдено.\n";
} else {
    echo "Начинаем миграцию...\n";
    foreach ($migrationFiles as $file) {
        if (migrate($connection, $file)) {
            echo basename($file) . " --- ОК.\n";
        }
        echo "Миграция завершена.\n";
    }
}