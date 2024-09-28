<?php
require_once('../database/db.php');

function getUsers($conn) {
    $sql = "SELECT id, name FROM users WHERE participate_in_roulette = 1";
    $result = $conn->query($sql);
    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Fehler beim Abrufen der Benutzer: " . $conn->error]);
        exit();
    }
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[$row['id']] = $row['name']; // Speichert user in assoziativem array, IDs funktionieren als Schlüssel
                                            // Namen werden als Werte verwendet
    }
    return $users;
}

function getExistingPairings($conn) {
    $sql = "SELECT user1, user2 FROM roulette_winners";
    $result = $conn->query($sql);
    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Fehler beim Abrufen der Paarungen: " . $conn->error]);
        exit();
    }
    $pairings = [];
    while ($row = $result->fetch_assoc()) { // Selbe wie getUsers, Paarungen abrufen und speichern
        $pairings[] = [$row['user1'], $row['user2']];
    }
    return $pairings;
}

function allPairsGenerated($users, $pairings) {
    $totalUsers = count($users); // User zählen
    $totalPairs = $totalUsers * ($totalUsers - 1) / 2; // n User * Anzahl nachdem ja ein user gewählt wurde, Gesamtanzahl der Möglichkeiten muss hier durch Zwei dividiert werden, da ja die Reihenfolge User a und User b oder User b und User a egal ist.
    return count($pairings) >= $totalPairs;
}

function generateNextRoundPairs($users, $existingPairings) {
    $pairs = [];
    $userCount = count($users);
    $existingPairSet = array();

    foreach ($existingPairings as $pair) {
        sort($pair);
        $existingPairSet[implode('-', $pair)] = true;
    }

    $userIds = array_keys($users);
    shuffle($userIds); // Shuffle users for randomness

    $usedUsers = [];
    for ($i = 0; $i < $userCount; $i++) {
        for ($j = $i + 1; $j < $userCount; $j++) {
            if (!in_array($userIds[$i], $usedUsers) && !in_array($userIds[$j], $usedUsers)) {
                $pair = [$userIds[$i], $userIds[$j]];
                sort($pair); // Sort to handle (a, b) == (b, a)
                if (!isset($existingPairSet[implode('-', $pair)])) {
                    $pairs[] = $pair;
                    $existingPairSet[implode('-', $pair)] = true;
                    $usedUsers[] = $userIds[$i];
                    $usedUsers[] = $userIds[$j];
                }
            }
        }
    }

    // Handle odd number of users by adding the last remaining user to the last pair
    if (count($usedUsers) < $userCount) {
        $remainingUsers = array_diff($userIds, $usedUsers);
        if (count($remainingUsers) == 1 && !empty($pairs)) {
            $pairs[count($pairs) - 1][] = array_values($remainingUsers)[0];
        }
    }

    return $pairs;
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$users = getUsers($conn);
$existingPairings = getExistingPairings($conn);

if (allPairsGenerated($users, $existingPairings)) {
    echo json_encode(["status" => "error", "message" => "Alle möglichen Paarungen wurden bereits generiert."]);
    exit();
}

// Clear the current_roulette table
$conn->query("TRUNCATE TABLE current_roulette");

$pairs = generateNextRoundPairs($users, $existingPairings);

$responsePairs = [];

foreach ($pairs as $pair) {
    $responsePair = [];
    foreach ($pair as $userId) {
        $responsePair[] = $users[$userId]; // Add user name to response pair
    }
    $responsePairs[] = $responsePair;
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

if (count($responsePairs) > 0) {
    echo json_encode(["status" => "success", "message" => "Neue Runde von Paarungen erfolgreich generiert.", "pairs" => $responsePairs]);
} else {
    echo json_encode(["status" => "error", "message" => "Keine neuen Paarungen konnten generiert werden."]);
}

$conn->close();
?>
