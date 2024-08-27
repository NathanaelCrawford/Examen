<?php
session_start();
require_once '..\config.php';
require_once '..\user.php';


$db = new DatabaseConnection();
$user = new User($db->getPdo());

// Controleer of de gebruiker is ingelogd en een admin is
if (!$user->isLoggedIn() || $user->getRoleId() != 1) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Verwerk het formulier als het is ingediend
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Voeg de nieuwe docent toe aan de database
    if ($user->createUser($username, $email, $password, 2)) {
        $success = "Nieuwe docent succesvol toegevoegd!";
    } else {
        $error = "Er is een fout opgetreden bij het toevoegen van de docent.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voeg Nieuwe Docent Toe</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<header>
    <h1>Voeg Nieuwe Docent Toe</h1>
    <nav>
        <a href="manage_teachers.php">Terug naar Beheer Docenten</a>
        <a href="../logout.php">Uitloggen</a>
    </nav>
</header>

<main>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>
    <form method="POST" action="add_teacher.php">
        <div>
            <label for="username">Gebruikersnaam:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="email">E-mailadres:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Wachtwoord:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <button type="submit">Voeg Docent Toe</button>
        </div>
    </form>
</main>
</body>
</html>
