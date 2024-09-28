<?php
session_start();
require_once('../database/db.php');
require_once('../navbar/nav.php');

// Redirect non-admins back to login page
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert Confirm -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . ($row['participate_in_roulette'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>') . "</td>";
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
                <button type="submit" class="btn-login" name="addUser">Benutzer hinzufügen</button>
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
        <div class="d-flex justify-content-around">
            <div class="m-2">
                <button type="button" class="btn-login btn-reset" onclick="resetAllTables()">Reset All</button>
            </div>
        </div>
    </div>
</body>
</html>
