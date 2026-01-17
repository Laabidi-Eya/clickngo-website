<?php
require_once(__DIR__ . '/../config.php');
require_once '../Controller/DemandeCovoiturageController.php';
require_once '../Model/DemandeCovoiturage.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$errorMessages = [];
$successMessage = '';
$demandeId = $_GET['id'] ?? null;
$demandeDetails = null;
$annonceDetails = null;
$showSuccessPopup = false;
$showErrorPopup = false;

try {
    // Get the database connection via config
    $pdo = config::getConnexion();
    
    // Create controller instances
    $demandeController = new DemandeCovoiturageController($pdo); // Pass PDO instance
    $annonceController = new AnnonceCovoiturageController($pdo);

    // Get demande details if ID is provided
    if ($demandeId) {
        $allDemandes = $demandeController->getAllDemandes();
        foreach ($allDemandes as $demande) {
            if ($demande['id_passager'] == $demandeId) {
                $demandeDetails = $demande;
                break;
            }
        }
        
        if (!$demandeDetails) {
            throw new Exception("Demande non trouvée");
        }
        
        // Get associated annonce details
        $annonces = $annonceController->getAllAnnonces();
        foreach ($annonces as $annonce) {
            if ($annonce->getIdConducteur() == $demandeDetails['id_conducteur']) {
                $annonceDetails = $annonce;
                break;
            }
        }
    } else {
        throw new Exception("ID de demande non fourni");
    }

    // Check if form is submitted
    if (isset($_POST['submit'])) {
        $data = $_POST;
        $data['id_passager'] = $demandeId;
        
        // Update the demande
        $success = $demandeController->updateDemande($data);
        
        if ($success) {
            // Set success message in session and redirect
            $_SESSION['success_message'] = "Demande mise à jour avec succès!";
            $showSuccessPopup = true;
            header("Location: ListPassager.php");
            exit();
        } else {
            throw new Exception("Échec de la mise à jour de la demande");
        }
    }
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
    $showErrorPopup = true;
}

// Check for success message in session
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    $showSuccessPopup = true;
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une demande - Click'N'go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="/clickngo/public/css/style.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;500&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            margin-top: 80px;
        }

        .container {
            display: flex;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-width: 900px;
            width: 85%;
            overflow: hidden;
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .preview {
            width: 40%;
            background: linear-gradient(135deg, #fff0f5, #f0f7ff);
            color: #333;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><circle cx="10" cy="10" r="2" fill="%23ff8fa3"/><circle cx="90" cy="90" r="3" fill="%23c084fc"/><circle cx="50" cy="20" r="2" fill="%23ff8fa3"/><circle cx="80" cy="50" r="2" fill="%23c084fc"/></svg>') repeat;
            z-index: 0;
        }

        .preview::after {
            content: '✨';
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 20px;
            opacity: 0.5;
            z-index: 1;
        }

        #map {
            width: 220px;
            height: 220px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 1;
            border: 2px solid #fff;
        }

        .preview h3 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            margin: 8px 0;
            text-align: center;
            color: #2d2d2d;
            z-index: 1;
            position: relative;
        }

        .preview h3::after {
            content: '';
            display: block;
            width: 30px;
            height: 2px;
            background: #ff8fa3;
            margin: 6px auto;
            border-radius: 1px;
        }

        .preview .details {
            font-size: 14px;
            color: #666;
            text-align: center;
            z-index: 1;
        }

        .preview .details p {
            margin: 5px 0;
        }

        .preview .price {
            font-size: 18px;
            font-weight: 500;
            color: #fff;
            background: linear-gradient(45deg, #ff8fa3, #c084fc);
            padding: 6px 15px;
            border-radius: 15px;
            z-index: 1;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            margin-top: 10px;
        }

        .preview .price:hover {
            transform: translateY(-2px);
        }

        .location-form {
            width: 60%;
            padding: 30px;
            background: #fff;
            transition: transform 0.3s ease;
        }

        .location-form:hover {
            transform: scale(1.02);
        }

        .location-form h2 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #2d2d2d;
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }

        .location-form h2::after {
            content: '';
            width: 50px;
            height: 2px;
            background: #ff8fa3;
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .location-form label {
            display: block;
            margin: 15px 0 8px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        .location-form input[type="text"],
        .location-form input[type="tel"],
        .location-form input[type="number"],
        .location-form select,
        .location-form textarea {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-size: 14px;
            background: #f9f9f9;
            transition: all 0.3s ease;
        }

        .location-form input:focus,
        .location-form select:focus,
        .location-form textarea:focus {
            border-color: #ff8fa3;
            box-shadow: 0 0 0 2px rgba(255, 143, 163, 0.2);
            outline: none;
            background: #fff;
        }

        .location-form textarea {
            min-height: 80px;
            resize: vertical;
        }

        .location-form button {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .location-form button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }

        .location-form button:hover::before {
            left: 100%;
        }

        .location-form button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 143, 163, 0.4);
        }

        .error {
            color: #e63946;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }

        .invalid {
            border-color: #e63946;
            box-shadow: 0 0 0 2px rgba(230, 57, 70, 0.2);
        }

        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            z-index: 1000;
            display: none;
            border: 2px solid #4CAF50;
        }

        .popup.show {
            display: block;
        }

        .popup .icon {
            font-size: 30px;
            color: #4CAF50;
            margin-bottom: 8px;
        }

        .popup h3 {
            font-family: 'Roboto', sans-serif;
            font-size: 20px;
            color: #333;
            margin-bottom: 8px;
        }

        .popup p {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .popup button {
            padding: 8px 15px;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .popup button:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(255, 143, 163, 0.3);
        }

        .popup.error {
            border: 2px solid #e63946;
        }

        .popup.error .icon {
            color: #e63946;
        }

        .popup.error button {
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
        }

        .popup.error button:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(255, 143, 163, 0.3);
        }

        .travel-time-notification {
            position: fixed;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #ff8fa3, #c084fc);
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 220px;
            z-index: 1000;
            animation: slideInFromLeft 0.8s ease-out forwards;
            opacity: 0;
        }

        @keyframes slideInFromLeft {
            0% {
                transform: translate(-100px, -50%);
                opacity: 0;
            }
            100% {
                transform: translate(0, -50%);
                opacity: 1;
            }
        }

        .travel-time-notification h3 {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            color: #fff;
            margin-bottom: 8px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .travel-time-notification p {
            font-size: 14px;
            color: #fff;
            margin-bottom: 0;
            font-weight: 300;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                width: 95%;
            }

            .preview,
            .location-form {
                width: 100%;
            }

            #map {
                width: 180px;
                height: 180px;
            }

            .travel-time-notification {
                width: 90%;
                left: 5%;
                top: auto;
                bottom: 20px;
                transform: translateY(0);
                animation: slideUp 0.8s ease-out forwards;
            }
            
            @keyframes slideUp {
                0% {
                    transform: translateY(100px);
                    opacity: 0;
                }
                100% {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
        }
    </style>
</head>
<body>
    
    <div class="main-content">
        <div class="container">
            <div class="preview">
                <div id="map"></div>
                <h3>Détails du covoiturage</h3>
                <?php if ($annonceDetails): ?>
                    <div class="details">
                        <p><strong>Conducteur:</strong> <?php echo htmlspecialchars($annonceDetails->getPrenomConducteur() . ' ' . $annonceDetails->getNomConducteur()); ?></p>
                        <p><strong>Trajet:</strong> <?php echo htmlspecialchars($annonceDetails->getLieuDepart() . ' → ' . $annonceDetails->getLieuArrivee()); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($annonceDetails->getDateDepart()->format('d/m/Y H:i')); ?></p>
                        <p><strong>Places disponibles:</strong> <?php echo htmlspecialchars($annonceDetails->getNombrePlaces()); ?></p>
                    </div>
                    <div class="price"><?php echo htmlspecialchars(number_format($annonceDetails->getPrixEstime(), 2)) . ' Dt'; ?></div>
                <?php endif; ?>
            </div>
            <div class="location-form">
                <h2>Modifier ma demande</h2>
                <form method="POST" action="" id="demandeForm">
                    <input type="hidden" name="id_passager" value="<?php echo htmlspecialchars($demandeId); ?>">
                    <input type="hidden" name="id_conducteur" value="<?php echo htmlspecialchars($demandeDetails['id_conducteur'] ?? ''); ?>">

                    <label for="prenom_passager">Prénom</label>
                    <input type="text" id="prenom_passager" name="prenom_passager" 
                           value="<?php echo htmlspecialchars($demandeDetails['prenom_passager'] ?? ''); ?>" 
                           placeholder="Votre prénom" required>
                    <div class="error" id="prenom_passager-error">Prénom invalide (minimum 2 caractères alphabétiques)</div>

                    <label for="nom_passager">Nom</label>
                    <input type="text" id="nom_passager" name="nom_passager" 
                           value="<?php echo htmlspecialchars($demandeDetails['nom_passager'] ?? ''); ?>" 
                           placeholder="Votre nom" required>
                    <div class="error" id="nom_passager-error">Nom invalide (minimum 2 caractères alphabétiques)</div>

                    <label for="tel_passager">Téléphone</label>
                    <input type="tel" id="tel_passager" name="tel_passager" 
                           value="<?php echo htmlspecialchars($demandeDetails['tel_passager'] ?? ''); ?>" 
                           placeholder="Votre numéro (8 chiffres)" maxlength="8" required>
                    <div class="error" id="tel_passager-error">Numéro de téléphone invalide (8 chiffres requis)</div>

                    <label for="nbr_places_reservees">Nombre de places à réserver</label>
                    <input type="number" id="nbr_places_reservees" name="nbr_places_reservees" 
                           value="<?php echo htmlspecialchars($demandeDetails['nbr_places_reservees'] ?? 1); ?>" 
                           min="1" max="<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getNombrePlaces() : 4); ?>" 
                           placeholder="Nombre de places" required>
                    <div class="error" id="nbr_places_reservees-error">Nombre de places invalide (1-<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getNombrePlaces() : 4); ?>)</div>

                    <label for="moyen_paiement">Moyen de paiement</label>
                    <select id="moyen_paiement" name="moyen_paiement" required>
                        <option value="">Sélectionnez un moyen</option>
                        <option value="espèces" <?php echo ($demandeDetails['moyen_paiement'] ?? '') === 'espèces' ? 'selected' : ''; ?>>Espèces</option>
                        <option value="carte bancaire" <?php echo ($demandeDetails['moyen_paiement'] ?? '') === 'carte bancaire' ? 'selected' : ''; ?>>Carte bancaire</option>
                        <option value="virement" <?php echo ($demandeDetails['moyen_paiement'] ?? '') === 'virement' ? 'selected' : ''; ?>>Virement</option>
                    </select>
                    <div class="error" id="moyen_paiement-error">Veuillez sélectionner un moyen de paiement</div>

                    <label for="message">Message (optionnel)</label>
                    <textarea id="message" name="message" placeholder="Message pour le conducteur"><?php echo htmlspecialchars($demandeDetails['message'] ?? ''); ?></textarea>

                    <button type="submit" name="submit" class="register-btn">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                    
                    <a href="ListPassager.php" style="display: block; text-align: center; margin-top: 20px; color: #c084fc; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </form>
            </div>
        </div>
    </div>
 
  

        

    <div class="popup error" id="errorPopup">
        <div class="icon">❌</div>
        <h3>Erreur</h3>
        <p id="error-message"><?php echo implode('<br>', array_map('htmlspecialchars', $errorMessages)); ?></p>
        <button onclick="closePopup()">Fermer</button>
    </div>

    <div class="travel-time-notification" id="travelTimeNotification">
        <div class="icon">⏱️</div>
        <h3>Durée Estimée</h3>
        <p id="travel-time-message">Chargement...</p>
    </div>

    <script>
        const form = document.getElementById('demandeForm');
        const successPopup = document.getElementById('successPopup');
        const errorPopup = document.getElementById('errorPopup');
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');
        const travelTimeMessage = document.getElementById('travel-time-message');

        // Map initialization
        let map;
        function initializeMap() {
            map = L.map('map', {
                center: [36.8065, 10.1815],
                zoom: 7,
                minZoom: 6,
                maxZoom: 12,
                dragging: false,
                zoomControl: false,
                doubleClickZoom: false,
                scrollWheelZoom: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const redPin = L.icon({
                iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                shadowSize: [41, 41]
            });

            const greenPin = L.icon({
                iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                shadowSize: [41, 41]
            });

            // Get departure and arrival locations
            const lieuDepart = "<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getLieuDepart() : ''); ?>";
            const lieuArrivee = "<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getLieuArrivee() : ''); ?>";

            // Geocode locations using approximate coordinates for Tunisian cities
            const tunisianLocations = {
                "Tunis": [36.8065, 10.1815],
                "Sfax": [34.7406, 10.7603],
                "Sousse": [35.8254, 10.6360],
                "Bizerte": [37.2744, 9.8739],
                "Gabès": [33.8815, 10.0983],
                "Kairouan": [35.6712, 10.1006],
                "Gafsa": [34.4229, 8.7841],
                "Ariana": [36.8665, 10.1647],
                "La Marsa": [36.8782, 10.3247],
                "Hammamet": [36.4072574, 10.6224706],
                "Nabeul": [36.4561, 10.7376]
            };

            const departCoords = tunisianLocations[lieuDepart] || [36.8065, 10.1815]; // Default to Tunis if not found
            const arriveeCoords = tunisianLocations[lieuArrivee] || [34.7406, 10.7603]; // Default to Sfax if not found

            // Add markers
            L.marker(departCoords, { icon: redPin }).addTo(map).bindPopup(lieuDepart);
            L.marker(arriveeCoords, { icon: greenPin }).addTo(map).bindPopup(lieuArrivee);

            // Draw a line between the two points
            const polyline = L.polyline([departCoords, arriveeCoords], {
                color: '#ff8fa3',
                weight: 3,
                opacity: 0.7,
                smoothFactor: 1
            }).addTo(map);

            // Fit map bounds to show both markers
            const bounds = L.latLngBounds([departCoords, arriveeCoords]);
            map.fitBounds(bounds.pad(0.2));

            // Calculate approximate travel time based on distance
            const distance = calculateDistance(departCoords[0], departCoords[1], arriveeCoords[0], arriveeCoords[1]);
            const averageSpeed = 60; // km/h
            const timeHours = distance / averageSpeed;
            const hours = Math.floor(timeHours);
            const minutes = Math.round((timeHours - hours) * 60);
            
            travelTimeMessage.textContent = `Durée: ${hours > 0 ? `${hours}h ${minutes}min` : `${minutes}min`} (${Math.round(distance)} km)`;

            // Optional: Try to get a more accurate route using OpenRouteService API
            tryGetRouteFromAPI(departCoords, arriveeCoords);
        }

        // Calculate distance between two points using Haversine formula
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the earth in km
            const dLat = deg2rad(lat2 - lat1);
            const dLon = deg2rad(lon2 - lon1);
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
                Math.sin(dLon/2) * Math.sin(dLon/2); 
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
            const distance = R * c; // Distance in km
            return distance;
        }

        function deg2rad(deg) {
            return deg * (Math.PI/180);
        }

        // Try to get a more accurate route from OpenRouteService API
        function tryGetRouteFromAPI(departCoords, arriveeCoords) {
            const apiKey = "5b3ce3597851110001cf6248c68ca10f25fb45699c5e735d3db74b61"; // Replace with your OpenRouteService API key
            const url = `https://api.openrouteservice.org/v2/directions/driving-car?api_key=${apiKey}&start=${departCoords[1]},${departCoords[0]}&end=${arriveeCoords[1]},${arriveeCoords[0]}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.features && data.features.length > 0) {
                        // Clear existing polyline
                        map.eachLayer(layer => {
                            if (layer instanceof L.Polyline && !(layer instanceof L.Marker)) {
                                map.removeLayer(layer);
                            }
                        });

                        // Add the route polyline
                        const coordinates = data.features[0].geometry.coordinates.map(coord => [coord[1], coord[0]]);
                        const route = L.polyline(coordinates, {
                            color: '#ff8fa3',
                            weight: 3,
                            opacity: 0.7
                        }).addTo(map);

                        // Extract and display travel time
                        const durationSeconds = data.features[0].properties.summary.duration;
                        const hours = Math.floor(durationSeconds / 3600);
                        const minutes = Math.floor((durationSeconds % 3600) / 60);
                        const distance = (data.features[0].properties.summary.distance / 1000).toFixed(1);
                        travelTimeMessage.textContent = `Durée: ${hours > 0 ? `${hours}h ${minutes}min` : `${minutes}min`} (${distance} km)`;
                    }
                })
                .catch(error => {
                    console.log("Error fetching route:", error);
                    // We already have a fallback calculation, so no need to handle the error further
                });
        }

        // Form validation functions
        function validatePrenom() {
            const prenom = document.getElementById('prenom_passager');
            const error = document.getElementById('prenom_passager-error');
            if (!prenom.value.match(/^[a-zA-ZÀ-ÿ -]{2,}$/)) {
                error.style.display = 'block';
                prenom.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                prenom.classList.remove('invalid');
                return true;
            }
        }

        function validateNom() {
            const nom = document.getElementById('nom_passager');
            const error = document.getElementById('nom_passager-error');
            if (!nom.value.match(/^[a-zA-ZÀ-ÿ -]{2,}$/)) {
                error.style.display = 'block';
                nom.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                nom.classList.remove('invalid');
                return true;
            }
        }

        function validateTel() {
            const tel = document.getElementById('tel_passager');
            const error = document.getElementById('tel_passager-error');
            if (!tel.value.match(/^[0-9]{8}$/)) {
                error.style.display = 'block';
                tel.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                tel.classList.remove('invalid');
                return true;
            }
        }

        function validateNombrePlaces() {
            const nombrePlaces = document.getElementById('nbr_places_reservees');
            const error = document.getElementById('nbr_places_reservees-error');
            const maxPlaces = parseInt(nombrePlaces.max);
            if (!nombrePlaces.value || nombrePlaces.value < 1 || nombrePlaces.value > maxPlaces) {
                error.style.display = 'block';
                nombrePlaces.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                nombrePlaces.classList.remove('invalid');
                return true;
            }
        }

        function validateMoyenPaiement() {
            const moyenPaiement = document.getElementById('moyen_paiement');
            const error = document.getElementById('moyen_paiement-error');
            if (!moyenPaiement.value) {
                error.style.display = 'block';
                moyenPaiement.classList.add('invalid');
                return false;
            } else {
                error.style.display = 'none';
                moyenPaiement.classList.remove('invalid');
                return true;
            }
        }

        // Add real-time validation on input
        document.getElementById('prenom_passager').addEventListener('input', validatePrenom);
        document.getElementById('nom_passager').addEventListener('input', validateNom);
        document.getElementById('tel_passager').addEventListener('input', validateTel);
        document.getElementById('nbr_places_reservees').addEventListener('input', validateNombrePlaces);
        document.getElementById('moyen_paiement').addEventListener('change', validateMoyenPaiement);

        // Phone number validation (8 digits only)
        document.getElementById('tel_passager').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 8);
        });

        // Form submission validation
        function validateForm() {
            const isValid = [
                validatePrenom(),
                validateNom(),
                validateTel(),
                validateNombrePlaces(),
                validateMoyenPaiement()
            ].every(Boolean);
            return isValid;
        }

        function showSuccessPopup() {
            successPopup.classList.add('show');
            setTimeout(() => {
                redirectToList();
            }, 2000);
        }

        function showErrorPopup() {
            errorPopup.classList.add('show');
        }

        function closePopup() {
            successPopup.classList.remove('show');
            errorPopup.classList.remove('show');
        }

        function redirectToList() {
            window.location.href = 'ListPassager.php';
        }

        form.addEventListener('submit', (e) => {
            if (!validateForm()) {
                e.preventDefault();
                showErrorPopup();
                errorMessage.textContent = 'Veuillez remplir tous les champs obligatoires correctement';
            }
        });

        window.addEventListener('DOMContentLoaded', () => {
            initializeMap();
            <?php if (!empty($successMessage) && $showSuccessPopup): ?>
                showSuccessPopup();
            <?php endif; ?>
            
            <?php if (!empty($errorMessages) && $showErrorPopup): ?>
                showErrorPopup();
            <?php endif; ?>
        });
    </script>
</body>
</html>