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
    $allFiles = glob($sqlFolder . '*.sql');
    $migrateVersionsTable = Database::getTable(DB_MIGRATE_VERSIONS);
    $migrateVersionsTableValue = $migrateVersionsTable->fetch(PDO::FETCH_ASSOC);

    // если false то создаем таблицу
    if (!$migrateVersionsTableValue['exists']) {
        echo "Создаем таблицу " . DB_MIGRATE_VERSIONS . "\n";
        $query = "CREATE TABLE " . DB_MIGRATE_VERSIONS . " (name varchar(255) NOT NULL)";
        $stmt = $connection->prepare($query);
        $stmt->execute();
    }

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

    return array_diff($allFiles, $versionsFile);
}

function migrate($connection, $file): void
{
    //$command = "PGPASSWORD=" . Database::DB_USER_PASSWORD . " psql -U " . Database::DB_USER . " -h " . Database::DB_HOST . " -d " . Database::DB_NAME . " < " . $file;
    //popen($command, 'w');

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
        echo basename($file) . " --- done.\n";
    }
    echo "Миграция завершена.\n";
}