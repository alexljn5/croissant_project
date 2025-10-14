<?php
session_start();

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
        echo "<ul>";
        foreach ($student_list as $row) {
            $name = htmlspecialchars($row["first_name"] . " " . $row["last_name"]);
            $email = htmlspecialchars($row["email"]);
            $class_type = htmlspecialchars($row["class_type"]);
            echo "<li style='margin-bottom:10px;'>Student name: $name ($email) - Class: $class_type</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No students found.</p>";
    }

    if ($action === "viewTickets" && !empty($_POST['student_id'])) {
    $student_id = $_POST['student_id'];

    // Huidige tickets
    $stmt = $pdo->prepare("
        SELECT st.description, st.expiration_date, c.class_type
        FROM croissantdb.student_ticket st
        JOIN croissantdb.account_has_student_ticket ahst
            ON st.ticket_id = ahst.student_ticket_id
        JOIN croissantdb.class c
            ON st.class_number = c.class_number
        WHERE ahst.account_id = :student_id
          AND st.expiration_date >= CURDATE()
    ");
    $stmt->execute([':student_id' => $student_id]);
    $current_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Huidige tickets:</h3>";
    if ($current_tickets) {
        echo "<ul>";
        foreach ($current_tickets as $t) {
            echo "<li>" . htmlspecialchars($t['description']) . " - Class: " . htmlspecialchars($t['class_type']) . " - Exp: " . htmlspecialchars($t['expiration_date']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Geen huidige tickets.</p>";
    }

    // Voltooide tickets
    $stmt = $pdo->prepare("
        SELECT st.description, st.expiration_date, c.class_type
        FROM croissantdb.student_ticket st
        JOIN croissantdb.account_has_student_ticket ahst
            ON st.ticket_id = ahst.student_ticket_id
        JOIN croissantdb.class c
            ON st.class_number = c.class_number
        WHERE ahst.account_id = :student_id
          AND st.expiration_date < CURDATE()
    ");
    $stmt->execute([':student_id' => $student_id]);
    $completed_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Voltooide tickets:</h3>";
    if ($completed_tickets) {
        echo "<ul>";
        foreach ($completed_tickets as $t) {
            echo "<li>" . htmlspecialchars($t['description']) . " - Class: " . htmlspecialchars($t['class_type']) . " - Exp: " . htmlspecialchars($t['expiration_date']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Geen voltooide tickets.</p>";
    }
}

    ?>

</body>

</html>