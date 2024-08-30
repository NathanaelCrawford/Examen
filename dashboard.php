<?php
session_start();
require_once 'config.php';
require_once 'User.php';

$db = new DatabaseConnection();
$user = new User($db->getPdo());

// Controleer of de gebruiker is ingelogd
if (!$user->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Haal de gebruikersnaam en rol op uit de sessie
$username = $_SESSION['username'];
$role_id = $_SESSION['role_id'];

function displayAdminDashboard() {
    echo '<h2>Admin Dashboard</h2>';
    echo '<p>Welkom, ' . htmlspecialchars($_SESSION['username']) . '!</p>';
    echo '<ul>';
    echo '<li><a href="AdminFunctions/manage_teachers.php">Beheer Docenten</a></li>';
    echo '<li><a href="AdminFunctions/manage_classes.php">Beheer Klassen</a></li>';
    echo '<li><a href="AdminFunctions/manage_students.php">Beheer Studenten</a></li>';
    echo '<li><a href="rooster/index.php">Beheer Roosters</a></li>';
    echo '<li><a href="AdminFunctions/manage_subjects.php">Beheer Vakken</a></li>';
    echo '</ul>';
}

function displayTeacherDashboard() {
    echo '<h2>Docenten Dashboard</h2>';
    echo '<p>Welkom, ' . htmlspecialchars($_SESSION['username']) . '!</p>';
    echo '<ul>';
    echo '<li><a href="AdminFunctions/manage_students.php">Beheer Studenten</a></li>';
    echo '<li><a href="AdminFunctions/manage_subjects.php">Beheer Vakken</a></li>';
    echo '<li><a href="AdminFunctions/manage_classes.php">Beheer Klassen</a></li>';
    echo '<li><a href="view_schedule.php">Bekijk Rooster</a></li>';
    echo '<li><a href="mentor_students.php">Mentor Gesprekken</a></li>';
    echo '<li><a href="AdminFunctions/assign_subjects.php">subjects</a></li>';
    echo '</ul>';
}

function displayStudentDashboard() {
    echo '<h2>Studenten Dashboard</h2>';
    echo '<p>Welkom, ' . htmlspecialchars($_SESSION['username']) . '!</p>';
    echo '<ul>';
    echo '<li><a href="rooster/view_schedule.php">Bekijk Rooster</a></li>';
    echo '</ul>';
}

function displaySchedulerDashboard() {
    echo '<h2>Roostermakers Dashboard</h2>';
    echo '<p>Welkom, ' . htmlspecialchars($_SESSION['username']) . '!</p>';
    echo '<ul>';
    echo '<li><a href="rooster/index.php">Maak Rooster</a></li>';
    echo '<li><a href="edit_schedule.php">Bewerk Rooster</a></li>';
    echo '<li><a href="view_schedule.php">Bekijk Rooster</a></li>';
    echo '</ul>';
}

?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css"> <!-- Koppeling naar de CSS -->
</head>
<body>
<header>
    <h1>Dashboard</h1>
    <nav>
        <a href="logout.php">Uitloggen</a>
    </nav>
</header>

<main>
    <?php
    // Toon het juiste dashboard op basis van de rol
    switch ($role_id) {
        case 1:
            displayAdminDashboard();
            break;
        case 2:
            displaySchedulerDashboard();
            break;
        case 3:
            displayTeacherDashboard();
            break;
        case 5:
            displayStudentDashboard();
            break;
        default:
            echo "<p>Onbekende rol. Neem contact op met de beheerder.</p>";
            break;
    }
    ?>
</main>
</body>
</html>
