# Siemens Lunch Roulette

## Inhaltsverzeichnis

- [Verwendung](#verwendung)

  - [index.php](#indexphp)
  - [signup.php](#signupphp)
  - [login.php](#loginphp)
  - [logout.php](#logoutphp)
  - [admin_dashboard.php](#admin_dashboardphp)
  - [nav.php](#navphp)
  - [generate_pairings.php](#generate_pairingsphp)
  - [add_new_user.php](#add_new_userphp)
  - [delete_user.php](#delete_userphp)
  - [toggle_participation.php](#toggle_participationphp)

- [DB,JS,CSS](#dbjscss)
- [Beispiele](#beispiele)
- [Beiträge](#beiträge)

## index.php

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

### Sessions und Requirements

```
<?php // PHP Opening Tag
session_start(); // Wird immer benötigt um eine PHP-Sitzung zu starten und aktuelle Sitzungsdaten zu verarbeiten.
require_once('../database/db.php');
require_once('../navbar/nav.php');
// Benötigt einmal die Navbar die extern eingebunden wird und den Datenbankzugriff.
```

### SQL Abfrage

```
$sql = "SELECT
            cr.id, // ID des Eintrags aus dem jetztigen Roulette.
            u1.name as user1_name,
            u2.name as user2_name,
            u3.name as user3_name
        FROM
            current_roulette cr /*roulette_winners rw oder current_roulette cr*/
```

**Left join um den Namen der User basierend auf der ID zu erhalten (Bei Bedarf User 3 für Trios).**

```

        LEFT JOIN
            users u1 ON cr.user1 = u1.id /*rw. oder cr.*/
        LEFT JOIN
            users u2 ON cr.user2 = u2.id
        LEFT JOIN
            users u3 ON cr.user3 = u3.id
        ORDER BY // Ergebnisse Absteigend sortieren, damit die neuesten Einträge zuerst angezeigt werden.
            cr.id DESC"; /*rw. oder cr.*/

```

**result speichert das Ergebnis und führt SQL - Abfrage durch**

```
$result = $conn->query($sql); // execute the query
?>
```

### HTML

```
            <div class="row">
                <?php
                if ($result->num_rows > 0) {
                //check if there is at least one row returned by the query.

                    $count = 1;
                    while ($row = $result->fetch_assoc()) {
                    // fetch every row from result.
                    // Anzahl an Rows -> Anzahl an Paarungen.


                    // Mit den Echos wird die Card gebaut.
                        echo '<div class="col-lg-4 col-md-6 mb-4">';
                        echo '<div class="card">';
                        echo '<div class="card-body">';

                    // Der . $count zählt die Anzahl der Paare mit.
                        echo '<h5 class="card-title">Paar ' . $count . '</h5>';
                        echo '<div class="winner-icon"><i class="fas fa-user-friends"></i></div>';
                        echo '<div class="winner-label">Mitarbeiter 1:</div>';
                        echo '<p class="winner-name">' . $row["user1_name"] . '</p>';
                        echo '<div class="winner-label">Mitarbeiter 2:</div>';
                        echo '<p class="winner-name">' . $row["user2_name"] . '</p>';


                    //Sollte die Row mit dem Usernamen der dritten Person nicht empty sein, wird dieser zur Card hinzugefügt.
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
```

**Im Normalen Code muss hier natürlich noch die Connection geschlossen werden.**

```
<?php
$conn->close();
?>
```

## signup.php

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

### Admin Check

Die Signup.php wurde umgebaut auf Grund einer Logikänderung, sie wird nurnoch benötigt um den verwaltenden Admin zu registrieren. Dieser Checkup zu Beginn überprüft, ob der Admin bereits angelegt wurde oder nicht. Falls der Admin registriert ist, wird hier immer direkt zum Login weitergeleitet.
Die Conn Query führt eine SQL-Abfrage durch, um den Wert der "admin_registered" Einstellung aus der "settings" Tabelle abzurufen.

```
    $admin_check = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'admin_registered'");
    $admin_exist = $admin_check->fetch_assoc();

    if ($admin_exist['setting_value'] === 'yes') {
        header("Location: login.php"); // Redirect
        exit;
    }
```

### Methodenüberprüfung

Hier wird gecheckt, ob das Formular über die POST - Methode abgeschickt wird und ob das "signup" Feld im Formular (Via button) übermittelt wurde.
Über diese Post Methode werden danach die Daten erhalten die wir benötigen und überflüssige Leerzeichen getrimmt.

```
// isset Signup refers to button
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
        // Retrieve form data
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
```

### Select Statement

Hier kommt nun eine If Else Verschachtelung bei der zuerst eine Validierung aufkommt, ob alle vorhandenen Felder korrekt ausgefüllt wurden. Danach wird noch auf das klassische E-Mail Format überprüft, bevor eine SQL - Abfrage vorbereitet wird, um zu schauen ob es in der Admins Tabelle bereits einen Eintrag mit Email UND Namen gibt, der ident wäre.
Mit bind_param werden die Variablen (strings ss) email und name mit den ? Platzhaltern verbunden. Danach wird die Abfrage ausgeführt und das Ergebnis gespeichert.

```
        // Check if any input field is empty
        if (empty($name) || empty($email) || empty($password)) {
            $errors[] = "Alle Felder sind erforderlich.";
        } else {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Falsches Email-Format.";
            } else {
                // Select statement to see wether Name Mail Combo exists already
                $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ? AND name = ? ");
                $stmt->bind_param("ss", $email, $name);
                //bind parameters to the ? placeholders
                $stmt->execute();
                $stmt->store_result();
                //save the result
```

### Existenz Checkup

Sollte nun tatsächlich die Anzahl der Reihen größer als 0 sein, was bedeuten würde, dass bereits ein User mit identem Namen und E-Mail Adresse existiert, wird eine Fehlermeldung ausgegeben.
Ist es jedoch ein neuer User wird das Passwort gehasht und ein Insert vorbereitet.

```
                if ($stmt->num_rows > 0) {
                    $errors[] = "Ein User mit diesem Namen und dieser E-Mail Adresse existiert bereits";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
```

### Insert Statement

In diesem Insert Statement wird nun ein neuer Admin angelegt.
Hierbei wird der Bool isAdmin mit 1 als "besitzt Adminrechte" angelegt (wird für den Login relevant).

```
                    $insertStmt = $conn->prepare("INSERT INTO admins (name, email, password, isAdmin, created_at) VALUES (?, ?, ?, 1, NOW())");
                    $insertStmt->bind_param("sss", $name, $email, $hashed_password);

```

### Admin Registriert (Property)

Wurde das Insert Statement nun ausgeführt wird im settings table ein registrierter Admin verzeichnet, was widerum für den Login interessant wird (da ja nur ein Admin registriert sein darf).

```
                    if($insertStmt->execute()){
                        // If the user was successfully added, update the setting
                        $update_setting = $conn->prepare("UPDATE settings SET setting_value = 'yes' WHERE setting_key = 'admin_registered'");
```

### Redirect

Hier wird nach erfolgreicher Registrierung ein Redirect auf die Login page gemacht wo man sich anmelden kann.

```
                        if ($update_setting->execute()) {
                            header("Location: login.php"); // Redirect to login after successful registration and update
                            exit;
                        } else {
                            $errors[] = "Failed to update settings.";
                        }
                    } else {
                        $errors[] = "Registrierung fehlgeschlagen";
                    }

                    $insertStmt->close();
                }

                $stmt->close();
            }
        }
    }

    $conn->close();

    ?>
```

### Script im HTML

Der EventListener im signupForm führt die Funktion jedes Mal aus, wenn der User auf den Submit Button klickt.
Danach werden die Werte aus den Eingabefeldern in die jeweilig zugehörigen Variablen gespeichert.
Ist jedoch eines dieser Felder leer (mindestens) wird eine Fehlermeldung ausgegeben.

```
            document.getElementById("signupForm").addEventListener("submit", function(event) {
                var name = document.forms["signupForm"]["name"].value;
                var email = document.forms["signupForm"]["email"].value;
                var password = document.forms["signupForm"]["password"].value;
                if (name === "" || email === "" || password === "") {
                    alert("All fields are mandatory");
                    event.preventDefault();
                }
            });
```

## login.php

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

### Password Verifikation

Zuerst wird geschaut ob überhaupt ein Admin existiert.
Sollte das der Fall sein, wird weiters gecheckt ob die isAdmin property true ist (Besitzt Adminrechte), um dann das Passwort zu verifizieren und den Admin einzuloggen (mit Validierungen).

```
        if ($result->num_rows == 1) {
            // Admin exists, verify password
            $row = $result->fetch_assoc();

            if ($row['isAdmin']) {
                if (password_verify($password, $row['password'])) {

```

'adminlogin' ist relevant wenn auf das Admin_Dashboard zugegriffen werden möchte. Danach werden ID, Name und isAdmin Status (true false) in der Session gespeichert.

```
                    // Password is correct, log in the admin
                    $_SESSION['adminlogin']=true;
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_name'] = $row['name'];
                    $_SESSION['is_admin'] = $row['isAdmin'];
                    header("Location: index.php"); // Redirect
                    exit();
```

Error Array: Errors fangen.

```
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
```

Connection schließen.

```
// Close connection
$conn->close();
?>
```

### Relevantes HTML

Ist noch kein Admin registriert erscheint eine Option zum Registrieren mit einem Link zum signup.php

```
        <?php if ($admin_registered == 'no'): ?>
        <div class="">
            <p>Noch kein Account? <a href="signup.php">Registrieren</a></p>
        </div>
        <?php endif; ?>
    </div>
```

## logout.php

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

Session_destroy löscht alle Session Variablen, User wird dann wieder auf die Index-Seite weitergeleitet.

```
<?php
session_start();
session_destroy();
header("Location: ../visuals/index.php");
//head to -> index.php, sends HTTP header to the browser
?>
```

## admin_dashboard.php

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

### Admin Login

Redirectet non-admins zurück zur Login Page

```
if (!isset($_SESSION['adminlogin']) || $_SESSION['adminlogin'] !== true) {
    header("Location: login.php");
    exit;
}
```

### Logik Anforderungen

Hier werden einige Funktions Files benötigt für die User Anbindung.

```
require_once('../admin_functionality/toggle_participation.php');
require_once('../admin_functionality/add_new_user.php');
require_once('../admin_functionality/delete_user.php');
?>
```

### HTML

### Table Überschriften

Mit dem Select wird aus dem Table Users der Name dargestellt (es wird über die id zugegriffen (unique)) und der Teilnahmestatus, der default auf True ist.

```

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
```

Nun wird jede Reihe durch das While solange ausgegeben bis 0 übrig sind.
Zugehörig zu den Überschriften wird dann für jede Reihe / für jeden User der Name, der Teilnahmestatus, ein Button zum ändern des Status und ein Button zum permanenten Löschen dargestellt.

```
                    while ($row = $result->fetch_assoc()) {
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
```

### Benutzer einfügen

Über den Button hier wird später der Benutzer hinzugefügt in der Logik File (isset addUser).

```
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
```

### Roulette Starten

Hier wird über den onclick die startRoulette Funktion aufgerufen.

```
    <div class="container">
        <div class="card">
            <button type="button" class="btn-login" onclick="startRoulette()">Roulette starten</button>
            <!-- Div to display the result -->
            <div id="roulette-result"></div>
        </div>
    </div>
```

## nav.php

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

PHP-Block für Benutzerüberprüfung checkt ob der user als Admin angemeldet ist und auch wirklich ein Admin ist und Adminrechte besitzt

```
                <?php
                // Check if the user is logged in and is an admin
                if (isset($_SESSION['adminlogin']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                    echo '<li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">Benutzermanagement</a>
                        </li>';
                }
                ?>
```

Dieser PHP-Block ändert nur das Login oder Logout Icon je nach Status

```
                <?php
                if (isset($_SESSION['adminlogin'])) {
                    echo '<li class="nav-item">
                            <a class="nav-link" href="../functional_files/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>';
                } else {
                    echo '<li class="nav-item">
                            <a class="nav-link logout-btn" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>';
                }
                ?>
```

## generate_pairings.php

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

### Function getUsers

Ein `Select` Statement aus einer SQL Query wählt die ID und den Namen aus dem Table Users aus, die am Roulette teilnehmen (`true`).
Das `Result` führt die Query dann aus.

```
function getUsers($conn) {
    $sql = "SELECT id, name FROM users WHERE participate_in_roulette = 1";
    $result = $conn->query($sql);
```

Errorhandling

```
    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Fehler beim Abrufen der Benutzer: " . $conn->error]);
        exit();
    }
```

Hier wird ein leeres Userarray angelegt.
Die While-Schleife iteriert über jede Row des Results, wobei `fetch_assoc()` jede Reihe als assoziatives Array zurückgibt.
Die Users werden dann ausgegeben und das Array enthält nun die IDs als Keys und die Namen als values.

```
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[$row['id']] = $row['name']; // Speichert user in assoziativem array, IDs funktionieren als Key
                                            // Namen werden als Werte verwendet
    }
    return $users;
}

```

### Function getExistingPairings

Die Function benötigt nur ein Argument (das Objekt für die Connection zur DB) und selektiert dann die beiden Users 1 und 2 aus dem `roulette_winners` table. (Wieder mit Errorhandling)

```
function getExistingPairings($conn) {
    $sql = "SELECT user1, user2 FROM roulette_winners";
    $result = $conn->query($sql);
    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Fehler beim Abrufen der Paarungen: " . $conn->error]);
        exit();
    }
```

Hier wir ein leeres `$pairings` Array initialisiert das in der While Loop wie bei `getUsers` die beiden Users 1 und 2 im `$pairings` Array speichert.

```
    $pairings = [];
    while ($row = $result->fetch_assoc()) { // Selbe wie getUsers, Paarungen abrufen und speichern
        $pairings[] = [$row['user1'], $row['user2']];
    }
    return $pairings;
}

```

### Function allPairsGenerated

Ziel dieser Funktion ist es nun zu schauen, ob alle möglichen Paare erfolgreich erstellt wurden. Hier wird nun aus unseren bereits befüllten `$user` und `$pairings` Arrays eine totale Anzahl gezählt.
Die `count` Funktion zählt die Gesamtanzahl an Users.

```
function allPairsGenerated($users, $pairings) {
    $totalUsers = count($users); // User zählen
```

Die Formel für alle Paarungen lautet n \* (n - 1) / 2.
n = Gesamtanzahl an Usern
n - 1 = Ist die Anzahl an Usern, mit der jeder User gepaart werden kann (Er kann ja nicht mit sichselbst gepaart werden).
Die Division durch die Zahl "2" ist nur nötig, da die Paarungsreihenfolge nicht gezählt wird (User A + User B = User B + User A).

```
    $totalPairs = $totalUsers * ($totalUsers - 1) / 2;
    return count($pairings) >= $totalPairs;
}
```

### Function generateNextRoundPairs

Diese Funktion erstellt neue Paarungen und garantiert, dass keine Paarung doppelt vorkommt, sowie die Handhabung der Trio-Paarung (Ungerade Anzahl an Teilnehmern).

Anfangs wird ein neues leeres Array `$pairs` initialisiert und die User gezählt.
Ein Set ist eine Datenstruktur, dass unqiue Elemente desselben Typs in einer sortierten Reihenfolge speichert.

```
function generateNextRoundPairs($users, $existingPairings) {
    $pairs = [];
    $userCount = count($users);
    $existingPairSet = array();
```

In dieser Schleife werden die Paare sortiert (damit a+b = b+a).
Dieses Paar wird dann in einen String (z.B. "1-2") converted (implode). Dieser String wird als Key im `$existingParSet` mit dem value `true` verwendet. Dadurch kann einfach überprüft werden, ob eine Paarung bereits vorgekommen ist.

```
    foreach ($existingPairings as $pair) {
        sort($pair);
        $existingPairSet[implode('-', $pair)] = true;
    }
```

`array_keys` holt alle user IDs aus dem `$users` Array und mischt diese für die Zufälligkeit.

```
    $userIds = array_keys($users);
    shuffle($userIds); // Shuffle users for randomness
```

Das `$usedUsers` Array speichert wer in dieser Runde gepaart wurde.
Die äußere Schleife (`$i`) iteriert nun über alle User und die innere Schleife (`$j`) startet beim User i+1 also beim nächsten User, was sicherstellt, dass jeder User nur mit Usern gepaart wird die nach ihm in der Liste kommen. (Verhindert Doppelpaare wie 2-1 und 1-2 (Stackt sich hoch, 1 mit 51, 2 mit 96 usw. und es ist egal, da "links" chronologisch einfach weitergezählt wird bis alle durch sind.)).

```
    $usedUsers = [];
    for ($i = 0; $i < $userCount; $i++) {
        for ($j = $i + 1; $j < $userCount; $j++) {
```

Die Paarungslogik dahinter ist:
`in_array($userIds[$i], $usedUsers)` checkt ob der User mit der ID `$userIds[$i]` bereits im `$usedUsers` Array vorhanden ist.
Mit der `!` Negation wird garantiert, dass nur User die noch nicht gepaart wurden (im Array sind) berücksichtigt werden.

```
            if (!in_array($userIds[$i], $usedUsers) && !in_array($userIds[$j], $usedUsers)) {
```

Das `$pair` erstellt ein Array mit den beiden User IDs um eine Paarung zu formen und dann wieder zu sortieren.

```
                $pair = [$userIds[$i], $userIds[$j]];
                sort($pair); // Sort to handle (a, b) == (b, a)
```

Das `isset` überprüft normalerweise ob etwas existiert und nicht null ist, weshalb durch die Negation hier geschaut wird, dass eine Paarung noch nicht existiert. Das implode wird dann wieder benötigt um aus den Elementen des Arrays (den beiden IDs) Strings zu bilden (Quick Check / Einfache Überprüfung).
In das `$pairs[]` Array wird nun das neue Paar hinzugefügt.
Mit `$existingPairSet[implode('-', $pair)] = true;` wird im Array `$existingPairSet` ein neuer Eintrag erstellt, wobei z.B. der Schlüssel (key) der String `"1-2"` ist und der Wert `true`. Zum Beispiel kann durch das Setzen von `$existingPairSet["1-2"] = true;` später leicht überprüft werden, dass diese Paarung bereits existiert.

```
                if (!isset($existingPairSet[implode('-', $pair)])) {
                    $pairs[] = $pair;
                    $existingPairSet[implode('-', $pair)] = true;
```

Hier werden dann einfach die beiden User IDs im Array gespeichert damit sie in dieser Runde nicht nochmal verwendet werden.

```
                    $usedUsers[] = $userIds[$i];
                    $usedUsers[] = $userIds[$j];
                }
            }
        }
    }
```

Gleich am Anfang wird im if statement die Anzahl der User die bereits gepaart wurde mit der Gesamtanzahl an teilnehmenden Usern verglichen. If: die Anzahl der gepaarten < als die Anzahl der Vorhandenen => Manche User sind übergeblieben, die noch nicht gepaart wurde, was normalerweise bei einer ungeraden Anzahl an Usern passiert.

Das `$remainingUsers` nimmt das Array mit allen User IDs (`$userIds`) und entfernt alle IDs die bereits genutzt wurden (`$usedUsers`).

```
    if (count($usedUsers) < $userCount) {
        $remainingUsers = array_diff($userIds, $usedUsers);
```

Folgendes If überprüft, ob tatsächlich genau 1 User verbleibt.
Zusätzlich wird geschaut, ob denn überhaupt schon ein Paar existiert, es könnte ja sein, dass es generell nur einen User gibt, den man dann ja nirgends hinzugeben könnte.
Falls ja wird der Index des letzten Paares (-1) ausgemacht aus dem `$pairs` Array und die ID des verbleibenden Users erhalten, bevor das Array returned wird. (`[]` fügt die verbleibende User ID in das letzte Pärchen ein.)

```
        if (count($remainingUsers) == 1 && !empty($pairs)) {
            $pairs[count($pairs) - 1][] = array_values($remainingUsers)[0];
        }
    }

    return $pairs;
}
```

### Restlicher Code

Zuerst werden alle teilnehmenden User und alle existierenden Pärchen aus den letzten Runden von der Datenbank geholt.

```
$users = getUsers($conn);
$existingPairings = getExistingPairings($conn);
```

Sind alle Paarungen erfolgreich generiert worden soll nun eine Error Message kommen.

```
if (allPairsGenerated($users, $existingPairings)) {
    echo json_encode(["status" => "error", "message" => "Alle möglichen Paarungen wurden bereits generiert."]);
    exit();
}
```

Zunächst wird der Table des letzten Roulettes gecleared.

```
// Clear the current_roulette table
$conn->query("TRUNCATE TABLE current_roulette");
```

Nun wird die generateNextRoundPairs Funktion aufgerufen, um neue Paarungen für die jetztige Runde zu erstellen, basierend auf den noch nicht gepaarten Usern.

```
$pairs = generateNextRoundPairs($users, $existingPairings);
```

Nun wird ein neues Array angelegt, das später das finale Ergebnis speichern wird. Die foreach Schleife iteriert durch alle Paarungen im `$pairs` Array.
Das `$pairs` Array existiert aus der `generateNextRoundPairs()` Funktion und enthält die User IDs.
In der äußeren Schleife befindet sich ein Array, welches temporär die Namen der Paare speichern wird.

```
$responsePairs = [];

foreach ($pairs as $pair) {
    $responsePair = [];
```

Die innere Schleife iteriert über jede User ID im `$pair` und weist jeder ID den jeweiligen Namen zu.
Dieser Vorgang wird so lange wiederholt bis das das `$responsePairs` (mit s) alle Namen der User anstelle ihrer ID beinhaltet.

```
    foreach ($pair as $userId) {
        $responsePair[] = $users[$userId]; // Add user name to response pair
    }
    $responsePairs[] = $responsePair;
```

### If Statements

Das if Statement überprüft ob die Anzahl der User pro Paar == 2 ist (Pair) oder == 3 (Trio).

Bei einem Pair werden zwei User in das `current_roulette` und in die insgesamten `roulette_winners` inserted und der user3 mit `NULL`, weil es keinen dritten User gibt.

Bei == 3 wird in diesem Fall der user3 einfach nicht mit `Null` gespeichert.

Im Bind_param werden alle als i = Integer gespeichert, da in den beiden Tables keine Namen sondern IDs stehen.

```
    if (count($pair) == 2) {
        $stmt = $conn->prepare("INSERT INTO current_roulette (user1, user2, user3) VALUES (?, ?, NULL)");
        $stmt->bind_param("ii", $pair[0], $pair[1]);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO roulette_winners (user1, user2, user3) VALUES (?, ?, NULL)");
        $stmt->bind_param("ii", $pair[0], $pair[1]);
        $stmt->execute();
        $stmt->close();
    } elseif (count($pair) == 3) {
        $stmt = $conn->prepare("INSERT INTO current_roulette (user1, user2, user3) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $pair[0], $pair[1], $pair[2]);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO roulette_winners (user1, user2, user3) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $pair[0], $pair[1], $pair[2]);
        $stmt->execute();
        $stmt->close();
    }
}
```

### Error und Success Messages

Je nach Erfolg oder Misserfolg soll auch hier eine Nachricht mit Animation ausgegeben werden.

```
if (count($responsePairs) > 0) {
    echo json_encode(["status" => "success", "message" => "Neue Runde von Paarungen erfolgreich generiert.", "pairs" => $responsePairs]);
} else {
    echo json_encode(["status" => "error", "message" => "Keine neuen Paarungen konnten generiert werden."]);
}
```

## add_new_user.php

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

`'addUser'` ist vom `admin_dashboard.php` auf dem Benutzer hinzufügen Button. Hier wird geschaut ob der Button geclickt wird.

```
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addUser'])) {

```

Der `'newUserName'` stammt ebenfalls aus dem admin_dashboard.php, aus dem Inputfeld und beinhaltet den Namen des Users, den der Admin adden möchte.
Danach folgt ein Insert Befehl wo der Name und der Teilnahmestatus (default auf true) in den `users` table eingefügt werden.

```
    $name = $_POST['newUserName'];

    $insertUser = $conn->prepare("INSERT INTO users (name, participate_in_roulette) VALUES (?, 1)");
    $insertUser->bind_param("s", $name);
```

Beim Einfügen kommt hier ebenfalls eine Success- oder Errormessage.

```
    if ($insertUser->execute()) {
        echo "<p>New user added successfully and ready to participate in the roulette.</p>";
    } else {
        echo "<p>Error adding user.</p>";
    }
    $insertUser->close();
```

Anschließend wird das Admin Dashboard refresht.

```
    header("Location: admin_dashboard.php");
    exit;
}
```

## delete_user.php

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

Das 'deleteUser' kommt aus einer JavaScript Funktion `confirmDelete`, welche im Admin Dashboard ausgeführt wird. Der Delete Button triggert das Confirmation Popup ("Wollen Sie den User wirklich löschen").

```
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deleteUser'])) {
    $userId = $_POST['deleteUser'];
```

`is_numeric` prüft, ob es sich bei der ID tatsächlich um eine Zahl hat (best Practice). Danach wird der user über die ID gelöscht. Darauf folgen Error oder Successmessages und das Admin Dashboard wird refresht.

```
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
```

## toggle_participation.php

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

Das `toggleParticipation` wird über den Teilnahme Button im Admin Dashboard getriggered.
Über einen einfachen `Update` Command im SQL wird der Teilnahmestatus am Roulette `participate_in_roulette` auf den gegenteiligen Wert verändert. Danach wird das Admin Dashboard refresht.

```
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggleParticipation'])) {
    $userId = $_POST['toggleParticipation'];
    // Query to toggle the participate_in_roulette status
    $stmt = $conn->prepare("UPDATE users SET participate_in_roulette = NOT participate_in_roulette WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_dashboard.php");
    exit;
}
```

# DB,JS,CSS

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

## db.php

Name der Datenbak, Username um sich damit zu verbinden, Passwort um sich einzuloggen als user und Name der Datenbank.

Nun wird die Connection erstellt, eine neue Instanz der MYSQLI Klasse, welche die obigen Namen als Parameter verwendet. Dieses Objekt, dass in `$conn` gespeichert ist wird zur Interaktion mit der DB verwendet.

```
<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "siemens_lunch_roulette";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

## script.js

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

Auskommentiert!

### Funktion confirmDelete

```
function confirmDelete(userId) {
  Swal.fire({
    title: "Sind Sie sicher?", //Titel des Alerts
    text: "Kann nicht rückgängig gemacht werden!", //Anzeigetext
    icon: "warning", //Icon Type
    showCancelButton: true, //Abbruch Button anzeigen
    confirmButtonColor: "#009999", //Bestätigung Button Farbe
    cancelButtonColor: "#d33", //Abbruch Button Farbe
    confirmButtonText: "Ja, Benutzer löschen!", //Individueller Text
  }).then((result) => {
    if (result.isConfirmed) { //Wenn die Action bestätigt wird
      const form = document.createElement("form"); //Neues Formelement
      form.method = "POST";
      form.action = "admin_dashboard.php";
      // Script sucht nach der deleteUser POST Variable im
      Admin Dashboard

      const hiddenField = document.createElement("input"); //Neues Input Feld erstellen
      hiddenField.type = "hidden"; //Input Feld verstecken
      hiddenField.name = "deleteUser"; //deletUser benennen

      /*hiddenField name entspricht dem Namen
      im Admin Dashboard auf dem button
      deshalb wird das JS getriggert wenn der button
      geclickt wird (Form absenden)*/

      hiddenField.value = userId; //Wert auf die userId setzen

      form.appendChild(hiddenField); //Input Feld in das Form einfügen
      document.body.appendChild(form); //Form dem Body hinzufügen
      form.submit(); //Form abschicken
    }
  });
}
```

Auskommentiert!

### Funktion startRoulette

```
function startRoulette() {
  Swal.fire({
    title: "Bist du sicher?",
    text: "Möchtest du das Roulette wirklich starten?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#009999",
    cancelButtonColor: "#d33",
    confirmButtonText: "Ja, starten!",
    cancelButtonText: "Abbrechen",
  }).then((result) => {
    if (result.isConfirmed) { //User muss Roulette Start bestätigen
    //Als Vorsorge gegen "unabsichtliches starten / button klicken"
      $.ajax({
        url: "../functional_files/generate_pairings.php", // Correct path, diese PHP File mit der Logik asuführen
        method: "POST",
        dataType: "json", // Expecting a JSON response
        success: function (response) {
          if (response.status === "success") { //Message bei Erfolg
            Swal.fire("Gestartet!", response.message, "success");
          } else { //Message bei Error
            Swal.fire("Fehler!", response.message, "error");
          }
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
```

## nav.css

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)

```
body {
  background-color: #f8f9fa;
  margin: 0;
  padding: 0;
  font-family: "Noto Sans Arabic", sans-serif;
}

/* Headings */
h1,
h2,
h3,
h4,
h5,
h6 {
  font-family: "Comfortaa", cursive;
}

/* Navbar Styles */
.navbar-custom {
  background-color: #fff;
  border-bottom: 1px solid #ddd;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.company-logo {
  width: 100px;
  height: auto;
  margin-left: 15px;
}

.nav-link {
  color: #333 !important;
}

.nav-link:hover {
  color: #66b3ff !important;
}

.logout-btn i {
  margin-right: 5px;
}

/* Page Container */
.main {
  margin-top: 50px;
}

.card {
  background-color: #fff;
  border-radius: 20px;
  border: 1px solid #ddd;
  transition: border 0.3s ease;
  margin-bottom: 30px;
  overflow: hidden;
  padding: 20px;
}

.card-title,
.winners-heading {
  font-size: 2rem;
  font-weight: bold;
  color: #333;
  text-align: center;
  margin-bottom: 20px;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.winners-heading {
  font-size: 3rem;
  color: #009999;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.card-footer {
  background-color: #009999;
  border-bottom-left-radius: 20px;
  border-bottom-right-radius: 20px;
  padding: 15px 0;
  text-align: center;
  color: #fff;
  font-size: 1.8rem;
  font-weight: bold;
}

/* User Management Table */
.table th,
.table td {
  border-top: none;
}

.btn-info {
  color: #fff;
  background-color: #009999;
  border-color: #009999;
}

.btn-info:hover {
  background-color: #007777;
  border-color: #006666;
}

.btn-signup,
.btn-login {
  background-color: #009999;
  color: #fff;
  border: none;
  border-radius: 50px;
  padding: 15px 35px;
  cursor: pointer;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  font-weight: bold;
  text-transform: uppercase;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  margin: 15px 15px 15px 15px;
}

.btn-signup::before,
.btn-login::before {
  content: "";
  position: absolute;
  top: 0;
  left: -50%;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.1);
  transition: all 0.5s ease;
  z-index: 1;
}

.btn-signup:hover::before,
.btn-login:hover::before {
  left: 0;
}

.btn-signup:hover,
.btn-login:hover {
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
  background-color: #66b3ff;
}

.signup-container,
.login-container {
  max-width: 400px;
  margin: 45px auto;
  background-color: #fff;
  padding: 40px;
  border-radius: 8px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.signup-container h2,
.login-container h2 {
  font-weight: 700;
  margin-bottom: 30px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group input {
  border-radius: 25px;
  padding: 15px;
  border: 1px solid #ced4da;
  box-shadow: none;
}

.form-group input:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: none;
}

.login-link,
.signup-link {
  margin-top: 20px;
  text-align: center;
}

.login-link a,
.signup-link a {
  color: #007bff;
  text-decoration: none;
  font-weight: 500;
}

.login-link a:hover,
.signup-link a:hover {
  text-decoration: underline;
}

.winner-name {
  font-size: 1.5rem;
  color: #333;
  text-align: center;
  margin-bottom: 5px;
}

.winner-icon {
  font-size: 80px;
  color: #009999;
  margin: 0 auto 20px;
  display: block;
  text-align: center;
}

.winner-label {
  font-size: 1rem;
  color: #333;
  font-weight: bold;
  margin-bottom: 5px;
}

.badge-success,
.badge-danger {
  color: #fff;
  padding: 10px;
  font-size: 1.2rem;
  font-weight: bold;
}

.badge-success {
  background-color: #28a745;
}

.badge-danger {
  background-color: #dc3545;
}

.alert {
  text-align: center;
}

.card-footer p {
  margin: 0;
}
```

[Zurück zum Inhaltsverzeichnis](#inhaltsverzeichnis)
