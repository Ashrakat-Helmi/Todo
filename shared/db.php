<?php
$dsn = 'mysql:dbname=todo-database;unix_socket=/cloudsql/ejada-internship-project:us-central1:todo-db';
$username = 'root';
$password = 'zvG,KAGUHXu/()|.';
//zvG,KAGUHXu/()|.

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
