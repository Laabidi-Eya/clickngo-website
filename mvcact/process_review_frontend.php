<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/controller/ReviewController.php';

// Vérifier si la requête est une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $customerName = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
    $customerEmail = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
    $activityName = isset($_POST['activity_name']) ? trim($_POST['activity_name']) : '';
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    // Log des données reçues pour debug (sans l'image pour éviter des logs trop volumineux)
    $logData = $_POST;
    if (isset($logData['review_image'])) {
        $logData['review_image'] = '[FICHIER IMAGE]';
    }
    error_log("Données reçues dans process_review_frontend.php : " . print_r($logData, true));
    
    // Validation manuelle
    $errors = [];
    if (empty($customerName)) {
        $errors[] = "Le nom est obligatoire";
    }
    if (empty($customerEmail)) {
        $errors[] = "L'email est obligatoire";
    } elseif (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }
    if (empty($activityName)) {
        $errors[] = "Le nom de l'activité est obligatoire";
    }
    if ($rating < 1 || $rating > 5) {
        $errors[] = "La note doit être entre 1 et 5";
    }
    if (empty($comment)) {
        $errors[] = "Le commentaire est obligatoire";
    }
    
    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => implode(', ', $errors)
        ]);
        exit;
    }
    
    try {
        // Initialiser le contrôleur
        $reviewController = new ReviewController();
        
        // Récupérer l'ID de l'activité si disponible, sinon utiliser 0 (pour les avis généraux)
        $activityId = isset($_POST['activity_id']) && !empty($_POST['activity_id']) ? intval($_POST['activity_id']) : null;
        
        // Gérer l'image si elle existe
        $image = isset($_FILES['review_image']) && $_FILES['review_image']['error'] === UPLOAD_ERR_OK ? $_FILES['review_image'] : null;
        
        // Débogage supplémentaire
        error_log("Tentative d'ajout d'avis - ActivityID: " . ($activityId ?? 'NULL') . ", ActivityName: $activityName");
        
        // Appeler la méthode avec tous les paramètres nécessaires
        $result = $reviewController->addReview(
            $activityId,
            $activityName,
            $customerName, 
            $customerEmail, 
            $rating, 
            $comment,
            $image
        );
        
        // Log du résultat pour debug
        error_log("Résultat de l'ajout : " . print_r($result, true));
        
        // Retourner le résultat au format JSON
        header('Content-Type: application/json');
        echo json_encode($result);
    } catch (Exception $e) {
        error_log("Exception dans process_review_frontend.php : " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'enregistrement de votre avis: ' . $e->getMessage()
        ]);
    }
    exit;
} else {
    // Si ce n'est pas une requête POST, renvoyer une erreur
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}
?> 