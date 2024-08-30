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
$username = $_SESSION['username'] ?? 'Unknown User'; // Ensure the session contains the username

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

// Handle self-assignment and removal of subjects
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = $_POST['subject_id'];
    
    if (isset($_POST['assign_subject'])) {
        // Assign the subject to the logged-in teacher
        $stmt = $db->getPdo()->prepare("UPDATE subjects SET teacher_id = :teacher_id WHERE id = :subject_id");
        $stmt->bindParam(':teacher_id', $logged_in_teacher_id);
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->execute();
        header('Location: assign_subjects.php?success=Subject assigned successfully');
        exit();
    } elseif (isset($_POST['remove_subject'])) {
        // Remove the subject assignment from the logged-in teacher
        $stmt = $db->getPdo()->prepare("UPDATE subjects SET teacher_id = NULL WHERE id = :subject_id AND teacher_id = :teacher_id");
        $stmt->bindParam(':teacher_id', $logged_in_teacher_id);
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
            <th>Assigned Teacher</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($subjects as $subject): ?>
            <tr>
                <td><?php echo $subject['id']; ?></td>
                <td><?php echo $subject['subject_name']; ?></td>
                <td>
                    <?php
                    if ($subject['teacher_id'] == $logged_in_teacher_id) {
                        echo htmlspecialchars($username);
                    } elseif ($subject['teacher_id']) {
                        // Fetch the username of the assigned teacher
                        $stmt = $db->getPdo()->prepare("SELECT username FROM users WHERE id = :teacher_id");
                        $stmt->bindParam(':teacher_id', $subject['teacher_id']);
                        $stmt->execute();
                        $assigned_teacher = $stmt->fetchColumn();
                        echo htmlspecialchars($assigned_teacher);
                    } else {
                        echo "None";
                    }
                    ?>
                </td>
                <td>
                    <?php if ($subject['teacher_id'] == $logged_in_teacher_id): ?>
                        <!-- Option to remove the current teacher from the subject -->
                        <form action="assign_subjects.php" method="POST" style="display:inline-block;">
                            <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                            <button type="submit" name="remove_subject">Remove Myself</button>
                        </form>
                    <?php elseif (!$subject['teacher_id']): ?>
                        <!-- Option to assign the current teacher to the subject -->
                        <form action="assign_subjects.php" method="POST" style="display:inline-block;">
                            <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                            <button type="submit" name="assign_subject">Assign Myself</button>
                        </form>
                    <?php else: ?>
                        Assigned to another teacher
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
