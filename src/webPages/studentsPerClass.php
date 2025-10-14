<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    header('Location: index.php');
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "password";
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

$action = $_POST["action"] ?? "";
$class_filter = $_POST["class_filter"] ?? null;

// Fetch all classes
$sql = "SELECT class_number, class_type FROM croissantdb.class ORDER BY class_type";
$classes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$message = "";
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students per Class</title>
    <link rel="stylesheet" href="/styles.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php define('INCLUDED', true);
    include '../components/header.php'; ?>

    <div class="main-content">
        <h1 class="page-title">Students per Class</h1>

        <form method="POST" action="">
            <input type="hidden" name="action" value="filterStudents">
            <label for="class_filter">Filter by class:</label>
            <select name="class_filter" id="class_filter">
                <option value="">-- Select class --</option>
                <?php foreach ($classes as $row): ?>
                    <option value="<?= htmlspecialchars($row['class_number']) ?>" <?= ($class_filter == $row['class_number']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['class_type']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="submit">Filter</button>
        </form>

        <?php
        // Fetch students
        $student_query = "SELECT a.first_name, a.last_name, a.email, c.class_type 
                     FROM croissantdb.account a 
                     JOIN croissantdb.account_has_class ahc ON a.account_id = ahc.account_id 
                     JOIN croissantdb.class c ON ahc.class_number = c.class_number 
                     WHERE a.is_teacher = 0";
        $params = [];

        if ($action === "filterStudents" && $class_filter) {
            $student_query .= " AND c.class_number = :class_number";
            $params[':class_number'] = $class_filter;
        }

        $stmt = $pdo->prepare($student_query);
        $stmt->execute($params);
        $student_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($student_list) {
            echo "<ul class='student-list'>";
            foreach ($student_list as $row) {
                $name = htmlspecialchars($row["first_name"] . " " . $row["last_name"]);
                $email = htmlspecialchars($row["email"]);
                $class_type = htmlspecialchars($row["class_type"]);
                echo "<li>Student name: $name ($email) - Class: $class_type</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No students found.</p>";
        }
        ?>
        <div class="nav-buttons">
            <a href="/home.php"><button type="button">Back to Dashboard</button></a>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>
</body>

</html>