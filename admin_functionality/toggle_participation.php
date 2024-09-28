<!-- toggle_participation.php -->
<?php
require_once('../database/db.php');
// Toggle participation request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggleParticipation'])) {
    $userId = $_POST['toggleParticipation'];
    
    // Query to toggle the participate_in_roulette status
    $stmt = $db->prepare("UPDATE users SET participate_in_roulette = NOT participate_in_roulette WHERE id = :id");
    $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit;
}
?>
