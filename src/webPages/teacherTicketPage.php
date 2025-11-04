<?php
session_start();

// --------------------------------------------------
// 1. Access Control
// --------------------------------------------------
if (!isset($_SESSION['account_id']) || $_SESSION['is_teacher'] != 1) {
    header('Location: /index.php');
    exit();
}

// --------------------------------------------------
// 2. Database Connection
// --------------------------------------------------
$servername = "db";
$username = "root";
$password = "password";
$dbname = "croissantdb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<p class='ongeldig'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// --------------------------------------------------
// 3. Load Classes (for dropdown)
// --------------------------------------------------
try {
    $stmt = $pdo->query("SELECT class_number, class_type FROM class ORDER BY class_type");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p class='ongeldig'>Error loading classes: " . htmlspecialchars($e->getMessage()) . "</p>");
}

$message = "";

// --------------------------------------------------
// 4. Handle Ticket Creation / Delete
// --------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Create Ticket
    if (isset($_POST['create_ticket'])) {
        $class_number = $_POST['class_number'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $expiration_date = $_POST['expiration_date'] ?? '';

        if (empty($class_number) || empty($description) || empty($expiration_date)) {
            $message = "<p class='ongeldig'>Please fill in all fields before submitting.</p>";
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO teacher_ticket (description, creation_date, expiration_date, class_number)
                    VALUES (?, CURDATE(), ?, ?)
                ");
                $stmt->execute([$description, $expiration_date, $class_number]);
                $message = "<p class='geldig'>✅ Ticket successfully created!</p>";
            } catch (PDOException $e) {
                $message = "<p class='ongeldig'>Error creating ticket: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }

    // Delete Ticket
    if (isset($_POST['delete_ticket_id'])) {
        $ticketId = intval($_POST['delete_ticket_id']);
        try {
            // Remove ticket assignments first (FK constraint)
            $pdo->prepare("DELETE FROM account_has_teacher_ticket WHERE teacher_ticket_id = ?")->execute([$ticketId]);
            $pdo->prepare("DELETE FROM teacher_ticket WHERE teacher_ticket_id = ?")->execute([$ticketId]);
            $message = "<p class='geldig'>🗑️ Ticket #$ticketId deleted successfully.</p>";
        } catch (PDOException $e) {
            $message = "<p class='ongeldig'>Error deleting ticket: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

// --------------------------------------------------
// 5. Fetch All Tickets
// --------------------------------------------------
try {
    $ticketsQuery = $pdo->query("
        SELECT 
            t.teacher_ticket_id,
            t.description,
            t.creation_date,
            t.expiration_date,
            c.class_type
        FROM teacher_ticket t
        JOIN class c ON t.class_number = c.class_number
        ORDER BY t.teacher_ticket_id DESC
    ");
    $tickets = $ticketsQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p class='ongeldig'>Error loading tickets: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docent Ticket Management - Tick-IT</title>
    <link rel="stylesheet" href="../styles.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php define('INCLUDED', true);
    include '../components/header.php'; ?>

    <div class="main-content">
        <h1 class="page-title">Docent Ticket Management</h1>

        <?php echo $message; ?>

        <div class="outer-div">
            <h2>Create New Ticket</h2>
            <form method="POST">
                <label for="class_number">Select Class:</label>
                <select class="form-input" name="class_number" id="class_number" required>
                    <option value="">-- Choose a class --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= htmlspecialchars($c['class_number']) ?>">
                            <?= htmlspecialchars($c['class_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="description">Description:</label>
                <textarea class="form-input" id="description" name="description" rows="3" required
                    placeholder="Enter assignment or project details..."></textarea>

                <label for="expiration_date">Expiration Date:</label>
                <input class="form-input" type="date" id="expiration_date" name="expiration_date" required>

                <button type="submit" name="create_ticket" class="submit">Create Ticket</button>
            </form>
        </div>




        <div class="nav-buttons">
            <a href="/home.php"><button type="button">← Back to Dashboard</button></a>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>
</body>

</html>