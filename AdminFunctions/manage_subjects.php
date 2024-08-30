<?php
// Include the necessary files for database and user handling
require_once '../config.php';
require_once '../User.php';

// Create a new database connection and user instance
$db = new DatabaseConnection();
$user = new User($db->getPdo());

// Fetch subjects from the database using the existing PDO connection
function fetchSubjects($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM subjects");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle add, edit, and delete requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subject'])) {
        $subject_name = $_POST['subject_name'];
        $stmt = $db->getPdo()->prepare("INSERT INTO subjects (subject_name) VALUES (:subject_name)");
        $stmt->bindParam(':subject_name', $subject_name);
        $stmt->execute();
        header('Location: manage_subjects.php?success=Subject added successfully');
        exit();
    } elseif (isset($_POST['edit_subject'])) {
        $id = $_POST['id'];
        $subject_name = $_POST['subject_name'];
        $stmt = $db->getPdo()->prepare("UPDATE subjects SET subject_name = :subject_name WHERE id = :id");
        $stmt->bindParam(':subject_name', $subject_name);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        header('Location: manage_subjects.php?success=Subject updated successfully');
        exit();
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $db->getPdo()->prepare("DELETE FROM subjects WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    header('Location: manage_subjects.php?success=Subject deleted successfully');
    exit();
}

// Fetch subjects for display
$subjects = fetchSubjects($db->getPdo());
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="../style.css"> <!-- Include your stylesheet -->
</head>
<body>
    <h2>Manage Subjects</h2>

    <!-- Display success messages -->
    <?php if (isset($_GET['success'])): ?>
        <p><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php endif; ?>

    <!-- Add new subject form -->
    <form action="manage_subjects.php" method="POST">
        <input type="text" name="subject_name" placeholder="Nieuwe naam" required>
        <button type="submit" name="add_subject">Add Subject</button>
    </form>

    <!-- List of subjects -->
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Subject Name</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($subjects as $subject): ?>
            <tr>
                <td><?php echo $subject['id']; ?></td>
                <td><?php echo $subject['subject_name']; ?></td>
                <td>
                    <form action="manage_subjects.php" method="POST" style="display:inline-block;">
                        <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
                        <input type="text" name="subject_name" placeholder="Nieuwe naam" required>
                        <button type="submit" name="edit_subject">Edit</button>
                    </form>
                    <a href="manage_subjects.php?delete=<?php echo $subject['id']; ?>" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
