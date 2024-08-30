<?php
include 'C:\xampp\htdocs\test2\Examen\config.php';

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);

$class_id = $data['class_id'];
$schedule = $data['schedule'][0];

$day = $schedule['day'];
$time_slot = $schedule['time_slot'];
$subject_id = $schedule['subject_id'];
$teacher_id = $schedule['teacher_id'];

try {
    $stmt = $pdo->prepare("
        INSERT INTO roosters (class_id, day, time_slot, subject_id, teacher_id) 
        VALUES (?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE subject_id = VALUES(subject_id), teacher_id = VALUES(teacher_id)
    ");
    $stmt->execute([$class_id, $day, $time_slot, $subject_id, $teacher_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
