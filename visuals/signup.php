    <?php
    session_start();

    require_once('../database/db.php');
    require_once('../navbar/nav.php');

    $admin_check = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'admin_registered'");
    $admin_exist = $admin_check->fetch_assoc();
    
    if ($admin_exist['setting_value'] === 'yes') {
        header("Location: login.php"); // Redirect
        exit;
    }

    $errors = []; // Initialize error array
    $success_message = ''; // Initialize success message
    // isset Signup refers to button
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
        // Retrieve form data
        $name = trim($_POST["name"]);
        $password = $_POST["password"];

        // Check if any input field is empty
        if (empty($name) || empty($password)) {
            $errors[] = "Alle Felder sind erforderlich.";
        } else {
                // Check if admin already exists
                $stmt = $conn->prepare("SELECT id FROM admins WHERE name = ? ");
                $stmt->bind_param("s", $name);
                //bind parameters to the ? placeholders
                $stmt->execute();
                $stmt->store_result();
                //save the result

                if ($stmt->num_rows > 0) {
                    $errors[] = "Ein User mit diesem Namen existiert bereits";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $insertStmt = $conn->prepare("INSERT INTO admins (name, password, isAdmin, created_at) VALUES (?, ?, 1, NOW())");
                    $insertStmt->bind_param("ss", $name, $hashed_password);

                    if($insertStmt->execute()){
                        // If the user was successfully added, update the setting
                        $update_setting = $conn->prepare("UPDATE settings SET setting_value = 'yes' WHERE setting_key = 'admin_registered'");
                        if ($update_setting->execute()) {
                            header("Location: login.php"); // Redirect to login after successful registration and update
                            exit;
                        } else {
                            $errors[] = "Der Table settings konnte nicht geupdated werden.";
                        }
                    } else {
                        $errors[] = "Registrierung fehlgeschlagen";
                    }

                    $insertStmt->close();
                }

                $stmt->close();
            
        }
    }

    $conn->close();

    ?>
    <!DOCTYPE html>
    <html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>

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

        <div class="signup-container">
            <h2 class="text-center">Sign Up</h2>
            <form id="signupForm" action="#" method="POST">
                <div class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="Name" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
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
                var password = document.forms["signupForm"]["password"].value;
                if (name === "" || email === "" || password === "") {
                    alert("All fields are mandatory");
                    event.preventDefault();
                }
            });
        </script>
    </body>

    </html>