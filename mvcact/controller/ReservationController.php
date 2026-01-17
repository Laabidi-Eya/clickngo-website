<?php
require_once __DIR__ . '/../model/ReservationModel.php';
require_once __DIR__ . '/../model/ActivityModel.php';
require_once __DIR__ . '/../model/EnterpriseModel.php';

class ReservationController {
    private $reservationModel;
    private $activityModel;
    private $enterpriseModel;

    public function __construct() {
        $this->reservationModel = new ReservationModel();
        $this->activityModel = new ActivityModel();
        $this->enterpriseModel = new EnterpriseModel();
    }

    /**
     * Affiche le formulaire de réservation pour une activité spécifique
     */
    public function showReservationForm($activity_id) {
        // Récupérer les informations de l'activité depuis la table Activities
        $activity = $this->activityModel->getActivityById($activity_id);
        
        // Si l'activité n'est pas trouvée, essayer dans la table Enterprise Activities
        if (!$activity) {
            $activity = $this->enterpriseModel->getEnterpriseActivityById($activity_id);
            
            // Si l'activité est toujours introuvable
            if (!$activity) {
                return [
                    'success' => false,
                    'message' => 'Activité non trouvée'
                ];
            }
        }
        
        return [
            'success' => true,
            'activity' => $activity
        ];
    }

    /**
     * Traite la soumission du formulaire de réservation
     */
    public function processReservation($data) {
        // Validation des données
        if (empty($data['activity_id']) || 
            empty($data['customer_name']) || 
            empty($data['customer_email']) || 
            empty($data['reservation_date']) || 
            empty($data['reservation_time']) || 
            empty($data['people_count']) || 
            empty($data['total_price'])) {
            
            return [
                'success' => false,
                'message' => 'Tous les champs sont obligatoires'
            ];
        }
        
        try {
            // Vérifier que l'activité existe (d'abord dans la table standard, puis dans entreprise)
            $activity = $this->activityModel->getActivityById($data['activity_id']);
            $isEnterpriseActivity = false;
            
            // Si l'activité n'est pas trouvée dans la table activities, vérifier dans enterprise_activities
            if (!$activity) {
                $activity = $this->enterpriseModel->getEnterpriseActivityById($data['activity_id']);
                $isEnterpriseActivity = true;
                
                if (!$activity) {
                    return [
                        'success' => false,
                        'message' => 'Activité non trouvée'
                    ];
                }
            }
            
            // Vérifier la disponibilité
            $availability = $this->reservationModel->checkAvailability(
                $data['activity_id'],
                $data['reservation_date'],
                $data['reservation_time']
            );
            
            if (!$availability['available'] || $availability['remaining'] < $data['people_count']) {
                return [
                    'success' => false,
                    'message' => 'Désolé, cette activité n\'est plus disponible pour le créneau sélectionné ou le nombre de places est insuffisant'
                ];
            }
            
            // Ajouter la réservation
            $reservation_id = $this->reservationModel->addReservation(
                $data['activity_id'],
                $activity['name'],
                $data['customer_name'],
                $data['customer_email'],
                $data['reservation_date'],
                $data['reservation_time'],
                $data['people_count'],
                $data['total_price'],
                'pending' // Statut initial
            );
            
            if (!$reservation_id) {
                return [
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de l\'enregistrement de votre réservation'
                ];
            }

            // Récupérer la réservation complète pour l'email
            $reservation = $this->reservationModel->getReservationById($reservation_id);
            // Envoi de l'email de confirmation
            if ($reservation && !empty($reservation['customer_email'])) {
                require_once __DIR__ . '/../view/front office/PHPMailerAutoload.php';
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $smtp = require __DIR__ . '/../view/front office/PHPMailer_config.php';
                try {
                    $mail->isSMTP();
                    $mail->Host = $smtp['host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtp['username'];
                    $mail->Password = $smtp['password'];
                    $mail->SMTPSecure = $smtp['secure'];
                    $mail->Port = $smtp['port'];
                    $mail->CharSet = 'UTF-8';
                    $mail->setFrom($smtp['from_email'], $smtp['from_name']);
                    $mail->addAddress($reservation['customer_email'], $reservation['customer_name']);
                    $mail->isHTML(true);
                    $mail->Subject = 'Confirmation de votre réservation ClickNGo';
                    // Génération du QR code avec QR Code Monkey (plus compatible avec les clients mail)
                    $qrData = 'Reservation ID: ' . $reservation['id'];
                    // Url principale
                    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qrData) . '&format=png&margin=10&qzone=4&color=e70a83';
                    // Url alternative (secours)
                    $qrAltUrl = 'https://quickchart.io/qr?text=' . urlencode($qrData) . '&dark=e70a83&size=250&margin=4&ecLevel=H';

                    $mail->Priority = 1; // Priorité haute
                    $mail->AddCustomHeader("List-Unsubscribe: <mailto:" . $smtp['from_email'] . ">");
                    $mail->Body = '
                    <!DOCTYPE html>
                    <html>
                    <head>
                      <meta charset="UTF-8">
                      <meta name="viewport" content="width=device-width, initial-scale=1.0">
                      <meta name="color-scheme" content="light">
                      <meta name="supported-color-schemes" content="light">
                      <title>Confirmation de réservation</title>
                      <style type="text/css">
                        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");
                        body { font-family: "Poppins", Arial, sans-serif; margin: 0; padding: 0; background: #f8f8f8; color: #333; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
                        .header { background: linear-gradient(135deg, #e70a83 0%, #ff5e5e 100%); padding: 30px 20px; text-align: center; }
                        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 600; }
                        .content { padding: 30px; }
                        .content h2 { color: #e70a83; margin-top: 0; }
                        .info-list { background-color: #f9f9f9; border-radius: 12px; padding: 20px; margin: 20px 0; }
                        .info-list li { margin-bottom: 10px; }
                        .qr-container { text-align: center; margin: 30px 0; }
                        .qr-code { width: 250px; height: 250px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin: 0 auto; display: block; }
                        .qr-note { margin-top: 15px; font-size: 14px; color: #777; }
                        .button { display: inline-block; background: linear-gradient(135deg, #e70a83 0%, #ff5e5e 100%); color: white; text-decoration: none; padding: 12px 25px; border-radius: 50px; font-weight: 500; margin-top: 10px; }
                        .footer { background: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eee; }
                        .footer p { margin: 0; color: #777; font-size: 14px; }
                      </style>
                    </head>
                    <body>
                      <div class="container">
                        <div class="header">
                          <h1>Confirmation de réservation</h1>
                        </div>
                        <div class="content">
                          <h2>Merci pour votre réservation !</h2>
                          <p>Bonjour <strong>' . htmlspecialchars($reservation['customer_name']) . '</strong>,</p>
                          <p>Votre réservation a bien été enregistrée et confirmée.</p>
                          
                          <div class="info-list">
                            <ul>
                              <li><strong>Numéro de réservation :</strong> ' . $reservation['id'] . '</li>
                              <li><strong>Activité :</strong> ' . htmlspecialchars($reservation['activity_name']) . '</li>
                              <li><strong>Date :</strong> ' . $reservation['reservation_date'] . ' à ' . $reservation['reservation_time'] . '</li>
                              <li><strong>Nombre de personnes :</strong> ' . $reservation['people_count'] . '</li>
                              <li><strong>Total payé :</strong> ' . $reservation['total_price'] . ' DT</li>
                              <li><strong>Statut du paiement :</strong> ' . ucfirst($reservation['payment_status']) . '</li>
                            </ul>
                          </div>
                          
                          <div class="qr-container">
                            <p><strong>Voici votre QR code unique à présenter lors de l\'événement :</strong></p>
                            <!-- Double QR code (méthode principale + secours pour maximiser l\'affichage) -->
                            <img src="' . $qrUrl . '" class="qr-code" alt="QR Code de réservation">
                            
                            <p class="qr-note">Si le QR code n\'apparaît pas correctement, veuillez <a href="' . $qrAltUrl . '" style="color:#e70a83;" target="_blank">cliquer ici</a> pour l\'afficher.</p>
                            
                            <a href="' . $qrAltUrl . '" class="button" target="_blank">Voir mon QR code</a>
                          </div>
                          
                          <p>Nous avons hâte de vous accueillir !</p>
                          <p><strong>L\'équipe ClickNGo</strong></p>
                        </div>
                        
                        <div class="footer">
                          <p>© ' . date('Y') . ' ClickNGo - Tous droits réservés</p>
                          <p>Pour toute question, veuillez nous contacter à <a href="mailto:' . $smtp['from_email'] . '" style="color:#e70a83;">contact@clickngo.tn</a></p>
                        </div>
                      </div>
                    </body>
                    </html>';
                    $mail->AltBody = 'Merci pour votre réservation chez ClickNGo.';
                    // Désactive la vérification SSL pour les tests locaux
                    $mail->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ];
                    $mail->send();
                } catch (Exception $e) {
                    error_log('Erreur envoi mail PHPMailer : ' . $mail->ErrorInfo);
                    die('Erreur PHPMailer : ' . $mail->ErrorInfo);
                }
            }
            // Réservation créée avec succès
            return [
                'success' => true,
                'message' => 'Votre réservation a été enregistrée avec succès',
                'reservation_id' => $reservation_id
            ];
        } catch (Exception $e) {
            error_log("Erreur dans le processus de réservation: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur système est survenue: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Confirme le paiement d'une réservation
     */
    public function confirmPayment($reservation_id) {
        // Vérifier que la réservation existe
        $reservation = $this->reservationModel->getReservationById($reservation_id);
        
        if (!$reservation) {
            return [
                'success' => false,
                'message' => 'Réservation non trouvée'
            ];
        }
        
        // Mettre à jour le statut de paiement
        $result = $this->reservationModel->updatePaymentStatus($reservation_id, 'confirmed');
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la confirmation du paiement'
            ];
        }
        
        // Paiement confirmé avec succès
        return [
            'success' => true,
            'message' => 'Paiement confirmé avec succès'
        ];
    }

    /**
     * Récupère les créneaux horaires disponibles pour une activité à une date donnée
     */
    public function getAvailableTimeSlots($activity_id, $date) {
        return $this->reservationModel->getAvailableTimeSlots($activity_id, $date);
    }

    /**
     * Récupère toutes les réservations (pour l'administration)
     */
    public function getAllReservations() {
        return $this->reservationModel->getAllReservations();
    }

    /**
     * Annule une réservation
     */
    public function cancelReservation($reservation_id) {
        $result = $this->reservationModel->cancelReservation($reservation_id);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'annulation de la réservation'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Réservation annulée avec succès'
        ];
    }

    /**
     * Récupère les réservations pour un mois et une année spécifiques
     */
    public function getReservationsByMonth($month, $year) {
        return $this->reservationModel->getReservationsByMonth($month, $year);
    }
}
?> 