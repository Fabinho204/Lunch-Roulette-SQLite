<?php
session_start();
require_once('../database/db.php');
require_once('../navbar/nav.php');

$sql = "SELECT 
            cr.id,
            u1.name as user1_name,
            u2.name as user2_name,
            u3.name as user3_name
        FROM 
            current_roulette cr /*roulette_winners rw oder current_roulette cr*/
        LEFT JOIN 
            users u1 ON cr.user1 = u1.id /*rw. oder cr.*/
        LEFT JOIN 
            users u2 ON cr.user2 = u2.id
        LEFT JOIN 
            users u3 ON cr.user3 = u3.id
        ORDER BY 
            cr.id DESC"; /*rw. oder cr.*/

$result = $conn->query($sql); // execute the query
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Essens-Paare</title>

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
<div class="container">
        <h2 class="text-center mt-5 mb-4 winners-heading">Essens-Paare</h2>
        <div class="container main">
            <div class="row">
                <?php
                if ($result->num_rows > 0) { //check if there is at least one row returned by the query
                    $count = 1;
                    while ($row = $result->fetch_assoc()) { // fetch every row from reesult
                        echo '<div class="col-lg-4 col-md-6 mb-4">';
                        echo '<div class="card">';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">Paar ' . $count . '</h5>';
                        echo '<div class="winner-icon"><i class="fas fa-user-friends"></i></div>';
                        echo '<div class="winner-label">Mitarbeiter 1:</div>';
                        echo '<p class="winner-name">' . $row["user1_name"] . '</p>';
                        echo '<div class="winner-label">Mitarbeiter 2:</div>';
                        echo '<p class="winner-name">' . $row["user2_name"] . '</p>';
                        if (!empty($row["user3_name"])) { // Check for third user
                            echo '<div class="winner-label">Mitarbeiter 3:</div>';
                            echo '<p class="winner-name">' . $row["user3_name"] . '</p>';
                        }
                        echo '</div>';
                        echo '<div class="card-footer">';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        $count++;
                    }
                } else { // No Pairs display message
                    echo '<div class="alert alert-info text-center" role="alert">
                            Es gibt noch keine Paare.
                        </div>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
