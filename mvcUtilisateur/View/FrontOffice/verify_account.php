<?php
session_start();
require_once '../../includes/phpmailer/src/PHPMailer.php';
require_once '../../includes/phpmailer/src/SMTP.php';
require_once '../../includes/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email = $_SESSION['user']['email'] ?? null;
if (!$email) {
    header("Location: login.php");
    exit;
}

$message = '';
$popupType = 'info';

// Initialisation des variables de session
if (!isset($_SESSION['send_count'])) $_SESSION['send_count'] = 0;
if (!isset($_SESSION['send_reset_time'])) $_SESSION['send_reset_time'] = time() + 300; // 5 min
if (!isset($_SESSION['last_send_time'])) $_SESSION['last_send_time'] = 0;

// Réinitialiser compteur après 5 min
if (time() >= $_SESSION['send_reset_time']) {
    $_SESSION['send_count'] = 0;
    $_SESSION['send_reset_time'] = time() + 300;
}

// Si l'utilisateur demande un envoi
if (isset($_GET['send']) && $_GET['send'] === '1') {
    if ($_SESSION['send_count'] >= 3) {
        $popupType = 'error';
        $wait = $_SESSION['send_reset_time'] - time();
        $message = "🚫 Trop d'envois. Réessayez dans $wait secondes.";
    } else {
        $code = random_int(100000, 999999);
        $_SESSION['verification_code'] = $code;
        $_SESSION['verification_attempts'] = 0;

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'karimmlayah14@gmail.com';
            $mail->Password = 'hmyi pvmk tsnp dhcd'; // Mot de passe d'application Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('karimmlayah14@gmail.com', 'Click\'N\'Go');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Votre code de vérification';
            $mail->Body = '
                <div style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; border-radius: 8px; max-width: 400px; margin: auto;">
                    <h2 style="color: #6c63ff; text-align: center;">🔐 Vérification du compte</h2>
                    <p style="font-size: 15px; color: #333;">Voici votre code de vérification :</p>
                    <div style="font-size: 28px; font-weight: bold; text-align: center; margin: 20px 0;">' . $code . '</div>
                    <p style="font-size: 13px; color: #777;">Ce code est personnel. Ne le partagez pas.</p>
                </div>
            ';

            $mail->send();
            $_SESSION['send_count']++;
            $_SESSION['last_send_time'] = time();
            $popupType = 'success';
            $message = "Un code a été envoyé à votre adresse e-mail.";
        } catch (Exception $e) {
            $popupType = 'error';
            $message = "Erreur d'envoi : " . $mail->ErrorInfo;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification du compte</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            padding: 40px;
            text-align: center;
        }
        h2 {
            color: #333;
        }
        input[type="text"] {
            padding: 10px;
            font-size: 16px;
            width: 200px;
            margin-top: 10px;
        }
        form {
            margin-top: 30px;
        }
        button {
            padding: 10px 18px;
            font-size: 15px;
            background-color: #6c63ff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:disabled {
            background-color: gray;
            cursor: not-allowed;
        }
        a {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
            color: #6c63ff;
        }
        #timerText {
            color: gray;
            margin-left: 10px;
        }
    </style>
</head>
<body>

<h2>Vérification de votre compte</h2>
<p>Appuyez sur le bouton pour recevoir un code :</p>

<form method="get" action="verify_account.php" style="margin-bottom: 20px;">
    <input type="hidden" name="send" value="1">
    <button id="sendBtn" type="submit">
        <?= ($_SESSION['send_count'] > 0) ? '🔁 Renvoyer le code' : '📨 Envoyer le code' ?>
    </button>
    <span id="timerText"></span>
</form>

<form method="post" action="verify_code.php">
    <p>Entrez le code reçu :</p>
    <input type="text" name="code" maxlength="6" required placeholder="Ex: 123456">
    <br><br>
    <button type="submit">✅ Vérifier</button>
</form>

<a href="profile.php">⬅️ Retour au profil</a>

<?php if (!empty($message)): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: <?= json_encode($popupType) ?>,
            title: <?= json_encode($popupType === 'success' ? 'Code envoyé' : 'Erreur') ?>,
            text: <?= json_encode($message) ?>,
            confirmButtonColor: '#6c63ff'
        });
    });
</script>
<?php endif; ?>

<script>
    const sendBtn = document.getElementById('sendBtn');
    const timerText = document.getElementById('timerText');

    <?php if (isset($_SESSION['last_send_time']) && time() - $_SESSION['last_send_time'] < 60): ?>
        let timeLeft = <?= 60 - (time() - $_SESSION['last_send_time']) ?>;
        sendBtn.disabled = true;

        const countdown = setInterval(() => {
            if (timeLeft > 0) {
                timerText.innerText = `Renvoyer dans ${timeLeft}s`;
                timeLeft--;
            } else {
                clearInterval(countdown);
                sendBtn.disabled = false;
                timerText.innerText = '';
            }
        }, 1000);
    <?php endif; ?>
</script>

</body>
</html>
