<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['account_id'])) {
    header('Location: index.php');
    exit();
}

// Set flag for included files
define('INCLUDED', true);

// Get user type for conditional rendering
$isTeacher = $_SESSION['is_teacher'] ?? false;
$isAdmin = $_SESSION['is_admin'] ?? false;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tick-IT</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include 'components/header.php'; ?>

    <div class="main-content">
        <div class="welcome-section">
            <h2>Welcome to your Dashboard</h2>
            <p>Email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
        </div>

        <div class="dashboard-grid">
            <?php if (!$isTeacher): ?>
                <div class="dashboard-item">
                    <a href="webPages/ticketCreation.php">
                    <div class="dashboard-icon">
                    <img src="img/penpapier.png"
                    alt="Create ticket"
                        style="width:20px; height:40px; filter:grayscale(1.2);" />
                        </div>
                        <h3>Create Student Ticket</h3>
                        <p>Create new student tickets</p>
                    </a>
                </div>
            <?php endif; ?>

            <div class="dashboard-item">
                <a href="webPages/ticketPage.php">
                    <div class="dashboard-icon">
                    <img src="img/penpapier.png"
                    alt="Create ticket"
                        style="width:20px; height:40px; filter:grayscale(1.2);" />
                        </div>
                    <h3>View Student Tickets</h3>
                    <p>View and manage student tickets</p>
                </a>
            </div>

            <?php if ($isTeacher): ?>
                <div class="dashboard-item">
                    <a href="webPages/docentTicketPage.php">
                    <div class="dashboard-icon">
                    <img src="img/penpapier.png"
                    alt="Create ticket"
                        style="width:20px; height:40px; filter:grayscale(1.2);" />
                        </div>
                        <h3>Create Teacher Ticket</h3>
                        <p>Create new teacher tickets</p>
                    </a>
                </div>
                <div class="dashboard-item">
                    <a href="webPages/viewTeacherTickets.php">
                    <div class="dashboard-icon">
                    <img src="img/eyes.png"
                    alt="Create ticket"
                        style="width:20px; height:40px; filter:grayscale(1.2);" />
                        </div>
                        <h3>View Teacher Tickets</h3>
                        <p>View and manage teacher tickets</p>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <div class="dashboard-item">
                    <a href="webPages/adminPage.php">
                    <div class="dashboard-icon">
                    <img src="img/tickItLogo.png"
                    alt="Create ticket"
                        style="width:20px; height:40px; filter:grayscale(1.2);" />
                        </div>
                        <h3>Admin Panel</h3>
                        <p>System administration</p>
                    </a>
                </div>
                <div class="dashboard-item">
                    <a href="webPages/addVakken.php">
                    <div class="dashboard-icon">
                    <img src="img/penpapier.png"
                    alt="Create ticket"
                        style="width:40px; height:40px; filter:grayscale(1.2);" />
                        </div>
                        <h3>Manage Classes</h3>
                        <p>Add or remove classes</p>
                    </a>
                </div>
            <?php endif; ?>

            <div class="dashboard-item">
                <a href="webPages/studentsPerClass.php">
                    <div class="dashboard-icon">
                    <img src="img/guy (2).png"
                    alt="Create ticket"
                        style="width:40px; height:60px; filter:grayscale(1.2);" />
                        </div>
                    <h3>Class Overview</h3>
                    <p>View students per class</p>
                </a>
            </div>

            <div class="dashboard-item">
                <a href="logout.php">
                    <div class="dashboard-icon">
                    <img src="img/Door.png"
                    alt="Create ticket"
                        style="width:30px; height:45px; filter:grayscale(1.2);" />
                        </div>
                    <h3>Logout</h3>
                    <p>Exit your account</p>
                </a>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>

</html>