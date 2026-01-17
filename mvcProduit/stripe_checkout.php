<?php
require_once 'vendor/autoload.php';

// Configure Stripe with your secret key
\Stripe\Stripe::setApiKey('sk_test_51RJL0sQabomDz0BaC9s0L184katdXI601GgaBipHtVd6KF39QhKgTN1sMfUNN0OwPgPE2kN5h1WHR3Y1Ke7Ml61U00xkJSFImX');

header('Content-Type: application/json');

try {
    // Initialize Stripe client
    $stripe = new \Stripe\StripeClient('sk_test_51RJL0sQabomDz0BaC9s0L184katdXI601GgaBipHtVd6KF39QhKgTN1sMfUNN0OwPgPE2kN5h1WHR3Y1Ke7Ml61U00xkJSFImX');
    
    // Get cart data from POST request
    $panier_data = json_decode($_POST['panier_data'] ?? '[]', true);
    $prix_total = floatval($_POST['prix_total'] ?? 0);
    
    if (empty($panier_data) || $prix_total <= 0) {
        throw new Exception('Invalid cart data or total price');
    }

    // Create line items for Stripe
    $line_items = [];
    foreach ($panier_data as $item) {
        $line_items[] = [
            'price_data' => [
                'currency' => 'eur', // Assuming TND as currency
                'product_data' => [
                    'name' => $item['nom'] ?? 'Product',
                    'description' => $item['description'] ?? 'No description',
                ],
                'unit_amount' => round(($item['prix'] ?? $prix_total) ), // Convert TND to millimes (1 TND = 1000 millimes)
            ],
            'quantity' => $item['quantite'] ?? 1,
        ];
    }

    // Create checkout session
    $checkout_session = $stripe->checkout->sessions->create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => 'http://localhost/projet%20web/mvcProduit/view/front%20office/commande_success.php',
        'cancel_url' => 'http://localhost/projet%20web/mvcProduit/view/front%20office/commande_success.php',
    ]);

    // Return session ID to frontend
    echo json_encode(['id' => $checkout_session->id]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>