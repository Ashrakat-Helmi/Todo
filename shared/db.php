<?php
$dsn = 'mysql:host=/cloudsql/ejada-internship-project:us-central1:todo-db;dbname=todo-database;charset=utf8mb4';
$username = 'dbuser';
$password = 'cR5&x;x8$?#$#.|n';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
