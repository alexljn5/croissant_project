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
      // Generate a random 6-digit account number
      do {
          $accountnr = mt_rand(100000, 999999);
          $checkAccount = $pdo->prepare("SELECT COUNT(*) FROM account WHERE accountnr = ?");
          $checkAccount->execute([$accountnr]);
      } while ($checkAccount->fetchColumn() > 0);

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

      // Since accountnr is manually generated (not AUTO_INCREMENT), lastInsertId() returns 0.
      // Show the actual generated account number to the user instead.
      $message = "✅ Success! User added. Accountnr: " . $accountnr;
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
  <style>
    .message {
      color: green;
      margin-bottom: 15px;
      font-weight: bold;
    }

    .error {
      color: red;
      margin-bottom: 15px;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <?php include 'addons/header.php'; ?>
  <div class="page-wrapper">
    <div class="outer-div">
      <div class="registreren">
        <h1 class="page-title">Registeren</h1>
      </div>
      <?php if (!empty($message)): ?>
        <p class="<?php echo (strpos($message, 'Success') !== false ? 'message' : 'error'); ?>">
          <?php echo htmlspecialchars($message); ?>
        </p>
      <?php endif; ?>
  <form id="register-form" action="" method="post">
        <label>Voornaam:</label>
        <input class="form-input" type="text" name="voornaam"
          value="<?php echo isset($_POST['voornaam']) ? htmlspecialchars($_POST['voornaam']) : ''; ?>" required>

        <label>Achternaam:</label>
        <input class="form-input" type="text" name="achternaam"
          value="<?php echo isset($_POST['achternaam']) ? htmlspecialchars($_POST['achternaam']) : ''; ?>" required>

        <label>Wachtwoord:</label>
        <input class="form-input" type="password" name="wachtwoord" required>

        <label for="gender">Gender:</label>
        <select class="form-input" name="gender" id="gender">
          <option value="man" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'man') ? 'selected' : ''; ?>>Man
          </option>
          <option value="vrouw" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'vrouw') ? 'selected' : ''; ?>>Vrouw</option>
          <option value="other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'other') ? 'selected' : ''; ?>>Other</option>
        </select>

        <label>E-mail:</label>
        <input class="form-input" type="email" name="email"
          value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>

        <label>Telefoonnummer:</label>
        <input class="form-input" type="text" name="telefoonnr"
          value="<?php echo isset($_POST['telefoonnr']) ? htmlspecialchars($_POST['telefoonnr']) : ''; ?>" required>

        <label>Adres:</label>
        <input class="form-input" type="text" name="adres"
          value="<?php echo isset($_POST['adres']) ? htmlspecialchars($_POST['adres']) : ''; ?>" required>

        <label>Postcode:</label>
        <input class="form-input" type="text" name="postcode"
          value="<?php echo isset($_POST['postcode']) ? htmlspecialchars($_POST['postcode']) : ''; ?>" required>

        <input id="verzenden" class="submit" type="submit" value="Submit">
      </form>
      <div class="nav-buttons">
        <a href="index.php"><button type="button">Login</button></a>
        <a href="register.php"><button type="button">Registreren</button></a>
      </div>
    </div>
  </div>
  <?php include 'addons/footer.php'; ?>
    <script src="javascript/account-id-assigner.js"></script>
</body>

</html>