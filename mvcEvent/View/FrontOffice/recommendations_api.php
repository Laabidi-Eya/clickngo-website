<?php
require_once '../../Controller/EventController.php';

header('Content-Type: application/json');
$controller = new EventController();

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? $_GET['action'] ?? '';
    $user_id = $data['user_id'] ?? $_GET['user_id'] ?? 0;
    $event_id = $data['event_id'] ?? 0;

    if (!is_numeric($user_id) || $user_id <= 0) {
        throw new Exception('Invalid user_id');
    }

    if ($action === 'track_click' && $user_id && $event_id) {
        $result = $controller->trackClick($user_id, $event_id);
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Click tracked successfully' : 'Failed to track click'
        ]);
    } elseif ($action === 'get_recommendations' && $user_id) {
        $recommendations = $controller->getRecommendations($user_id);
        echo json_encode($recommendations);
    } else {
        throw new Exception('Invalid request');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>