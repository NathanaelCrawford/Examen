<?php
session_start();
require_once '../config.php';
require_once '../User.php';

$db = new DatabaseConnection();
$user = new User($db->getPdo());

// Check if the user is logged in and has the teacher role (role_id = 3)
if (!$user->isLoggedIn() || $user->getRoleId() != 3) {
    header("Location: ../login.php");
    exit();
}

// Fetch current teacher data based on session username
$username = $_SESSION['username'] ?? 'Unknown User';

// Fetch user data
function fetchUserData($pdo, $username) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$currentUser = fetchUserData($db->getPdo(), $username);

// Handle form submission to update user data
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = $_POST['username'];
    $newEmail = $_POST['email'];
    $newPassword = $_POST['password'];

    try {
        // Update username and email
        $stmt = $db->getPdo()->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
        $stmt->bindParam(':username', $newUsername);
        $stmt->bindParam(':email', $newEmail);
        $stmt->bindParam(':id', $currentUser['id']);
        $stmt->execute();

        // Update password if provided
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->getPdo()->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $currentUser['id']);
            $stmt->execute();
        }

        // Update session username if it was changed
        $_SESSION['username'] = $newUsername;
        $success = 'Profile updated successfully.';
    } catch (PDOException $e) {
        $error = 'Error updating profile: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h2>Edit Profile</h2>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <!-- Ensure the form action points to the correct page -->
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($currentUser['username']); ?>" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
        </div>
        <div>
            <label for="password">New Password (leave blank to keep current):</label>
            <input type="password" id="password" name="password">
        </div>
        <div>
            <button type="submit">Update Profile</button>
        </div>
    </form>

    <a href="../dashboard.php">Back to Dashboard</a>
</body>
</html>

