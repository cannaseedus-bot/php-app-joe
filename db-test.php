<?php
require_once 'config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "DB Connected successfully!";
} catch (PDOException $e) {
    echo "DB Connection failed: " . $e->getMessage();
}
?>