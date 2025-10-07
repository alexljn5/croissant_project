<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "password"; // change if needed
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $voornaam   = trim($_POST['voornaam']);
    $achternaam = trim($_POST['achternaam']);
    $wachtwoord = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);

    $genderMap = [
        'man'   => 'M',
        'vrouw' => 'V',
        'other' => 'O',
    ];
    $geslacht = $genderMap[$_POST['gender']] ?? 'U';

    $email      = trim($_POST['email']);
    $telefoonnr = trim($_POST['telefoonnr']);
    $adres      = trim($_POST['adres']);
    $postcode   = trim($_POST['postcode']);
    $aanmaakstijd = date('H:i:s');

    try {
        // 1. Check for duplicate email
        $checkSql = "SELECT COUNT(*) FROM account WHERE email = :email";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([':email' => $email]);

        if ($checkStmt->fetchColumn() > 0) {
            $message = "⚠️ Whoops, that email is already taken.";
        } else {
            // 2. Insert into DB
            $sql = "INSERT INTO account 
                    (aanmaakstijd, voornaam, achternaam, wachtwoord, telefoonnr, email, geslacht, isDocent, isAdmin, adres, postcode) 
                    VALUES 
                    (:aanmaakstijd, :voornaam, :achternaam, :wachtwoord, :telefoonnr, :email, :geslacht, 0, 0, :adres, :postcode)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':aanmaakstijd' => $aanmaakstijd,
                ':voornaam'     => $voornaam,
                ':achternaam'   => $achternaam,
                ':wachtwoord'   => $wachtwoord,
                ':telefoonnr'   => $telefoonnr,
                ':email'        => $email,
                ':geslacht'     => $geslacht,
                ':adres'        => $adres,
                ':postcode'     => $postcode
            ]);

            // 3. Create a session-based username (not stored in DB)
            $accountId = $pdo->lastInsertId();
            $username = strtolower($voornaam . "." . $achternaam) . $accountId;

            // 4. Save session
            $_SESSION['username'] = $username;
            $_SESSION['accountnr'] = $accountId;
            $_SESSION['email'] = $email;

            // 5. Redirect to home
            header("Location: home.php");
            exit();
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
  <title>Tick-IT - Sign Up</title>
  <link rel="icon" type="image/x-icon" href="./img/tickItLogo.png">
<<<<<<< Updated upstream
<<<<<<< Updated upstream
  <link rel="stylesheet" href="styles.css">
=======
=======
>>>>>>> Stashed changes
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
  <script src="javascript/account-id-assigner.js"></script>
>>>>>>> Stashed changes
</head>
<body>

  <div class="top">
<<<<<<< Updated upstream
<<<<<<< Updated upstream
    <img class="logo-border" src="./img/tickItLogo.png" alt="Tick-IT Logo">
=======
=======
>>>>>>> Stashed changes
    <div class="logo-border">
      <img src="./img/tickItLogo.png" alt="Tick-IT Logo">
    </div>
    <div class="header-container">
      <h1 class="header-title">Tick-IT</h1>
    </div>
>>>>>>> Stashed changes
  </div>

  <div class="page-wrapper">
    <div class="outer-div">

      <!-- Show PHP messages if any -->
      <?php if (!empty($message)): ?>
        <p style="color:red; text-align:center;"><?= htmlspecialchars($message) ?></p>
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

        <input id="verzenden" class="submit" type="submit" value="Sign Up">
      </form>
    </div>
  </div>

  <div class="bodem">
    <p>© Tick-IT 2025</p>
  </div>

</body>
</html>