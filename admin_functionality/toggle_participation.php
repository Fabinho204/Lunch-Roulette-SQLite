<!-- toggle_participation.php -->
<?php
// toggle participation request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggleParticipation'])) {
    $userId = $_POST['toggleParticipation'];
    // Query to toggle the participate_in_roulette status
    $stmt = $conn->prepare("UPDATE users SET participate_in_roulette = NOT participate_in_roulette WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit;
}
?>