<?php
require_once '../config.php'; // Ensure the correct path to your config file

header('Content-Type: application/json'); // Set the response header to JSON

// Disable HTML in error messages and suppress display
ini_set('display_errors', 0);
ini_set('html_errors', 0); 
ini_set('log_errors', 1);
ini_set('error_log', 'C:\xampp\htdocs\test2\Examen\logs\php_errors.log'); // Log errors to this file
error_reporting(E_ALL); 

// Start output buffering to catch unexpected output
ob_start();

// Check if subject_id is passed
if (!isset($_GET['subject_id'])) {
    ob_end_clean(); // Clean the buffer to avoid HTML output
    echo json_encode(['error' => 'Subject ID is required']);
    exit;
}

$subject_id = $_GET['subject_id'];

try {
    // Fetch the teacher assigned to the selected subject from the subjects table
    $stmt = $pdo->prepare("
        SELECT u.id, u.username 
        FROM users u
        JOIN subjects s ON u.id = s.teacher_id
        WHERE s.id = ? AND u.role_id = 3
    ");
    $stmt->execute([$subject_id]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clean the buffer and return the list of teachers without applying any disabled state
    ob_end_clean();
    echo json_encode(['success' => true, 'teachers' => $teachers]);
} catch (PDOException $e) {
    ob_end_clean(); 
    error_log("Error fetching teachers: " . $e->getMessage());
    echo json_encode(['error' => 'Error fetching teachers.']);
}

exit(); // End script execution to prevent any further output
?>
