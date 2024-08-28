<?php
session_start();
require_once '..\config.php';
require_once '..\User.php';


$db = new DatabaseConnection();
$user = new User($db->getPdo());

// Controleer of de gebruiker is ingelogd en een admin of ewen docent is
if (!$user->isLoggedIn() || ($user->getRoleId() != 1 && $user->getRoleId() != 3)) {

    header("Location: ../login.php");
    exit();
}

// Haal alle studenten op uit de database
$students = $user->getAllStudents();

$error = '';
$success = '';

// Verwerk het formulier om een student te verwijderen
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_student'])) {
    $student_id = $_POST['student_id'];
    if ($user->deleteUser($student_id)) {
        $success = "Student succesvol verwijderd.";
        $students = $user->getAllStudents(); // Haal de lijst opnieuw op
    } else {
        $error = "Er is een fout opgetreden bij het verwijderen van de student.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beheer Studenten</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<header>
    <h1>Beheer Studenten</h1>
    <nav>
        <a href="../dashboard.php">Terug naar Dashboard</a>
        <a href="add_student.php">Voeg Nieuwe Student Toe</a>
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
            <th>Klas</th>
            <th>Mentor</th>
            <th>Acties</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['username']); ?></td>
                <td><?php echo htmlspecialchars($student['email']); ?></td>
                <td><?php echo htmlspecialchars($student['class_name']); ?></td>
                <td><?php echo htmlspecialchars($student['mentor_name'] ?? 'Geen mentor toegewezen'); ?></td>
                <td>
                    <a href="edit_student.php?id=<?php echo $student['id']; ?>">Bewerk</a> |
                    <form method="POST" action="manage_students.php" style="display:inline;">
                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                        <button type="submit" name="delete_student" onclick="return confirm('Weet u zeker dat u deze student wilt verwijderen?');">Verwijder</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>