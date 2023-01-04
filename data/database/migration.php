<?php
require_once 'Database.php';
require_once 'Model.php';


class Migration
{
    // таблица миграций
    const DB_MIGRATE_VERSIONS = 'migrate_versions';
    public string $tableName;

    /**
     * Получаем файлы
     * @param $folder
     * @param $fileExtensions
     * @param $dirname
     * @return array|false
     */
    function getFiles($folder, $fileExtensions, $dirname): bool|array
    {
        $path = self::getFolder($folder, $dirname);
        return glob($path . '/' . $fileExtensions);
    }

    /**
     * Получаем директорию
     * @param $folder
     * @param $dirname
     * @return array|string|string[]
     */
    function getFolder($folder, $dirname): array|string
    {
        return str_replace('\\', '/', realpath($dirname . $folder . '/'));
    }

    /**
     * Загрузка класса моделей
     * @param $classNames
     */
    function createLoadClassesFile($classNames): void
    {
        $create_require_once = "<?php\n";

        foreach ($classNames as $className) {
            $create_require_once .= "require_once '../models/" . basename($className) . "';\n";
        }

        file_put_contents(self::getFolder('', dirname(__FILE__)) . '/loadModels.php', $create_require_once);
    }

    /**
     * Создания файлов миграций
     * @return void
     * @throws ReflectionException
     */
    function createMigrationFiles(): void
    {
        echo "Инициализация миграций:\n";
        $dateTime = new DateTime();
        // получем список моделей
        $models = self::getFiles('/models', '*.php', dirname(__DIR__));
        //print_r($models);
        require_once 'loadModels.php'; // загружаем здесь, что бы не было ошибки, при котрой отсутсвует загруженный класс модели
        // обходим модели и создаем файлы миграций
        foreach ($models as $model) {
            $modelClass = new ReflectionClass(basename($model, '.php')); // получаем класс модели
            $modelClassColumns = $modelClass->newInstanceArgs(); // создаем экземпляр класса модели
            $modelClassColumnsArray = $modelClassColumns->columns(); // вызываем метод класса модели
            $this->tableName = $modelClassColumns->tableName; // получем наименование таблицы модели
            $query = self::createQuery($modelClassColumnsArray); // получем сгенерированный sql запрос
            // если запрос не пустой
            if (!is_null($query)) {
                echo "  => Найдена модель: " . basename($model, ".php") . "\n";
                // генерация случайных шестнадцатеричных строк, для уникальности имени файла миграции
                // для более случайной генерции добавляем имя модели, иначе если создается несколько файлов миграций с одинаковым именем
                $rangeText = substr(md5('327CH4jHISdwJ77F' . basename($model, '.php')), 0, 10); 
                // имя файла миграции, сотоит из текущей даты, времени и случайной строки
                $nameFileMigration = $dateTime->format('Y_m_d_his') . "_" . $rangeText . ".sql";
                try {
                    file_put_contents(self::getFolder('/migrations', dirname(__FILE__)) . '/' . $nameFileMigration, $query);
                    echo "  => Создан файл миграции: \033[33m" . $nameFileMigration . "\033[0m\n";
                } catch (Exception $exception) {
                    echo "  => Ошибка при создании файла миграции: " . $exception  . "\n";
                }
            }
        }
    }

    /**
     * Получаем файлы миграций
     * @return array|bool
     */
    function getMigrationFile(): bool|array
    {
        $connection = Database::connection();
        // файлы миграций
        $allFiles = self::getFiles('/migrations', '*.sql', dirname(__FILE__));
        //print_r('----> ');
        //print_r($allFiles);
        // проверяем наличие таблицы DB_MIGRATE_VERSIONS
        $migrateVersionsTable = Database::getTable(self::DB_MIGRATE_VERSIONS);
        // получаем сторку в виде массива проиндексированного по имени столбца
        $migrateVersionsTableRow = $migrateVersionsTable->fetch(PDO::FETCH_ASSOC);

        if (!$migrateVersionsTableRow['exists']) {
            echo "  => Создаем таблицу " . self::DB_MIGRATE_VERSIONS . "\n";
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
            $versionsFile[] = $row['name'];
        }
        // сравниваем миграции в таблицы и в директории
        $allFilesName = [];
        foreach ($allFiles as $fileName) {
                $allFilesName[] = basename($fileName);
        }
        //print_r('----->');
        //print_r($allFilesName);
        //print_r($versionsFile);
        return array_diff($allFilesName, $versionsFile);
    }

    function migrate($file): bool
    {
        $connection = Database::connection();
        $sqlMigrate = file_get_contents(self::getFolder('/migrations', dirname(__FILE__)) . '/' . $file);
        try {
            $migrate = $connection->prepare($sqlMigrate);
            $migrate->execute();
        } catch (PDOException $exception) {
            echo "  => Во время миграции произошла ошибка: " . $exception . "\n";
            return false;
        }

        $baseName = basename($file);
        $query = "insert into " . self::DB_MIGRATE_VERSIONS . " (name) values ('" . $baseName . "')";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        return true;
    }

    /**
     * В зависимости от условий будет формироваться добавление всей таблицы, если она новая,
     * либо определенных колонок, либо удаление колонок.
     * TODO придумать как удалять всю таблицу.
     * @param array $listColumns
     * @return string|null
     */
    public function createQuery(array $listColumns): ?string
    {
        $sql = '';

        // получаем текущее состояние таблицы
        $queryNameColumns = Database::getColumns($this->tableName);
        $queryNameColumnsRows = $queryNameColumns->fetchAll(PDO::FETCH_ASSOC);

        $queryAllNameColumns = [];
        foreach ($queryNameColumnsRows as $row) {
            $queryAllNameColumns[] = $row['column_name']; // массив с наименованием колонок из базы
        }

        // если таблица существует то добавляем новые колонки
        $checkTable = Database::getTable($this->tableName);
        $valueCheckTable = $checkTable->fetch(PDO::FETCH_ASSOC);
        if ($valueCheckTable['exists']) {
            // формируем массив с наименованием колонок из модели
            $nameColumns = [];
            foreach ($listColumns as $column) {
                $nameColumns[] = $column['name']; // массив с наименованием колонок из модели
            }
            // сравниваем колонки текушей таблицы и модели и получаем разницу
            $addColumns = [];
            if (count($nameColumns) > count($queryAllNameColumns)) {
                // если в модели есть новые столбцы
                $addColumns = array_diff($nameColumns, $queryAllNameColumns);
            } else {
                // если в модели были удалены столбцы
                $addColumns = array_diff($queryAllNameColumns, $nameColumns);
            }
            // если есть разница
            if ($addColumns) {
                // формируем условие по которому определяем добавляем или удаляем колонки из таблицы.
                // если в модели колонок больше, чем в базе, то добавляем...
                if (count($nameColumns) > $queryNameColumns->rowCount()) {
                    $columnRows = [];
                    foreach ($listColumns as $columns) {
                        // добавляем только те колонки которых нет в текущей таблице.
                        if (in_array($columns['name'], $addColumns)) {
                            $columnRows[] = $columns;
                        }
                    }
                    $sql = Database::addColumns($this->tableName, $columnRows);
                    // ...иначе удаляем
                } else if (count($nameColumns) < $queryNameColumns->rowCount()){
                    $columnRows = [];
                    // удаляем колонки которых нет в модели, для этого формируем массив с наименованием столбцов из базы
                    foreach ($addColumns as $column) {
                        $columnRows[] = $column;
                    }
                    $sql = Database::removeColumns($this->tableName, $columnRows);
                }
            } else {
                return null;
            }
        } else {
            $sql = Database::createTable($this->tableName, $listColumns);
        }
        return $sql;
    }

}