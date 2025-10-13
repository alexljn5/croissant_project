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
$sql = "SELECT klasnr, klastype FROM croissantdb.klas ORDER BY klastype";
$result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$message = "";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Studenten per Klas</title>
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
    <label for="klas">Filter op klas:</label>
    <select name="klas" id="klas">
        <option value="">-- Kies klas --</option>
        <?php foreach($result as $row): ?>
            <option value="<?= htmlspecialchars($row['klasnr']) ?>" 
                    <?= ($klasFilter == $row['klasnr']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($row['klastype']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Filter</button>
</form>

<?php
// Haal studenten op
$StudentList = "SELECT a.voornaam, a.achternaam, a.email, k.klastype 
                FROM croissantdb.account a 
                JOIN croissantdb.account_has_klas ahk ON a.accountnr = ahk.account_accountnr 
                JOIN croissantdb.klas k ON ahk.klas_klasnr = k.klasnr 
                WHERE a.isDocent = 0";
$params = [];

if ($action === "filterStudenten" && $klasFilter) {
    $StudentList .= " AND k.klasnr = :klas";
    $params[':klas'] = $klasFilter;
}

$stmt = $pdo->prepare($StudentList);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($students) {
    echo "<ul>";
    foreach ($students as $row) {
        $naam = htmlspecialchars($row["voornaam"] . " " . $row["achternaam"]);
        $email = htmlspecialchars($row["email"]);
        $klas = htmlspecialchars($row["klastype"]);
        echo "<li style='margin-bottom:10px;'>Naam student: $naam ($email) - Klas: $klas</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Geen studenten gevonden.</p>";
}
?>

</body>
</html>