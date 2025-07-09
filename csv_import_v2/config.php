<?php
// Database configuration
$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db' => 'csv_import_db'
];

// Database connection
function db_connect($config) {
    $conn = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['db']);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}
?>
