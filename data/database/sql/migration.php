<?php
require_once '../Database.php';
$connection = Database::connection();
const DB_MIGRATE_VERSIONS = 'migrate_versions';
function getMigrationFile($connection): bool|array
{
    $sqlFolder = str_replace('\\', '/', realpath(dirname(__FILE__)) . '/');
    $allFiles = glob($sqlFolder . '*.sql');
    $migrateVersionsTable = Database::getTable(DB_MIGRATE_VERSIONS);
    $firstMigration = !$migrateVersionsTable->rowCount();

    if ($firstMigration) {
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
    $command = "psql -U " . Database::DB_USER . " -W '" . Database::DB_USER_PASSWORD . "' -h " . Database::DB_HOST . " -d " . Database::DB_NAME . " < " . $file;
    echo "\n";
    echo $command . "\n";
    popen($command, 'w');

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
        echo basename($file) . "\n";
    }
    echo "Миграция завершена.";
}