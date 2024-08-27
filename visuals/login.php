<?php
session_start();

require_once('../database/db.php'); // SQLite Verbindung
require_once('../navbar/nav.php');
$errors = []; // Fehler-Array initialisieren

// Überprüfen, ob der Admin registriert ist
$admin_check = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'admin_registered'");
$admin_registered = $admin_check->fetchArray(SQLITE3_ASSOC)['setting_value'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Formular-Daten abrufen
    $username = trim($_POST["name"]);
    $password = $_POST["password"];

    // Überprüfen, ob irgendein Eingabefeld leer ist
    if (empty($username) || empty($password)) {
        $errors[] = "Name und Passwort sind erforderlich.";
    } else {
        // Überprüfen, ob der Admin in der Datenbank existiert
        $stmt = $db->prepare("SELECT id, name, password, isAdmin FROM admins WHERE name = :name");
        $stmt->bindValue(':name', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            // Admin existiert, Passwort verifizieren

            // password_verify($password, $row['password']) später den Hash verwenden für Sicherheit

            if ($row['isAdmin']) {
                if (password_verify($password, $row['password'])) {
                    // Passwort ist korrekt, Admin einloggen
                    $_SESSION['adminlogin'] = true;
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_name'] = $row['name'];
                    $_SESSION['is_admin'] = $row['isAdmin'];
                    header("Location: index.php"); // Weiterleitung
                    exit();
                } else {
                    $errors[] = "Falsches Passwort.";
                }
            } else {
                $errors[] = "Keine Administratorrechte!";
            }
        } else {
            $errors[] = "Username nicht gefunden.";
        }

        $stmt->close();
    }
}

// Verbindung schließen
$db->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log-In</title>
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
            // Fehler aus dem $errors[]-Array anzeigen
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
