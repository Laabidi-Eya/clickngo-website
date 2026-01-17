<?php
require_once 'vendor/autoload.php'; // Inclure l'autoloader de Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Config {
    private static $pdo = null;
    private static $stripe = null;
    private static $mailer = null;

    // Configuration de la base de données
    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=click\'n\'go;',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die('Erreur de connexion : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    // Configuration de Stripe
    public static function getStripe() {
        if (!isset(self::$stripe)) {
            try {
                \Stripe\Stripe::setApiKey('sk_test_51RJL0sQabomDz0BaC9s0L184katdXI601GgaBipHtVd6KF39QhKgTN1sMfUNN0OwPgPE2kN5h1WHR3Y1Ke7Ml61U00xkJSFImX');
                self::$stripe = new \Stripe\StripeClient('sk_test_51RJL0sQabomDz0BaC9s0L184katdXI601GgaBipHtVd6KF39QhKgTN1sMfUNN0OwPgPE2kN5h1WHR3Y1Ke7Ml61U00xkJSFImX');
            } catch (Exception $e) {
                die('Erreur de configuration Stripe : ' . $e->getMessage());
            }
        }
        return self::$stripe;
    }

    // Clé publique Stripe
    public static function getStripePublicKey() {
        return 'pk_test_51RJL0sQabomDz0Baxnej9s98KKxxRyqRzsJUCLkHaveIB3FGVPF2rhdi8jLPCydMO4lF95QJotwMvL0QjOXjAud200GjafxVKG';
    }

    // Configuration de PHPMailer
    public static function getMailer() {
        if (!isset(self::$mailer)) {
            try {
                self::$mailer = new PHPMailer(true); // Activer les exceptions
                
                // Activer le débogage SMTP
                self::$mailer->SMTPDebug = 2; // Niveau de débogage : 2 = client et serveur
                self::$mailer->Debugoutput = function($str, $level) {
                    error_log("PHPMailer Debug: $str");
                };

                // Configuration du serveur SMTP
                self::$mailer->isSMTP();
                self::$mailer->Host = 'smtp.gmail.com';
                self::$mailer->SMTPAuth = true;
                self::$mailer->Username = 'laabidieya6@gmail.com';
                self::$mailer->Password = 'pmda jocs mfeu fipz';
                self::$mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                self::$mailer->Port = 587;

                // Configuration de l'expéditeur
                self::$mailer->setFrom('laabidieya6@gmail.com', 'Click\'N\'Go');
                self::$mailer->CharSet = 'UTF-8';
                
                // Désactiver la vérification SSL (à utiliser uniquement en développement)
                self::$mailer->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            } catch (Exception $e) {
                error_log('Erreur de configuration PHPMailer : ' . $e->getMessage());
                throw $e;
            }
        }
        return self::$mailer;
    }
}
?>