<?php
session_start();

header('Content-Type: application/json');

try {
    $input = file_get_contents('php://input');
    $orderDetails = json_decode($input, true);

    if (!$orderDetails) {
        throw new Exception('Invalid order details');
    }

    $_SESSION['order_details'] = [
        'nom' => $orderDetails['nom'] ?? '',
        'prenom' => $orderDetails['prenom'] ?? '',
        'email' => $orderDetails['email'] ?? '',
        'telephone' => $orderDetails['telephone'] ?? '',
        'quantite' => $orderDetails['quantite'] ?? 1,
        'prix_total' => $orderDetails['prix_total'] ?? 0,
        'paiement' => $orderDetails['paiement'] ?? '',
        'panier' => $orderDetails['panier'] ?? []
    ];

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>