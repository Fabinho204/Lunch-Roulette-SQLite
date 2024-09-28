<!-- add_new_user.php -->
<?php
// Redirect non-admins back to login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addUser'])) {
    $name = $_POST['newUserName'];

    // Insert new user into the database
    $insertUser = $conn->prepare("INSERT INTO users (name, participate_in_roulette) VALUES (?, 1)");
    $insertUser->bind_param("s", $name);
    if ($insertUser->execute()) {
        echo "<p>New user added successfully and ready to participate in the roulette.</p>";
    } else {
        echo "<p>Error adding user.</p>";
    }
    $insertUser->close();

    header("Location: admin_dashboard.php");
    exit;
}
?>