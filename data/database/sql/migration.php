<?php
require_once '../Database.php';
$connection = Database::connection();
const DB_MIGRATE_VERSIONS = 'migrate_versions';

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

function migrate($connection, $file): void
{
    $sqlMigrate = file_get_contents($file);
    $migrate = $connection->prepare($sqlMigrate);
    $migrate->execute();

    $baseName = basename($file);
    $query = "insert into " . DB_MIGRATE_VERSIONS . " (name) values ('" . $baseName . "')";
    $stmt = $connection->prepare($query);
    $stmt->execute();
}

$migrationFiles = getMigrationFile($connection);

if (empty($migrationFiles)) {
    echo "Новых миграций не найдено.\n";
} else {
    echo "Начинаеи миграцию...\n";

    foreach ($migrationFiles as $file) {
        migrate($connection, $file);
        echo basename($file) . " --- ОК.\n";
    }
    echo "Миграция завершена.\n";
}