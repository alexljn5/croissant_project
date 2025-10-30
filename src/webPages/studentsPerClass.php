<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['account_id'])) {
    header('Location: index.php');
    exit();
}

// Database connection
$pdo = null;
try {
    $pdo = new PDO(
        "mysql:host=db;dbname=croissantdb;charset=utf8",
        "root",
        "password",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("<p style='color:red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// Handle actions and filters
$action = $_POST["action"] ?? "";
$classFilter = $_POST["class_filter"] ?? null;
$studentId = $_POST["student_id"] ?? null;
$showTickets = ($action === "viewTickets" && $studentId);

// Fetch all classes
$classes = $pdo->query("SELECT class_number, class_type FROM class ORDER BY class_type")->fetchAll(PDO::FETCH_ASSOC);

// Fetch students
$studentQuery = "
    SELECT a.account_id, a.first_name, a.last_name, a.email, c.class_type, c.class_number
    FROM account a
    JOIN account_has_class ahc ON a.account_id = ahc.account_id
    JOIN class c ON ahc.class_number = c.class_number
    WHERE a.is_teacher = 0
";
$params = [];
if ($showTickets) {
    // When viewing tickets, get the selected student info
} elseif ($action === "filterStudents" && $classFilter) {
    $studentQuery .= " AND c.class_number = :class_number";
    $params[':class_number'] = $classFilter;
}
$studentQuery .= " ORDER BY a.last_name, a.first_name";
$stmt = $pdo->prepare($studentQuery);
$stmt->execute($params);
$studentList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected student info if viewing tickets
$selectedStudent = null;
if ($showTickets) {
    foreach ($studentList as $student) {
        if ($student['account_id'] == $studentId) {
            $selectedStudent = $student;
            break;
        }
    }
    // Fetch tickets
    $stmt = $pdo->prepare("
        SELECT st.ticket_id, st.description, st.expiration_date, st.creation_date, c.class_type
        FROM student_ticket st
        JOIN account_has_student_ticket ahst ON st.ticket_id = ahst.student_ticket_id
        JOIN class c ON st.class_number = c.class_number
        WHERE ahst.account_id = :student_id
        ORDER BY st.expiration_date DESC
    ");
    $stmt->execute([':student_id' => $studentId]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students per Class</title>
    <link rel="stylesheet" href="/styles.css?v=<?= time() ?>">
</head>

<body>
    <?php
    define('INCLUDED', true);
    include '../components/header.php';
    ?>

    <div class="main-content">
        <h1 class="page-title">Students per Class</h1>

        <?php if ($showTickets && $selectedStudent): ?>
            <!-- Tickets View Mode -->
            <div class="tickets-section">
                <div class="nav-buttons">
                    <a href="?<?= $classFilter ? 'class_filter=' . urlencode($classFilter) : '' ?>">
                        <button type="button" class="submit">← Back to Students</button>
                    </a>
                </div>

                <h2>Tickets for:
                    <?= htmlspecialchars($selectedStudent['first_name'] . ' ' . $selectedStudent['last_name']) ?>
                </h2>
                <p><strong>Email:</strong> <?= htmlspecialchars($selectedStudent['email']) ?></p>
                <p><strong>Class:</strong> <?= htmlspecialchars($selectedStudent['class_type']) ?></p>

                <div class="ticket-container-vertical">
                    <div class="ticket-grid">
                        <?php if (isset($tickets) && $tickets): ?>
                            <?php foreach ($tickets as $ticket):
                                $isExpired = ($ticket['expiration_date'] < date('Y-m-d'));
                                $statusClass = $isExpired ? 'expired' : 'assigned';
                                ?>
                                <div class="ticket-card <?= $statusClass ?>">
                                    <div class="ticket-info">
                                        <p><strong>Description:</strong> <?= htmlspecialchars($ticket['description']) ?></p>
                                        <p><strong>Class:</strong> <?= htmlspecialchars($ticket['class_type']) ?></p>
                                        <p><strong>Expires:</strong> <?= htmlspecialchars($ticket['expiration_date']) ?></p>
                                        <?php if ($isExpired): ?>
                                            <p style="color: #dc3545; font-weight: bold;">Expired</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No tickets found for this student.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Students List View -->
            <!-- Filter Form + Back Button Row -->
            <div class="filter-row">
                <form method="POST" action="" class="filter-form" style="display:flex; align-items:center; gap:10px;">
                    <input type="hidden" name="action" value="filterStudents">
                    <label for="class_filter">Filter by class:</label>
                    <select name="class_filter" id="class_filter">
                        <option value="">-- All Classes --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= htmlspecialchars($class['class_number']) ?>"
                                <?= ($classFilter == $class['class_number']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['class_type']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="submit">Filter</button>
                </form>

                <?php if ($classFilter): ?>
                    <a href="?" class="clear-filter" onclick="return confirm('Clear filter?')">Clear Filter</a>
                <?php endif; ?>
            </div>
            <a href="/home.php"><button type="button" class="submit">Back to Dashboard</button></a>
            <!-- Students Grid -->
            <?php if ($studentList): ?>
                <div class="student-grid-container">
                    <div class="dashboard-grid">
                        <?php foreach ($studentList as $student):
                            $fullName = htmlspecialchars($student["first_name"] . " " . $student["last_name"]);
                            $email = htmlspecialchars($student["email"]);
                            $classType = htmlspecialchars($student["class_type"]);
                            $id = $student["account_id"];
                            ?>
                            <div class="dashboard-item">
                                <h3><?= $fullName ?></h3>
                                <p><strong>Email:</strong> <?= $email ?></p>
                                <p><strong>Class:</strong> <?= $classType ?></p>
                                <form method="POST" action="" style="margin-top: 15px;">
                                    <input type="hidden" name="action" value="viewTickets">
                                    <input type="hidden" name="student_id" value="<?= $id ?>">
                                    <input type="hidden" name="class_filter" value="<?= htmlspecialchars($classFilter ?? '') ?>">
                                    <button type="submit" class="submit small">View Tickets</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No students found. Try a different filter!</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include '../components/footer.php'; ?>
</body>

</html>