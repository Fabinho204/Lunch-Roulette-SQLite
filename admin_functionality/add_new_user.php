<!-- add_new_user.php -->
<?php
require_once('../database/db.php');

// Check if the request method is POST and the 'addUser' button was clicked
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addUser'])) {
    $name = $_POST['newUserName'];

    // Prepare an SQL statement to insert a new user into the 'users' table
    $insertUser = $db->prepare("INSERT INTO users (name, participate_in_roulette) VALUES (:name, 1)");
    $insertUser->bindValue(':name', $name, SQLITE3_TEXT);

    // Execute the prepared statement and check if it was successful
    if ($insertUser->execute()) {
        echo "<p>New user added successfully and ready to participate in the roulette.</p>";
    } else {
        echo "<p>Error adding user.</p>";
    }

    // Close the statement
    $insertUser->close();

    // Redirect to the admin dashboard
    header("Location: admin_dashboard.php");
    exit;
}
?>
