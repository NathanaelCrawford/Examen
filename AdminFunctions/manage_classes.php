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

// Haal alle klassen op uit de database
$classes = $user->getAllClasses();

// Haal de studenten van een geselecteerde klas op

// Haal de studenten van een geselecteerde klas op
$selected_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;
$students = [];
if ($selected_class_id) {
    $stmt = $db->getPdo()->prepare("
        SELECT s.id, s.username, s.email, ms.conversation AS mentoring_note 
        FROM users s
        LEFT JOIN mentor_gesprekken ms ON s.id = ms.student_id
        WHERE s.class_id = :class_id AND s.role_id = 4
    ");
    $stmt->bindParam(':class_id', $selected_class_id);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}



// Verwerk het formulier om een mentorgesprek toe te voegen
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
    $stmt->bindParam(':mentor_id', $_SESSION['user_id']); // Gebruik de id van de ingelogde gebruiker als mentor_id
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
        <a href="dashboard.php">Terug naar Dashboard</a>
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

    <h2>Klassen</h2>
    <ul>
        <?php foreach ($classes as $class): ?>
            <li><a href="manage_classes.php?class_id=<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_name']); ?></a></li>
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
                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                    <td>
                        <form method="POST" action="manage_classes.php?class_id=<?php echo $selected_class_id; ?>">
                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                            <input type="text" name="mentoring_note" value="<?php echo htmlspecialchars($student['mentoring_note'] ?? ''); ?>">
                            <button type="submit" name="add_note">Opslaan</button>
                        </form>
                    </td>

                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>