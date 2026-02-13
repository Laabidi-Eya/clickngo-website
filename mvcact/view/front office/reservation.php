<?php

session_start(); // assure-toi que la session est bien démarrée

$loggedIn = isset($_SESSION['user']);
$customer_name = $loggedIn && !empty($_SESSION['user']['full_name']) ? htmlspecialchars($_SESSION['user']['full_name']) : '';
$customer_email = $loggedIn && !empty($_SESSION['user']['email']) ? htmlspecialchars($_SESSION['user']['email']) : '';

require_once __DIR__ . '/../../controller/ReservationController.php';
require_once __DIR__ . '/../../controller/ActivityController.php';

// Initialiser les contrôleurs
$reservationController = new ReservationController();
$activityController = new ActivityController();

// Récupérer l'ID de l'activité depuis l'URL
$activity_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si l'ID est valide, récupérer les informations de l'activité
$activity = null;
if ($activity_id > 0) {
    $result = $reservationController->showReservationForm($activity_id);
    if ($result['success']) {
        $activity = $result['activity'];
    }
}

// Traitement du formulaire de réservation
$reservation_success = false;
$reservation_message = '';
$reservation_id = 0;
$reservation_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reservation'])) {
    // Récupérer les données du formulaire
    if (!isset($_POST['on_site_payment'])) {
        $reservation_error = true;
        $reservation_message = "Veuillez accepter de payer sur place pour valider la réservation.";
    } else {
        $data = [
            'activity_id' => $activity_id,
            'customer_name' => $_POST['customer_name'] ?? '',
            'customer_email' => $_POST['customer_email'] ?? '',
            'reservation_date' => $_POST['reservation_date'] ?? '',
            'reservation_time' => $_POST['reservation_time'] ?? '',
            'people_count' => intval($_POST['people_count'] ?? 1),
            'total_price' => floatval($_POST['total_price'] ?? 0),
            'on_site_payment' => true
        ];
        // Traiter la réservation
        try {
            $result = $reservationController->processReservation($data);
            $reservation_success = $result['success'];
            $reservation_message = $result['message'];
            if ($reservation_success) {
                $reservation_id = $result['reservation_id'];
            }
        } catch (Exception $e) {
            $reservation_error = true;
            $reservation_message = "Une erreur est survenue lors du traitement de votre réservation: " . $e->getMessage();
            error_log("Erreur de réservation: " . $e->getMessage());
        }
    }
}

// Fonction pour formater le prix
function formatPrice($price) {
    return number_format($price, 2, '.', ' ');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservation - <?php echo htmlspecialchars($activity['name'] ?? 'Activité'); ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .reservation-container {
            max-width: 900px;
            margin: 40px auto;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .reservation-container h1 {
            background: linear-gradient(135deg, #e70a83 0%, #ff5e5e 100%);
            color: white;
            margin: 0;
            padding: 25px 30px;
            font-size: 24px;
            text-align: center;
        }
        
        /* Styles pour les étapes */
        .steps {
            display: flex;
            justify-content: space-between;
            padding: 20px 40px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            color: #999;
            font-size: 14px;
            font-weight: 500;
        }
        
        .step::before {
            content: '';
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #f0f0f0;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .step.active {
            color: #e70a83;
            font-weight: 600;
        }
        
        .step.active::before {
            background-color: #fff;
            border-color: #e70a83;
            position: relative;
        }
        
        .step.active::after {
            content: '✓';
            position: absolute;
            top: 7px;
            color: #e70a83;
            font-weight: bold;
        }
        
        /* Ligne de progression entre les étapes */
        .steps::after {
            content: '';
            position: absolute;
            top: 40px;
            left: 40px;
            right: 40px;
            height: 2px;
            background-color: #eee;
            z-index: -1;
        }
        
        /* Styles des sections */
        .section {
            padding: 30px 40px;
        }
        
        .section h3 {
            color: #333;
            margin-top: 0;
            font-size: 20px;
            margin-bottom: 20px;
        }
        
        /* Style de la section Tarif */
        .activity-summary {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .activity-summary h2 {
            color: #e70a83;
            margin: 0 0 10px 0;
            font-size: 22px;
        }
        
        .pricing {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .price-details {
            flex: 1;
            min-width: 150px;
        }
        
        .price-details p {
            margin: 0 0 5px 0;
            color: #666;
            font-size: 14px;
        }
        
        .price-details strong {
            color: #e70a83;
            font-size: 24px;
            font-weight: 600;
        }
        
        .quantity {
            flex: 1;
            min-width: 150px;
        }
        
        .quantity label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-size: 14px;
        }
        
        .quantity input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .quantity input:focus {
            border-color: #e70a83;
            outline: none;
        }
        
        .total {
            flex: 1;
            min-width: 150px;
            text-align: right;
        }
        
        .total p {
            margin: 0 0 5px 0;
            color: #666;
            font-size: 14px;
        }
        
        .total strong {
            color: #e70a83;
            font-size: 24px;
            font-weight: 600;
        }
        
        /* Styles pour le calendrier */
        .calendar-container {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-bottom: 25px;
        }
        
        .calendar-header {
            grid-column: span 7;
            text-align: center;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 18px;
            color: #333;
        }
        
        .day-name {
            text-align: center;
            font-weight: 500;
            background-color: #f8f8f8;
            padding: 8px 5px;
            border-radius: 8px;
            font-size: 13px;
        }
        
        .day {
            text-align: center;
            padding: 12px 5px;
            cursor: pointer;
            background-color: #f8f8f8;
            border-radius: 8px;
            position: relative;
            transition: all 0.2s ease;
        }
        
        .day.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        .day.today {
            border: 2px solid #e70a83;
            font-weight: 600;
        }
        
        .day.selected {
            background-color: #e70a83;
            color: white;
            font-weight: 600;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(231, 10, 131, 0.2);
        }
        
        .day:hover:not(.disabled) {
            background-color: #ffe6f5;
            transform: translateY(-2px);
        }
        
        .time-selection {
            margin-top: 20px;
            display: none;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        
        .time-selection.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        .time-selection h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            font-size: 16px;
        }
        
        .time-slot {
            display: inline-block;
            margin: 5px;
            padding: 10px 16px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .time-slot.selected {
            background-color: #e70a83;
            color: white;
            border-color: #e70a83;
            font-weight: 500;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(231, 10, 131, 0.2);
        }
        
        .time-slot.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .time-slot:hover:not(.disabled) {
            background-color: #ffe6f5;
            border-color: #e70a83;
            transform: translateY(-2px);
        }
        
        /* Styles pour les champs de formulaire */
        .input-container {
            margin-bottom: 20px;
        }
        
        .input-container label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        .input-container input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .input-container input:focus {
            border-color: #e70a83;
            outline: none;
            box-shadow: 0 0 0 2px rgba(231, 10, 131, 0.1);
        }
        
        .input-container input.error {
            border-color: #ff3b30;
        }
        
        /* Styles pour le résumé */
        .summary {
            margin-top: 25px;
            padding: 25px;
            background-color: #fff6fc;
            border-radius: 12px;
            border: 1px solid #ffbce4;
        }
        
        .summary h3 {
            margin-top: 0;
            color: #e70a83;
            font-size: 18px;
            border-bottom: 1px solid #ffbce4;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .summary p {
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
        }
        
        .summary p strong {
            color: #555;
        }
        
        /* Boutons de navigation */
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding: 0 40px 30px;
        }
        
        .prev-btn, .next-btn {
            padding: 12px 25px;
            font-size: 15px;
            border: none;
            cursor: pointer;
            border-radius: 30px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .prev-btn {
            background-color: #f0f0f0;
            color: #666;
        }
        
        .next-btn {
            background-color: #e70a83;
            color: white;
        }
        
        .prev-btn:hover {
            background-color: #e0e0e0;
            transform: translateY(-2px);
        }
        
        .next-btn:hover {
            background-color: #d60077;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 10, 131, 0.3);
        }
        
        .prev-btn:disabled {
            background-color: #ddd;
            cursor: not-allowed;
            transform: none;
        }
        
        #pay-button {
            background: linear-gradient(135deg, #e70a83 0%, #ff5e5e 100%);
            width: 100%;
            margin-top: 20px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 5px 15px rgba(231, 10, 131, 0.3);
        }
        
        #pay-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(231, 10, 131, 0.4);
        }
        
        /* Messages de confirmation et d'erreur */
        .confirmation-message, .error-message {
            margin: 30px;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
        }
        
        .confirmation-message {
            background-color: #e8f7ee;
            border: 1px solid #a8e6c1;
            color: #1d7d4a;
        }
        
        .error-message {
            background-color: #ffeeee;
            border: 1px solid #ffbdbd;
            color: #d63031;
        }
        
        .confirmation-message h2, .error-message h2 {
            margin-top: 0;
            font-size: 20px;
        }
        
        .confirmation-message a, .error-message a {
            display: inline-block;
            margin-top: 15px;
            color: #e70a83;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .confirmation-message a:hover, .error-message a:hover {
            color: #d60077;
            text-decoration: underline;
        }
        
        .no-fees {
            text-align: center;
            padding: 0 40px 20px;
            color: #666;
            font-size: 14px;
        }
        
        /* Animation pour les transitions */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .reservation-container {
                margin: 20px;
                border-radius: 15px;
            }
            
            .steps {
                padding: 15px;
            }
            
            .section {
                padding: 20px;
            }
            
            .pricing {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .price-details, .quantity, .total {
                width: 100%;
                margin-bottom: 15px;
            }
            
            .button-container {
                padding: 0 20px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="button-container">
        <a href="/mvcact/view/front office/activite.php" class="prev-btn" id="prev-btn">Retour</a>
    </div>
    <div class="reservation-container">
        <h1>Réservez votre activité</h1>
        
        <?php if ($reservation_success): ?>
            <div class="confirmation-message">
                <h2>Réservation confirmée !</h2>
                <p><?php echo htmlspecialchars($reservation_message); ?></p>
                <p>Votre numéro de réservation : <?php echo $reservation_id; ?></p>
                <p>Un email de confirmation vous a été envoyé.</p>
                <p><a href="activite.php">Retour à la liste des activités</a></p>
            </div>
        <?php elseif ($reservation_error): ?>
            <div class="error-message">
                <h2>Erreur de traitement</h2>
                <p><?php echo htmlspecialchars($reservation_message); ?></p>
                <p>Veuillez réessayer plus tard ou contacter notre support.</p>
                <p><a href="javascript:history.back()">Retour à la page précédente</a></p>
            </div>
        <?php elseif (!$activity): ?>
            <div class="error-message">
                <p>Activité non trouvée ou invalide.</p>
                <p>ID activité reçu: <?php echo htmlspecialchars($activity_id); ?></p>
                <div>
                  <p><strong>Débogage:</strong></p>
                  <?php 
                  // Essai direct de récupération pour débogage
                  require_once __DIR__ . '/../../model/ActivityModel.php';
                  require_once __DIR__ . '/../../model/EnterpriseModel.php';
                  
                  $activityModel = new ActivityModel();
                  $enterpriseModel = new EnterpriseModel();
                  
                  $activityResult = $activityModel->getActivityById($activity_id);
                  $enterpriseResult = $enterpriseModel->getEnterpriseActivityById($activity_id);
                  
                  echo "<p>Recherche dans table 'activities': " . ($activityResult ? "Trouvé" : "Non trouvé") . "</p>";
                  echo "<p>Recherche dans table 'enterprise_activities': " . ($enterpriseResult ? "Trouvé" : "Non trouvé") . "</p>";
                  ?>
                </div>
                <p><a href="activite.php">Retour à la liste des activités</a></p>
            </div>
        <?php else: ?>

<!-- Étapes de la réservation -->
<!-- BARRE D'ÉTAPES SUPPRIMÉE ICI -->

<form method="POST" action="" id="reservation-form">
    <input type="hidden" name="activity_id" value="<?php echo $activity_id; ?>">
    <input type="hidden" name="reservation_date" id="reservation_date" value="">
    <input type="hidden" name="reservation_time" id="reservation_time" value="">
    <input type="hidden" name="total_price" id="total_price" value="<?php echo $activity['price']; ?>">
    <div class="slider-wrapper">
        <div class="slider" id="slider">
            <!-- Section Tarif -->
            <div class="section" id="section-1">
                <div class="activity-summary">
                    <h2 id="activity-title"><?php echo htmlspecialchars($activity['name']); ?></h2>
                    <?php if (!empty($activity['duration'])): ?>
                        <p>Durée : <?php echo htmlspecialchars($activity['duration']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="pricing">
                    <div class="price-details">
                        <p>Prix / personne</p>
                        <strong id="unit-price"><?php echo formatPrice($activity['price']); ?> DT</strong>
                    </div>
                    <div class="quantity">
                        <label for="people_count">Nombre de personnes :</label>
                        <input type="number" id="people_count" name="people_count" min="1" value="1" required>
                    </div>
                    <div class="total">
                        <p>Total :</p>
                        <strong id="total-amount"><?php echo formatPrice($activity['price']); ?> DT</strong>
                    </div>
                </div>
            </div>
            <!-- Section Date -->
            <div class="section" id="section-2">
                <h3>Choisir la date</h3>
                <div class="calendar-container">
                    <div class="calendar-nav">
                        <button type="button" id="prev-month">&#8592;</button>
                        <span id="calendar-month-year"></span>
                        <button type="button" id="next-month">&#8594;</button>
                    </div>
                    <div class="calendar" id="calendar"></div>
                </div>
                <div class="time-selection" id="time-selection">
                    <h4>Choisissez l'horaire</h4>
                </div>
            </div>
            <!-- Section Infos -->
            <div class="section" id="section-3">
                <h3>Informations personnelles</h3>
<div class="input-container">
    <label for="customer_name">Nom :</label>
    <input type="text" id="customer_name" name="customer_name"
           placeholder="Votre nom"
           value="<?= $customer_name ?>"
           <?= $loggedIn ? 'readonly' : 'required' ?>>
    <small class="error-text" id="name-error" style="display: none; color: #ff3b30; font-size: 12px; margin-top: 5px;">
        Le nom ne doit pas contenir de chiffres
    </small>
</div>

<div class="input-container">
    <label for="customer_email">Email :</label>
    <input type="email" id="customer_email" name="customer_email"
           placeholder="Votre email"
           value="<?= $customer_email ?>"
           <?= $loggedIn ? 'readonly' : 'required' ?>>
    <small class="error-text" id="email-error" style="display: none; color: #ff3b30; font-size: 12px; margin-top: 5px;">
        L'email doit contenir un '@'
    </small>
</div>
            <!-- Section Paiement -->
            <div class="section" id="section-4">
                <h3>Informations de paiement</h3>
                <div class="input-container">
                    <input type="checkbox" id="on-site-payment" name="on_site_payment" required>
                    <label for="on-site-payment">Je paierai sur place</label>
                </div>
                <button type="submit" name="submit_reservation" class="next-btn" id="pay-button">Valider la réservation</button>
            </div>
        </div>
    </div>
    <div class="summary" id="reservation-summary" style="display:none;">
        <h3>Résumé de votre réservation</h3>
        <p><strong>Nom de l'activité :</strong> <span id="summary-activity-name"><?php echo htmlspecialchars($activity['name']); ?></span></p>
        <p><strong>Nombre de personnes :</strong> <span id="summary-people">1</span></p>
        <p><strong>Date choisie :</strong> <span id="summary-date">Non sélectionnée</span></p>
        <p><strong>Horaire choisi :</strong> <span id="summary-time">Non sélectionné</span></p>
        <p><strong>Email :</strong> <span id="summary-email"></span></p>
        <p><strong>Prix total :</strong> <span id="summary-total"><?php echo formatPrice($activity['price']); ?> DT</span></p>
    </div>
    

</form>
<!-- BOUTONS DE NAVIGATION SUPPRIMÉS ICI -->
<p class="no-fees">✅ Zéro frais de réservation</p>
        
        <?php endif; ?>
    </div>

    <script>
        // Variables globales
        const activityPrice = <?php echo $activity ? $activity['price'] : 0; ?>;
        let selectedDate = '';
        let selectedTime = '';
        
        // Mettre à jour le total lorsque le nombre de personnes change
        const peopleCountInput = document.getElementById('people_count');
        const totalAmountElement = document.getElementById('total-amount');
        const totalPriceInput = document.getElementById('total_price');
        
        function updateTotal() {
            const peopleCount = parseInt(peopleCountInput.value) || 1;
            const totalPrice = (peopleCount * activityPrice).toFixed(2);
            totalAmountElement.textContent = `${totalPrice} DT`;
            totalPriceInput.value = totalPrice;
            
            // Mettre à jour le résumé
            document.getElementById('summary-people').textContent = peopleCount;
            document.getElementById('summary-total').textContent = `${totalPrice} DT`;
        }
        
        if (peopleCountInput) {
            peopleCountInput.addEventListener('input', updateTotal);
            updateTotal(); // Initialisation
        }
        
        // Gestion de l'affichage des sections
        let currentSection = 1;
        const totalSections = 4;
        let currentMonth, currentYear;
        
        function updateSlider() {
            const slider = document.getElementById('slider');
            slider.style.transform = `translateX(-${(currentSection-1)*100}%)`;
        }
        
        // Fonctions de validation
        function validateName(name) {
            // Vérifie que le nom ne contient pas de chiffres
            return !/\d/.test(name);
        }
        
        function validateEmail(email) {
            // Vérifie que l'email contient un @
            return email.includes('@');
        }
        
        function validateRequiredFields(section) {
            const requiredFields = section.querySelectorAll('input[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (field.type === 'checkbox') {
                    if (!field.checked) {
                        valid = false;
                        field.classList.add('error');
                    } else {
                        field.classList.remove('error');
                    }
                } else {
                    if (!field.value) {
                        valid = false;
                        field.classList.add('error');
                    } else {
                        field.classList.remove('error');
                    }
                }
            });
            
            return valid;
        }
        
        function validateSection(sectionNumber) {
            const section = document.getElementById(`section-${sectionNumber}`);
            const nameInput = document.getElementById('customer_name');
            const emailInput = document.getElementById('customer_email');
            
            // Réinitialiser les messages d'erreur
            document.querySelectorAll('.error-text').forEach(el => el.style.display = 'none');
            
            // Vérifier que tous les champs requis sont remplis
            if (!validateRequiredFields(section)) {
                return false;
            }
            
            // Validations spécifiques selon la section
            if (sectionNumber === 2 && (!selectedDate || !selectedTime)) {
                alert("Veuillez sélectionner une date et un horaire.");
                return false;
            }
            
            if (sectionNumber === 3) {
                // Validation du nom (pas de chiffres)
                if (!validateName(nameInput.value)) {
                    nameInput.classList.add('error');
                    document.getElementById('name-error').style.display = 'block';
                    return false;
                } else {
                    nameInput.classList.remove('error');
                }
                
                // Validation de l'email (doit contenir @)
                if (!validateEmail(emailInput.value)) {
                    emailInput.classList.add('error');
                    document.getElementById('email-error').style.display = 'block';
                    return false;
                } else {
                    emailInput.classList.remove('error');
                }
            }
            
            return true;
        }
        
        function changeSection(direction) {
            if (direction === 'next') {
                if (!validateSection(currentSection)) {
                    return;
                }
                
                if (currentSection < totalSections) {
                    currentSection++;
                }
            } else if (direction === 'prev' && currentSection > 1) {
                currentSection--;
            }
            
            updateSlider();
            document.getElementById('prev-btn').disabled = currentSection === 1;
            document.getElementById('next-btn').style.display = currentSection === 4 ? 'none' : 'inline-block';
            
            if (currentSection === 4) {
                updateSummary();
                document.getElementById('reservation-summary').style.display = 'block';
            } else {
                document.getElementById('reservation-summary').style.display = 'none';
            }
        }
        
        // Validation du formulaire lors de la soumission
        document.getElementById('reservation-form').addEventListener('submit', function(event) {
            const nameInput = document.getElementById('customer_name');
            const emailInput = document.getElementById('customer_email');
            const dateInput = document.getElementById('reservation_date');
            const timeInput = document.getElementById('reservation_time');
            const paymentCheckbox = document.getElementById('on-site-payment');
            
            // Réinitialiser les messages d'erreur
            document.querySelectorAll('.error-text').forEach(el => el.style.display = 'none');
            
            // Valider le nom (pas de chiffres)
            if (!validateName(nameInput.value)) {
                event.preventDefault();
                nameInput.classList.add('error');
                document.getElementById('name-error').style.display = 'block';
                alert("Le nom ne doit pas contenir de chiffres.");
                return false;
            }
            
            // Valider l'email (contient @)
            if (!validateEmail(emailInput.value)) {
                event.preventDefault();
                emailInput.classList.add('error');
                document.getElementById('email-error').style.display = 'block';
                alert("L'email doit contenir un '@'.");
                return false;
            }
            
            // Vérifier que la date et l'heure sont sélectionnées
            if (!dateInput.value || !timeInput.value) {
                event.preventDefault();
                alert("Veuillez sélectionner une date et un horaire.");
                return false;
            }
            
            // Vérifier que le paiement sur place est accepté
            if (!paymentCheckbox.checked) {
                event.preventDefault();
                paymentCheckbox.classList.add('error');
                alert("Veuillez accepter de payer sur place pour valider la réservation.");
                return false;
            }
        });
        
        // Mettre à jour le résumé de la réservation
        function updateSummary() {
            const customerName = document.getElementById('customer_name').value;
            const customerEmail = document.getElementById('customer_email').value;
            
            document.getElementById('summary-date').textContent = selectedDate || 'Non sélectionnée';
            document.getElementById('summary-time').textContent = selectedTime || 'Non sélectionné';
            document.getElementById('summary-email').textContent = customerEmail || 'Non renseigné';
        }
        
        // Validation en temps réel des champs
        document.getElementById('customer_name').addEventListener('input', function() {
            if (!validateName(this.value) && this.value.length > 0) {
                this.classList.add('error');
                document.getElementById('name-error').style.display = 'block';
            } else {
                this.classList.remove('error');
                document.getElementById('name-error').style.display = 'none';
            }
        });
        
        document.getElementById('customer_email').addEventListener('input', function() {
            if (!validateEmail(this.value) && this.value.length > 0) {
                this.classList.add('error');
                document.getElementById('email-error').style.display = 'block';
            } else {
                this.classList.remove('error');
                document.getElementById('email-error').style.display = 'none';
            }
        });
        
        // Calendrier annuel
        function generateCalendar(month, year) {
            const calendar = document.getElementById('calendar');
            if (!calendar) return;
            calendar.innerHTML = '';
            
            // Vider le calendrier
            while (calendar.children.length > 1) {
                calendar.removeChild(calendar.lastChild);
            }
            
            const today = new Date();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();
            
            // Mettre à jour le mois et l'année
            const monthNames = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
            document.getElementById('calendar-month-year').textContent = `${monthNames[currentMonth]} ${currentYear}`;
            
            // Ajouter les jours de la semaine
            const dayNames = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
            dayNames.forEach(day => {
                const dayNameDiv = document.createElement('div');
                dayNameDiv.className = 'day-name';
                dayNameDiv.textContent = day;
                calendar.appendChild(dayNameDiv);
            });
            
            // Déterminer le premier jour du mois
            const firstDay = new Date(currentYear, currentMonth, 1);
            const startingDay = firstDay.getDay() || 7; // Convertir dimanche (0) en 7 pour la semaine commençant par lundi
            
            // Ajouter des cellules vides pour les jours avant le début du mois
            for (let i = 1; i < startingDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'day disabled';
                calendar.appendChild(emptyDay);
            }
            
            // Nombre de jours dans le mois actuel
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const todayDate = today.getDate();
            
            // Créer les jours du mois
            for (let day = 1; day <= daysInMonth; day++) {
                const dayDiv = document.createElement('div');
                dayDiv.className = 'day';
                dayDiv.textContent = day;
                
                // Marquer aujourd'hui
                if (day === todayDate) {
                    dayDiv.classList.add('today');
                }
                
                // Désactiver les jours passés
                if (day < todayDate) {
                    dayDiv.classList.add('disabled');
                } else {
                    // Ajouter un événement de clic pour les jours futurs
                    dayDiv.addEventListener('click', function() {
                        // Supprimer la sélection précédente
                        document.querySelectorAll('.day').forEach(d => d.classList.remove('selected'));
                        dayDiv.classList.add('selected');
                        
                        // Stocker la date sélectionnée
                        selectedDate = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        document.getElementById('reservation_date').value = selectedDate;
                        
                        // Afficher les créneaux horaires disponibles
                        showTimeSlots(selectedDate);
                    });
                }
                
                calendar.appendChild(dayDiv);
            }
        }
        
        // Afficher les créneaux horaires disponibles pour une date donnée
        function showTimeSlots(date) {
            const timeSelection = document.getElementById('time-selection');
            timeSelection.innerHTML = '<h4>Choisissez l\'horaire</h4>';
            timeSelection.classList.add('active');
            
            // Exemple de créneaux horaires (à remplacer par des données réelles)
            const timeSlots = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
            
            timeSlots.forEach(time => {
                const timeSlot = document.createElement('div');
                timeSlot.className = 'time-slot';
                timeSlot.textContent = time;
                
                // Exemple : désactiver aléatoirement certains créneaux (à remplacer par une logique réelle)
                if (Math.random() < 0.3) {
                    timeSlot.classList.add('disabled');
                } else {
                    timeSlot.addEventListener('click', function() {
                        // Supprimer la sélection précédente
                        document.querySelectorAll('.time-slot').forEach(t => t.classList.remove('selected'));
                        timeSlot.classList.add('selected');
                        
                        // Stocker l'horaire sélectionné
                        selectedTime = time;
                        document.getElementById('reservation_time').value = selectedTime;
                    });
                }
                
                timeSelection.appendChild(timeSlot);
            });
        }
        
        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            currentMonth = today.getMonth();
            currentYear = today.getFullYear();
            generateCalendar(currentMonth, currentYear);
            
            // Mettre à jour le résumé lorsque les informations personnelles changent
            document.getElementById('customer_name').addEventListener('input', updateSummary);
            document.getElementById('customer_email').addEventListener('input', updateSummary);
        });

        document.getElementById('prev-month').onclick = function() {
            if (currentMonth === 0) {
                currentMonth = 11;
                currentYear--;
            } else {
                currentMonth--;
            }
            generateCalendar(currentMonth, currentYear);
        };
        document.getElementById('next-month').onclick = function() {
            if (currentMonth === 11) {
                currentMonth = 0;
                currentYear++;
            } else {
                currentMonth++;
            }
            generateCalendar(currentMonth, currentYear);
        };
    </script>
</body>
</html> 