<?php
session_start();

if (!isset($_SESSION['accountnr'])) {
    header("Location: index.php"); // go back to signup/login if no session
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - Tick-IT</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="top">
    <img class="logo-border" src="./img/tickItLogo.png" alt="Tick-IT Logo">
  </div>

  <div class="page-wrapper">
    <h1>Welkom, <?php echo htmlspecialchars($_SESSION['username']); ?> 🎉</h1>
    <p>Je accountnummer is: <?php echo htmlspecialchars($_SESSION['accountnr']); ?></p>
    <p>Je e-mailadres is: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
  </div>

  <div class="bodem">
    <p>© Tick-IT 2025</p>
  </div>
</body>
</html>
