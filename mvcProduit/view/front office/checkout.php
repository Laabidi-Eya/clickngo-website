<?php
require_once '../../config.php';

// Récupérer l'instance de Stripe
$stripe = Config::getStripe();
$publicKey = Config::getStripePublicKey();

// Récupérer les données du formulaire
$nom = isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : '';
$prenom = isset($_GET['prenom']) ? htmlspecialchars($_GET['prenom']) : '';
$telephone = isset($_GET['telephone']) ? htmlspecialchars($_GET['telephone']) : '';
$quantite = isset($_GET['quantite']) ? intval($_GET['quantite']) : 1;
$prix_total = isset($_GET['prix_total']) ? floatval($_GET['prix_total']) : 0;
$panier_data = isset($_GET['panier_data']) ? json_decode(urldecode($_GET['panier_data']), true) : [];
$paiement = isset($_GET['paiement']) ? htmlspecialchars($_GET['paiement']) : '';

// Vérifier si le prix est valide
if ($prix_total <= 0) {
    die('Erreur : Le prix total doit être supérieur à 0.');
}

try {
    // Créer une session de paiement
    $session = $stripe->checkout->sessions->create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur', // Changé en TND pour correspondre à votre devise
                'product_data' => [
                    'name' => 'Commande Panier',
                ],
                'unit_amount' => $prix_total * 1000, // Montant en millimes (Stripe utilise la plus petite unité)
            ],
            'quantity' => $quantite,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost/projet%20web/mvcProduit/view/front%20office/commande_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/projet%20web/mvcProduit/view/front%20office/panier.php',
        'metadata' => [
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'quantite' => $quantite,
            'prix_total' => $prix_total,
            'panier_data' => json_encode($panier_data),
            'paiement' => $paiement
        ]
    ]);

} catch (Exception $e) {
    die('Erreur lors de la création de la session : ' . $e->getMessage());
}
?>
