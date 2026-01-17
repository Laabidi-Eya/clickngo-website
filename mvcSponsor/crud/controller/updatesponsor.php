<?php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);
error_log("updatesponsor.php started");

session_start();
error_log("Loading Config.php");
require_once(__DIR__ . "../../../../mvcEvent/Config.php");
error_log("Loading model.php");
require_once(__DIR__ . "/../model/model.php");
error_log("Loading controller.php");
require_once(__DIR__ . "/controller.php");
error_log("Loading PHPMailer.php");
require_once(__DIR__ . "/../PHPMailer/PHPMailer.php");
error_log("Loading Exception.php");
require_once(__DIR__ . "/../PHPMailer/Exception.php");
error_log("Loading SMTP.php");
require_once(__DIR__ . "/../PHPMailer/SMTP.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure the response is always JSON
header('Content-Type: application/json');

// Disable error display to prevent HTML output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Clear any previous output
ob_clean();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_sponsor = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $status = isset($_POST['status']) ? htmlspecialchars($_POST['status']) : null;

    if ($id_sponsor === null || $status === null) {
        echo json_encode([
            'success' => false,
            'message' => 'Paramètres manquants.'
        ]);
        exit;
    }

    error_log("Creating sponsorController");
    $controllerTmp = new sponsorController();
    error_log("Calling getSponsorById for id: $id_sponsor");
    $existingSponsor = $controllerTmp->getSponsorById($id_sponsor);

    if (!$existingSponsor) {
        echo json_encode([
            'success' => false,
            'message' => 'Sponsor non trouvé.'
        ]);
        exit;
    }

    // Use existing sponsor data for other fields
    $nom_entreprise = $existingSponsor['nom_entreprise'];
    $email = $existingSponsor['email'];
    $telephone = $existingSponsor['telephone'];
    $montant = $existingSponsor['montant'];
    $duree = $existingSponsor['duree'];
    $avantage = $existingSponsor['avantage'];
    $id_offre = $existingSponsor['id_offre'];
    $logoFilename = $existingSponsor['logo'];

    try {
        error_log("Creating sponsor object");
        $sponsor = new sponsor(
            $nom_entreprise,
            $email,
            $telephone,
            $montant,
            $duree,
            $avantage,
            $status,
            $id_offre,
            null, // $id_user
            $logoFilename, // $logo
            null // $payment_code
        );
        $sponsor->setId_sponsor($id_sponsor);

        error_log("Creating sponsorController for update");
        $controller = new sponsorController();

        $mailSent = false;
        $mailError = '';

        if (strtolower($status) === 'accepted') {
            // Generate a payment code
            $paymentCode = bin2hex(random_bytes(4));
            $sponsor->setPayment_code($paymentCode);

            // Generate payment link (match the path used in index.php)
            $paymentLink = "http://localhost/Projet%20Web/mvcSponsor/crud/view/front/index.php?payment=1&id=$id_sponsor&code=$paymentCode";

            // Send email using PHPMailer
            error_log("Preparing to send email to: $email");
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'yesminechourou04@gmail.com'; // Replace with your Gmail
                $mail->Password = 'juxinvxhfzvrgvbt'; // Replace with your App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Disable SSL verification (for testing only; remove in production)
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];

                $mail->setFrom('yesminechourou04@gmail.com', 'Support Technique');
                $mail->addAddress($email);
                $mail->Subject = 'Demande acceptée - Paiement requis';
                $mail->Body = "Bonjour,\n\nVotre demande de sponsoring pour l'entreprise $nom_entreprise a été acceptée.\nVeuillez effectuer le paiement en utilisant le lien suivant:\n$paymentLink\n\nVotre code de paiement est : $paymentCode\n\nMerci,\nClick'N'Go";

                $mail->send();
                $mailSent = true;
                error_log("Email sent successfully to: " . $email);
            } catch (Exception $e) {
                $mailError = $e->getMessage();
                error_log('Email sending failed: ' . $mailError);
            }

            // Update offre2 table (logic moved from back.php)
            error_log("Connecting to database");
            $pdo = config::getConnexion();
            error_log("Database connected");
            $pdo->beginTransaction();

            try {
                // Get current montant_offre and status from offre2
                $stmtOffre = $pdo->prepare("SELECT montant_offre, status FROM offre2 WHERE id_offre = ?");
                $stmtOffre->execute([$id_offre]);
                $offreData = $stmtOffre->fetch(PDO::FETCH_ASSOC);

                if ($offreData) {
                    $currentMontantOffre = (float)$offreData['montant_offre'];
                    $currentStatus = $offreData['status'];

                    // Calculate new montant_offre
                    $newMontantOffre = $currentMontantOffre - (float)$montant;
                    if ($newMontantOffre < 0) {
                        $newMontantOffre = 0;
                    }

                    // Determine new status
                    $newStatus = $currentStatus;
                    if ($newMontantOffre == 0) {
                        $newStatus = 'occupé';
                    }

                    // Update offre2 with new montant_offre and status
                    $stmtUpdateOffre = $pdo->prepare("UPDATE offre2 SET montant_offre = ?, status = ? WHERE id_offre = ?");
                    $stmtUpdateOffre->execute([$newMontantOffre, $newStatus, $id_offre]);
                }

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log('Error updating offre2: ' . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour de l\'offre: ' . $e->getMessage(),
                    'mailSent' => $mailSent,
                    'mailError' => $mailError
                ]);
                exit;
            }
        } else {
            $sponsor->setPayment_code(null);
        }

        // Update the sponsor
        error_log("Updating sponsor");
        $success = $controller->updateSponsor($sponsor);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Sponsor mis à jour avec succès!',
                'mailSent' => $mailSent,
                'mailError' => $mailError
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Échec de la mise à jour du sponsor',
                'mailSent' => $mailSent,
                'mailError' => $mailError
            ]);
        }
    } catch (Exception $e) {
        error_log('Error in updatesponsor.php: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}

exit();