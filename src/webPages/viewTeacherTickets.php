<?php
session_start();

// --------------------------------------------------
// Access Control
// --------------------------------------------------
if (!isset($_SESSION['account_id']) || $_SESSION['is_teacher'] != 1) {
    header('Location: /index.php');
    exit();
}

// --------------------------------------------------
// Database Connection
// --------------------------------------------------
$servername = "db";
$username = "root";
$password = "password";
$dbname = "croissantdb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<p class='error'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// --------------------------------------------------
// Fetch All Teacher Tickets (with Class Name)
// --------------------------------------------------
try {
    $stmt = $pdo->query("
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
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p class='error'>Error loading tickets: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Tickets - Tick-IT</title>
    <link rel="stylesheet" href="/styles.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php define('INCLUDED', true);
    include '../components/header.php'; ?>

    <div class="main-content">
        <h1 class="page-title">All Teacher Tickets</h1>

        <div class="outer-div">
            <?php if (empty($tickets)): ?>
                <p>No tickets have been created yet.</p>
            <?php else: ?>
                <div class="ticket-grid">
                    <?php foreach ($tickets as $t): ?>
                        <div class="ticket-card assigned">
                            <div class="ticket-info">
                                <p><strong>ID:</strong> <?= htmlspecialchars($t['teacher_ticket_id']) ?></p>
                                <p><strong>Class:</strong> <?= htmlspecialchars($t['class_type']) ?></p>
                                <p><strong>Description:</strong> <?= htmlspecialchars($t['description']) ?></p>
                                <p><strong>Created:</strong> <?= htmlspecialchars($t['creation_date']) ?></p>
                                <p><strong>Expires:</strong> <?= htmlspecialchars($t['expiration_date']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="nav-buttons">
            <a href="/home.php"><button type="button">← Back to Dashboard</button></a>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>
</body>

</html>