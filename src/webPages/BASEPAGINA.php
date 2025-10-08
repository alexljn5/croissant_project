<?php
session_start();

//http://localhost:8080/webpages/AddVakken.php
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


$message = "";
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nieuwe Pagina</title>
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
  <link rel="stylesheet" href="src/styles.css">
=======
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
>>>>>>> Stashed changes
=======
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
>>>>>>> Stashed changes
=======
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
>>>>>>> Stashed changes
</head>


<body>
  <div class="top">
    <div class="logo-border">
      <img src="./img/tickItLogo.png" alt="Tick-IT Logo">
    </div>
    <div class="header-container">
      <h1 class="header-title">Tick-IT</h1>
    </div>
  </div>
  <div class="top">
    <div class="logo-border">
      <img src="./img/tickItLogo.png" alt="Tick-IT Logo">
    </div>
    <div class="header-container">
      <h1 class="header-title">Tick-IT</h1>
    </div>
  </div>