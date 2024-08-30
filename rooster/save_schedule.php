<?php
require_once '../config.php'; // Ensure correct path to your config file

header('Content-Type: application/json'); // Set the response header to JSON

// Decode the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Extract necessary information from the request
$class_id = $data['class_id'];
$schedule = $data['schedule'];

try {
    // Iterate through the schedule data and save each entry
    foreach ($schedule as $entry) {
        $day = $entry['day'];
        $time_slot = $entry['time_slot'];
        $subject_id = $entry['subject_id'];
        $teacher_id = $entry['teacher_id']; // Ensure teacher_id is being captured

        // Check if there is already a schedule entry for the same class, day, and time slot
        $stmt = $pdo->prepare("
            SELECT id FROM roosters 
            WHERE class_id = ? AND day = ? AND time_slot = ?
        ");
        $stmt->execute([$class_id, $day, $time_slot]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            // Update existing schedule entry
            $stmt = $pdo->prepare("
                UPDATE roosters 
                SET subject_id = ?, teacher_id = ? 
                WHERE id = ?
            ");
            $stmt->execute([$subject_id, $teacher_id, $existing]);
        } else {
            // Insert new schedule entry
            $stmt = $pdo->prepare("
                INSERT INTO roosters (class_id, day, time_slot, subject_id, teacher_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$class_id, $day, $time_slot, $subject_id, $teacher_id]);
        }
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Log the error message for debugging
    error_log('Error saving schedule: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
