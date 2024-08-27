<?php
// Verbindung zur SQLite-Datenbank herstellen
$db = new SQLite3('lunch_roulette.db');

// Erstellen der Tabellen
$db->exec("
    CREATE TABLE IF NOT EXISTS roulette_winners (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user1 INTEGER NOT NULL,
        user2 INTEGER NOT NULL,
        user3 INTEGER DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
");

$db->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        participate_in_roulette INTEGER NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
");

$db->exec("
    CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        password TEXT NOT NULL,
        participate_in_roulette INTEGER NOT NULL,
        isAdmin INTEGER NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
");

$db->exec("
    CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key TEXT NOT NULL,
        setting_value TEXT NOT NULL
    );
");

$db->exec("
    CREATE TABLE IF NOT EXISTS current_roulette (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user1 INTEGER NOT NULL,
        user2 INTEGER NOT NULL,
        user3 INTEGER DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
");

// Set initial admin registration status
$db->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('admin_registered', 'no')");

// Schließen der Datenbankverbindung
$db->close();

echo "SQLite-Datenbank und Tabellen erfolgreich erstellt.";
?>
