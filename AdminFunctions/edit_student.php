<?php
session_start();
require_once '../config.php';
require_once '../User.php';

$db = new DatabaseConnection();
$user = new User($db->getPdo());


// Controleer of de gebruiker is ingelogd en een admin of docent is
if (!$user->isLoggedIn() || ($user->getRoleId() != 1 && $user->getRoleId() != 3)) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Haal de studentgegevens op
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];
    $student = $user->getUserById($student_id);
    if (!$student) {
        $error = "Student niet gevonden.";
    }
}

// Haal alle klassen op
$classes = $user->getAllClasses();

// Verwerk het formulier als het is ingediend
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $class_id = $_POST['class_id'];

    // Werk de studentgegevens bij in de database
    if ($user->updateUser($student_id, $username, $email, 4, $class_id)) {
        $success = "Student succesvol bijgewerkt!";
        $student = $user->getUserById($student_id); // Update de gegevens op de pagina
    } else {
        $error = "Er is een fout opgetreden bij het bijwerken van de student.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bewerk Student</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<header>
    <h1>Bewerk Student</h1>
    <nav>
        <a href="manage_students.php">Terug naar Beheer Studenten</a>
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
    <?php if ($student): ?>
        <form method="POST" action="edit_student.php?id=<?php echo $student['id']; ?>">
            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
            <div>
                <label for="username">Gebruikersnaam:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($student['username']); ?>" required>
            </div>
            <div>
                <label for="email">E-mailadres:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
            </div>
            <div>
                <label for="class_id">Klas:</label>
                <select id="class_id" name="class_id" required>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php if ($class['id'] == $student['class_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($class['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="mentor">Mentor:</label>
                <input type="text" id="mentor" value="<?php echo htmlspecialchars($student['mentor_name'] ?? 'Geen mentor toegewezen'); ?>" disabled>
            </div>
            <div>
                <button type="submit">Bijwerken</button>
            </div>
        </form>
    <?php endif; ?>
</main>
</body>
</html>