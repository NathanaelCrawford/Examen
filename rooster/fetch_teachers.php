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
    // Fetch the teacher IDs assigned to the selected subject from the subjects table
    $stmt = $pdo->prepare("SELECT teacher_id FROM subjects WHERE id = ?");
    $stmt->execute([$subject_id]);
    $teacher_ids = $stmt->fetchColumn();

    if (empty($teacher_ids)) {
        ob_end_clean();
        echo json_encode(['success' => true, 'teachers' => []]); // Return an empty list if no teachers are assigned
        exit;
    }

    // Convert the comma-separated teacher IDs into an array and prepare for the query
    $teacher_ids_array = array_map('intval', explode(',', $teacher_ids));

    // Fetch all teachers based on the IDs in the array
    $placeholders = implode(',', array_fill(0, count($teacher_ids_array), '?'));
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id IN ($placeholders) AND role_id = 3");
    $stmt->execute($teacher_ids_array);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clean the buffer and return the list of teachers
    ob_end_clean();
    echo json_encode(['success' => true, 'teachers' => $teachers]);
} catch (PDOException $e) {
    ob_end_clean(); 
    error_log("Error fetching teachers: " . $e->getMessage());
    echo json_encode(['error' => 'Error fetching teachers.']);
}

exit(); // End script execution to prevent any further output
?>
