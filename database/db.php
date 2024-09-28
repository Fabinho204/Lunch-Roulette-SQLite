<?php
// Verbindung zur SQLite-Datenbank herstellen
$db = new SQLite3(__DIR__ . '/lunch_roulette.db');

if (!$db) {
    die("Verbindung zur SQLite-Datenbank fehlgeschlagen.");
}
?>
