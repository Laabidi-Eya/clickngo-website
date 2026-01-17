<?php
header('Content-Type: application/json');
require_once 'Controller/ChaiseController.php';

$data = json_decode(file_get_contents('php://input'), true);
$chaiseId = $data['chaiseId'] ?? null;
$userId = $data['userId'] ?? null;
$action = $data['action'] ?? null;

error_log("Requête reçue: chaiseId=$chaiseId, userId=$userId, action=$action");

try {
    $chaiseController = new ChaiseController();

    if (!$chaiseId || !$action) {
        throw new Exception('Paramètres chaiseId et action requis');
    }

    if ($action === 'reserve') {
        $chaiseController->reserverChaise($chaiseId, $userId);
        error_log("Succès: Chaise $chaiseId réservée");
        echo json_encode(['status' => 'success', 'message' => 'Chaise réservée']);
    } elseif ($action === 'free') {
        $chaiseController->libererChaise($chaiseId);
        error_log("Succès: Chaise $chaiseId libérée");
        echo json_encode(['status' => 'success', 'message' => 'Chaise libérée']);
    } else {
        throw new Exception('Action invalide');
    }
} catch (Exception $e) {
    error_log("Erreur manage_chaise: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>