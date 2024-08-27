<?php
session_start();
require_once 'config.php';
require_once 'User.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $db = new DatabaseConnection();
    $user = new User($db->getPdo());

    if ($user->login($email, $password)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Ongeldig e-mailadres of wachtwoord.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <h2>Inloggen</h2>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div>
            <label for="email">E-mailadres:</label>
            <input type="email" id="email" name="email" required placeholder="Voer je e-mail in">
        </div>
        <div>
            <label for="password">Wachtwoord:</label>
            <input type="password" id="password" name="password" required placeholder="Voer je wachtwoord in">
        </div>
        <div>
            <button type="submit">Inloggen</button>
        </div>
    </form>
</main>
</body>
</html>
