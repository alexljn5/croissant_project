<?php
session_start();

// Database credentials (hardcoded for now, but we'll secure them)
$servername = "db"; // Matches docker-compose.yml
$username = "root";
$password = "password"; // Must match MYSQL_ROOT_PASSWORD
$dbname = "croissantdb";

try {
  $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
  $pdo = new PDO($dsn, $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // Cheese sneers, "Hmph, database tamed!"
} catch (PDOException $e) {
  //if any of you teammates care to see the bunny reference here, come up to me with the die message changed and I will give you a euro.
  die("Database error, you silly bun-bun Creamy rabbit!: " . htmlspecialchars($e->getMessage()) . " (Code: " . $e->getCode() . ")");
}

$message = "";

// Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';

  if (empty($email) || empty($password)) {
    $message = "Please fill in all fields.";
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
        $message = "Login successful! Redirecting...";
        header("Refresh: 2; url=/webPages/ticketPage.php");
      } else {
        $message = "Invalid email or password.";
      }
    } catch (PDOException $e) {
      $message = "Error: " . htmlspecialchars($e->getMessage());
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
      <?php if (!empty($message)): ?>
        <p style="color:<?php echo strpos($message, 'successful') !== false ? 'green' : 'red'; ?>; text-align:center;">
          <?php echo htmlspecialchars($message); ?>
        </p>
      <?php endif; ?>
      <form action="" method="post">
        <label>Email:</label>
        <input class="form-input" type="email" name="email" required>
        <label>Password:</label>
        <input class="form-input" type="password" name="password" required>
        <div class="nav-buttons">
          <button class="submit" type="submit">Log In</button>
          <a href="register.php"><button type="button">Register</button></a>
        </div>
      </form>
    </div>
  </div>
  <div class="bodem">
    <p>© Tick-IT 2025</p>
  </div>
</body>

</html>