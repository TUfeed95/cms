<?php

require_once 'database/Database.php';
require_once 'database/Migration.php';

$conn = new Database();
$conn->connection();

//$model = new \ReflectionClass('models');
$test = new Migration();
$dirname = dirname(__FILE__);
$folder = $test->getFolder('/models', $dirname);
print_r($folder);
print_r("</br>");
$files = glob($folder . '/*.php');
print_r($files);
print_r("</br>");
foreach ($files as $file) {
    print_r(basename($file, '.php'));
}
print_r("</br>");

foreach ($test->getFiles('/models', '*.php', $dirname) as $file) {
    print_r(basename($file, '.php'));
}
