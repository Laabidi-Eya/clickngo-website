<?php
session_start();
require_once '../../config.php';
require_once '../../vendor/autoload.php';
require_once '../../Controller/commandeController.php'; // Corrected file name

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$orderDetails = $_SESSION['order_details'] ?? [];
$sendEmail = false;
error_log("orderDetails in commande_success.php: " . print_r($_SESSION['order_details'], true));
// Handle Stripe payment success
if (isset($_GET['session_id'])) {
    try {
        $stripe = Config::getStripe();
        $session = $stripe->checkout->sessions->retrieve($_GET['session_id']);

        if ($session->payment_status === 'paid') {
            $_SESSION['commande_success'] = true;

            // Store order details for receipt
            $orderDetails = [
                'nom' => $orderDetails['nom'] ?? 'N/A',
                'prenom' => $orderDetails['prenom'] ?? 'N/A',
                'email' => $orderDetails['email'] ?? 'N/A',
                'telephone' => $orderDetails['telephone'] ?? 'N/A',
                'prix_total' => $session->amount_total / 100,
                'panier' => $orderDetails['panier'] ?? []
            ];

            // Ajouter la commande dans la base de données
            $controller = new CommandController();
            $controller->ajouterCommandeDepuisStripe($orderDetails);

            $_SESSION['derniere_commande'] = [
                'client' => [
                    'nom' => $orderDetails['nom'],
                    'prenom' => $orderDetails['prenom'],
                    'email' => $orderDetails['email'],
                    'telephone' => $orderDetails['telephone'],
                ],
                'produits' => $orderDetails['panier'],
                'total' => $session->amount_total / 100,
                'paiement' => 'Carte'
            ];

            $sendEmail = true;
        } else {
            header("Location: panier.php?error=payment_failed");
            exit;
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        error_log('Stripe API Error: ' . $e->getMessage());
        header("Location: panier.php?error=invalid_session");
        exit;
    }
}

// Handle non-Stripe payment success
if (!isset($_GET['session_id']) && isset($_SESSION['commande_success']) && isset($_SESSION['derniere_commande'])) {
    $sendEmail = true; // Mark email to be sent for non-Stripe payment
}

// Redirect if no valid command
if (!isset($_SESSION['commande_success'])) {
    header("Location: produit.php");
    exit;
}

// Send confirmation email if needed
if ($sendEmail && !empty($orderDetails) && isset($_SESSION['derniere_commande'])) {
    $total = $_SESSION['derniere_commande']['total'] ?? 0;
    $paymentMethod = $_SESSION['derniere_commande']['paiement'] ?? 'N/A';
    
    // Ne pas envoyer un second email si le paiement est en Especes
    if ($paymentMethod !== 'Especes') {
        sendConfirmationEmail($orderDetails, $total, $paymentMethod);
    }
}

// Create a session for the receipt
$_SESSION['allow_recu'] = true;

// Clear session data
unset($_SESSION['commande_success']);
unset($_SESSION['order_details']);

// Function to send confirmation email
function sendConfirmationEmail($orderDetails, $total, $paymentMethod) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'laabidieya6@gmail.com';
        $mail->Password = 'pmda jocs mfeu fipz'; // Mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // SSL options (remove in production)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Set email details
        $mail->setFrom('laabidieya6@gmail.com', 'Votre Boutique');
        $mail->addAddress($orderDetails['email'], "{$orderDetails['nom']} {$orderDetails['prenom']}");
        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de votre commande';

        // Email template
        $mail->Body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden; }
                .header { background: linear-gradient(135deg, #ff8fa3, #c084fc); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; font-weight: 600; }
                .content { padding: 25px; background-color: #fff; }
                .greeting { font-size: 18px; margin-bottom: 20px; color: #555; }
                .details { background: #f9f9f9; padding: 15px; border-radius: 5px; }
                .details ul { list-style: none; padding: 0; }
                .details li { margin-bottom: 12px; font-size: 14px; }
                .details li strong { color: #ff8fa3; }
                .divider { border-bottom: 2px dashed #ddd; margin: 20px 0; }
                .items { margin-top: 20px; }
                .item-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
                .item-row:last-child { border-bottom: none; }
                .total { font-size: 20px; font-weight: bold; text-align: right; margin-top: 20px; color: #c084fc; }
                .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #777; }
                .footer a { color: #ff8fa3; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Merci pour votre commande, ' . htmlspecialchars($orderDetails['prenom']) . ' !</h1>
                </div>
                <div class="content">
                    <p class="greeting">Nous sommes ravis de confirmer votre commande. Voici tous les détails pour votre suivi :</p>
                    <div class="details">
                        <ul>
                            <li><strong>Nom :</strong> ' . htmlspecialchars($orderDetails['nom']) . '</li>
                            <li><strong>Prénom :</strong> ' . htmlspecialchars($orderDetails['prenom']) . '</li>
                            <li><strong>Email :</strong> ' . htmlspecialchars($orderDetails['email']) . '</li>
                            <li><strong>Téléphone :</strong> ' . htmlspecialchars($orderDetails['telephone']) . '</li>
                            <li><strong>Mode de paiement :</strong> ' . htmlspecialchars($paymentMethod) . '</li>
                        </ul>
                    </div>
                    <div class="divider"></div>
                    <div class="items">
                        <h2 style="color: #ff8fa3; font-size: 20px;">Articles commandés</h2>';

        foreach ($orderDetails['panier'] as $item) {
            $mail->Body .= '
                <div class="item-row">
                    <span>' . htmlspecialchars($item['nom']) . ' x' . $item['quantite'] . '</span>
                    <span>' . number_format($item['prix'] * $item['quantite'], 2) . ' TND</span>
                </div>';
        }

        $mail->Body .= '
                    </div>
                    <div class="total">Total : ' . number_format($total, 2) . ' TND</div>
                    <p style="margin-top: 20px;">Nous vous contacterons sous peu pour organiser la livraison. Merci de votre confiance !</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' Votre Boutique. <a href="#">Nous contacter</a></p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = strip_tags($mail->Body);
        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de Commande</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 50px;
            text-align: center;
            background-color: #f2f2f2;
        }
        .message {
            padding: 30px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #28a745;
        }
        a {
            display: inline-block;
            margin: 10px;
            text-decoration: none;
            color: #007bff;
        }
        .btn-recu {
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .btn-recu:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 143, 163, 0.3);
        }
        #recu-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            position: relative;
        }
        .close-btn {
            position: absolute;
            right: 15px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        .recu-details {
            text-align: left;
            margin-top: 20px;
        }
        .recu-details h3 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .recu-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="message">
        <h2>✅ Commande enregistrée avec succès !</h2>
        <p>Votre achat a été traité avec succès. Merci pour votre confiance.</p>
        <a href="recu.php" class="btn-recu">Reçu</a>
        <br>
        <a href="produit.php">Retour à la boutique</a>
    </div>

    <!-- Modal pour le reçu -->
    <div id="recu-modal">
        <div class="modal-content">
            <span class="close-btn" onclick="fermerRecu()">×</span>
            <h2>Reçu de commande</h2>
            <div class="recu-details">
                <h3>Détails de la commande</h3>
                <div id="recu-contenu"></div>
            </div>
        </div>
    </div>

    <script>
        // Clear the cart from localStorage
        localStorage.removeItem('panier');
        const commandeDetails = <?php echo json_encode($_SESSION['derniere_commande'] ?? []); ?>;

        // Store commandeDetails in localStorage for receipt
        localStorage.setItem('derniere_commande', JSON.stringify(commandeDetails));

        // Update cart count if function exists
        if (typeof updateCartCount === 'function') {
            updateCartCount(0);
        }

        function afficherRecu() {
            const modal = document.getElementById('recu-modal');
            const contenu = document.getElementById('recu-contenu');
            
            if (commandeDetails && Object.keys(commandeDetails).length > 0) {
                let html = `
                    <div class="recu-row">
                        <span>Date:</span>
                        <span>${new Date().toLocaleDateString()}</span>
                    </div>
                    <div class="recu-row">
                        <span>Numéro de commande:</span>
                        <span>#${Math.random().toString(36).substr(2, 9).toUpperCase()}</span>
                    </div>
                    <div class="recu-row">
                        <span>Mode de paiement:</span>
                        <span>${commandeDetails.paiement || 'N/A'}</span>
                    </div>
                `;

                if (commandeDetails.client) {
                    html += `
                        <div class="recu-row">
                            <span>Nom:</span>
                            <span>${commandeDetails.client.nom || ''}</span>
                        </div>
                        <div class="recu-row">
                            <span>Prénom:</span>
                            <span>${commandeDetails.client.prenom || ''}</span>
                        </div>
                        <div class="recu-row">
                            <span>Téléphone:</span>
                            <span>${commandeDetails.client.telephone || ''}</span>
                        </div>
                    `;
                }

                if (commandeDetails.produits && commandeDetails.produits.length > 0) {
                    html += '<h3>Produits</h3>';
                    commandeDetails.produits.forEach(produit => {
                        html += `
                            <div class="recu-row">
                                <span>${produit.nom} x${produit.quantite}</span>
                                <span>${(produit.prix * produit.quantite).toFixed(2)} TND</span>
                            </div>
                        `;
                    });
                }

                if (commandeDetails.total) {
                    html += `
                        <div class="recu-row" style="font-weight: bold; margin-top: 20px;">
                            <span>Total:</span>
                            <span>${commandeDetails.total.toFixed(2)} TND</span>
                        </div>
                    `;
                }

                contenu.innerHTML = html;
            } else {
                contenu.innerHTML = '<p>Aucun détail de commande disponible.</p>';
            }

            modal.style.display = 'block';
        }

        function fermerRecu() {
            document.getElementById('recu-modal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('recu-modal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>