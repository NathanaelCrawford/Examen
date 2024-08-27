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

// Haal de docentgegevens op
if (isset($_GET['id'])) {
    $teacher_id = $_GET['id'];
    $teacher = $user->getUserById($teacher_id);
    if (!$teacher) {
        $error = "Docent niet gevonden.";
    }
}

// Verwerk het formulier als het is ingediend
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_POST['teacher_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Werk de docentgegevens bij in de database
    if ($user->updateUser($teacher_id, $username, $email, 2)) {
        $success = "Docent succesvol bijgewerkt!";
        $teacher = $user->getUserById($teacher_id); // Update de gegevens op de pagina
    } else {
        $error = "Er is een fout opgetreden bij het bijwerken van de docent.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bewerk Docent</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<header>
    <h1>Bewerk Docent</h1>
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
    <?php if ($teacher): ?>
        <form method="POST" action="edit_teacher.php?id=<?php echo $teacher['id']; ?>">
            <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
            <div>
                <label for="username">Gebruikersnaam:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($teacher['username']); ?>" required>
            </div>
            <div>
                <label for="email">E-mailadres:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
            </div>
            <div>
                <button type="submit">Bijwerken</button>
            </div>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
