<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Controller/DemandeCovoiturageController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Méthode non autorisée']));
}

$id_demande = (int)($_POST['id_demande'] ?? 0);
$action = $_POST['action'] ?? '';
$id_annonce = (int)($_POST['id_annonce'] ?? 0);

try {
    $controller = new DemandeCovoiturageController();
    
    if ($id_demande <= 0 || !in_array($action, ['approve', 'reject'])) {
        throw new Exception("Données invalides");
    }

    $newStatus = ($action === 'approve') ? 'approuvé' : 'rejeté';
    
    if (!$controller->updateDemandeStatus($id_demande, $newStatus)) {
        throw new Exception("Échec de la mise à jour");
    }

    echo json_encode(['success' => true, 'newStatus' => $newStatus]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}