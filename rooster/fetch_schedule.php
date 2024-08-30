<?php
require_once '../config.php'; // Ensure the correct path to your config file

header('Content-Type: application/json'); // Set the response header to JSON

// Check if class_id is passed
if (!isset($_GET['class_id'])) {
    echo json_encode(['error' => 'Class ID is required']);
    exit;
}

$class_id = $_GET['class_id'];

try {
    // Fetch schedule entries with subject and assigned teacher details
    $stmt = $pdo->prepare("
        SELECT r.day, r.time_slot, s.subject_name, r.subject_id, r.teacher_id, u.username AS teacher_name 
        FROM roosters r
        JOIN subjects s ON r.subject_id = s.id
        LEFT JOIN users u ON r.teacher_id = u.id
        WHERE r.class_id = ?
    ");
    $stmt->execute([$class_id]);
    $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($schedule);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching schedule: ' . $e->getMessage()]);
}
?>
