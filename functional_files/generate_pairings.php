<?php
require_once('../database/db.php');

function getUsers($db) {
    $sql = "SELECT id, name FROM users WHERE participate_in_roulette = 1";
    $result = $db->query($sql);
    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Fehler beim Abrufen der Benutzer: " . $db->lastErrorMsg()]);
        exit();
    }
    $users = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $users[$row['id']] = $row['name']; // Speichert user in assoziativem array, IDs funktionieren als Schlüssel
    }
    return $users;
}

function getExistingPairings($db) {
    $sql = "SELECT user1, user2 FROM roulette_winners";
    $result = $db->query($sql);
    if (!$result) {
        echo json_encode(["status" => "error", "message" => "Fehler beim Abrufen der Paarungen: " . $db->lastErrorMsg()]);
        exit();
    }
    $pairings = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $pairings[] = [$row['user1'], $row['user2']];
    }
    return $pairings;
}

function allPairsGenerated($users, $pairings) {
    $totalUsers = count($users);
    $totalPairs = $totalUsers * ($totalUsers - 1) / 2;
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
    shuffle($userIds);

    $usedUsers = [];
    for ($i = 0; $i < $userCount; $i++) {
        for ($j = $i + 1; $j < $userCount; $j++) {
            if (!in_array($userIds[$i], $usedUsers) && !in_array($userIds[$j], $usedUsers)) {
                $pair = [$userIds[$i], $userIds[$j]];
                sort($pair);
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

$users = getUsers($db);
$existingPairings = getExistingPairings($db);

if (allPairsGenerated($users, $existingPairings)) {
    echo json_encode(["status" => "error", "message" => "Alle möglichen Paarungen wurden bereits generiert."]);
    exit();
}

// Clear the current_roulette table
$db->exec("DELETE FROM current_roulette");

$pairs = generateNextRoundPairs($users, $existingPairings);

$responsePairs = [];

foreach ($pairs as $pair) {
    $responsePair = [];
    foreach ($pair as $userId) {
        $responsePair[] = $users[$userId];
    }
    $responsePairs[] = $responsePair;

    if (count($pair) == 2) {
        $stmt = $db->prepare("INSERT INTO current_roulette (user1, user2, user3) VALUES (:user1, :user2, NULL)");
        $stmt->bindValue(':user1', $pair[0], SQLITE3_INTEGER);
        $stmt->bindValue(':user2', $pair[1], SQLITE3_INTEGER);
        $stmt->execute();
        
        $stmt = $db->prepare("INSERT INTO roulette_winners (user1, user2, user3) VALUES (:user1, :user2, NULL)");
        $stmt->bindValue(':user1', $pair[0], SQLITE3_INTEGER);
        $stmt->bindValue(':user2', $pair[1], SQLITE3_INTEGER);
        $stmt->execute();
    } elseif (count($pair) == 3) {
        $stmt = $db->prepare("INSERT INTO current_roulette (user1, user2, user3) VALUES (:user1, :user2, :user3)");
        $stmt->bindValue(':user1', $pair[0], SQLITE3_INTEGER);
        $stmt->bindValue(':user2', $pair[1], SQLITE3_INTEGER);
        $stmt->bindValue(':user3', $pair[2], SQLITE3_INTEGER);
        $stmt->execute();
        
        $stmt = $db->prepare("INSERT INTO roulette_winners (user1, user2, user3) VALUES (:user1, :user2, :user3)");
        $stmt->bindValue(':user1', $pair[0], SQLITE3_INTEGER);
        $stmt->bindValue(':user2', $pair[1], SQLITE3_INTEGER);
        $stmt->bindValue(':user3', $pair[2], SQLITE3_INTEGER);
        $stmt->execute();
    }
}

if (count($responsePairs) > 0) {
    echo json_encode(["status" => "success", "message" => "Neue Runde von Paarungen erfolgreich generiert.", "pairs" => $responsePairs]);
} else {
    echo json_encode(["status" => "error", "message" => "Keine neuen Paarungen konnten generiert werden."]);
}

$db->close();
?>
