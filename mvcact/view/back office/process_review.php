<?php
require_once __DIR__ . '/../../controller/ReviewController.php';

// Ce fichier sera implémenté ultérieurement quand le ReviewController et le ReviewModel seront disponibles
// Pour l'instant, il redirige simplement vers le dashboard avec un message

// Démarrer la session pour les messages
session_start();

// Vérifier si l'action et l'ID sont spécifiés
if (!isset($_GET['action']) || !isset($_GET['id'])) {
    $_SESSION['error'] = "Action ou ID d'avis non spécifié";
    header("Location: dashboard.php?action=reviews");
    exit;
}

$action = $_GET['action'];
$id = intval($_GET['id']);

// Initialiser le contrôleur
$reviewController = new ReviewController();

// Traiter l'action demandée
switch ($action) {
    case 'approve':
        $result = $reviewController->approveReview($id);
        if ($result['success']) {
            $_SESSION['success'] = "L'avis #$id a été approuvé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'approbation de l'avis : " . $result['message'];
        }
        break;
        
    case 'reject':
        $result = $reviewController->rejectReview($id);
        if ($result['success']) {
            $_SESSION['success'] = "L'avis #$id a été rejeté avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors du rejet de l'avis : " . $result['message'];
        }
        break;
        
    case 'delete':
        $result = $reviewController->deleteReview($id);
        if ($result['success']) {
            $_SESSION['success'] = "L'avis #$id a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de l'avis : " . $result['message'];
        }
        break;
        
    default:
        $_SESSION['error'] = "Action non reconnue.";
        break;
}

// Rediriger vers la page des avis
header("Location: dashboard.php?action=reviews");
exit;
?> 