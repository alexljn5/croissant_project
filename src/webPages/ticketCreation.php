<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    header('Location: /index.php');
    exit();
}

// Database connection
$servername = "db"; // Matches MySQL service name in docker-compose.yml
$username = "root";
$password = "password"; // Must match MYSQL_ROOT_PASSWORD in docker-compose.yml
$dbname = "croissantdb";

try {
    // Retry mechanism to handle MySQL initialization delay
    $retries = 5;
    $retryInterval = 5; // Seconds
    while ($retries > 0) {
        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            break; // Exit loop on successful connection
        } catch (PDOException $e) {
            $retries--;
            if ($retries == 0) {
                throw $e; // Re-throw to handle in outer catch
            }
            sleep($retryInterval); // Wait before retrying
        }
    }

    // Fetch classes for dropdown
    $stmt = $pdo->query("SELECT class_number, class_type FROM class ORDER BY class_type");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p class='ongeldig'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_number = $_POST['class_number'] ?? '';
    $expiration_date = $_POST['expiration_date'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($class_number) || empty($expiration_date) || empty($description)) {
        $message = "<p class='ongeldig'>Please fill in all fields</p>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO student_ticket (expiration_date, creation_date, description, class_number) VALUES (?, CURDATE(), ?, ?)");
            $stmt->execute([$expiration_date, $description, $class_number]);
            $ticket_id = $pdo->lastInsertId();

            $message = "<p class='geldig'>Ticket successfully created! Ticket ID: " . htmlspecialchars($ticket_id) . "</p>";
        } catch (PDOException $e) {
            $message = "<p class='ongeldig'>Error creating ticket: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Tickets - Tick-IT</title>
    <link rel="stylesheet" href="/styles.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php define('INCLUDED', true);
    include '../components/header.php'; ?>

    <div class="main-content">
        <h1 class="page-title">Create New Student Ticket</h1>

        <?php echo $message; ?>

        <form method="post" action="" class="outer-div">
            <label for="class_number">Select Class:</label>
            <select class="form-input" name="class_number" id="class_number" required>
                <option value="">-- Choose a class --</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo htmlspecialchars($class['class_number']); ?>">
                        <?php echo htmlspecialchars($class['class_type']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="expiration_date">Expiration Date:</label>
            <input class="form-input" type="date" id="expiration_date" name="expiration_date" required>

            <label for="description">Assignment Description:</label>
            <textarea class="form-input" id="description" name="description" rows="4" required
                placeholder="Enter ticket description..."></textarea>

            <div class="nav-buttons">
                <button class="submit" type="submit">Create Ticket</button>
                <a href="/home.php"><button type="button">Back to Dashboard</button></a>
            </div>
        </form>
    </div>
    <?php include '../components/footer.php'; ?>
</body>

</html>