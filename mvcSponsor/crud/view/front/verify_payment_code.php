<?php
header('Content-Type: application/json');
require_once(__DIR__ . "/../../controller/controller.php");

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_sponsor']) || !isset($data['payment_code'])) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$id_sponsor = (int)$data['id_sponsor'];
$payment_code = $data['payment_code'];

$controller = new sponsorController();
$sponsor = $controller->getSponsorById($id_sponsor);

if (!$sponsor) {
    echo json_encode(['success' => false, 'message' => 'Sponsor non trouvé']);
    exit;
}

if (isset($sponsor['payment_code']) && $sponsor['payment_code'] === $payment_code) {
    // Optionally, mark payment as done in DB here
    echo json_encode(['success' => true, 'message' => 'Paiement validé']);
} else {
    echo json_encode(['success' => false, 'message' => 'Code de paiement incorrect']);
}
?>
