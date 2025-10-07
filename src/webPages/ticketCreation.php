<<<<<<< Updated upstream
=======
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

  
    $stmt = $pdo->prepare("INSERT IGNORE INTO account (accountnr, aanmaakstijd, voornaam, achternaam, wachtwoord, telefoonnr, email, geslacht, isDocent, isAdmin, adres, postcode) 
                          VALUES (1, CURRENT_TIME(), 'Test', 'Docent', 'test123', '0612345678', 'test@test.nl', 'M', 1, 0, 'Teststraat 1', '1234AB')");
    $stmt->execute();
    
} catch (PDOException $e) {
    die("<p style='color:red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}


try {
    $stmt = $pdo->query("SELECT klasnr, klastype FROM klas ORDER BY klastype");
    $vakken = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p style='color:red;'>Fout bij ophalen vakken: " . htmlspecialchars($e->getMessage()) . "</p>");
}

$message = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $klasnr = $_POST['klasnr'] ?? '';
    $verloopdatum = $_POST['verloopdatum'] ?? '';
    $omschrijving = $_POST['omschrijving'] ?? '';

    if (empty($klasnr) || empty($verloopdatum) || empty($omschrijving)) {
        $message = "<p style='color:red;'>Vul alle velden in</p>";
    } else {
        try {
            
            $stmt = $pdo->prepare("INSERT INTO student_ticket (verloopdatum, aanmaaksdatum) VALUES (?, CURDATE())");
            $stmt->execute([$verloopdatum]);
            $ticketId = $pdo->lastInsertId();
            
            $message = "<p style='color:green;'>Ticket succesvol aangemaakt! Ticket nummer: " . htmlspecialchars($ticketId) . "</p>";
            $message = "<p style='color:green;'>Ticket succesvol aangemaakt!</p>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "<p style='color:red;'>Fout bij aanmaken ticket: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Aanmaken</title>
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
  
    <h1>Nieuwe Ticket Aanmaken</h1>
    
    <?php echo $message; ?>

    <form method="post" action="">
        <p>
            <label for="klasnr">Selecteer Vak:</label><br>
            <select name="klasnr" id="klasnr" required>
                <option value="">-- Kies een vak --</option>
                <?php foreach ($vakken as $vak): ?>
                    <option value="<?php echo htmlspecialchars($vak['klasnr']); ?>">
                        <?php echo htmlspecialchars($vak['klastype']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="verloopdatum">Verloopdatum:</label><br>
            <input type="date" id="verloopdatum" name="verloopdatum" required>
        </p>

        <p>
            <label for="omschrijving">Omschrijving van de opdracht:</label><br>
            <textarea id="omschrijving" name="omschrijving" rows="4" cols="50" required></textarea>
        </p>

        <p>
            <button type="submit">Ticket Aanmaken</button>
        </p>
    </form>

    <p>
        <a href="index.php">Terug naar Dashboard</a>
    </p>
</body>
</html>
>>>>>>> Stashed changes
