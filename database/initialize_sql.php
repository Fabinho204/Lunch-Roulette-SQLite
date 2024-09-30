<?php
// Step 1: Connect to the database
$servername = "localhost";
$username = "root"; // Update with your DB username
$password = ""; // Update with your DB password

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$createDatabase = "CREATE DATABASE IF NOT EXISTS siemens_lunch_roulette CHARACTER SET utf8 COLLATE utf8_unicode_ci";
if ($conn->query($createDatabase) === TRUE) {
    echo "Database created or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

$conn->select_db('siemens_lunch_roulette');

$conn->begin_transaction();

try {
    // roulette_winners table
    $createRouletteWinners = "
        CREATE TABLE IF NOT EXISTS `roulette_winners` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user1` INT(11) NOT NULL,
            `user2` INT(11) NOT NULL,
            `user3` INT(11) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP (),
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ";
    $conn->query($createRouletteWinners);

    // users table
    $createUsers = "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(200) NOT NULL,
            `participate_in_roulette` TINYINT(1) NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP (),
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ";
    $conn->query($createUsers);

    // admins table
    $createAdmins = "
        CREATE TABLE IF NOT EXISTS `admins` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(200) NOT NULL,
            `password` VARCHAR(300) NOT NULL,
            `participate_in_roulette` TINYINT(1) NOT NULL,
            `isAdmin` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP (),
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ";
    $conn->query($createAdmins);

    // settings table
    $createSettings = "
        CREATE TABLE IF NOT EXISTS `settings` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `setting_key` VARCHAR(255) NOT NULL DEFAULT 'admin_registered',
            `setting_value` VARCHAR(255) NOT NULL DEFAULT 'no',
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ";
    $conn->query($createSettings);

    // Insert initial setting for admin registration
    $insertSetting = "INSERT INTO `settings` (setting_key, setting_value)
                      VALUES ('admin_registered', 'no')
                      ON DUPLICATE KEY UPDATE setting_value = 'no'";
    $conn->query($insertSetting);

    // current_roulette table
    $createCurrentRoulette = "
        CREATE TABLE IF NOT EXISTS `current_roulette` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user1` INT(11) NOT NULL,
            `user2` INT(11) NOT NULL,
            `user3` INT(11) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP (),
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ";
    $conn->query($createCurrentRoulette);

    // Commit transaction
    $conn->commit();

    echo "Tables created successfully!";
} catch (Exception $e) {
    // If there's an error, rollback the transaction
    $conn->rollback();
    echo "Failed to create tables: " . $e->getMessage();
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log-In</title>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Local Style and Script -->
    <link rel="stylesheet" href="../css/nav.css">
    <script src="../js/script.js"></script>

    <!-- Schriftart Font -->
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Noto+Sans+Arabic:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .full-screen-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>

<body>

    <!-- Full screen container for the button -->
    <div class="full-screen-container">
        <a href="/company-lunch-roulette/visuals/index.php" class="btn btn-login">HOME</a>
    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
