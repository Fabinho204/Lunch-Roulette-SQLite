<?php
session_start();
require_once('../database/db.php'); // Verbindung zur SQLite-Datenbank
require_once('../navbar/nav.php');

// Umleitung für Nicht-Admins zur Login-Seite
if (!isset($_SESSION['adminlogin']) || $_SESSION['adminlogin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once('../admin_functionality/toggle_participation.php');
require_once('../admin_functionality/add_new_user.php');
require_once('../admin_functionality/delete_user.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
    
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Noto+Sans+Arabic:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">

</head>
<body>
    <div class="container main">
        <form id="deleteForm" method="POST" action="admin_dashboard.php">
        <h2 class="winners-heading">User Management</h2>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Teilnahmestatus</th>
                        <th>Status ändern</th>
                        <th>Benutzer permanent löschen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, name, participate_in_roulette FROM users";
                    $result = $db->query($sql); // SQLite-Abfrage

                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) { // Verwendung von fetchArray für SQLite
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . ($row['participate_in_roulette'] ? '<span class="badge badge-success">Ja</span>' : '<span class="badge badge-danger">Nein</span>') . "</td>";
                        echo "<td>
                            <button type='submit' class='btn btn-info' name='toggleParticipation' value='{$row['id']}'>Teilnahme</button>
                        </td>";
                        echo "<td>
                        <button type='button' class='btn btn-danger' onclick='confirmDelete({$row['id']})'>Delete</button>
                        </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </form>
    <div class="card">
        <h3 class="card-title">Neuen Benutzer anlegen</h3>
            <form method="post" action="admin_dashboard.php">
                <div class="form-group">
                    <label for="newUserName">Name:</label>
                    <input type="text" class="form-control" id="newUserName" name="newUserName" required>
                </div>
                <button type="submit" class="btn btn-login" name="addUser">Benutzer hinzufügen</button>
            </form>
        </div>
    </div>
    <div class="container">
        <div class="card">
            <button type="button" class="btn-login" onclick="startRoulette()">Roulette starten</button>
            <!-- Div to display the result -->
            <div id="roulette-result"></div>
        </div>
    </div>
    <div class="container">
        <div class="card">
            <div class="d-flex justify-content-around">
                <div class="m-2">
                    <form action="../admin_functionality/export_pairs.php" method="get">
                        <button type="submit" class="btn-login">Pärchen exportieren</button>
                    </form>
                </div>
                <div class="m-2">
                    <button type="button" class="btn-login" onclick="resetAllTables()">Reset All</button>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.all.min.js"></script>
    <script src="../js/script.js"></script>

    <script>
    // Defining the resetAllTables function directly if it's not in an external script
    function resetAllTables() {
        Swal.fire({
            title: "Bist du sicher?",
            text: "Möchtest du ALLE Tabellen zurücksetzen?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#009999",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ja, zurücksetzen!",
            cancelButtonText: "Abbrechen",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "../admin_functionality/drop_tables.php",
                    method: "POST",
                    success: function (response) {
                        Swal.fire(
                            "Zurückgesetzt!",
                            "Alle Tabellen wurden erfolgreich zurückgesetzt.",
                            "success"
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr, status, error) {
                        Swal.fire(
                            "Fehler!",
                            "Es ist ein Fehler aufgetreten: " + error,
                            "error"
                        );
                    },
                });
            }
        });
    }
    </script>
</body>
</html>
