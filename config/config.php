<?php
// config.php
session_start();

$DB_HOST = '127.0.0.1';
$DB_NAME = 'kce_mechanic';
$DB_USER = 'root';
$DB_PASS = ''; 

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function is_logged_in() {
    return isset($_SESSION['user']);
}
function is_admin() {
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}
