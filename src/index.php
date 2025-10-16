<?php
session_start();

// Debug flag: set to true to accept plain text passwords (for testing)
define('DEBUG_PLAINTEXT_PASSWORDS', false);

// Database credentials
$servername = "db";
$username = "root";
$password = "password";
$dbname = "croissantdb";

try {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database error, you silly bun-bun Creamy rabbit!: " . htmlspecialchars($e->getMessage()) . " (Code: " . $e->getCode() . ")");
}

$message = "";

// Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $passwordInput = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($passwordInput)) {
        $message = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT account_id, email, password, is_teacher, is_admin FROM account WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $passwordMatches = false;

                if (DEBUG_PLAINTEXT_PASSWORDS) {
                    // Bunny debug mode: accept plain text
                    $passwordMatches = ($passwordInput === $user['password']);
                } else {
                    // Production: check hashed password
                    $passwordMatches = password_verify($passwordInput, $user['password']);
                }

                if ($passwordMatches) {
                    $_SESSION['account_id'] = $user['account_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['is_teacher'] = $user['is_teacher'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    $message = "Login successful! Redirecting...";
                    header("Refresh: 2; url=/home.php");
                } else {
                    $message = "Invalid email or password.";
                }
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
  <?php
  define('INCLUDED', true);
  include 'components/header.php';
  ?>
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
  <?php include 'components/footer.php'; ?>
</body>

</html>