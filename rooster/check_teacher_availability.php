<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config.php'; // Ensure correct path to your config file

header('Content-Type: application/json'); // Set the response header to JSON

// Get the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Extract the day and time slot from the input data
$day = $data['day'];
$time_slot = $data['time_slot'];

try {
    // Query to find teachers who are already scheduled at the same time on the same day
    $stmt = $pdo->prepare("
        SELECT teacher_id 
        FROM roosters 
        WHERE day = ? AND time_slot = ?
    ");
    $stmt->execute([$day, $time_slot]);
    $unavailableTeachers = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Debugging output
    error_log('Unavailable Teachers: ' . implode(', ', $unavailableTeachers));

    // Return the list of unavailable teachers
    echo json_encode(['success' => true, 'unavailable_teachers' => $unavailableTeachers]);
} catch (PDOException $e) {
    // Log the error message for debugging
    error_log('PDOException: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
