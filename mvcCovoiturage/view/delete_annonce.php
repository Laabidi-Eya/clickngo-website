<?php
require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$response = ['success' => false, 'message' => ''];

try {
    // Récupérer l'ID envoyé via POST
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? intval($data['id']) : null;

    if (!$id) {
        throw new Exception("ID de l'annonce manquant.");
    }

    // Instancier le contrôleur
    $pdo = config::getConnexion();
    $controller = new AnnonceCovoiturageController($pdo);

    // Supprimer l’annonce
    $result = $controller->deleteAnnonce($id);

    if ($result) {
        $response['success'] = true;
        $response['message'] = "Annonce supprimée avec succès.";
    } else {
        $response['message'] = "Échec de la suppression de l'annonce.";
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>