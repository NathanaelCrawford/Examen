<?php
include 'C:\xampp\htdocs\test2\Examen\config.php';

// Fetch classes for dropdown
$classes = $pdo->query("SELECT * FROM klassen")->fetchAll(PDO::FETCH_ASSOC);

// Fetch teachers from the users table where role_id is 3
$teachers = $pdo->query("SELECT * FROM users WHERE role_id = 3")->fetchAll(PDO::FETCH_ASSOC);

// Fetch subjects for the dropdown
$subjects = $pdo->query("SELECT * FROM subjects")->fetchAll(PDO::FETCH_ASSOC);

// Set the start date (Monday of the current week)
$startDate = new DateTime('monday this week');

// Create an array of days with corresponding dates
$daysOfWeek = [];
for ($i = 0; $i < 5; $i++) {
    $dayName = $startDate->format('l'); // Day name (e.g., Monday)
    $date = $startDate->format('d-m'); // Date (e.g., 12-09)
    $daysOfWeek[] = [
        'name' => $dayName,
        'date' => $date,
    ];
    $startDate->modify('+1 day');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Schedule</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body style="background-color: #007BFF;">
    <div class="container">
        <a href="../dashboard.php" class="btn-back">Terug naar Dashboard</a>
        <h1>School Schedule</h1>

        <label for="class-select">Select a class:</label>
        <select id="class-select">
            <option value="">--Select Class--</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <div id="schedule-container" style="display: none;">
            <table id="schedule-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <?php foreach ($daysOfWeek as $day): ?>
                            <th><?= $day['name'] ?> <br><small><?= $day['date'] ?></small></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 8; $i < 16; $i++): ?>
                        <tr>
                            <td><?= $i ?>:00 - <?= $i + 1 ?>:00</td>
                            <?php for ($day = 1; $day <= 5; $day++): ?>
                                <td class="schedule-slot" data-day="<?= $day ?>" data-time="<?= $i ?>">
                                    <div class="schedule-content"></div>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Schedule</h2>
            <form id="modal-form">
                <label for="subject-select">Subject:</label>
                <select id="subject-select" name="subject_id" required>
                    <option value="">--Select Subject--</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['subject_name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="teacher-select">Teacher:</label>
                <select id="teacher-select" name="teacher_id" required>
                    <option value="">--Select Teacher--</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= $teacher['id'] ?>"><?= htmlspecialchars($teacher['username']) ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="hidden" id="modal-class-id" name="class_id">
                <input type="hidden" id="modal-day" name="day">
                <input type="hidden" id="modal-time" name="time_slot">
                <button type="submit">Save</button>
                <button type="button" id="delete-schedule-btn" class="remove-btn">Remove</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="scripts.js"></script>
</body>
</html>
