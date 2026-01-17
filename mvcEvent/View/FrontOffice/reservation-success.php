<?php
session_start();

// Vérification CSRF pour le retour Stripe
if (!isset($_SESSION['stripe_csrf_token']) || !hash_equals($_SESSION['stripe_csrf_token'], $_SESSION['csrf_token'])) {
    $_SESSION['reservation_error'] = "Erreur de sécurité lors du retour de paiement";
    header("Location: reservation.php?event_id=" . ($_GET['event_id'] ?? ''));
    exit;
}

require_once '../../Controller/EventController.php';
require_once '../../Controller/ChaiseController.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51RKl7PRuD152msSdq4WLSb7t9HhOXxMBv4mCBzHQp18sp3Ilyk2XhVfkaQwLTV7TjSAc6Zo3dUZEZ1DYTmWkz0vF00kvIzlzpM');

// Validation des entrées
$eventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
$session_id = $_GET['session_id'] ?? null;

if (!$eventId || !$session_id) {
    $_SESSION['reservation_error'] = "Paramètres de réservation invalides";
    header("Location: evenemant.php");
    exit;
}

try {
    // Vérification du paiement Stripe
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    
    if ($session->payment_status !== 'paid') {
        throw new Exception("Le paiement n'a pas été confirmé");
    }

    $eventController = new EventController();
    $chaiseController = new ChaiseController();

    $event = $eventController->getEventById($eventId);
    if (!$event) {
        throw new Exception("L'événement n'existe plus");
    }

    // Récupérer user_id depuis les métadonnées Stripe
    $user_id = $session->metadata->user_id ?? null;

    // Vérifier la cohérence avec la session utilisateur (optionnel)
    if ($user_id && isset($_SESSION['user_id']) && $user_id != $_SESSION['user_id']) {
        throw new Exception("Incohérence dans l'identifiant utilisateur");
    }

    // Traitement des sièges réservés
    $selectedSeats = json_decode($session->metadata->seat_ids, true) ?? [];
    if (empty($selectedSeats)) {
        throw new Exception("Aucun siège spécifié dans la réservation");
    }

    $allChaises = $chaiseController->getChaisesByEvent($eventId);
    $availableSeats = array_filter($allChaises, fn($chaise) => $chaise['statut'] === 'libre');
    $availableSeatIds = array_column($availableSeats, 'id');

    $seatsToReserve = [];
    foreach ($selectedSeats as $seatId) {
        if (!in_array($seatId, $availableSeatIds)) {
            throw new Exception("Le siège $seatId n'est plus disponible");
        }
        $seatsToReserve[] = $seatId;
    }

    // Confirmation des réservations
    $successCount = 0;
    foreach ($seatsToReserve as $seatId) {
        try {
            $chaiseController->reserverChaise($seatId, $user_id); // Utiliser user_id au lieu de $session->customer
            $successCount++;
        } catch (Exception $e) {
            error_log("Erreur réservation siège $seatId: " . $e->getMessage());
        }
    }

    if ($successCount > 0) {
        $_SESSION['reservation_success'] = "Paiement confirmé et réservation effectuée pour $successCount chaise(s) !";
    } else {
        throw new Exception("Aucun siège n'a pu être réservé");
    }

} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log("Stripe API Error: " . $e->getMessage());
    $_SESSION['reservation_error'] = "Erreur lors de la vérification du paiement";
} catch (Exception $e) {
    error_log("Reservation Error: " . $e->getMessage());
    $_SESSION['reservation_error'] = $e->getMessage();
}

header("Location: evenemant.php");
exit;
?>