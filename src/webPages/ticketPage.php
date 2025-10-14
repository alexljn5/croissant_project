<?php
session_start();

// --------------------------------------------------
// Access Control
// --------------------------------------------------
if (!isset($_SESSION['account_id'])) {
    header('Location: /index.php');
    exit();
}

$account_id = $_SESSION['account_id'];

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
// Fetch all tickets and student’s assigned tickets
// --------------------------------------------------
$allTicketsQuery = $pdo->prepare("
    SELECT 
        t.ticket_id,
        t.description,
        t.expiration_date,
        t.creation_date,
        c.class_type
    FROM croissantdb.student_ticket t
    JOIN croissantdb.class c ON t.class_number = c.class_number
    ORDER BY t.ticket_id ASC
");
$allTicketsQuery->execute();
$allTickets = $allTicketsQuery->fetchAll(PDO::FETCH_ASSOC);

$studentTicketsQuery = $pdo->prepare("
    SELECT 
        t.ticket_id,
        t.description,
        t.expiration_date,
        t.creation_date,
        c.class_type
    FROM croissantdb.account_has_student_ticket ast
    JOIN croissantdb.student_ticket t ON ast.student_ticket_id = t.ticket_id
    JOIN croissantdb.class c ON t.class_number = c.class_number
    WHERE ast.account_id = ?
");
$studentTicketsQuery->execute([$account_id]);
$studentTickets = $studentTicketsQuery->fetchAll(PDO::FETCH_ASSOC);

// --------------------------------------------------
// Handle Ticket Assignment
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_ticket_id'])) {
    $ticketId = intval($_POST['assign_ticket_id']);

    $check = $pdo->prepare("
        SELECT 1 FROM croissantdb.account_has_student_ticket 
        WHERE account_id = ? AND student_ticket_id = ?
    ");
    $check->execute([$account_id, $ticketId]);

    if ($check->rowCount() === 0) {
        $assign = $pdo->prepare("
            INSERT INTO croissantdb.account_has_student_ticket (account_id, student_ticket_id)
            VALUES (?, ?)
        ");
        $assign->execute([$account_id, $ticketId]);
        header("Location: ticketPage.php");
        exit();
    }
}

// --------------------------------------------------
// Filter available tickets
// --------------------------------------------------
$assignedIds = array_column($studentTickets, 'ticket_id');
$availableTickets = array_filter($allTickets, fn($t) => !in_array($t['ticket_id'], $assignedIds));
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Ticket Dashboard - Tick-IT</title>
    <link rel="stylesheet" href="/styles.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php define('INCLUDED', true);
    include '../components/header.php'; ?>

    <div class="main-content">
        <h1 class="page-title">Student Ticket Dashboard</h1>

        <!-- Available Tickets -->
        <div class="outer-div">
            <h2>Available Tickets</h2>
            <?php if (empty($availableTickets)): ?>
                <p>No available tickets at the moment.</p>
            <?php else: ?>
                <div class="ticket-grid">
                    <?php foreach ($availableTickets as $t): ?>
                        <div class="ticket-card">
                            <div class="ticket-info">
                                <p><strong>ID:</strong> <?= htmlspecialchars($t['ticket_id']) ?></p>
                                <p><strong>Description:</strong> <?= htmlspecialchars($t['description']) ?></p>
                                <p><strong>Class:</strong> <?= htmlspecialchars($t['class_type']) ?></p>
                                <p><strong>Created:</strong> <?= htmlspecialchars($t['creation_date']) ?></p>
                                <p><strong>Expires:</strong> <?= htmlspecialchars($t['expiration_date']) ?></p>
                            </div>
                            <form method="POST" class="ticket-form">
                                <input type="hidden" name="assign_ticket_id" value="<?= htmlspecialchars($t['ticket_id']) ?>">
                                <button type="submit" class="submit small">Assign</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Assigned Tickets -->
        <div class="outer-div">
            <h2>Your Tickets</h2>
            <?php if (empty($studentTickets)): ?>
                <p>You currently have no assigned tickets.</p>
            <?php else: ?>
                <div class="ticket-grid">
                    <?php foreach ($studentTickets as $t): ?>
                        <div class="ticket-card assigned">
                            <div class="ticket-info">
                                <p><strong>ID:</strong> <?= htmlspecialchars($t['ticket_id']) ?></p>
                                <p><strong>Description:</strong> <?= htmlspecialchars($t['description']) ?></p>
                                <p><strong>Class:</strong> <?= htmlspecialchars($t['class_type']) ?></p>
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