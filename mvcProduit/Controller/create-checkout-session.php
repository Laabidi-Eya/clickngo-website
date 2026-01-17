<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Enable detailed error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/stripe_errors.log');

try {
    // Validate input
    if (!isset($_POST['panier_data']) || empty(trim($_POST['panier_data']))) {
        throw new Exception("Missing or empty field: panier_data");
    }
    if (!isset($_POST['prix_total']) || empty(trim($_POST['prix_total']))) {
        throw new Exception("Missing or empty field: prix_total");
    }

    $panier_data = json_decode($_POST['panier_data'], true);
    $prix_total = floatval($_POST['prix_total']);

    if (empty($panier_data) || $prix_total <= 0) {
        throw new Exception('Invalid cart data or total price');
    }

    $stripe = Config::getStripe();

    // Create line items for Stripe
    $line_items = [];
    foreach ($panier_data as $item) {
        $line_items[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $item['nom'] ?? 'Product',
                    'description' => $item['description'] ?? 'No description',
                ],
                'unit_amount' => round(($item['prix']  * 100)/3.3 ), // Convert TND to millimes
            ],
            'quantity' => $item['quantite'] ?? 1,
        ];
    }

    // Create Checkout Session
    $checkout_session = $stripe->checkout->sessions->create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => 'http://localhost/projet%20web/mvcProduit/view/front%20office/commande_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/projet%20web/mvcProduit/view/front%20office/panier.php?error=cancelled',
        'locale' => 'fr',
    ]);

    // Log session details for debugging
    error_log('Checkout session created: ' . $checkout_session->id . ' at ' . date('Y-m-d H:i:s'));
    error_log('Checkout session details: ' . json_encode($checkout_session));

    echo json_encode(['id' => $checkout_session->id]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log('Stripe API Error: ' . $e->getMessage() . ' at ' . date('Y-m-d H:i:s'));
    http_response_code(500);
    echo json_encode(['error' => 'Stripe API error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log('General Error: ' . $e->getMessage() . ' at ' . date('Y-m-d H:i:s'));
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create checkout session: ' . $e->getMessage()]);
}
?>