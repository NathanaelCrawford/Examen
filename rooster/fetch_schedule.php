<?php
require_once '../config.php'; // Ensure the correct path to your config file

// Disable HTML in error messages and suppress display
ini_set('display_errors', 0);
ini_set('html_errors', 0); // Disable HTML formatting in errors
ini_set('log_errors', 1);
ini_set('error_log', 'C:\xampp\htdocs\test2\Examen\logs\php_errors.log'); // Log errors to this file
error_reporting(E_ALL); // Report all errors

// Start output buffering to catch and clean any unexpected output
ob_start();
header('Content-Type: application/json'); // Set the response header to JSON early to prevent any HTML

$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    ob_end_clean(); // End and clean the buffer to ensure only JSON output
    echo json_encode(['error' => 'Class ID is required']);
    exit();
}

try {
    // Prepare the query to fetch schedule entries
    $stmt = $pdo->prepare("
        SELECT r.day, r.time_slot, s.subject_name, u.username AS teacher_name
        FROM roosters r
        JOIN subjects s ON r.subject_id = s.id
        JOIN users u ON r.teacher_id = u.id
        WHERE r.class_id = ?
    ");
    $stmt->execute([$class_id]);
    $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$schedule) {
        throw new Exception("No schedule found for class ID: $class_id");
    }

    // Clean the buffer and output the result as JSON
    ob_end_clean(); // Fully clean the buffer to ensure no stray output
    echo json_encode($schedule);
} catch (Exception $e) {
    // Clean any buffer and output a JSON error message
    ob_end_clean();
    error_log("Error fetching schedule: " . $e->getMessage());
    echo json_encode(['error' => 'Error fetching schedule. Details logged.']);
}

exit(); // Ensure script ends here to prevent any further output
?>