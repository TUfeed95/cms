<?php

require_once 'Database.php';
require_once 'models/Users.php';
class Migration
{
    // таблица миграций
    const DB_MIGRATE_VERSIONS = 'migrate_versions';

    /**
     * Получаем файлы
     * @param $folder
     * @param $fileExtensions
     * @return array|false
     */
    function getFiles($folder, $fileExtensions): bool|array
    {
        $path = self::getFolder($folder);
        return glob($path . $fileExtensions);
    }

    /**
     * Получаем директорию
     * @param $folder
     * @return array|string|string[]
     */
    function getFolder($folder): array|string
    {
        return str_replace('\\', '/', realpath(dirname(__FILE__)) . $folder . '/');
    }

    /**
     * Создания фалов миграций
     * @return void
     */
    function init(): void
    {
        $dateTime = new DateTime();
        $query = Users::add();

        if (!is_null($query)) {
            $nameFileMigration = $dateTime->format('Y_m_d_his') . ".sql";
            echo "Инициализация миграций.\n";
            try {
                file_put_contents($nameFileMigration, $query);
                echo "Создан файл миграции: " . $nameFileMigration . "\n";
            } catch (Exception $exception) {
                echo "Ошибка при создании файла миграции: " . $exception  . "\n";
            }
        }
    }

    /**
     * @param $connection PDO|null
     * @return bool|array
     */
    function getMigrationFile(): bool|array
    {
        self::init();
        $connection = Database::connection();
        $sqlFolder = self::getFolder('migrations');
        // файлы миграций
        //$allFiles = glob($sqlFolder . '*.sql');
        $allFiles = self::getFiles('migrations', '*.sql');
        // проверяем наличие таблицы DB_MIGRATE_VERSIONS
        $migrateVersionsTable = Database::getTable(self::DB_MIGRATE_VERSIONS);
        // получаем сторку в виде массива проиндексированного по имени столбца
        $migrateVersionsTableRow = $migrateVersionsTable->fetch(PDO::FETCH_ASSOC);

        if (!$migrateVersionsTableRow['exists']) {
            echo "Создаем таблицу " . self::DB_MIGRATE_VERSIONS . "\n";
            $query = "CREATE TABLE " . self::DB_MIGRATE_VERSIONS . " (name varchar(255) NOT NULL)";
            $stmt = $connection->prepare($query);
            $stmt->execute();
        }
        // если таблица пустая то возвращем все файлы миграций
        if (!$migrateVersionsTable->rowCount()) {
            return $allFiles;
        }

        $versionsFile = array();

        $query = "select name from " . self::DB_MIGRATE_VERSIONS;
        $data = $connection->prepare($query);
        $data->execute();
        $rows = $data->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $versionsFile[] = $sqlFolder . $row['name'];
        }
        // сравниваем миграции в таблицы и в директории
        return array_diff($allFiles, $versionsFile);
    }

    function migrate($file): bool
    {
        $connection = Database::connection();
        $sqlMigrate = file_get_contents($file);
        try {
            $migrate = $connection->prepare($sqlMigrate);
            $migrate->execute();
        } catch (PDOException $exception) {
            echo "Во время миграции произошла ошибка: " . $exception . "\n";
            return false;
        }

        $baseName = basename($file);
        $query = "insert into " . self::DB_MIGRATE_VERSIONS . " (name) values ('" . $baseName . "')";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        return true;
    }



}