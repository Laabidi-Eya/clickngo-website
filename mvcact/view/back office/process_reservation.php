<?php
require_once __DIR__ . '/../../controller/ReservationController.php';

// Démarrer la session pour les messages
session_start();

// Vérifier si l'action et l'ID sont spécifiés
if (!isset($_GET['action']) || !isset($_GET['id'])) {
    $_SESSION['error'] = "Action ou ID de réservation non spécifié";
    header("Location: dashboard.php?action=reservations");
    exit;
}

$action = $_GET['action'];
$id = intval($_GET['id']);

// Initialiser le contrôleur de réservation
$reservationController = new ReservationController();

// Traiter l'action demandée
switch ($action) {
    case 'confirm':
        $result = $reservationController->confirmPayment($id);
        if ($result['success']) {
            $_SESSION['success'] = "La réservation #$id a été confirmée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la confirmation de la réservation : " . $result['message'];
        }
        break;
        
    case 'cancel':
        $result = $reservationController->cancelReservation($id);
        if ($result['success']) {
            $_SESSION['success'] = "La réservation #$id a été annulée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'annulation de la réservation : " . $result['message'];
        }
        break;
        
    default:
        $_SESSION['error'] = "Action non reconnue.";
        break;
}

// Rediriger vers la page des réservations
header("Location: dashboard.php?action=reservations");
exit;
?> 