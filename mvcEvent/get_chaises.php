<?php
header('Content-Type: application/json');
require_once 'Controller/ChaiseController.php';

$chaiseController = new ChaiseController();
$eventId = $_GET['event_id'] ?? null;
$user_id = $_GET['user_id'] ?? null; // Récupérer user_id depuis la requête

try {
    if ($eventId) {
        $chaises = $chaiseController->getChaisesByEvent($eventId);
        $stats = $chaiseController->getEventSeatStats($eventId);
        echo json_encode([
            'status' => 'success',
            'chaises' => $chaises,
            'stats' => $stats
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Event ID requis']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>