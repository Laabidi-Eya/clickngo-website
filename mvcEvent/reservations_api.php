<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__.'/Controller/EventController.php';
require_once __DIR__.'/Controller/ChaiseController.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
$action = $_GET['action'] ?? '';
$user_id = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

$eventController = new EventController();
$chaiseController = new ChaiseController();

try {
    switch ($action) {
        case 'get_all':
            // For testing, allow hardcoded user_id
            if (!$user_id) {
                if (isset($_SESSION['user']) && isset($_SESSION['user']['id_user'])) {
    $user_id = $_SESSION['user']['id_user'];
} else {
    // Redirection ou message d'erreur si l'utilisateur n'est pas connecté
    header("Location: login.php");
    exit;
} // Fallback to match global_user_id
            }
            $reservations = $eventController->getReservationsByUser();
            echo json_encode([
                'success' => true,
                'reservations' => $reservations,
                'count' => count($reservations)
            ]);
            break;

        case 'get_event':
            $eventId = $_GET['event_id'] ?? null;
            if (!$eventId) throw new Exception('ID événement manquant');
            
            $event = $eventController->getEventById($eventId);
            echo json_encode(['success' => true, 'event' => $event]);
            break;

        case 'get_seats':
            $eventId = $_GET['event_id'] ?? null;
            if (!$eventId) throw new Exception('ID événement manquant');
            
            $chaises = $chaiseController->getChaisesByEvent($eventId);
            $seats = array_map(function($chaise) {
                return [
                    'number' => $chaise['numero'],
                    'status' => $chaise['statut']
                ];
            }, $chaises);
            
            echo json_encode(['success' => true, 'seats' => $seats]);
            break;


case 'get_reservation':
    $eventId = $_GET['event_id'] ?? null;
    $user_id = $_SESSION['user']['id_user'] ?? null;

    if (!$eventId || !$user_id) {
        throw new Exception('ID événement ou utilisateur manquant');
    }

    $reservations = $chaiseController->getReservationsByUserAndEvent($eventId, $user_id);
    echo json_encode(['success' => true, 'reservation' => ['seats' => array_column($reservations, 'numero')]]);
    break;



case 'update':
    $data = json_decode(file_get_contents('php://input'), true);
    $eventId = $data['event_id'] ?? null;
    $seatNumbers = $data['seat_numbers'] ?? [];
    $user_id = $_SESSION['user']['id_user'] ?? null;

    if (!$eventId || empty($seatNumbers) || !$user_id) {
        throw new Exception('Données manquantes ou utilisateur non connecté');
    }

    $success = $chaiseController->updateMultipleReservations($eventId, $seatNumbers, $user_id);
    echo json_encode(['success' => $success]);
    break;



        case 'cancel':
            $data = json_decode(file_get_contents('php://input'), true);
            $eventId = $data['event_id'] ?? null;
    $user_id = $_SESSION['user']['id_user'] ?? null;
            
            if (!$eventId) {
                throw new Exception('ID événement manquant');
            }
            
            $success = $chaiseController->cancelReservation($eventId);
            echo json_encode(['success' => $success]);
            break;

        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'code' => 'SERVER_ERROR'
    ]);
}
?>