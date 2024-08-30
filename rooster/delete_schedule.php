<?php
include 'C:\xampp\htdocs\test2\Examen\config.php';

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);

$class_id = $data['class_id'];
$day = $data['day'];
$time_slot = $data['time_slot'];

try {
    $stmt = $pdo->prepare("
        DELETE FROM roosters WHERE class_id = ? AND day = ? AND time_slot = ?
    ");
    $stmt->execute([$class_id, $day, $time_slot]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
