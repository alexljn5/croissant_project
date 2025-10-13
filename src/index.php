<?php
session_start();

$servername = "db";
$username = "root";
$password = "password";
$dbname = "croissantdb";

try {
  $retries = 5;
  $retryInterval = 5;
  while ($retries > 0) {
    try {
      $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      break;
    } catch (PDOException $e) {
      $retries--;
      if ($retries == 0) {
        die("<p style='color:red;'>Database connection failed after retries: " . htmlspecialchars($e->getMessage()) . " (Code: " . $e->getCode() . ")</p>");
      }
      sleep($retryInterval);
    }
  }
} catch (PDOException $e) {
  die("<p style='color:red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . " (Code: " . $e->getCode() . ")</p>");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $voornaam = isset($_POST['voornaam']) ? trim($_POST['voornaam']) : '';
  $achternaam = isset($_POST['achternaam']) ? trim($_POST['achternaam']) : '';
  $wachtwoord = isset($_POST['wachtwoord']) ? password_hash(trim($_POST['wachtwoord']), PASSWORD_DEFAULT) : '';
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $telefoonnr = isset($_POST['telefoonnr']) ? trim($_POST['telefoonnr']) : '';
  $adres = isset($_POST['adres']) ? trim($_POST['adres']) : '';
  $postcode = isset($_POST['postcode']) ? trim($_POST['postcode']) : '';
  $aanmaakstijd = date('H:i:s');

  $genderMap = [
    'man' => 'M',
    'vrouw' => 'V',
    'other' => 'O',
  ];
  $geslacht = isset($_POST['gender']) && isset($genderMap[$_POST['gender']]) ? $genderMap[$_POST['gender']] : 'U';

  try {
    $checkSql = "SELECT COUNT(*) FROM account WHERE email = :email";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':email' => $email]);

    if ($checkStmt->fetchColumn() > 0) {
      $message = "Email already taken.";
    } else {
      $sql = "INSERT INTO account 
                    (aanmaakstijd, voornaam, achternaam, wachtwoord, telefoonnr, email, geslacht, isDocent, isAdmin, adres, postcode) 
                    VALUES 
                    (:aanmaakstijd, :voornaam, :achternaam, :wachtwoord, :telefoonnr, :email, :geslacht, 0, 0, :adres, :postcode)";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':aanmaakstijd' => $aanmaakstijd,
        ':voornaam' => $voornaam,
        ':achternaam' => $achternaam,
        ':wachtwoord' => $wachtwoord,
        ':telefoonnr' => $telefoonnr,
        ':email' => $email,
        ':geslacht' => $geslacht,
        ':adres' => $adres,
        ':postcode' => $postcode
      ]);
      $message = "Registratie succesvol";
    }
  } catch (PDOException $e) {
    $message = "Error: " . htmlspecialchars($e->getMessage());
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Tick-IT</title>
  <script src="javascript/account-id-assigner.js"></script>
  <link rel="stylesheet" href="styles.css">
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

  <div class="page-wrapper">
    <div class="outer-div">
      <div class="registreren">
        <h1 class="page-title">Log In</h1>
      </div>

      <!-- Show PHP messages if any -->
      <?php if (!empty($message)): ?>
        <p style="color:<?php echo strpos($message, 'successful') !== false ? 'green' : 'red'; ?>; text-align:center;">
          <?= htmlspecialchars($message) ?>
        </p>
      <?php endif; ?>

      <form action="" method="post">
        <label>E-mail:</label>
        <input class="form-input" type="email" name="email" required>

        <label>Wachtwoord:</label>
        <input class="form-input" type="password" name="wachtwoord" required>
      </form>

      <div class="nav-buttons">
        <a href="index.php"><button type="button">Login</button></a>
        <a href="register.php"><button type="button">Registreren</button></a>
      </div>
    </div>
  </div>

  <div class="bodem">
    <p>© Tick-IT 2025</p>
  </div>
</body>

</html>