<?php
// Connection to the SQLite database
$db = new SQLite3('../database/lunch_roulette.db');

// Drop and recreate the `users` table
$db->exec("DROP TABLE IF EXISTS users");
$db->exec("
    CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        participate_in_roulette INTEGER NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
");

// Drop and recreate the `roulette_winners` table
$db->exec("DROP TABLE IF EXISTS roulette_winners");
$db->exec("
    CREATE TABLE roulette_winners (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user1 INTEGER NOT NULL,
        user2 INTEGER NOT NULL,
        user3 INTEGER DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
");

// Drop and recreate the `current_roulette` table
$db->exec("DROP TABLE IF EXISTS current_roulette");
$db->exec("
    CREATE TABLE current_roulette (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user1 INTEGER NOT NULL,
        user2 INTEGER NOT NULL,
        user3 INTEGER DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
");

// Drop and recreate the `admins` table
$db->exec("DROP TABLE IF EXISTS admins");
$db->exec("
    CREATE TABLE admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        password TEXT NOT NULL,
        participate_in_roulette INTEGER NOT NULL,
        isAdmin INTEGER NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
");

// Drop and recreate the `settings` table
$db->exec("DROP TABLE IF EXISTS settings");
$db->exec("
    CREATE TABLE settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key TEXT NOT NULL,
        setting_value TEXT NOT NULL
    );
");

// Insert default setting for admin registration
$db->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('admin_registered', 'no')");

// Close the database connection
$db->close();

echo "All tables have been reset successfully.";
?>
