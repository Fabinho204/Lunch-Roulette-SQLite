<!-- delete_user.php -->
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deleteUser'])) {
    $userId = $_POST['deleteUser'];

    if (is_numeric($userId)) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
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
