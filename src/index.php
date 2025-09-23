<?php
session_start();

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

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $voornaam = trim($_POST['voornaam']);
  $achternaam = trim($_POST['achternaam']);
  $wachtwoord = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);
  $genderMap = [
    'man' => 'M',
    'vrouw' => 'V',
    'other' => 'O',
  ];
  $geslacht = $genderMap[$_POST['gender']] ?? 'U'; // 'U' = unknown fallback
  $email = trim($_POST['email']);
  $telefoonnr = trim($_POST['telefoonnr']);
  $adres = trim($_POST['adres']);
  $postcode = trim($_POST['postcode']);
  $aanmaakstijd = date('H:i:s');

  try {
    // Check for duplicate email first
    $checkEmailSql = "SELECT COUNT(*) FROM account WHERE email = :email";
    $checkEmailStmt = $pdo->prepare($checkEmailSql);
    $checkEmailStmt->execute([':email' => $email]);
    if ($checkEmailStmt->fetchColumn() > 0) {
      $message = "Whoops, that email is already taken.";
    } else {
      // Generate unique 6-digit accountnr (server-side for safety)
      do {
        $accountnr = random_int(100000, 999999); // Secure random, PHP 7+ (falls back to mt_rand if needed)
        // Check if it already exists
        $checkIdSql = "SELECT COUNT(*) FROM account WHERE accountnr = :accountnr";
        $checkIdStmt = $pdo->prepare($checkIdSql);
        $checkIdStmt->execute([':accountnr' => $accountnr]);
        $exists = $checkIdStmt->fetchColumn() > 0;
      } while ($exists); // Loop until unique (very rare to loop more than once)

      // Now insert with accountnr included
      $sql = "INSERT INTO account 
              (accountnr, aanmaakstijd, voornaam, achternaam, wachtwoord, telefoonnr, email, geslacht, isDocent, isAdmin, adres, postcode) 
              VALUES 
              (:accountnr, :aanmaakstijd, :voornaam, :achternaam, :wachtwoord, :telefoonnr, :email, :geslacht, 0, 0, :adres, :postcode)";

      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':accountnr' => $accountnr,
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

      $message = "Success! User added. Accountnr: " . $accountnr;
    }
  } catch (PDOException $e) {
    $message = "Insert failed: " . htmlspecialchars($e->getMessage());
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
  <!-- Removed <script src="javascript/account-id-assigner.js"></script> since ID is now handled in PHP -->
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
      <h2 class="page-title">Registreren</h2>
      <?php if ($message): ?>
        <p><?php echo $message; ?></p>
      <?php endif; ?>
      <form action="" method="post">
        <label>Voornaam:</label>
        <input class="form-input" type="text" name="voornaam" required>

        <label>Achternaam:</label>
        <input class="form-input" type="text" name="achternaam" required>

        <label>Wachtwoord:</label>
        <input class="form-input" type="password" name="wachtwoord" required>

        <label for="gender">Gender:</label>
        <select class="form-input" name="gender" id="gender">
          <option value="man">Man</option>
          <option value="vrouw">Vrouw</option>
          <option value="other">Other</option>
        </select>

        <label>E-mail:</label>
        <input class="form-input" type="email" name="email" required>

        <label>Telefoonnummer:</label>
        <input class="form-input" type="text" name="telefoonnr" required>

        <label>Adres:</label>
        <input class="form-input" type="text" name="adres" required>

        <label>Postcode:</label>
        <input class="form-input" type="text" name="postcode" required>

        <input id="verzenden" class="submit" type="submit" value="Submit">
      </form>
    </div>
  </div>

  <div class="bodem">
    <p>© Tick-IT 2025</p>
  </div>

</body>

</html>