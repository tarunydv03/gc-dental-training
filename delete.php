<?php
session_start();
require 'db.php';
$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = "Record deleted.";
}
header("Location: index.php");
exit;
?>
