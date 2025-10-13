<?php
session_start();

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
                die("<p style='color:red;'>Database connection failed after retries: " . htmlspecialchars($e->getMessage()) . " (Code: " . $e->getCode() . ")</p>");
            }
            sleep($retryInterval); // Wait before retrying
        }
    }

    // Insert test teacher account
    $stmt = $pdo->prepare("INSERT IGNORE INTO account (account_id, creation_time, first_name, last_name, password, phone_number, email, gender, is_teacher, is_admin, address, postal_code) 
                          VALUES (1, CURRENT_TIME(), 'Test', 'Teacher', 'test123', '0612345678', 'test@test.nl', 'M', 1, 0, 'Test Street 1', '1234AB')");
    $stmt->execute();
} catch (PDOException $e) {
    die("<p style='color:red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

try {
    $stmt = $pdo->query("SELECT class_number, class_type FROM class ORDER BY class_type");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p style='color:red;'>Error fetching classes: " . htmlspecialchars($e->getMessage()) . "</p>");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_number = $_POST['class_number'] ?? '';
    $expiration_date = $_POST['expiration_date'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($class_number) || empty($expiration_date) || empty($description)) {
        $message = "<p style='color:red;'>Please fill in all fields</p>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO student_ticket (expiration_date, creation_date, description, class_number) VALUES (?, CURDATE(), ?, ?)");
            $stmt->execute([$expiration_date, $description, $class_number]);
            $ticket_id = $pdo->lastInsertId();

            $message = "<p style='color:green;'>Ticket successfully created! Ticket ID: " . htmlspecialchars($ticket_id) . "</p>";
        } catch (PDOException $e) {
            $message = "<p style='color:red;'>Error creating ticket: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket</title>
</head>

<body>
    <h1>Create New Ticket</h1>

    <?php echo $message; ?>

    <form method="post" action="">
        <p>
            <label for="class_number">Select Class:</label><br>
            <select name="class_number" id="class_number" required>
                <option value="">-- Choose a class --</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo htmlspecialchars($class['class_number']); ?>">
                        <?php echo htmlspecialchars($class['class_type']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="expiration_date">Expiration Date:</label><br>
            <input type="date" id="expiration_date" name="expiration_date" required>
        </p>

        <p>
            <label for="description">Assignment Description:</label><br>
            <textarea id="description" name="description" rows="4" cols="50" required></textarea>
        </p>

        <p>
            <button type="submit">Create Ticket</button>
        </p>
    </form>

    <p>
        <a href="index.php">Back to Dashboard</a>
    </p>
</body>

</html>