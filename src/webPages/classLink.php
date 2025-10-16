<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    header('Location: ../index.php');
    exit();
}

// Restrict access to teachers and admins
$isTeacher = $_SESSION['is_teacher'] ?? false;
$isAdmin = $_SESSION['is_admin'] ?? false;
if (!$isTeacher && !$isAdmin) {
    header('Location: ../home.php');
    exit();
}

// Database connection
$servername = "db";
$username = "root";
$password = "password";
$dbname = "croissantdb";

try {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<p class='ongeldig'>Database error, oh no, Cream is sad! 🐰: " . htmlspecialchars($e->getMessage()) . " (Code: " . $e->getCode() . ")</p>");
}

// Initialize message
$message = "";

// Fetch students and classes for the form
try {
    $studentStmt = $pdo->prepare("SELECT account_id, first_name, last_name FROM account WHERE is_teacher = 0 ORDER BY last_name");
    $studentStmt->execute();
    $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

    $classStmt = $pdo->query("SELECT class_number, class_type FROM class ORDER BY class_type");
    $classes = $classStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "<p class='ongeldig'>Error loading data: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Handle form submission to link students to classes
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['link_student'])) {
    $account_id = $_POST['account_id'] ?? '';
    $class_numbers = $_POST['class_numbers'] ?? [];

    if (empty($account_id) || empty($class_numbers)) {
        $message = "<p class='ongeldig'>Please select a student and at least one class.</p>";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT IGNORE INTO account_has_class (account_id, class_number) VALUES (?, ?)");
            foreach ($class_numbers as $class_number) {
                $stmt->execute([$account_id, $class_number]);
            }
            $pdo->commit();
            $message = "<p class='geldig'>✅ Student successfully linked to selected classes!</p>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "<p class='ongeldig'>Error linking student: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

// Fetch student-class relationships
try {
    $stmt = $pdo->prepare("
        SELECT a.account_id, a.first_name, a.last_name, a.email, c.class_number, c.class_type 
        FROM account a 
        JOIN account_has_class ahc ON a.account_id = ahc.account_id 
        JOIN class c ON ahc.class_number = c.class_number 
        WHERE a.is_teacher = 0 
        ORDER BY c.class_type, a.last_name
    ");
    $stmt->execute();
    $relationships = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "<p class='ongeldig'>Error fetching relationships: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Links - Tick-IT</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <?php
    define('INCLUDED', true);
    include '../components/header.php';
    ?>

    <div class="page-wrapper">
        <div class="outer-div">
            <div class="registreren">
                <h1 class="page-title">Manage Student-Class Links</h1>
            </div>

            <?php if (!empty($message)): ?>
                <?php echo $message; ?>
            <?php endif; ?>

            <h2>Link Student to Classes</h2>
            <form method="POST">
                <label for="account_id">Select Student:</label>
                <select class="form-input" name="account_id" id="account_id" required>
                    <option value="">-- Choose a student --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo htmlspecialchars($student['account_id']); ?>">
                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="class_numbers">Select Classes:</label>
                <select class="form-input multi-select" name="class_numbers[]" id="class_numbers" multiple required>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo htmlspecialchars($class['class_number']); ?>">
                            <?php echo htmlspecialchars($class['class_type']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="link_student" class="submit">Link Student</button>
            </form>

            <h2>Current Student-Class Links</h2>
            <?php if (empty($relationships)): ?>
                <p style="text-align:center;">No student-class relationships found. Let's get some students enrolled! 🐰</p>
            <?php else: ?>
                <div class="class-grid">
                    <?php foreach ($relationships as $rel): ?>
                        <div class="class-card">
                            <div class="ticket-info">
                                <p><strong>Class:</strong> <?php echo htmlspecialchars($rel['class_type']); ?></p>
                                <p><strong>Name:</strong>
                                    <?php echo htmlspecialchars($rel['first_name'] . ' ' . $rel['last_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($rel['email']); ?></p>
                                <p><strong>Account ID:</strong> <?php echo htmlspecialchars($rel['account_id']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="nav-buttons">
                <a href="../home.php"><button type="button">Back to Dashboard</button></a>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>
</body>

</html>