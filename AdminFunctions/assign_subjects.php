<?php
session_start(); // Start the session
require_once '../config.php';
require_once '../User.php';

$db = new DatabaseConnection();
$user = new User($db->getPdo());

// Check if the user is logged in and has the teacher role (role_id = 3)
if (!$user->isLoggedIn() || $user->getRoleId() != 3) {
    header("Location: ../login.php");
    exit();
}

// Fetch the username directly from the session
$username = $_SESSION['username'] ?? 'Unknown User';

// Fetch the teacher's ID based on the current username from the users table
function fetchTeacherId($pdo, $username) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND role_id = 3");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->fetchColumn();
}

$logged_in_teacher_id = fetchTeacherId($db->getPdo(), $username);

// Fetch all subjects
function fetchSubjects($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM subjects");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get a list of assigned teachers for display
function getAssignedTeachers($teacher_ids, $pdo) {
    if (empty($teacher_ids)) return "None";
    $ids = implode(',', array_map('intval', explode(',', $teacher_ids)));
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id IN ($ids)");
    $stmt->execute();
    return implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

// Handle self-assignment and removal of subjects
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = $_POST['subject_id'];

    if (isset($_POST['assign_subject'])) {
        // Fetch current teacher IDs and append the new one
        $stmt = $db->getPdo()->prepare("SELECT teacher_id FROM subjects WHERE id = :subject_id");
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->execute();
        $current_teacher_ids = $stmt->fetchColumn();

        // Convert current_teacher_ids to an array, handling NULL by using an empty array
        $teacher_ids_array = $current_teacher_ids ? array_filter(explode(',', $current_teacher_ids)) : [];
        if (!in_array($logged_in_teacher_id, $teacher_ids_array)) {
            $teacher_ids_array[] = $logged_in_teacher_id; // Add the logged-in teacher ID
        }

        // Save updated teacher IDs back to the database
        $updated_teacher_ids = implode(',', $teacher_ids_array);
        $stmt = $db->getPdo()->prepare("UPDATE subjects SET teacher_id = :teacher_ids WHERE id = :subject_id");
        $stmt->bindParam(':teacher_ids', $updated_teacher_ids);
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->execute();
        header('Location: assign_subjects.php?success=Subject assigned successfully');
        exit();
    } elseif (isset($_POST['remove_subject'])) {
        // Fetch current teacher IDs and remove the logged-in one
        $stmt = $db->getPdo()->prepare("SELECT teacher_id FROM subjects WHERE id = :subject_id");
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->execute();
        $current_teacher_ids = $stmt->fetchColumn();

        // Convert current_teacher_ids to an array, handling NULL by using an empty array
        $teacher_ids_array = $current_teacher_ids ? array_filter(explode(',', $current_teacher_ids)) : [];
        $teacher_ids_array = array_diff($teacher_ids_array, [$logged_in_teacher_id]); // Remove logged-in teacher ID

        // Save updated teacher IDs back to the database
        $updated_teacher_ids = implode(',', $teacher_ids_array);
        $stmt = $db->getPdo()->prepare("UPDATE subjects SET teacher_id = :teacher_ids WHERE id = :subject_id");
        $stmt->bindParam(':teacher_ids', $updated_teacher_ids);
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->execute();
        header('Location: assign_subjects.php?success=Subject removed successfully');
        exit();
    }
}

$subjects = fetchSubjects($db->getPdo());
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Subjects</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h2>Assign Subjects</h2>
    <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>

    <?php if (isset($_GET['success'])): ?>
        <p><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php endif; ?>

    <!-- List of all subjects with options to assign or remove the current teacher -->
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Subject Name</th>
            <th>Assigned Teachers</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($subjects as $subject): ?>
            <?php $teacher_ids = $subject['teacher_id'] ?? ''; // Use empty string if teacher_id is NULL ?>
            <tr>
                <td><?php echo $subject['id']; ?></td>
                <td><?php echo $subject['subject_name']; ?></td>
                <td><?php echo getAssignedTeachers($teacher_ids, $db->getPdo()); ?></td>
                <td>
                    <?php if (strpos($teacher_ids, (string)$logged_in_teacher_id) !== false): ?>
                        <!-- Option to remove the current teacher from the subject -->
                        <form action="assign_subjects.php" method="POST" style="display:inline-block;">
                            <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                            <button type="submit" name="remove_subject">Remove Myself</button>
                        </form>
                    <?php else: ?>
                        <!-- Option to assign the current teacher to the subject -->
                        <form action="assign_subjects.php" method="POST" style="display:inline-block;">
                            <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                            <button type="submit" name="assign_subject">Assign Myself</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
