<?php
$dsn = 'mysql:host=db;dbname=db;charset=utf8mb4';
$user = 'db';
$pass = 'db';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    die("DB error: " . $e->getMessage());
}
?>
