<?php
// Include the database connection
require_once('../database/db.php');

// Start a transaction for safety
$conn->begin_transaction();

try {
    // Step 1: Drop all existing tables
    $conn->query("DROP TABLE IF EXISTS `roulette_winners`");
    $conn->query("DROP TABLE IF EXISTS `users`");
    $conn->query("DROP TABLE IF EXISTS `current_roulette`");

    // Step 2: Recreate all tables
    
    // Recreate roulette_winners table
    $conn->query("
        CREATE TABLE IF NOT EXISTS `roulette_winners` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user1` INT(11) NOT NULL,
            `user2` INT(11) NOT NULL,
            `user3` INT(11) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ");

    // Recreate users table
    $conn->query("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(200) NOT NULL,
            `participate_in_roulette` TINYINT(1) NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ");

    // Recreate current_roulette table
    $conn->query("
        CREATE TABLE IF NOT EXISTS `current_roulette` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user1` INT(11) NOT NULL,
            `user2` INT(11) NOT NULL,
            `user3` INT(11) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ");

    // Commit the transaction if everything is successful
    $conn->commit();

    echo "Tables dropped and recreated successfully!";
} catch (Exception $e) {
    // If there's an error, rollback the transaction
    $conn->rollback();
    echo "Failed to drop and recreate tables: " . $e->getMessage();
}

// Close the connection
$conn->close();
?>
