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

// Haal alle docenten op uit de database
$teachers = $user->getAllTeachers();

$error = '';
$success = '';

// Verwerk het formulier om een docent te verwijderen
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_teacher'])) {
    $teacher_id = $_POST['teacher_id'];
    if ($user->deleteUser($teacher_id)) {
        $success = "Docent succesvol verwijderd.";
        $teachers = $user->getAllTeachers(); // Haal de lijst opnieuw op
    } else {
        $error = "Er is een fout opgetreden bij het verwijderen van de docent.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beheer Docenten</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<header>
    <h1>Beheer Docenten</h1>
    <nav>
        <a href="../dashboard.php">Terug naar Dashboard</a>
        <a href="add_teacher.php">Voeg Nieuwe Docent Toe</a> <!-- Link om een nieuwe docent toe te voegen -->
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

    <table>
        <thead>
        <tr>
            <th>Gebruikersnaam</th>
            <th>E-mailadres</th>
            <th>Acties</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($teachers as $teacher): ?>
            <tr>
                <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                <td>
                    <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>">Bewerk</a> |
                    <form method="POST" action="manage_teachers.php" style="display:inline;">
                        <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                        <button type="submit" name="delete_teacher" onclick="return confirm('Weet u zeker dat u deze docent wilt verwijderen?');">Verwijder</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
