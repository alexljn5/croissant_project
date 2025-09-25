<?php
session_start();

require_once 'database.php';   

$message = "";

// Uitloggen verwerken
if (isset($_POST['logout'])) {
  session_unset();
  session_destroy();
  header("Location: index.php");
  exit;
}

if (!isset($_SESSION['accountnr'])) {
  header("Location: index.php");
  exit;
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
  <script src="javascript/account-id-assigner.js"></script>
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
        <h1 class="page-title">Dashboard</h1>
      </div>
    <form action="" method="post">
    <p>Je bent ingelogd als accountnummer: <?php echo htmlspecialchars($_SESSION['accountnr']); ?></p>
            <div class="nav-buttons">
        <button type="submit" name="logout">Uitloggen</button>
      </form>
        </div>
    </div>
  </div> <!-- einde .page-wrapper -->



  <div class="bodem">
    <p>© Tick-IT 2025</p>
  </div>

</body>

</html>
