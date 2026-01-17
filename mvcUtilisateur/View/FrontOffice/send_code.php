<?php
session_start();
require_once '../../includes/phpmailer/src/PHPMailer.php';
require_once '../../includes/phpmailer/src/SMTP.php';
require_once '../../includes/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Vérifie si l'email existe dans la session (connecté ou invité)
$email = $_SESSION['user']['email'] ?? $_SESSION['reset_email'] ?? null;

if (!$email) {
    echo "Email manquant.";
    exit;
}

// ✅ Génère et enregistre le code en session
$code = rand(100000, 999999);
$_SESSION['verification_code'] = $code;

$mail = new PHPMailer(true);

try {
    // ✅ Configuration SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'karimmlayah14@gmail.com'; // Remplace par ton email
    $mail->Password = 'hmyi pvmk tsnp dhcd';      // Mot de passe d'application Gmail
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // ✅ Expéditeur & destinataire
    $mail->setFrom('karimmlayah14@gmail.com', 'Support');
    $mail->addAddress($email);

    // ✅ Contenu du mail
    $mail->isHTML(true);
    $mail->Subject = 'Code de réinitialisation';
    $mail->Body = "<p>Bonjour,<br>Voici votre code de vérification : <strong>$code</strong></p>";

    // ✅ Envoi
    $mail->send();
    echo "ok";
} catch (Exception $e) {
    echo "Erreur : " . $mail->ErrorInfo;
}
