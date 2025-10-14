<?php
session_start();

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
    echo "<p style='color:green;'>Class type <b>" . htmlspecialchars($class_name) . "</b> created.</p>";
  } catch (PDOException $e) {
    echo "<p style='color:red;'>Error creating: " . htmlspecialchars($e->getMessage()) . "</p>";
  }
}

if ($action === "delete" && !empty($_POST["class_id"])) {
  $class_id = (int) $_POST["class_id"];
  try {
    $stmt = $pdo->prepare("DELETE FROM class WHERE class_number = :class_number");
    $stmt->execute([":class_number" => $class_id]);
    echo "<p style='color:green;'>Class with number $class_id deleted.</p>";
  } catch (PDOException $e) {
    echo "<p style='color:red;'>Error deleting: " . htmlspecialchars($e->getMessage()) . "</p>";
  }
}

try {
  $class_query = "SELECT class_number, class_type FROM class";
  $class_result = $pdo->query($class_query);
  $classes = $class_result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("<p style='color:red;'>Error fetching: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Classes</title>
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
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

  <h1>Class Overview</h1>

  <?php
  if ($classes) {
    echo "<ul>";
    foreach ($classes as $row) {
      $class_id = htmlspecialchars($row["class_number"]);
      $class_name = htmlspecialchars($row["class_type"]);
      echo "<li style='margin-bottom:10px;'>
                Class Number: $class_id <br>
                Class Type: $class_name <br>
                <form method='post' action='' style='display:inline;'>
                    <input type='hidden' name='action' value='delete'>
                    <input type='hidden' name='class_id' value='$class_id'>
                    <button type='submit' class='button'>Delete</button>
                </form>
              </li>";
    }
    echo "</ul>";
  } else {
    echo "<p>No classes found.</p>";
  }
  ?>

  <hr>

  <h2>Add New Class</h2>
  <form method="post" action="">
    <input type="hidden" name="action" value="add">
    <label for="class_type">Name:</label>
    <input type="text" id="class_type" name="class_type" required>
    <button type="submit" class="submit">Add</button>
  </form>

  <hr>
  <a href="index.php" class="button">Back to index</a>

  <div class="bodem">
    <p>© Tick-IT 2025</p>
  </div>
</body>

</html>