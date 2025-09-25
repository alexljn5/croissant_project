<?php
$servername = "localhost";
$username = "root";
$password = "password"; // change if needed
$dbname = "croissantdb";

try {
    $pdo = new PDO(
        "mysql:host=db;dbname=croissantdb;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<p style='color:red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>