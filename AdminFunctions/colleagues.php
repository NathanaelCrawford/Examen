<?php
session_start();
require_once '../config.php';
require_once '../User.php';

// Check if the user is logged in and has the teacher role (role_id = 3)
$db = new DatabaseConnection();
$user = new User($db->getPdo());

if (!$user->isLoggedIn() || $user->getRoleId() != 3) {
    header("Location: ../login.php");
    exit();
}

// Fetch all teachers from the users table where role_id is 3
function fetchTeachers($pdo) {
    $stmt = $pdo->prepare("SELECT username, email FROM users WHERE role_id = 3 ORDER BY username ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$teachers = fetchTeachers($db->getPdo());
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Colleagues</title>
    <link rel="stylesheet" href="../style.css"> <!-- Adjust path as needed -->
</head>
<body>
    <h2>Colleagues</h2>

    <!-- Display the list of teachers -->
    <table border="1">
        <tr>
            <th>Username</th>
            <th>Email</th>
        </tr>
        <?php foreach ($teachers as $teacher): ?>
            <tr>
                <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="teacher_dashboard.php">Back to Dashboard</a> <!-- Link back to the dashboard -->
</body>
</html>
