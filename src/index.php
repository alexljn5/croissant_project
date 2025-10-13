<?php
session_start();

// Database connection
$servername = "db"; // Matches docker-compose.yml
$username = "root";
$password = "password"; // Must match MYSQL_ROOT_PASSWORD
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
  die("<p style='color:red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

$message = "";

// Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';

  if (empty($email) || empty($password)) {
    $message = "<p style='color:red;'>Please fill in all fields.</p>";
  } else {
    try {
      $stmt = $pdo->prepare("SELECT account_id, email, password, is_teacher, is_admin FROM account WHERE email = ?");
      $stmt->execute([$email]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user && password_verify($password, $user['password'])) {
        $_SESSION['account_id'] = $user['account_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_teacher'] = $user['is_teacher'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $message = "<p style='color:green;'>Login successful! Redirecting...</p>";
        header("Refresh: 2; url=/webPages/ticketPage.php"); // Redirect to ticketPage.php after 2 seconds
      } else {
        $message = "<p style='color:red;'>Invalid email or password.</p>";
      }
    } catch (PDOException $e) {
      $message = "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Tick-IT</title>
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
      <div class="login">
        <h1 class="page-title">Log In</h1>
      </div>

      <!-- Show PHP messages if any -->
      <?php if (!empty($message)): ?>
        <p style="color:<?php echo strpos($message, 'successful') !== false ? 'green' : 'red'; ?>; text-align:center;">
          <?= htmlspecialchars($message) ?>
        </p>
      <?php endif; ?>

      <form action="" method="post">
        <label>Email:</label>
        <input class="form-input" type="email" name="email" required>

        <label>Password:</label>
        <input class="form-input" type="password" name="password" required>

        <input class="submit" type="submit" value="Log In">
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