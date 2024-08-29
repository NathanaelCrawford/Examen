<?php
session_start();
require_once '../config.php';
require_once '../User.php';

$db = new DatabaseConnection();
$user = new User($db->getPdo());

// Controleer of de gebruiker is ingelogd en een admin of docent is
if (!$user->isLoggedIn() || ($user->getRoleId() != 1 && $user->getRoleId() != 3)) {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

// Verwerk het formulier om een nieuwe klas toe te voegen (alleen beschikbaar voor admins)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_class'])) {
    if ($user->getRoleId() == 1) { // Alleen admins kunnen klassen toevoegen
        $class_name = $_POST['class_name'];

        // Voeg de nieuwe klas toe aan de database
        $stmt = $db->getPdo()->prepare("INSERT INTO klassen (class_name) VALUES (:class_name)");
        $stmt->bindParam(':class_name', $class_name);

        if ($stmt->execute()) {
            $success = "Klas succesvol toegevoegd!";
        } else {
            $error = "Er is een fout opgetreden bij het toevoegen van de klas.";
        }
    } else {
        $error = "U heeft geen toestemming om een nieuwe klas toe te voegen.";
    }
}

// Haal alle klassen op uit de database
$classes = $user->getAllClasses();

// Haal de studenten van een geselecteerde klas op
$selected_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;
$students = [];
if ($selected_class_id) {
    $stmt = $db->getPdo()->prepare("
        SELECT s.id, s.username, s.email, ms.conversation AS mentoring_note 
        FROM users s
        LEFT JOIN mentor_gesprekken ms ON s.id = ms.student_id AND ms.mentor_id = :mentor_id
        WHERE s.class_id = :class_id AND s.role_id = 4
    ");
    $stmt->bindParam(':class_id', $selected_class_id);
    $stmt->bindParam(':mentor_id', $_SESSION['user_id']); // Alleen gesprekken voor de ingelogde mentor tonen
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Verwerk het formulier om een mentorgesprek toe te voegen of bij te werken
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_note'])) {
    $student_id = $_POST['student_id'];
    $mentoring_note = $_POST['mentoring_note'];

    // Voeg de mentorgespreknote toe of werk deze bij
    $stmt = $db->getPdo()->prepare("
        INSERT INTO mentor_gesprekken (student_id, mentor_id, conversation)
        VALUES (:student_id, :mentor_id, :conversation)
        ON DUPLICATE KEY UPDATE conversation = :conversation
    ");
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':mentor_id', $_SESSION['user_id']);
    $stmt->bindParam(':conversation', $mentoring_note);

    if ($stmt->execute()) {
        $success = "Mentorgesprek succesvol toegevoegd/bijgewerkt!";
        // Refresh de studentenlijst
        header("Location: manage_classes.php?class_id=$selected_class_id");
        exit();
    } else {
        $error = "Er is een fout opgetreden bij het toevoegen/bijwerken van het mentorgesprek.";
    }
}

// Verwerk het formulier om een mentorgesprek te verwijderen
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_note'])) {
    $student_id = $_POST['student_id'];

    // Verwijder het mentorgesprek
    $stmt = $db->getPdo()->prepare("DELETE FROM mentor_gesprekken WHERE student_id = :student_id AND mentor_id = :mentor_id");
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':mentor_id', $_SESSION['user_id']);

    if ($stmt->execute()) {
        $success = "Mentorgesprek succesvol verwijderd!";
        // Refresh de studentenlijst
        header("Location: manage_classes.php?class_id=$selected_class_id");
        exit();
    } else {
        $error = "Er is een fout opgetreden bij het verwijderen van het mentorgesprek.";
    }
}

// Verwerk het formulier om een student aan een klas toe te voegen
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_student'])) {
    $student_id = $_POST['student_id'];
    $class_id = $_POST['class_id'];

    // Update de student met de nieuwe class_id
    $stmt = $db->getPdo()->prepare("UPDATE users SET class_id = :class_id WHERE id = :student_id AND role_id = 4");
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':class_id', $class_id);

    if ($stmt->execute()) {
        $success = "Student succesvol toegewezen aan de klas!";
        // Refresh de studentenlijst
        header("Location: manage_classes.php?class_id=$selected_class_id");
        exit();
    } else {
        $error = "Er is een fout opgetreden bij het toewijzen van de student aan de klas.";
    }
}

// Verwerk het formulier om een student te bewerken
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_student'])) {
    $student_id = $_POST['student_id'];
    $new_username = $_POST['new_username'];
    $new_email = $_POST['new_email'];

    // Update de studentinformatie
    $stmt = $db->getPdo()->prepare("UPDATE users SET username = :username, email = :email WHERE id = :student_id AND role_id = 4");
    $stmt->bindParam(':username', $new_username);
    $stmt->bindParam(':email', $new_email);
    $stmt->bindParam(':student_id', $student_id);

    if ($stmt->execute()) {
        $success = "Studentinformatie succesvol bijgewerkt!";
        // Refresh de studentenlijst
        header("Location: manage_classes.php?class_id=$selected_class_id");
        exit();
    } else {
        $error = "Er is een fout opgetreden bij het bijwerken van de studentinformatie.";
    }
}

// Verwerk het formulier om een student te verwijderen
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_student'])) {
    $student_id = $_POST['student_id'];

    // Verwijder de student
    $stmt = $db->getPdo()->prepare("DELETE FROM users WHERE id = :student_id AND role_id = 4");
    $stmt->bindParam(':student_id', $student_id);

    if ($stmt->execute()) {
        $success = "Student succesvol verwijderd!";
        // Refresh de studentenlijst
        header("Location: manage_classes.php?class_id=$selected_class_id");
        exit();
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
    <title>Beheer Klassen</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<header>
    <h1>Beheer Klassen</h1>
    <nav>
        <a href="../dashboard.php">Terug naar Dashboard</a>
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

    <?php if ($user->getRoleId() == 1): ?>
        <!-- Alleen admins kunnen nieuwe klassen toevoegen -->
        <h2>Nieuwe Klas Toevoegen</h2>
        <form method="POST" action="manage_classes.php">
            <div>
                <label for="class_name">Klasnaam:</label>
                <input type="text" id="class_name" name="class_name" required>
            </div>
            <div>
                <button type="submit" name="add_class">Voeg Klas Toe</button>
            </div>
        </form>
    <?php endif; ?>

    <h2>Bestaande Klassen</h2>
    <ul>
        <?php foreach ($classes as $class): ?>
            <li>
                <?php echo htmlspecialchars($class['class_name']); ?>
                <?php if ($user->getRoleId() == 1): ?>
                    <!-- Alleen admins kunnen de klas bewerken -->
                    <form method="POST" action="manage_classes.php" style="display:inline;">
                        <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                        <input type="text" name="new_class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>" required>
                        <button type="submit" name="edit_class">Bewerk</button>
                        <button type="submit" name="delete_class" onclick="return confirm('Weet u zeker dat u deze klas en alle bijbehorende studenten wilt verwijderen?');">Verwijder</button>
                    </form>
                <?php else: ?>
                    <a href="manage_classes.php?class_id=<?php echo $class['id']; ?>">Bekijk studenten</a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($selected_class_id): ?>
        <h2>Studenten in Klas <?php echo htmlspecialchars($classes[array_search($selected_class_id, array_column($classes, 'id'))]['class_name']); ?></h2>
        <table>
            <thead>
            <tr>
                <th>Gebruikersnaam</th>
                <th>E-mailadres</th>
                <th>Mentorgesprek</th>
                <th>Acties</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td>
                        <form method="POST" action="manage_classes.php?class_id=<?php echo $selected_class_id; ?>">
                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                            <input type="text" name="new_username" value="<?php echo htmlspecialchars($student['username']); ?>" required>
                    </td>
                    <td>
                        <input type="email" name="new_email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                    </td>
                    <td>
                        <input type="text" name="mentoring_note" value="<?php echo htmlspecialchars($student['mentoring_note'] ?? ''); ?>">
                        <button type="submit" name="add_note">Opslaan</button>
                    </td>
                    <td>
                        <button type="submit" name="edit_student">Bewerk</button>
                        <button type="submit" name="delete_student" onclick="return confirm('Weet u zeker dat u deze student wilt verwijderen?');">Verwijder</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Voeg Student toe aan Klas</h2>
        <form method="POST" action="manage_classes.php?class_id=<?php echo $selected_class_id; ?>">
            <div>
                <label for="student_id">Selecteer Student:</label>
                <select name="student_id" id="student_id" required>
                    <?php
                    // Haal alle studenten op die nog niet aan een klas zijn toegewezen of naar een andere klas moeten
                    $available_students = $db->getPdo()->query("SELECT id, username FROM users WHERE role_id = 4 AND (class_id IS NULL OR class_id != $selected_class_id)")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($available_students as $student) {
                        echo '<option value="' . htmlspecialchars($student['id']) . '">' . htmlspecialchars($student['username']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <button type="submit" name="assign_student">Toewijzen aan Klas</button>
            </div>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
