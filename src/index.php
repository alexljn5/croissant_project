<?php
session_start();

require_once 'database.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST['email']);
  $wachtwoord = $_POST['wachtwoord'];

  try {
    // Check for duplicate email
    $checkSql = "SELECT COUNT(*) FROM account WHERE email = :email";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':email' => $email]);

    if ($checkStmt->fetchColumn() > 0) {
      $message = "⚠️ Whoops, that email is already taken.";
    } else {
      $message = "❌ Ongeldige e-mail of wachtwoord.";
    }
  } catch (PDOException $e) {
    $message = "❌ Login mislukt: " . htmlspecialchars($e->getMessage());
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tick-IT</title>
  <link rel="icon" type="image/x-icon" href="./img/tickItLogo.png">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <div class="top">
    <img class="logo-border" src="./img/tickItLogo.png" alt="Tick-IT Logo">
    <div class="header-container">
      <h1 class="header-title">Tick-IT</h1>
    </div>
  </div>


  <div class="page-wrapper">
  <div class="outer-div">
      <div class="registreren">
        <h1 class="page-title">Login</h1>
      </div>
    <form action="" method="post">

        <label>E-mail:</label>
        <input class="form-input" type="email" name="email" required>

        <label>Wachtwoord:</label>
        <input class="form-input" type="password" name="wachtwoord" required>

        <input id="verzenden" class="submit" type="submit" value="Submit">
      </form>
        <div class="nav-buttons">
          <a href="index.php"><button type="button">Login</button></a>
          <a href="register.php"><button type="button">Registreren</button></a>
        </div>
    </div>
  </div> <!-- einde .page-wrapper -->



  <div class="bodem">
    <p>© Tick-IT 2025</p>
  </div>

</body>
</html>
