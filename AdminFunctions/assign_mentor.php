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

$error = '';
$success = '';

// Fetch all classes
function fetchClasses($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM klassen");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get the teacher's ID based on the session data
$teacher_id = $_SESSION['user_id']; // Assuming user_id is stored in the session

// Handle form submission for assigning as mentor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_mentor'])) {
    $class_id = $_POST['class_id'];

    // Assign the logged-in teacher as the mentor for the selected class
    $stmt = $db->getPdo()->prepare("UPDATE klassen SET mentor_id = :mentor_id WHERE id = :class_id");
    $stmt->bindParam(':mentor_id', $teacher_id);
    $stmt->bindParam(':class_id', $class_id);

    if ($stmt->execute()) {
        $success = "You have been successfully assigned as the mentor for the class!";
    } else {
        $error = "There was an error assigning you as the mentor.";
    }
}

$classes = fetchClasses($db->getPdo());
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Mentor</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<header>
    <h1>Assign Yourself as Mentor</h1>
    <nav>
        <a href="../dashboard.php">Back to Dashboard</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>

<main>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST" action="assign_mentor.php">
        <label for="class-select">Select a class to mentor:</label>
        <select name="class_id" id="class-select" required>
            <option value="">--Select Class--</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?php echo $class['id']; ?>">
                    <?php echo htmlspecialchars($class['class_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="assign_mentor">Assign Myself as Mentor</button>
    </form>
</main>
</body>
</html>
