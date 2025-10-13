<?php
session_start();

// Database connection
$servername = "db"; // Matches MySQL service name in docker-compose.yml
$username = "root";
$password = "password"; // Must match MYSQL_ROOT_PASSWORD in docker-compose.yml
$dbname = "croissantdb";

try {
  // Retry mechanism to handle MySQL initialization delay
  $retries = 5;
  $retryInterval = 5; // Seconds
  while ($retries > 0) {
    try {
      $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      break; // Exit loop on successful connection
    } catch (PDOException $e) {
      $retries--;
      if ($retries == 0) {
        die("<p style='color:red;'>Database connection failed after retries: " . htmlspecialchars($e->getMessage()) . " (Code: " . $e->getCode() . ")</p>");
      }
      sleep($retryInterval); // Wait before retrying
    }
  }
} catch (PDOException $e) {
  die("<p style='color:red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . " (Code: " . $e->getCode() . ")</p>");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Validate and sanitize input
  $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
  $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
  $password = isset($_POST['password']) ? password_hash(trim($_POST['password']), PASSWORD_DEFAULT) : '';
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
  $address = isset($_POST['address']) ? trim($_POST['address']) : '';
  $postal_code = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';
  $creation_time = date('H:i:s');

  $genderMap = [
    'man' => 'M',
    'vrouw' => 'V',
    'other' => 'O',
  ];
  $gender = isset($_POST['gender']) && isset($genderMap[$_POST['gender']]) ? $genderMap[$_POST['gender']] : 'U';

  try {
    $checkSql = "SELECT COUNT(*) FROM account WHERE email = :email";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':email' => $email]);

    if ($checkStmt->fetchColumn() > 0) {
      $message = "Email already taken.";
    } else {
      // Generate a random 6-digit account ID
      do {
        $account_id = mt_rand(100000, 999999);
        $checkAccount = $pdo->prepare("SELECT COUNT(*) FROM account WHERE account_id = ?");
        $checkAccount->execute([$account_id]);
      } while ($checkAccount->fetchColumn() > 0);

      $sql = "INSERT INTO account 
                    (account_id, creation_time, first_name, last_name, password, phone_number, email, gender, is_teacher, is_admin, address, postal_code) 
                    VALUES 
                    (:account_id, :creation_time, :first_name, :last_name, :password, :phone_number, :email, :gender, 0, 0, :address, :postal_code)";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':account_id' => $account_id,
        ':creation_time' => $creation_time,
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':password' => $password,
        ':phone_number' => $phone_number,
        ':email' => $email,
        ':gender' => $gender,
        ':address' => $address,
        ':postal_code' => $postal_code
      ]);
      $message = "✅ Success! User added. Account ID: " . htmlspecialchars($account_id);
    }
  } catch (PDOException $e) {
    $message = "❌ Error: " . htmlspecialchars($e->getMessage());
  }
}
?>
|
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
        <h1 class="page-title">Register</h1>
      </div>

      <!-- Show PHP messages if any -->
      <?php if (!empty($message)): ?>
        <p style="color:<?php echo strpos($message, 'Success') !== false ? 'green' : 'red'; ?>; text-align:center;">
          <?= htmlspecialchars($message) ?>
        </p>
      <?php endif; ?>

      <form action="" method="post">
        <label>First Name:</label>
        <input class="form-input" type="text" name="first_name" required>

        <label>Last Name:</label>
        <input class="form-input" type="text" name="last_name" required>

        <label>Email:</label>
        <input class="form-input" type="email" name="email" required>

        <label>Password:</label>
        <input class="form-input" type="password" name="password" required>

        <label>Gender:</label>
        <select class="form-input" name="gender" required>
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
        </select>

        <label>Phone Number:</label>
        <input class="form-input" type="text" name="phone_number" required>

        <label>Address:</label>
        <input class="form-input" type="text" name="address" required>

        <label>Postal Code:</label>
        <input class="form-input" type="text" name="postal_code" required>

        <input id="verzenden" class="submit" type="submit" value="Sign Up">
      </form>

      <div class="nav-buttons">
        <a href="index.php"><button type="button">Login</button></a>
        <a href="register.php"><button type="button">Register</button></a>
      </div>
    </div>
  </div>

  <div class="bodem">
    <p>© Tick-IT 2025</p>
  </div>
</body>

</html>