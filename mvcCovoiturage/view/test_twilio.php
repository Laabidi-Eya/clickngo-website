<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure la bibliothèque Twilio
require_once '../vendor/autoload.php';
use Twilio\Rest\Client;

echo "<h1>Test d'envoi de SMS via Twilio</h1>";

// Identifiants Twilio
$twilioAccountSid = 'AC5cff2ba64b72d5a1299a7801c943bb17';
$twilioAuthToken = 'f3c914f863775b667fea26369e04b22a';
$twilioPhoneNumber = '+18559544315';

// Numéro de téléphone à tester (utilisez votre propre numéro)
$testPhoneNumber = '+21655436637';

echo "<form method='post'>";
echo "<p>Numéro de téléphone à tester: <input type='text' name='phone' value='{$testPhoneNumber}' /></p>";
echo "<p><input type='submit' value='Envoyer un SMS de test' /></p>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneToTest = isset($_POST['phone']) ? $_POST['phone'] : $testPhoneNumber;
    
    echo "<h2>Tentative d'envoi...</h2>";
    echo "<p>Envoi d'un SMS à {$phoneToTest}</p>";
    
    try {
        $twilio = new Client($twilioAccountSid, $twilioAuthToken);
        $message = $twilio->messages->create(
            $phoneToTest,
            [
                'from' => $twilioPhoneNumber,
                'body' => 'ClickNgo: Your ride request has been APPROVED. Thank you for using our service!'
            ]
        );
        
        echo "<p style='color: green; font-weight: bold;'>SMS envoyé avec succès!</p>";
        echo "<p>SID du message: {$message->sid}</p>";
        echo "<p>Statut: {$message->status}</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red; font-weight: bold;'>Erreur lors de l'envoi du SMS:</p>";
        echo "<pre style='background-color: #ffeeee; padding: 10px; border: 1px solid #ffaaaa;'>{$e->getMessage()}</pre>";
        
        echo "<h3>Solutions possibles:</h3>";
        echo "<ul>";
        echo "<li>Vérifiez que votre compte Twilio est actif</li>";
        echo "<li>Vérifiez que votre token d'authentification est correct et à jour</li>";
        echo "<li>Si vous utilisez un compte de test, vérifiez que le numéro de téléphone est vérifié dans votre compte Twilio</li>";
        echo "<li>Assurez-vous que le format du numéro est correct (format E.164: +21655436637)</li>";
        echo "</ul>";
    }
}
?>