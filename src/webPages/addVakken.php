<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
  header('Location: /index.php');
  exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "croissantdb";

$action = $_POST["action"] ?? "";

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

if ($action === "add" && !empty($_POST["class_type"])) {
  $class_name = trim($_POST["class_type"]);
  try {
    $insert_stmt = $pdo->prepare("INSERT INTO class (class_type) VALUES (:class_type)");
    $insert_stmt->execute([":class_type" => $class_name]);
    echo "<p class='geldig'>Class type <b>" . htmlspecialchars($class_name) . "</b> created.</p>";
  } catch (PDOException $e) {
    echo "<p class='ongeldig'>Error creating: " . htmlspecialchars($e->getMessage()) . "</p>";
  }
}

if ($action === "delete" && !empty($_POST["class_id"])) {
  $class_id = (int) $_POST["class_id"];
  try {
    $stmt = $pdo->prepare("DELETE FROM class WHERE class_number = :class_number");
    $stmt->execute([":class_number" => $class_id]);
    echo "<p class='geldig'>Class with number $class_id deleted.</p>";
  } catch (PDOException $e) {
    echo "<p class='ongeldig'>Error deleting: " . htmlspecialchars($e->getMessage()) . "</p>";
  }
}

try {
  $class_query = "SELECT class_number, class_type FROM class";
  $class_result = $pdo->query($class_query);
  $classes = $class_result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("<p class='ongeldig'>Error fetching: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Classes</title>
  <link rel="stylesheet" href="/styles.css?v=<?php echo time(); ?>">
</head>

<body>
  <?php define('INCLUDED', true);
  include '../components/header.php'; ?>

  <div class="main-content">
    <h1 class="page-title">Class Overview</h1>

    <?php
    if ($classes) {
      echo "<ul class='class-list'>";
      foreach ($classes as $row) {
        $class_id = htmlspecialchars($row["class_number"]);
        $class_name = htmlspecialchars($row["class_type"]);
        echo "<li>
                      Class Number: $class_id <br>
                      Class Type: $class_name <br>
                      <form method='post' action='' style='display:inline;'>
                          <input type='hidden' name='action' value='delete'>
                          <input type='hidden' name='class_id' value='$class_id'>
                          <button type='submit' class='submit'>Delete</button>
                      </form>
                    </li>";
      }
      echo "</ul>";
    } else {
      echo "<p>No classes found.</p>";
    }
    ?>

    <hr>

    <h2 class="page-title">Add New Class</h2>
    <form method="post" action="" class="outer-div">
      <input type="hidden" name="action" value="add">
      <label for="class_type">Name:</label>
      <input class="form-input" type="text" id="class_type" name="class_type" required>
      <button type="submit" class="submit">Add</button>
    </form>

    <hr>
    <div class="nav-buttons">
      <a href="/home.php"><button type="button">Back to Dashboard</button></a>
    </div>
  </div>

  <?php include '../components/footer.php'; ?>
</body>

</html>