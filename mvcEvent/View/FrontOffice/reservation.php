<?php
session_start();

// Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Messages de retour
if (isset($_SESSION['reservation_success'])) {
    $successMessage = $_SESSION['reservation_success'];
    unset($_SESSION['reservation_success']);
} elseif (isset($_SESSION['reservation_error'])) {
    $errorMessages[] = $_SESSION['reservation_error'];
    unset($_SESSION['reservation_error']);
}

require_once '../../Controller/EventController.php';
require_once '../../Controller/ChaiseController.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

// Validation de l'événement
$eventId = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
if (!$eventId) {
    header("Location: evenemant.php");
    exit;
}

$eventController = new EventController();
$chaiseController = new ChaiseController();

$event = $eventController->getEventById($eventId);
if (!$event) {
    header("Location: evenemant.php");
    exit;
}

// Configuration Stripe
\Stripe\Stripe::setApiKey('sk_test_51RKl7PRuD152msSdq4WLSb7t9HhOXxMBv4mCBzHQp18sp3Ilyk2XhVfkaQwLTV7TjSAc6Zo3dUZEZ1DYTmWkz0vF00kvIzlzpM');

// Initialisation des données
$event = array_merge([
    'totalSeats' => 0,
    'reservedSeats' => 0,
    'imageUrl' => 'images/default-event.jpg',
    'price' => 0,
    'longitude' => 10.1815,
    'latitude' => 36.8065,
    'place_name' => 'Tunis, Tunisia'
], $event);

$chaises = $chaiseController->getChaisesByEvent($eventId);
$errorMessages = [];
$successMessage = '';
$paymentStep = filter_input(INPUT_POST, 'payment_step', FILTER_VALIDATE_INT) ?? 1;
$selectedSeats = [];
$totalPrice = 0;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!isset($_POST['csrf_token'])) {
        die("Token CSRF manquant");
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMessages[] = "Erreur de sécurité. Veuillez actualiser la page et réessayer.";
        $paymentStep = 1;
    }
    elseif ($paymentStep == 1) {
        // Traitement sélection sièges
        $selectedSeatsJson = filter_input(INPUT_POST, 'seats');
        $selectedSeats = json_decode($selectedSeatsJson, true) ?? [];
        
        if (!empty($selectedSeats)) {
            $validSeats = true;
            foreach ($selectedSeats as $seatId) {
                if (!in_array($seatId, array_column($chaises, 'id'))) {
                    $validSeats = false;
                    break;
                }
            }
            
            if ($validSeats) {
                $_SESSION['selected_seats'] = $selectedSeats;
                $paymentStep = 2;
                $totalPrice = count($selectedSeats) * $event['price'];
            } else {
                $errorMessages[] = "Erreur : Sièges invalides sélectionnés.";
            }
        } else {
            $errorMessages[] = "Erreur : Aucune chaise sélectionnée.";
        }
    }
    elseif ($paymentStep == 2 && isset($_SESSION['selected_seats'])) {
        // Paiement Stripe
        $_SESSION['stripe_csrf_token'] = $_SESSION['csrf_token'];
        $selectedSeats = $_SESSION['selected_seats'];
        $totalPrice = count($selectedSeats) * $event['price'] * 100;
        
        try {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            
            $checkout_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Réservation pour ' . htmlspecialchars($event['name']),
                            'images' => [htmlspecialchars($event['imageUrl'])],
                        ],
                        'unit_amount' => $event['price'] * 100,
                    ],
                    'quantity' => count($selectedSeats),
                ]],
                'mode' => 'payment',
                'success_url' => $protocol . '://' . $host . '/projet%20Web/mvcEvent/View/FrontOffice/reservation-success.php?session_id={CHECKOUT_SESSION_ID}&event_id=' . $eventId,
                'cancel_url' => $protocol . '://' . $host . '/projet%20Web/mvcEvent/View/FrontOffice/reservation.php?event_id=' . $eventId,
                'metadata' => [
                    'event_id' => $eventId,
                    'seat_ids' => json_encode($selectedSeats),
                    'user_id' => $_SESSION['user_id'] ?? null
                ],
'customer_email' => $_SESSION['user']['email'],

            ]);
            
            header("HTTP/1.1 303 See Other");
            header("Location: " . $checkout_session->url);
            exit;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe Error: " . $e->getMessage());
            $errorMessages[] = "Erreur lors du traitement du paiement. Veuillez réessayer.";
        } catch (Exception $e) {
            error_log("General Error: " . $e->getMessage());
            $errorMessages[] = "Une erreur inattendue est survenue. Veuillez contacter le support.";
        }
    }
}

// Nouveau token CSRF pour la prochaine requête
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - <?= htmlspecialchars($event['name'] ?? 'Événement') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="reservation.css">
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css' rel='stylesheet' />
    <style>
        .success-message, .error-message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }
        .payment-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
            outline: none;
        }
        .error-field {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            display: none;
        }
        .loading-spinner {
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 5px solid var(--primary);
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .payment-summary {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .summary-total {
            font-weight: 600;
            font-size: 1.2rem;
            border-top: 1px solid #ddd;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }
        /* Style pour le bouton de retour */
.back-to-events {
    margin: 20px 0;
    text-align: center;
}

.btn-back {
    display: inline-block;
    padding: 10px 20px;
    background-color: #6c5ce7;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-back:hover {
    background-color: #5649c0;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-back i {
    margin-right: 8px;
}
/* Mapbox styles */
#map {
    height: 300px;
    width: 100%;
    border-radius: 8px;
    margin-top: 10px;
}
    </style>
</head>
<body>
    <section class="reservation-section">
        <div class="reservation-container">
            <h1 class="magic-text">
                <i class="fas fa-ticket-alt"></i> Réservez pour <?= htmlspecialchars($event['name'] ?? 'Événement') ?>
            </h1>
            <!-- Nouveau bouton de retour -->
<div class="back-to-events">
    <a href="evenemant.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Retour aux événements
    </a>
</div>
            
            <?php if ($successMessage): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?>
                    <p><a href="evenemant.php">Retour aux événements</a></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessages)): ?>
                <div class="error-message">
                    <?php foreach ($errorMessages as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="event-details">
                <div class="event-image">
                    <img src="<?= htmlspecialchars($event['imageUrl']) ?>" 
                         alt="<?= htmlspecialchars($event['name'] ?? 'Événement') ?>">
                </div>
                <div class="event-info">
                    <h2><?= htmlspecialchars($event['name'] ?? 'Événement') ?></h2>
                    <p><i class="fas fa-calendar-alt"></i> <strong>Date :</strong> <?= !empty($event['date']) ? date('d/m/Y', strtotime($event['date'])) : 'Non spécifiée' ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <strong>Lieu :</strong> <?= htmlspecialchars($event['place_name'] ?? 'Non spécifié') ?></p>
                    <p><i class="fas fa-money-bill-wave"></i> <strong>Prix :</strong> <?= htmlspecialchars($event['price'] ?? '0') ?> DT</p>
                    <p><i class="fas fa-chair"></i> <strong>Places disponibles :</strong> <?= ($event['totalSeats'] - $event['reservedSeats']) ?></p>
                    <div id="map"></div>
                </div>
            </div>

            <?php if ($paymentStep == 1): ?>
                <form method="POST" action="reservation.php?event_id=<?= $eventId ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="seat-selection">
                        <h2><i class="fas fa-chair"></i> Sélectionnez vos sièges</h2>
                        <div class="seat-grid-container">
                            <div class="stage">
                                <div class="stage-lights">
                                    <div class="light"></div>
                                    <div class="light"></div>
                                    <div class="light"></div>
                                </div>
                                <div class="stage-label">Scène</div>
                            </div>
                            <div class="seat-grid" id="seat-grid">
                                <?php if (!empty($chaises)): ?>
                                    <?php foreach ($chaises as $chaise): ?>
                                        <div class="seat <?= $chaise['statut'] === 'libre' ? 'available' : 'reserved' ?>"
                                             data-id="<?= $chaise['id'] ?>"
                                             data-status="<?= $chaise['statut'] ?>">
                                            <i class="fas fa-chair"></i>
                                            <span><?= $chaise['numero'] ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="no-seats">Aucun siège disponible pour cet événement.</p>
                                <?php endif; ?>
                            </div>
                            <div class="seat-legend">
                                <div class="legend-item">
                                    <span class="seat available"><i class="fas fa-chair"></i></span> Disponible
                                </div>
                                <div class="legend-item">
                                    <span class="seat reserved"><i class="fas fa-chair"></i></span> Réservé
                                </div>
                                <div class="legend-item">
                                    <span class="seat selected"><i class="fas fa-chair"></i></span> Sélectionné
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="seats" id="selected-seats">
                        <button type="submit" class="confirm-btn" id="confirm-btn" <?= empty($chaises) ? 'disabled' : '' ?>>
                            <i class="fas fa-arrow-right"></i> Passer au paiement
                        </button>
                    </div>
                </form>
                <?php elseif ($paymentStep == 2): ?>
    <div class="payment-form">
        <h2><i class="fas fa-credit-card"></i> Paiement sécurisé avec Stripe</h2>
        
        <div class="payment-summary">
            <h3>Récapitulatif de votre réservation</h3>
            <div class="summary-item">
                <span>Nombre de sièges:</span>
                <span><?= count($selectedSeats) ?></span>
            </div>
            <div class="summary-item">
                <span>Prix unitaire:</span>
                <span><?= $event['price'] ?> DT</span>
            </div>
            <div class="summary-item summary-total">
                <span>Total à payer:</span>
                <span><?= count($selectedSeats) * $event['price'] ?> DT</span>
            </div>
        </div>
        
        <p style="text-align: center; margin: 20px 0;">
            <i class="fas fa-lock" style="color: #6772e5;"></i> Paiement 100% sécurisé avec Stripe
        </p>
        
        <form method="POST" action="reservation.php?event_id=<?= $eventId ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="payment_step" value="2">
            
            <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <a href="reservation.php?event_id=<?= $eventId ?>" class="cancel-btn" style="flex: 1;">
                    <i class="fas fa-arrow-left"></i> Retour à la sélection
                </a>
                <button type="submit" class="confirm-btn" style="flex: 1; background-color: #6772e5;">
                    <i class="fab fa-stripe"></i> Payer avec Stripe
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>
        </div>
    </section>

    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <script src='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js'></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Mapbox
    mapboxgl.accessToken = 'pk.eyJ1IjoiaGFvYXVzMDEiLCJhIjoiY21hOHhqcGttMWJ5NjJtczg3eGJxazM0MiJ9.lm0YeqM7TkpDT4r6_Pf6aw';
    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v11',
        center: [<?php echo $event['longitude']; ?>, <?php echo $event['latitude']; ?>],
        zoom: 12
    });

    new mapboxgl.Marker()
        .setLngLat([<?php echo $event['longitude']; ?>, <?php echo $event['latitude']; ?>])
        .setPopup(new mapboxgl.Popup().setText('<?php echo htmlspecialchars($event['place_name']); ?>'))
        .addTo(map);

    // Gestion de la sélection des sièges
    if (document.getElementById('seat-grid')) {
        const seats = document.querySelectorAll('.seat.available');
        const selectedSeatsInput = document.getElementById('selected-seats');
        const confirmBtn = document.getElementById('confirm-btn');
        let selectedSeats = [];
        
        seats.forEach(seat => {
            seat.addEventListener('click', function() {
                const seatId = this.getAttribute('data-id');
                const index = selectedSeats.indexOf(seatId);
                
                if (index === -1) {
                    selectedSeats.push(seatId);
                    this.classList.add('selected');
                } else {
                    selectedSeats.splice(index, 1);
                    this.classList.remove('selected');
                }
                
                selectedSeatsInput.value = JSON.stringify(selectedSeats);
                confirmBtn.disabled = selectedSeats.length === 0;
                
                if (selectedSeats.length > 0) {
                    confirmBtn.innerHTML = `<i class="fas fa-arrow-right"></i> Passer au paiement (${selectedSeats.length})`;
                } else {
                    confirmBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Passer au paiement';
                }
            });
        });

        setInterval(() => {
            const availableSeats = document.querySelectorAll('.seat.available:not(.selected)');
            if (availableSeats.length > 0) {
                const randomSeat = availableSeats[Math.floor(Math.random() * availableSeats.length)];
                randomSeat.style.transform = 'translateY(-3px)';
                setTimeout(() => {
                    randomSeat.style.transform = '';
                }, 500);
            }
        }, 1000);
    }

    // Validation du formulaire de paiement
    if (document.getElementById('payment-form')) {
        const form = document.getElementById('payment-form');
        const loadingOverlay = document.getElementById('loading-overlay');

        // Fonctions de validation avec la même structure que validateName
        function validateCardNumber() {
            let cardNumber = document.getElementById('card_number').value.trim();
            let cardNumberError = document.getElementById('card_number_error');
            
            if (!/^\d{16}$/.test(cardNumber)) {
                cardNumberError.textContent = "Numéro de carte invalide (16 chiffres requis)";
                cardNumberError.classList.add("error");
                cardNumberError.classList.remove("valid");
                document.getElementById('card_number').style.borderColor = '#ff4757';
                return false;
            } else {
                cardNumberError.textContent = "✔ Valide";
                cardNumberError.classList.add("valid");
                cardNumberError.classList.remove("error");
                document.getElementById('card_number').style.borderColor = '#2ed573';
                return true;
            }
        }

        function validateCardExpiry() {
            let cardExpiry = document.getElementById('card_expiry').value.trim();
            let cardExpiryError = document.getElementById('card_expiry_error');
            
            if (!/^(0[1-9]|1[0-2])\/?([0-9]{2})$/.test(cardExpiry)) {
                cardExpiryError.textContent = "Format invalide (MM/AA requis)";
                cardExpiryError.classList.add("error");
                cardExpiryError.classList.remove("valid");
                document.getElementById('card_expiry').style.borderColor = '#ff4757';
                return false;
            } else {
                cardExpiryError.textContent = "✔ Valide";
                cardExpiryError.classList.add("valid");
                cardExpiryError.classList.remove("error");
                document.getElementById('card_expiry').style.borderColor = '#2ed573';
                return true;
            }
        }

        function validateCardCvv() {
            let cardCvv = document.getElementById('card_cvv').value.trim();
            let cardCvvError = document.getElementById('card_cvv_error');
            
            if (!/^\d{3,4}$/.test(cardCvv)) {
                cardCvvError.textContent = "CVV invalide (3 ou 4 chiffres requis)";
                cardCvvError.classList.add("error");
                cardCvvError.classList.remove("valid");
                document.getElementById('card_cvv').style.borderColor = '#ff4757';
                return false;
            } else {
                cardCvvError.textContent = "✔ Valide";
                cardCvvError.classList.add("valid");
                cardCvvError.classList.remove("error");
                document.getElementById('card_cvv').style.borderColor = '#2ed573';
                return true;
            }
        }

        // Écouteurs pour validation en temps réel
        document.getElementById('card_number').addEventListener('input', validateCardNumber);
        document.getElementById('card_expiry').addEventListener('input', validateCardExpiry);
        document.getElementById('card_cvv').addEventListener('input', validateCardCvv);

        // Validation à la soumission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;

            // Réinitialiser les messages
            document.querySelectorAll('.error-field').forEach(el => {
                el.textContent = '';
                el.classList.remove('valid', 'error');
            });

            // Valider tous les champs
            if (!validateCardNumber()) isValid = false;
            if (!validateCardExpiry()) isValid = false;
            if (!validateCardCvv()) isValid = false;

            // Si tout est valide, afficher l'overlay et soumettre
            if (isValid) {
                loadingOverlay.style.display = 'flex';
                this.submit();
            }
        });

        // Formatage automatique des champs
        document.getElementById('card_number').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
            validateCardNumber();
        });

        document.getElementById('card_expiry').addEventListener('input', function(e) {
            this.value = this.value.replace(/^(\d{2})(\d)/, '$1/$2')
                                 .replace(/[^\d\/]/g, '')
                                 .substring(0, 5);
            validateCardExpiry();
        });

        document.getElementById('card_cvv').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
            validateCardCvv();
        });
    }
    const cancelBtn = document.querySelector('.cancel-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            // Optionnel: Confirmation avant de retourner
            if (!confirm('Voulez-vous vraiment annuler le paiement et retourner à la sélection des sièges ?')) {
                e.preventDefault();
            }
        });
    }
});
</script>
</body>
</html>