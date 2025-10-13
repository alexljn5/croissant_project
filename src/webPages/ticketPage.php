<?php
session_start();

// --------------------------------------------------
// Database connection
// --------------------------------------------------
$servername = "db"; // Matches MySQL service name in docker-compose.yml
$username = "root";
$password = "password"; // Must match MYSQL_ROOT_PASSWORD in docker-compose.yml
$dbname = "croissantdb";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// --------------------------------------------------
// Check login
// --------------------------------------------------
if (!isset($_SESSION['account_id'])) {
    header('Location: login.php');
    exit();
}

$account_id = $_SESSION['account_id'];

// --------------------------------------------------
// 1. Fetch all student tickets
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

// --------------------------------------------------
// 2. Fetch tickets the student already has
// --------------------------------------------------
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
// 3. Handle ticket assignment (POST)
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_ticket_id'])) {
    $ticketId = intval($_POST['assign_ticket_id']);

    // Check if already assigned
    $check = $pdo->prepare("SELECT * FROM croissantdb.account_has_student_ticket WHERE account_id = ? AND student_ticket_id = ?");
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
// 4. Filter available tickets (exclude ones already taken by this student)
// --------------------------------------------------
$assignedIds = array_column($studentTickets, 'ticket_id');
$availableTickets = array_filter($allTickets, function ($t) use ($assignedIds) {
    return !in_array($t['ticket_id'], $assignedIds);
});
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Page 🐰</title>
    <link rel="stylesheet" href="../styles.css">
</head>

<body class="ticket-body">
    <div class="ticket-container">
        <h1 class="ticket-heading">🎫 Student Ticket Dashboard</h1>

        <h2 class="ticket-heading">Available Tickets</h2>
        <?php if (empty($availableTickets)): ?>
            <p class="ticket-message">No available tickets right now, Cream~ 🐰💤</p>
        <?php else: ?>
            <table class="ticket-table">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Description</th>
                        <th>Class Type</th>
                        <th>Created</th>
                        <th>Expires</th>
                        <th>Assign</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($availableTickets as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['ticket_id']) ?></td>
                            <td><?= htmlspecialchars($t['description']) ?></td>
                            <td><?= htmlspecialchars($t['class_type']) ?></td>
                            <td><?= htmlspecialchars($t['creation_date']) ?></td>
                            <td><?= htmlspecialchars($t['expiration_date']) ?></td>
                            <td>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="assign_ticket_id"
                                        value="<?= htmlspecialchars($t['ticket_id']) ?>">
                                    <button type="submit" class="ticket-button">Assign</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2 class="ticket-heading">Your Tickets</h2>
        <?php if (empty($studentTickets)): ?>
            <p class="ticket-message">You have no assigned tickets, nya~ 💤</p>
        <?php else: ?>
            <table class="ticket-table">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Description</th>
                        <th>Class Type</th>
                        <th>Created</th>
                        <th>Expires</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($studentTickets as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['ticket_id']) ?></td>
                            <td><?= htmlspecialchars($t['description']) ?></td>
                            <td><?= htmlspecialchars($t['class_type']) ?></td>
                            <td><?= htmlspecialchars($t['creation_date']) ?></td>
                            <td><?= htmlspecialchars($t['expiration_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>