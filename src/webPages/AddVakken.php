<?php
session_start();


$servername = "localhost";
$username = "root";
$password = "password"; 
$dbname = "croissantdb";

$action = $_POST["action"] ?? "";

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


if ($action === "add" && !empty($_POST["klastype"])) {
    $Vaknaam = trim($_POST["klastype"]);
    try {
        $insert_stmt = $pdo->prepare("INSERT INTO klas (klastype) VALUES (:vaknaam)");
        $insert_stmt->execute([":vaknaam" => $Vaknaam]);
        echo "<p style='color:green;'>Klastype <b>" . htmlspecialchars($Vaknaam) . "</b> is aangemaakt.</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Fout bij aanmaken: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

if ($action === "delete" && !empty($_POST["Id"])) {
    $delete_id = (int) $_POST["Id"];
    try {
        $stmt = $pdo->prepare("DELETE FROM klas WHERE klasnr = :id");
        $stmt->execute([":id" => $delete_id]);
        echo "<p style='color:green;'>Klas met nummer $delete_id is verwijderd.</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Fout bij verwijderen: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}


try {
    $KlasTypeList = "SELECT klasnr, klastype FROM klas";
    $KlasListResult = $pdo->query($KlasTypeList);
    $rows = $KlasListResult->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p style='color:red;'>Fout bij ophalen: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Vakken</title>
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<h1>Overzicht Klassen/Vakken</h1>

<?php
if ($rows) {
    echo "<ul>";
    foreach ($rows as $row) {
        $Id = htmlspecialchars($row["klasnr"]);
        $Naam = htmlspecialchars($row["klastype"]);

        echo "<li style='margin-bottom:10px;'>
                Klasnummer: $Id <br>
                Klas/Vak type: $Naam <br>
                <form method='post' action='' style='display:inline;'>
                    <input type='hidden' name='action' value='delete'>
                    <input type='hidden' name='Id' value='$Id'>
                    <button type='submit'>Verwijder</button>
                </form>
              </li>";
    }
    echo "</ul>";
} else {
    echo "<p>Geen klassen gevonden.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
      <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Tick-IT</title>
  <script src="javascript/account-id-assigner.js"></script>
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

<hr>

<h2>Nieuwe klas/vak toevoegen</h2>
<form method="post" action="">
    <input type="hidden" name="action" value="add">
    <label for="klastype">Naam:</label>
    <input type="text" id="klastype" name="klastype" required>
    <button type="submit">Toevoegen</button>
</form>

<hr>
<a href="index.php" class="button">Terug naar index</a>

  <div class="bodem">
    <p>© Tick-IT 2025</p>
  </div>
</body>
</html>
