<?php
session_start();

// Database connectie (zelfde code, zodat deze pagina zelfstandig werkt)
$servername = "localhost";
$username = "root";
$password = "password"; // pas aan indien nodig
$dbname = "croissantdb";

try {
    $pdo = new PDO(
        "mysql:host=db;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<p style='color:red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// Eventueel eigen logica voor deze pagina
$message = "";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nieuwe Pagina</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>