<?php
session_start();

require_once('../database/db.php'); // SQLite Verbindung
require_once('../navbar/nav.php');

// Überprüfen, ob der Admin registriert ist
$admin_check = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'admin_registered'");
$admin_exist = $admin_check->fetchArray(SQLITE3_ASSOC);

if ($admin_exist['setting_value'] === 'yes') {
    header("Location: login.php"); // Weiterleitung
    exit;
}

$errors = []; // Fehler-Array initialisieren
$success_message = ''; // Erfolgsnachricht initialisieren

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    // Formular-Daten abrufen
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"]; // Confirm password field

    // Überprüfen, ob irgendein Eingabefeld leer ist
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "Alle Felder sind erforderlich.";
    } elseif ($password !== $confirm_password) { // Check if passwords match
        $errors[] = "Die Passwörter stimmen nicht überein.";
    } else {
        // E-Mail-Format validieren
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Falsches Email-Format.";
        } else {
            // Überprüfen, ob die E-Mail und der Name bereits existieren
            $stmt = $db->prepare("SELECT id FROM admins WHERE email = :email AND name = :name");
            $stmt->bindValue(':email', $email, SQLITE3_TEXT);
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $result = $stmt->execute();

            if ($result->fetchArray(SQLITE3_ASSOC)) {
                $errors[] = "Ein User mit diesem Namen und dieser E-Mail Adresse existiert bereits";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Updated query to include participate_in_roulette
                $insertStmt = $db->prepare("INSERT INTO admins (name, email, password, participate_in_roulette, isAdmin, created_at) VALUES (:name, :email, :password, :participate_in_roulette, 1, datetime('now'))");
                $insertStmt->bindValue(':name', $name, SQLITE3_TEXT);
                $insertStmt->bindValue(':email', $email, SQLITE3_TEXT);
                $insertStmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
                $insertStmt->bindValue(':participate_in_roulette', 1, SQLITE3_INTEGER); // Default value for participate_in_roulette

                if ($insertStmt->execute()) {
                    // Wenn der Benutzer erfolgreich hinzugefügt wurde, aktualisieren Sie die Einstellung
                    $update_setting = $db->prepare("UPDATE settings SET setting_value = 'yes' WHERE setting_key = 'admin_registered'");
                    if ($update_setting->execute()) {
                        header("Location: login.php"); // Weiterleitung nach erfolgreicher Registrierung
                        exit;
                    } else {
                        $errors[] = "Failed to update settings.";
                    }
                } else {
                    $errors[] = "Registrierung fehlgeschlagen";
                }
            }
        }
    }
}

$db->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- Bootstrap und Font-Awesome -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Lokale Styles und Skripte -->
    <link rel="stylesheet" href="../css/nav.css">
    <script src="../js/script.js"></script>

    <!-- Schriftart -->
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Noto+Sans+Arabic:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="signup-container">
        <h2 class="text-center">Sign Up</h2>
        <form id="signupForm" action="#" method="POST">
            <div class="form-group">
                <input type="text" name="name" class="form-control" placeholder="Name" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" pattern="^[^ ]+@[^ ]+\.[a-z]{2,6}$" class="form-control" placeholder="Email" required>
                <!-- Email Pattern REGEX -->
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>
            <div class="form-group">
                <button type="submit" name="signup" class="btn btn-signup">Sign Up</button>
            </div>
        </form>

        <button name="home" class="btn btn-home"><a href="index.php">Home</a></button>

    </div>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("signupForm").addEventListener("submit", function(event) {
            var name = document.forms["signupForm"]["name"].value;
            var email = document.forms["signupForm"]["email"].value;
            var password = document.forms["signupForm"]["password"].value;
            var confirm_password = document.forms["signupForm"]["confirm_password"].value;
            if (name === "" || email === "" || password === "" || confirm_password === "") {
                alert("All fields are mandatory");
                event.preventDefault();
            } else if (password !== confirm_password) {
                alert("Passwords do not match");
                event.preventDefault();
            }
        });
    </script>
</body>

</html>
