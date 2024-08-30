<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'C:\xampp\htdocs\test2\Examen\config.php';

$data = json_decode(file_get_contents('php://input'), true);

$day = $data['day'];
$time_slot = $data['time_slot'];

try {
    // Query to find teachers who are already scheduled at the same time on the same day
    $stmt = $pdo->prepare("
        SELECT teacher_id FROM roosters 
        WHERE day = ? AND time_slot = ?
    ");
    $stmt->execute([$day, $time_slot]);
    $unavailableTeachers = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Debugging output
    error_log('Unavailable Teachers: ' . implode(', ', $unavailableTeachers));

    echo json_encode(['success' => true, 'unavailable_teachers' => $unavailableTeachers]);
} catch (PDOException $e) {
    // Log the error message for debugging
    error_log('PDOException: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
