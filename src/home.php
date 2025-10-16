<?php
session_start();

if (!isset($_SESSION['accountnr'])) {
    header("Location: index.php"); // go back to signup/login if no session
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - Tick-IT</title>
  <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
  <div class="top">
    <img class="logo-border" src="./img/tickItLogo.png" alt="Tick-IT Logo">
  </div>

  <div class="page-wrapper">
    <h1>Welkom, <?php echo htmlspecialchars($_SESSION['username']); ?> 🎉</h1>
    <p>Je accountnummer is: <?php echo htmlspecialchars($_SESSION['accountnr']); ?></p>
    <p>Je e-mailadres is: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
  </div>

<<<<<<< Updated upstream
  <div class="bodem">
    <p>© Tick-IT 2025</p>
  </div>
=======
        <div class="dashboard-grid">
            <?php if (!$isTeacher): ?>
                <div class="dashboard-item">
                    <a href="webPages/ticketCreation.php">
                        <div class="dashboard-icon">
                        <img src="img/penpaper.png" alt="Create Ticket" style="width:40px;height:40px;filter:invert(0.1);">
                        </div>
                        <h3>Create Student Ticket</h3>
                        <p>Create new student tickets</p>
                    </a>
                </div>
            <?php endif; ?>

            <div class="dashboard-item">
                <a href="webPages/ticketPage.php">
                    <div class="dashboard-icon">
                    <img src="img/eyes.png" alt="Check Tickets" style="width:40px;height:40px;filter:invert(0.1);">
                    </div>
                    <h3>View Student Tickets</h3>
                    <p>View and manage student tickets</p>
                </a>
            </div>

            <?php if ($isTeacher): ?>
                <div class="dashboard-item">
                    <a href="webPages/docentTicketPage.php">
                        <div class="dashboard-icon">
                        <img src="img/guy.png" alt="Check Teacher Tickets" style="width:40px;height:40px;filter:invert(0.1);">
                        </div>
                        <h3>Create Teacher Ticket</h3>
                        <p>Create new teacher tickets</p>
                    </a>
                </div>
                <div class="dashboard-item">
                    <a href="webPages/viewTeacherTickets.php">
                        <div class="dashboard-icon">👀</div>
                        <h3>View Teacher Tickets</h3>
                        <p>View and manage teacher tickets</p>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <div class="dashboard-item">
                    <a href="webPages/adminPage.php">
                        <div class="dashboard-icon">⚙️</div>
                        <h3>Admin Panel</h3>
                        <p>System administration</p>
                    </a>
                </div>
                <div class="dashboard-item">
                    <a href="webPages/addVakken.php">
                        <div class="dashboard-icon">📚</div>
                        <h3>Manage Classes</h3>
                        <p>Add or remove classes</p>
                    </a>
                </div>
            <?php endif; ?>

            <div class="dashboard-item">
                <a href="webPages/studentsPerClass.php">
                    <div class="dashboard-icon">
                    <img src="img/guy.png" alt="Check Students" style="width:40px;height:50px;filter:invert(0.1);">
                    </div>                    <h3>Class Overview</h3>
                    <p>View students per class</p>
                </a>
            </div>

            <div class="dashboard-item">
                <a href="logout.php">
                    <div class="dashboard-icon">🚪</div>
                    <h3>Logout</h3>
                    <p>Exit your account</p>
                </a>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
>>>>>>> Stashed changes
</body>
</html>
