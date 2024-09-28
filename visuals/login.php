<?php
session_start();

require_once('../database/db.php');
require_once('../navbar/nav.php');
$errors = []; // Error Array initialisieren

$admin_check = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'admin_registered'");
$admin_registered = $admin_check->fetch_assoc()['setting_value'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Retrieve form data
    $username = trim($_POST["name"]);
    $password = $_POST["password"];

    // Check any input field empty
    if (empty($username) || empty($password)) {
        $errors[] = "Name und Passwort sind erforderlich.";
    } else {
        // Check if admin exists in the database
        $stmt = $conn->prepare("SELECT id, `name`, `password`, isAdmin FROM admins WHERE `name` = ?");
        $stmt->bind_param("s", $username); // Bind parameters
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            // Admin exists, verify password
            $row = $result->fetch_assoc();

            // password_verify($password, $row['password']) später den Hash verwenden für Sicherheit

            if ($row['isAdmin']) {
                if (password_verify($password, $row['password'])) {
                    // Password is correct, log in.
                    $_SESSION['adminlogin']=true;
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_name'] = $row['name'];
                    $_SESSION['is_admin'] = $row['isAdmin'];
                    header("Location: index.php"); // Redirect
                    exit();
                } else {
                    $errors[] = "Falsches passwort.";
                }
            } else{
                $errors[] = "Keine Administratorrechte!";
            }
        } else {
            $errors[] = "Username nicht gefunden.";
        }

        $stmt->close();
    }
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log-In</title>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

     <!-- Local Style and Script -->
    <link rel="stylesheet" href="../css/nav.css">
    <script src="../js/script.js"></script>

    <!-- Schriftart Font -->
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Noto+Sans+Arabic:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="login-container">
        <h2 class="text-center">Login</h2>
        <form action="#" method="POST">
            <div class="form-group">
                <input type="text" name="name" class="form-control" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="form-group">
                <button type="submit" name="login" class="btn btn-login">Login</button>
            </div>
        </form>
        <?php
            // Display errors from $errors[] array
            if (!empty($errors)) {
                echo '<div class="alert alert-danger">';
                foreach ($errors as $error) {
                    echo '<div>' . htmlspecialchars($error) . '</div>';
                }
                echo '</div>';
            }
        ?>
        <?php if ($admin_registered == 'no'): ?>
        <div class="">
            <p>Noch kein Account? <a href="signup.php">Registrieren</a></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>