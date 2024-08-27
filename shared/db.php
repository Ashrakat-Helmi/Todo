<?php

$dsn = 'mysql:host=127.0.0.1;dbname=todo-database;charset=utf8mb4';
$username = 'dbuser';
$password = '123456789';


try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
