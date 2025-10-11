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
    // Check for duplicate email
    $checkSql = "SELECT COUNT(*) FROM account WHERE email = :email";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':email' => $email]);

    if ($checkStmt->fetchColumn() > 0) {
      $message = "⚠️ Whoops, that email is already taken.";
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

      $message = "✅ Success! User added. Accountnr: " . $pdo->lastInsertId();
    }
  } catch (PDOException $e) {
    $message = "❌ Insert failed: " . htmlspecialchars($e->getMessage());
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
  <script src="javascript/account-id-assigner.js"></script>
</head>

<body>
  <?php include 'addons/header.php'; ?>
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
  <?php include 'addons/footer.php'; ?>
</body>

</html>