<?php
session_start();

if (!isset($_SESSION['account_id'])) {
    header('Location: /index.php');
    exit();
}

$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "croissantdb";

try {
    $pdo = new PDO("mysql:host=db;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<p style='color:red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// Fetch all student tickets
$stmt = $pdo->query("
    SELECT 
        st.ticket_id,
        st.description,
        st.creation_date,
        st.expiration_date,
        c.class_type
    FROM student_ticket st
    JOIN class c ON st.class_number = c.class_number
    ORDER BY st.expiration_date ASC
");
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Tickets</title>
    <link rel="stylesheet" href="/styles.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php define('INCLUDED', true);
    include '../components/header.php'; ?>

    <div class="main-content">
        <h1 class="page-title">All Student Tickets</h1>

        <?php if (empty($tickets)): ?>
            <p>No student tickets available at the moment!</p>
        <?php else: ?>
            <table class="ticket-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Class</th>
                        <th>Created</th>
                        <th>Expires</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $t): ?>
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

        <div class="nav-buttons">
            <button class="submit" type="submit">Create Ticket</button>
            <a href="/home.php"><button type="button">Back to Dashboard</button></a>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>
</body>

</html>