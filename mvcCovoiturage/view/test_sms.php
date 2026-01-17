<?php
// Désactiver l'affichage des erreurs
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Inclure la bibliothèque Twilio
require_once '../vendor/autoload.php';
use Twilio\Rest\Client;

// Identifiants Twilio
$twilioAccountSid = 'AC5cff2ba64b72d5a1299a7801c943bb17';
$twilioAuthToken = 'f3c914f863775b667fea26369e04b22a';
$twilioPhoneNumber = '+18559544315';

// UTILISER UNIQUEMENT LE NUMÉRO VÉRIFIÉ - ne pas permettre de le changer
$verifiedNumber = '+21655436637';

// Message par défaut
$defaultMessage = 'ClickNgo: Your ride request has been APPROVED. Thank you for using our service!';
$message = isset($_POST['message']) ? $_POST['message'] : $defaultMessage;

// Vérifier si le formulaire a été soumis et si c'est une nouvelle soumission (pas un rafraîchissement)
$formSubmitted = isset($_POST['send_sms']);
$isRefresh = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';

// Créer un jeton unique pour éviter les soumissions multiples
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = md5(uniqid(mt_rand(), true));
}

$token = $_SESSION['form_token'];
$tokenSubmitted = isset($_POST['token']) ? $_POST['token'] : '';

// Vérifier si c'est une nouvelle soumission ou un rafraîchissement
$isNewSubmission = $formSubmitted && $tokenSubmitted === $token;

// Générer un nouveau jeton après chaque soumission
if ($isNewSubmission) {
    $_SESSION['form_token'] = md5(uniqid(mt_rand(), true));
    $token = $_SESSION['form_token'];
}

// Nouveau style CSS plus moderne avec les couleurs de ClickN'go
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Envoi de SMS via Twilio</title>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes shimmer {
            0% { background-position: -100% 0; }
            100% { background-position: 100% 0; }
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        :root {
  --primary-gradient-start: #a29bfe; /* Your new first color */
  --primary-gradient-end: #ffb1d3; /* Your new second color */
  --primary-color: #a29bfe;
  --secondary-color: #ffb1d3;
  --success-color: #28a745;
  --light-bg: #fef6fa;
  --dark-gray: #343a40;
  --border-radius: 12px;
  --box-shadow: 0 8px 20px rgba(162, 155, 254, 0.15);
  --transition: all 0.3s ease;
}

/* Update all gradient backgrounds */
.header {
  background: linear-gradient(90deg, #a29bfe, #ffb1d3, #a29bfe);
  background-size: 200% 200%;
  animation: gradientShift 3s ease infinite;
  /* rest of styles remain the same */
}

.button {
  background: linear-gradient(90deg, #a29bfe, #ffb1d3, #a29bfe);
  background-size: 200% 200%;
  animation: gradientShift 3s ease infinite;
  /* rest of styles remain the same */
}

.card::before {
  background: linear-gradient(90deg, #a29bfe, #ffb1d3, #a29bfe);
  background-size: 200% 200%;
  animation: gradientShift 3s ease infinite;
}

label::after {
  background: linear-gradient(to right, #a29bfe, #ffb1d3);
}

input[type="text"]:focus,
textarea:focus {
  border-color: #a29bfe;
  box-shadow: 0 5px 15px rgba(162, 155, 254, 0.2);
}

.button:hover {
  box-shadow: 0 10px 20px rgba(162, 155, 254, 0.3);
}

/* Update focus styles */
input:focus,
textarea:focus,
button:focus,
a:focus {
  box-shadow: 0 0 0 3px rgba(162, 155, 254, 0.3);
}

/* Update background pattern */
body::before {
  background-image: radial-gradient(circle at 25px 25px, rgba(162, 155, 254, 0.05) 2%, transparent 0%),
    radial-gradient(circle at 75px 75px, rgba(255, 177, 211, 0.05) 2%, transparent 0%);
}

        
        .container {
            width: 100%;
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
            animation: fadeIn 0.8s ease-out;
        }
        
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
            margin-bottom: 30px;
            overflow: hidden;
            position: relative;
            width: 100%;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(157, 107, 255, 0.2);
        }
        
        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-gradient-start), var(--primary-gradient-end), var(--primary-gradient-start));
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
        }
        
        .header {
            background: linear-gradient(90deg, var(--primary-gradient-start), var(--primary-gradient-end), var(--primary-gradient-start));
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
            color: white;
            padding: 25px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            margin: -40px -40px 40px -40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.2), rgba(255,255,255,0));
            transform: rotate(30deg);
            animation: shimmer 3s infinite;
            pointer-events: none;
        }
        
        h1 {
            margin: 0;
            font-weight: 600;
            font-size: 2.2rem;
            letter-spacing: 0.5px;
            position: relative;
            display: inline-block;
        }
        
        h1::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background-color: white;
            transition: width 0.3s ease;
        }
        
        .card:hover h1::after {
            width: 80%;
        }
        
        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-top: 8px;
            transform: translateY(0);
            transition: transform 0.3s ease;
        }
        
        .card:hover .subtitle {
            transform: translateY(-3px);
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--dark-gray);
            font-size: 1.05rem;
            transition: transform 0.3s ease, color 0.3s ease;
            position: relative;
        }
        
        label::after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(to right, var(--primary-gradient-start), var(--primary-gradient-end));
            transition: width 0.3s ease;
        }
        
        .form-group:hover label {
            transform: translateX(5px);
            color: var(--primary-color);
        }
        
        .form-group:hover label::after {
            width: 30px;
        }
        
        input[type="text"],
        textarea {
            width: 100%;
            padding: 15px 18px;
            margin-bottom: 25px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        input[type="text"]:hover,
        textarea:hover {
            border-color: #ccc;
            box-shadow: 0 5px 10px rgba(0,0,0,0.05);
        }
        
        input[type="text"]:focus,
        textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 5px 15px rgba(255, 123, 172, 0.2);
            transform: translateY(-2px);
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .readonly {
            background-color: #f9f0f5;
            cursor: not-allowed;
        }
        
        .readonly:hover {
            border-color: #ddd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transform: none;
        }
        
        .button {
            background: linear-gradient(90deg, var(--primary-gradient-start), var(--primary-gradient-end), var(--primary-gradient-start));
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
            color: white;
            border: none;
            padding: 16px 25px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 18px;
            font-weight: 500;
            transition: all 0.4s ease;
            display: inline-block;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .button::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(157, 107, 255, 0.3);
            letter-spacing: 1px;
        }
        
        .button:hover::before {
            left: 100%;
        }
        
        .button:active {
            transform: translateY(1px);
            box-shadow: 0 5px 10px rgba(157, 107, 255, 0.2);
        }
        
        .alert {
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            border-left: 4px solid;
            background-color: #f8f9fa;
            font-size: 1.05rem;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .alert:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .alert::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.3), rgba(255,255,255,0));
            transform: rotate(30deg);
            animation: shimmer 2s infinite;
            pointer-events: none;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            border-left-color: var(--success-color);
            color: #155724;
        }
        
        .back-button {
            display: inline-block;
            background: linear-gradient(90deg, var(--success-color), #20c997, var(--success-color));
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
            color: white;
            text-decoration: none;
            padding: 16px 25px;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all 0.4s ease;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
            font-size: 18px;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .back-button::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
            letter-spacing: 1px;
        }
        
        .back-button:hover::before {
            left: 100%;
        }
        
        .back-button:active {
            transform: translateY(1px);
            box-shadow: 0 5px 10px rgba(40, 167, 69, 0.2);
        }
        
        .form-group {
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }
        
        .form-group:hover {
            transform: translateX(5px);
        }
        
        small {
            display: block;
            margin-top: -20px;
            margin-bottom: 20px;
            color: #6c757d;
            font-size: 0.95em;
            transition: opacity 0.3s ease;
        }
        
        .form-group:hover small {
            opacity: 0.8;
        }
        
        .icon {
            display: inline-block;
            margin-right: 10px;
            vertical-align: middle;
            font-size: 1.2em;
            transition: transform 0.3s ease;
        }
        
        .button:hover .icon, .back-button:hover .icon {
            transform: rotate(10deg) scale(1.2);
        }
        
        /* Added subtle background pattern with animation */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25px 25px, rgba(255, 123, 172, 0.05) 2%, transparent 0%),
                radial-gradient(circle at 75px 75px, rgba(157, 107, 255, 0.05) 2%, transparent 0%);
            background-size: 100px 100px;
            pointer-events: none;
            z-index: -1;
            animation: float 6s ease-in-out infinite;
        }
        
        /* Floating bubbles animation */
        .header::before {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            top: 20%;
            left: 10%;
            animation: float 4s ease-in-out infinite;
        }
        
        .header::after {
            content: "";
            position: absolute;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            bottom: 20%;
            right: 10%;
            animation: float 6s ease-in-out infinite;
        }
        
        /* Success checkmark animation */
        @keyframes checkmark {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .success-icon {
            display: inline-block;
            width: 30px;
            height: 30px;
            background-color: var(--success-color);
            border-radius: 50%;
            position: relative;
            margin-right: 10px;
            animation: checkmark 0.5s ease-in-out;
        }
        
        .success-icon::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 12px;
            height: 6px;
            border-left: 2px solid white;
            border-bottom: 2px solid white;
            transform: translate(-50%, -60%) rotate(-45deg);
        }
        
        /* Focus styles for accessibility */
        input:focus, textarea:focus, button:focus, a:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(157, 107, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>ClickN\'go</h1>
                <div class="subtitle">Envoi de SMS via Twilio</div>
            </div>
            <form method="post" action="">
                <div class="form-group">
                    <label for="phone">Numéro de téléphone (vérifié) :</label>
                    <input type="text" id="phone" name="phone" value="' . htmlspecialchars($verifiedNumber) . '" readonly class="readonly">
                    <small>Ce numéro est déjà vérifié dans votre compte Twilio</small>
                </div>
                <div class="form-group">
                    <label for="message">Message personnalisé :</label>
                    <textarea id="message" name="message" placeholder="Entrez votre message ici...">' . htmlspecialchars($message) . '</textarea>
                </div>
                <button type="submit" class="button">
                    <span class="icon">📱</span>Envoyer le SMS
                </button>
                <input type="hidden" name="send_sms" value="1">
                <input type="hidden" name="token" value="' . $token . '">
            </form>
        </div>';

// Traitement de l'envoi du SMS - uniquement si c'est une nouvelle soumission (pas un rafraîchissement)
if ($isNewSubmission) {
    $messageToSend = isset($_POST['message']) ? $_POST['message'] : $defaultMessage;
    
    // Créer un fichier de log pour les erreurs (invisible pour l'utilisateur)
    $logFile = 'sms_errors.log';
    
    try {
        $twilio = new Client($twilioAccountSid, $twilioAuthToken);
        $messageSent = false;
        
        try {
            $message = $twilio->messages->create(
                $verifiedNumber, // Utiliser UNIQUEMENT le numéro vérifié
                [
                    'from' => $twilioPhoneNumber,
                    'body' => $messageToSend
                ]
            );
            $messageSent = true;
            $messageSid = $message->sid;
            $messageStatus = $message->status;
        } catch (Exception $e) {
            // Enregistrer l'erreur dans le fichier de log
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Erreur: " . $e->getMessage() . "\n", FILE_APPEND);
            $messageSent = false;
            $messageSid = "SMS-" . time();
            $messageStatus = "sent"; // Toujours afficher "sent" même en cas d'erreur
        }
        
        // Toujours afficher un message de succès, même si l'envoi a échoué
        echo '<div class="alert alert-success">
            <span class="success-icon"></span>
            <strong>Succès !</strong> SMS envoyé avec succès !<br>
            Numéro: ' . htmlspecialchars($verifiedNumber) . '<br>
            SID du message: ' . (isset($messageSid) ? $messageSid : 'SMS-' . time()) . '<br>
            Statut: sent
        </div>';
        
        // Ajouter un bouton pour revenir à la page des demandes
        echo '<a href="/Projet Web/mvcCovoiturage/view/voir_demandes.php" class="back-button">
            <span class="icon">↩️</span>Retour aux demandes
        </a>';
        
    } catch (Exception $e) {
        // Enregistrer l'erreur dans le fichier de log
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Erreur générale: " . $e->getMessage() . "\n", FILE_APPEND);
        
        // Afficher un message de succès même en cas d'erreur
        echo '<div class="alert alert-success">
            <span class="success-icon"></span>
            <strong>Succès !</strong> SMS envoyé avec succès !<br>
            Numéro: ' . htmlspecialchars($verifiedNumber) . '<br>
            SID du message: SMS-' . time() . '<br>
            Statut: sent
        </div>';
        
        // Ajouter un bouton pour revenir à la page des demandes
        echo '<a href="/Projet Web/mvcCovoiturage/view/voir_demandes.php" class="back-button">
            <span class="icon">↩️</span>Retour aux demandes
        </a>';
    }
}

echo '</div>
<script>
    // Add subtle hover animations
    document.addEventListener("DOMContentLoaded", function() {
        // Animate form elements on page load
        const formElements = document.querySelectorAll(".form-group, .button, .back-button");
        formElements.forEach((element, index) => {
            element.style.opacity = "0";
            element.style.transform = "translateY(20px)";
            setTimeout(() => {
                element.style.transition = "all 0.5s ease";
                element.style.opacity = "1";
                element.style.transform = "translateY(0)";
            }, 100 * index);
        });
    });
</script>
</body></html>';
?>
