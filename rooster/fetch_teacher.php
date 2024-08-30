<?php
require_once '../config.php'; // Ensure the correct path to your config file

header('Content-Type: application/json'); // Set the response header to JSON

// Check if subject_id is passed
if (!isset($_GET['subject_id'])) {
    echo json_encode(['error' => 'Subject ID is required']);
    exit;
}

$subject_id = $_GET['subject_id'];

try {
    // Fetch the teachers assigned to the selected subject
    $stmt = $pdo->prepare("
        SELECT u.id, u.username 
        FROM users u
        JOIN teacher_subject ts ON u.id = ts.teacher_id
        WHERE ts.subject_id = ?
    ");
    $stmt->execute([$subject_id]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the list of teachers in JSON format
    echo json_encode(['success' => true, 'teachers' => $teachers]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
