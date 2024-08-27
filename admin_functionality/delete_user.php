<!-- delete_user.php -->
<?php
require_once('../database/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deleteUser'])) {
    $userId = $_POST['deleteUser'];

    if (is_numeric($userId)) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        if ($result && $db->changes() > 0) {
            $_SESSION['message'] = "User deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting user.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Invalid user ID.";
    }

    header("Location: admin_dashboard.php");
    exit;
}
?>
