<?php
session_start();
require_once(__DIR__ . "/../../config.php");

$currentUserId = $_SESSION['user']['id_user']; // par exemple 96

$db = Config::getConnexion();

// Requête SQL simple
$stmt = $db->prepare("
    SELECT id, seen_by
    FROM chat_messages
    ORDER BY created_at DESC
");
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$badgeCount = 0;

// Boucle PHP
foreach ($messages as $message) {
    $seenByArray = !empty($message['seen_by']) ? explode(',', $message['seen_by']) : [];

    if (in_array($currentUserId, $seenByArray)) {
        break; // user fi seen_by, stop
    } else {
        $badgeCount++;
    }
}

// Résultat
echo json_encode(['unread' => $badgeCount]);
?>
