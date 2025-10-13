<?php
session_start();

// Database connectie
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
$klasFilter = $_POST["klas"] ?? null;

// Haal alle klassen op
$sql = "SELECT DISTINCT klas FROM account";
$result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$message = "";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nieuwe Pagina</title>
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
    <input type="hidden" name="action" value="filterStudenten">
    <label for="student">Filter op klas:</label>
    <select name="klas" id="student">
        <option value="">-- Kies klas --</option>
        <?php foreach($result as $row): ?>
            <option value="<?= $row['Id'] ?>" <?= ($klasFilter == $row['Id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['klas']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Filter</button>
</form>

<?php
// Haal studenten op
$StudentList = "SELECT voornaam FROM account where isDocent = false";
$params = [];

if ($action === "filterStudenten" && $klasFilter) {
    $StudentList .= " WHERE klas = :klas";
    $params[':klas'] = $klasFilter;
}

$stmt = $pdo->prepare($StudentList);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($students) {
    echo "<ul>";
    foreach ($students as $row) {
        $naam = htmlspecialchars($row["voornaam"]);
        echo "<li style='margin-bottom:10px;'>Naam student: $naam</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Geen studenten gevonden.</p>";
}
?>

</body>
</html>
