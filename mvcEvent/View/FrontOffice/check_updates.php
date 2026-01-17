<?php
// Activation du buffering strict
ob_start();

require_once __DIR__.'/../../Controller/EventController.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache');

session_start();

$controller = new EventController();
$lastKnownHash = $_SESSION['last_event_hash'] ?? '';

$result = $controller->checkForUpdates($lastKnownHash);

// Debug logging
error_log("Update Check - HasChanges: ".($result['has_changes'] ? 'true' : 'false'));

if ($result['has_changes']) {
    $_SESSION['last_event_hash'] = $result['new_hash'];
}

ob_end_clean(); // Nettoyage garantie
die(json_encode([
    'status' => 'success',
    'has_changes' => $result['has_changes'],
    'event_count' => $result['event_count'],
    'timestamp' => time()
]));