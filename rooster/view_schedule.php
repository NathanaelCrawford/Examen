<?php
include 'C:\xampp\htdocs\test2\Examen\config.php';

// Fetch classes for dropdown
$classes = $pdo->query("SELECT * FROM klassen")->fetchAll(PDO::FETCH_ASSOC);

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
    <title>View Schedule</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>View Schedule</h1>
        </header>

        <main>
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
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="view_schedule.js"></script>
</body>
</html>
