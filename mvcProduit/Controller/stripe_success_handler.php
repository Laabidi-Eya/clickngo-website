<?php
session_start();
require_once '../config.php';
require_once '../model/CommandModel.php';
require_once 'vendor/autoload.php'; // Include Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $session_id = $_GET['session_id'] ?? null;
    if (!$session_id) {
        throw new Exception('No Stripe session ID provided');
    }

    $orderDetails = $_SESSION['order_details'] ?? null;
    if (!$orderDetails) {
        throw new Exception('Order details not found in session');
    }

    // Verify Stripe session
    $stripe = Config::getStripe();
    $checkout_session = $stripe->checkout->sessions->retrieve($session_id);

    if ($checkout_session->payment_status !== 'paid') {
        throw new Exception('Payment not confirmed');
    }

    // Extract order details
    $nom = $orderDetails['nom'];
    $prenom = $orderDetails['prenom'];
    $email = $orderDetails['email'];
    $telephone = $orderDetails['telephone'];
    $quantite = $orderDetails['quantite'];
    $prix_total = $orderDetails['prix_total'];
    $paiement = $orderDetails['paiement'];
    $panier_data = $orderDetails['panier'];

    // Save order to database
    $db = Config::getConnexion();
    $idUtilisateur = 1; // Adapt to your authentication system
    if (!CommandModel::creerCommandeDepuisTableau($idUtilisateur, $panier_data, $db)) {
        throw new Exception('Failed to save order to database');
    }

    // Store details for receipt display in commande_success.php
    $_SESSION['derniere_commande'] = [
        'client' => [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone,
        ],
        'produits' => $panier_data,
        'total' => $prix_total,
        'paiement' => 'Carte (via Stripe)'
    ];

    // Send confirmation email
    $mail = Config::getMailer();
    $mail->addAddress($email, "$nom $prenom");
    $mail->isHTML(true);
    $mail->Subject = 'Confirmation de votre commande';

    $itemsList = '';
    foreach ($panier_data as $item) {
        $itemsList .= "<li>{$item['nom']} x{$item['quantite']} - " . ($item['prix'] * $item['quantite']) . " TND</li>";
    }

    $mail->Body = "
        <h2>Merci pour votre commande !</h2>
        <p>Voici les détails de votre commande :</p>
        <ul>
            <li><strong>Nom :</strong> $nom</li>
            <li><strong>Prénom :</strong> $prenom</li>
            <li><strong>Email :</strong> $email</li>
            <li><strong>Téléphone :</strong> $telephone</li>
            <li><strong>Quantité totale :</strong> $quantite</li>
            <li><strong>Prix total :</strong> $prix_total TND</li>
            <li><strong>Mode de paiement :</strong> Carte (via Stripe)</li>
        </ul>
        <h3>Articles commandés :</h3>
        <ul>$itemsList</ul>
        <p>Nous vous contacterons bientôt pour la livraison.</p>
    ";
    $mail->AltBody = strip_tags($mail->Body);
    $mail->send();

    // Clear order details from session
    unset($_SESSION['order_details']);
    unset($_SESSION['stripe_session_id']);

    // Redirect to commande_success.php
    header('Location: http://localhost/projet%20web/mvcProduit/view/front%20office/commande_success.php');
    exit;
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log('Stripe Error: ' . $e->getMessage());
    header('Location: http://localhost/projet%20web/mvcProduit/view/front%20office/panier.php?error=' . urlencode('Erreur de paiement Stripe: ' . $e->getMessage()));
    exit;
} catch (Exception $e) {
    error_log('General Error: ' . $e->getMessage());
    header('Location: http://localhost/projet%20web/mvcProduit/view/front%20office/panier.php?error=' . urlencode('Erreur: ' . $e->getMessage()));
    exit;
}