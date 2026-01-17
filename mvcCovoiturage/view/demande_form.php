<?php
require_once(__DIR__ . '/../config.php');
require_once '../Controller/DemandeCovoiturageController.php';
require_once '../Model/DemandeCovoiturage.php';
require_once '../Controller/AnnonceCovoiturageController.php';
$errorMessages = [];
$successMessage = '';
$annonceId = $_GET['id'] ?? null;
$annonceDetails = null;

try {
    $pdo = config::getConnexion();
    $controller = new DemandeCovoiturageController($pdo); // Pass PDO instance
    if ($annonceId) {
        $annonceController = new AnnonceCovoiturageController($pdo);
        $annonceDetails = $annonceController->getAnnonceById($annonceId);
        if (!$annonceDetails) {
            throw new Exception("Annonce non trouvée pour l'ID $annonceId.");
        }
        $currentDateTime = new DateTime();
        $dateDepart = $annonceDetails->getDateDepart();
        if ($annonceDetails->getStatus() !== 'disponible' || $dateDepart < $currentDateTime) {
            throw new Exception("Cette annonce n'est plus disponible pour la réservation (statut: " . $annonceDetails->getStatus() . ", date de départ: " . $dateDepart->format('Y-m-d H:i:s') . ").");
        }
    } else {
        throw new Exception("Erreur: Aucun ID d'annonce fourni dans l'URL. Utilisez ?id=[ID] dans l'URL.");
    }
    if (isset($_POST['submit'])) {
        $data = $_POST;
        $data['id_conducteur'] = $annonceId;
        $controller->reserverAnnonce($data);
        $successMessage = 'Réservation effectuée avec succès.';
    }
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver un covoiturage - Click'N'go</title>
    <link rel="stylesheet" href="/clickngo/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
            background: linear-gradient(to right, #A29BFE, #FFB1D3);
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
            background: linear-gradient(to right, #A29BFE, #FFB1D3);
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
        background: linear-gradient(to right, #A29BFE, #FFB1D3);
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

        @media (max-width: 768px) {
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
            .travel-time-notification {
                width: 90%;
                left: 5%;
                bottom: 10px;
            }
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
                bottom: 10px;
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
                <h2>Réserver un covoiturage</h2>
                <form method="POST" action="" id="reservationForm">
                    <input type="hidden" name="id_conducteur" value="<?php echo htmlspecialchars($annonceId); ?>">

                    <label for="prenom_passager">Prénom</label>
                    <input type="text" id="prenom_passager" name="prenom_passager" placeholder="Votre prénom" value="<?php echo htmlspecialchars($_POST['prenom_passager'] ?? ''); ?>" required>
                    <div class="error" id="prenom_passager-error">Prénom invalide (minimum 2 caractères alphabétiques)</div>

                    <label for="nom_passager">Nom</label>
                    <input type="text" id="nom_passager" name="nom_passager" placeholder="Votre nom" value="<?php echo htmlspecialchars($_POST['nom_passager'] ?? ''); ?>" required>
                    <div class="error" id="nom_passager-error">Nom invalide (minimum 2 caractères alphabétiques)</div>

                    <label for="tel_passager">Téléphone</label>
                    <input type="tel" id="tel_passager" name="tel_passager" placeholder="Votre numéro (8 chiffres)" maxlength="8" value="<?php echo htmlspecialchars($_POST['tel_passager'] ?? ''); ?>" required>
                    <div class="error" id="tel_passager-error">Numéro de téléphone invalide (8 chiffres requis)</div>

                    <label for="nbr_places_reservees">Nombre de places à réserver</label>
                    <input type="number" id="nbr_places_reservees" name="nbr_places_reservees" min="1" max="<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getNombrePlaces() : 8); ?>" placeholder="Nombre de places" value="<?php echo htmlspecialchars($_POST['nbr_places_reservees'] ?? ''); ?>" required>
                    <div class="error" id="nbr_places_reservees-error">Nombre de places invalide (1-<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getNombrePlaces() : 8); ?>)</div>

                    <label for="moyen_paiement">Moyen de paiement</label>
                    <select id="moyen_paiement" name="moyen_paiement" required>
                        <option value="">Sélectionnez un moyen</option>
                        <option value="espèces" <?php echo (isset($_POST['moyen_paiement']) && $_POST['moyen_paiement'] === 'espèces') ? 'selected' : ''; ?>>Espèces</option>
                        <option value="carte bancaire" <?php echo (isset($_POST['moyen_paiement']) && $_POST['moyen_paiement'] === 'carte bancaire') ? 'selected' : ''; ?>>Carte bancaire</option>
                        <option value="virement" <?php echo (isset($_POST['moyen_paiement']) && $_POST['moyen_paiement'] === 'virement') ? 'selected' : ''; ?>>Virement</option>
                    </select>
                    <div class="error" id="moyen_paiement-error">Veuillez sélectionner un moyen de paiement</div>

                    <label for="message">Message (optionnel)</label>
                    <textarea id="message" name="message" placeholder="Message pour le conducteur"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>

                    <button type="submit" name="submit" class="register-btn">Confirmer la réservation</button>
                </form>
            </div>
        </div>
    </div>

    <div class="popup" id="successPopup">
        <div class="icon">✔</div>
        <h3>Succès!</h3>
        <p id="success-message"><?php echo htmlspecialchars($successMessage); ?></p>
        <button onclick="redirectToList()">Fermer</button>
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
        const form = document.getElementById('reservationForm');
        const successPopup = document.getElementById('successPopup');
        const errorPopup = document.getElementById('errorPopup');
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');
        const travelTimeMessage = document.getElementById('travel-time-message');

        let map;
        function initializeMap() {
            map = L.map('map', {
                center: [34.0, 9.0],
                zoom: 7,
                minZoom: 5,
                maxZoom: 12,
                zoomControl: true,
                scrollWheelZoom: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const startIcon = L.icon({
                iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                shadowSize: [41, 41]
            });

            const endIcon = L.icon({
                iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                shadowSize: [41, 41]
            });

            const lieuDepart = "<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getLieuDepart() : ''); ?>";
            const lieuArrivee = "<?php echo htmlspecialchars($annonceDetails ? $annonceDetails->getLieuArrivee() : ''); ?>";

            function geocodeLocation(locationName, callback) {
                const searchQuery = locationName + ", Tunisia";
                const encodedQuery = encodeURIComponent(searchQuery);
                
                fetch(`https://nominatim.openstreetmap.org/search?q=${encodedQuery}&format=json&limit=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            callback([parseFloat(data[0].lat), parseFloat(data[0].lon)]);
                        } else {
                            const coords = findPredefinedLocation(locationName);
                            callback(coords);
                        }
                    })
                    .catch(error => {
                        console.error("Geocoding error:", error);
                        const coords = findPredefinedLocation(locationName);
                        callback(coords);
                    });
            }

            function findPredefinedLocation(locationName) {
                const tunisianLocations = {
                    "tunis": [36.8065, 10.1815],
                    "sfax": [34.7406, 10.7603],
                    "sousse": [35.8254, 10.6360],
                    "bizerte": [37.2744, 9.8739],
                    "gabès": [33.8815, 10.0983],
                    "kairouan": [35.6712, 10.1006],
                    "gafsa": [34.4229, 8.7841],
                    "ariana": [36.8665, 10.1647],
                    "la marsa": [36.8782, 10.3247],
                    "hammamet": [36.4072, 10.6224],
                    "nabeul": [36.4561, 10.7376],
                    "monastir": [35.7643, 10.8113],
                    "djerba": [33.8075, 10.8453],
                    "tozeur": [33.9197, 8.1335],
                    "le bardo": [36.8092, 10.1407],
                    "le kef": [36.1796, 8.7093],
                    "jendouba": [36.5010, 8.7809],
                    "béja": [36.7333, 9.1833],
                    "mahdia": [35.5111, 11.0705],
                    "mégrine": [36.7667, 10.2167],
                    "ben arous": [36.7531, 10.2189],
                    "carthage": [36.8614, 10.3247],
                    "sidi bou said": [36.8703, 10.3419],
                    "marsa": [36.8782, 10.3247],
                    "la goulette": [36.8167, 10.3000],
                    "radès": [36.7667, 10.2667],
                    "menzel temime": [36.7833, 10.9833],
                    "korba": [36.5833, 10.8667],
                    "kelibia": [36.8500, 11.0833],
                    "zarzis": [33.5042, 11.1122],
                    "djerba midoun": [33.8075, 10.8453],
                    "tataouine": [32.9292, 10.4517],
                    "médenine": [33.3547, 10.5055],
                    "tabarka": [36.9547, 8.7592],
                    "sidi bouzid": [35.0382, 9.4846],
                    "gabes": [33.8815, 10.0983]
                };
                
                const lowerName = locationName.toLowerCase().trim();
                
                if (tunisianLocations[lowerName]) {
                    return tunisianLocations[lowerName];
                }
                
                for (const [city, coords] of Object.entries(tunisianLocations)) {
                    if (lowerName.includes(city) || city.includes(lowerName)) {
                        return coords;
                    }
                }
                
                console.log("No location match found for: " + locationName + ", defaulting to Tunis");
                return tunisianLocations["tunis"];
            }

            geocodeLocation(lieuDepart, departCoords => {
                geocodeLocation(lieuArrivee, arriveeCoords => {
                    const startMarker = L.marker(departCoords, { icon: startIcon })
                        .addTo(map)
                        .bindPopup("<b>Départ:</b><br>" + lieuDepart);
                    
                    const endMarker = L.marker(arriveeCoords, { icon: endIcon })
                        .addTo(map)
                        .bindPopup("<b>Arrivée:</b><br>" + lieuArrivee);
                    
                    const bounds = L.latLngBounds([departCoords, arriveeCoords]);
                    map.fitBounds(bounds.pad(0.3));
                    
                    fetchAndDrawRoute(departCoords, arriveeCoords);
                    
                    setTimeout(() => {
                        startMarker.openPopup();
                        setTimeout(() => {
                            startMarker.closePopup();
                            endMarker.openPopup();
                        }, 1500);
                    }, 500);
                });
            });
            
            function fetchAndDrawRoute(startCoords, endCoords) {
                const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${startCoords[1]},${startCoords[0]};${endCoords[1]},${endCoords[0]}?overview=full&geometries=polyline`;
                
                fetch(osrmUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.routes && data.routes.length > 0) {
                            const encodedPolyline = data.routes[0].geometry;
                            const routeCoordinates = L.Polyline.fromEncoded(encodedPolyline).getLatLngs();
                            
                            const routeLine = L.polyline(routeCoordinates, {
                                color: '#FF3333',
                                weight: 4,
                                opacity: 0.7,
                                lineJoin: 'round'
                            }).addTo(map);
                            
                            const durationSeconds = data.routes[0].duration;
                            const distanceKm = data.routes[0].distance / 1000;
                            displayTravelInfo(durationSeconds, distanceKm);
                        } else {
                            fallbackToStraightLine(startCoords, endCoords);
                        }
                    })
                    .catch(error => {
                        console.error("OSRM Error:", error);
                        tryGraphHopperAPI(startCoords, endCoords);
                    });
            }
            
            function tryGraphHopperAPI(startCoords, endCoords) {
                const graphHopperKey = "your_graphhopper_key";
                const graphHopperUrl = `https://graphhopper.com/api/1/route?point=${startCoords[0]},${startCoords[1]}&point=${endCoords[0]},${endCoords[1]}&vehicle=car&locale=fr&calc_points=true&key=${graphHopperKey}`;
                
                tryOpenRouteService(startCoords, endCoords);
            }
            
            function tryOpenRouteService(startCoords, endCoords) {
                const orsKey = "5b3ce3597851110001cf6248c68ca10f25fb45699c5e735d3db74b61";
                const orsUrl = `https://api.openrouteservice.org/v2/directions/driving-car?api_key=${orsKey}&start=${startCoords[1]},${startCoords[0]}&end=${endCoords[1]},${endCoords[0]}`;
                
                fetch(orsUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.features && data.features.length > 0) {
                            const routeGeometry = data.features[0].geometry.coordinates;
                            const routeCoords = routeGeometry.map(coord => [coord[1], coord[0]]);
                            
                            const routeLine = L.polyline(routeCoords, {
                                color: '#FF3333',
                                weight: 4,
                                opacity: 0.7,
                                lineJoin: 'round'
                            }).addTo(map);
                            
                            const durationSeconds = data.features[0].properties.summary.duration;
                            const distanceKm = data.features[0].properties.summary.distance / 1000;
                            displayTravelInfo(durationSeconds, distanceKm);
                        } else {
                            fallbackToStraightLine(startCoords, endCoords);
                        }
                    })
                    .catch(error => {
                        console.error("OpenRouteService Error:", error);
                        fallbackToStraightLine(startCoords, endCoords);
                    });
            }
            
            function fallbackToStraightLine(startCoords, endCoords) {
                console.log("Falling back to enhanced straight line");
                
                const distanceKm = calculateHaversineDistance(startCoords, endCoords);
                
                const routeCoords = createCurvedLine(startCoords, endCoords);
                
                const routeLine = L.polyline(routeCoords, {
                    color: '#FF3333',
                    weight: 4,
                    opacity: 0.7,
                    lineJoin: 'round'
                }).addTo(map);
                
                const durationSeconds = (distanceKm / 60) * 3600;
                displayTravelInfo(durationSeconds, distanceKm);
            }
            
            function createCurvedLine(start, end) {
                const midLat = (start[0] + end[0]) / 2;
                const midLng = (start[1] + end[1]) / 2;
                
                const dx = end[0] - start[0];
                const dy = end[1] - start[1];
                const dist = Math.sqrt(dx*dx + dy*dy);
                
                const perpX = -dy / dist;
                const perpY = dx / dist;
                
                const curveFactor = Math.min(dist / 10, 0.05);
                const curvePoint = [
                    midLat + perpX * curveFactor,
                    midLng + perpY * curveFactor
                ];
                
                const curve = [];
                curve.push(start);
                
                for (let i = 1; i < 10; i++) {
                    const t = i / 10;
                    const t2 = t * t;
                    const t1 = 1 - t;
                    const t12 = t1 * t1;
                    
                    curve.push([
                        t12 * start[0] + 2 * t1 * t * curvePoint[0] + t2 * end[0],
                        t12 * start[1] + 2 * t1 * t * curvePoint[1] + t2 * end[1]
                    ]);
                }
                
                curve.push(end);
                return curve;
            }
            
            function calculateHaversineDistance(startCoords, endCoords) {
                const lat1 = startCoords[0];
                const lon1 = startCoords[1];
                const lat2 = endCoords[0];
                const lon2 = endCoords[1];
                
                const R = 6371;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = 
                    Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                    Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            }
            
            function displayTravelInfo(durationSeconds, distanceKm) {
                const hours = Math.floor(durationSeconds / 3600);
                const minutes = Math.floor((durationSeconds % 3600) / 60);
                const formattedDistance = Math.round(distanceKm);
                
                let timeDisplay = "";
                if (hours > 0) {
                    timeDisplay = `${hours}h ${minutes}min`;
                } else {
                    timeDisplay = `${minutes}min`;
                }
                
                document.getElementById('travel-time-message').innerHTML = 
                    `<strong>Distance:</strong> ${formattedDistance} km<br>` +
                    `<strong>Durée:</strong> ${timeDisplay}`;
                    
                document.getElementById('travelTimeNotification').style.opacity = '1';
            }
        }

        if (!L.Polyline.fromEncoded) {
            L.Polyline.fromEncoded = function(encoded, options) {
                var points = [];
                var index = 0, len = encoded.length;
                var lat = 0, lng = 0;

                while (index < len) {
                    var b, shift = 0, result = 0;
                    do {
                        b = encoded.charAt(index++).charCodeAt(0) - 63;
                        result |= (b & 0x1f) << shift;
                        shift += 5;
                    } while (b >= 0x20);
                    var dlat = ((result & 1) != 0 ? ~(result >> 1) : (result >> 1));
                    lat += dlat;

                    shift = 0;
                    result = 0;
                    do {
                        b = encoded.charAt(index++).charCodeAt(0) - 63;
                        result |= (b & 0x1f) << shift;
                        shift += 5;
                    } while (b >= 0x20);
                    var dlng = ((result & 1) != 0 ? ~(result >> 1) : (result >> 1));
                    lng += dlng;

                    points.push([lat * 1e-5, lng * 1e-5]);
                }
                return new L.Polyline(points, options || {});
            };
        }

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

        document.getElementById('prenom_passager').addEventListener('input', validatePrenom);
        document.getElementById('nom_passager').addEventListener('input', validateNom);
        document.getElementById('tel_passager').addEventListener('input', validateTel);
        document.getElementById('nbr_places_reservees').addEventListener('input', validateNombrePlaces);
        document.getElementById('moyen_paiement').addEventListener('change', validateMoyenPaiement);

        document.getElementById('tel_passager').addEventListener('input', function() {
            const phoneRegex = /^[0-9]{0,8}$/;
            if (!phoneRegex.test(this.value)) {
                this.value = this.value.slice(0, -1);
            }
        });

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
            <?php if (!empty($successMessage)): ?>
                showSuccessPopup();
            <?php elseif (!empty($errorMessages)): ?>
                showErrorPopup();
            <?php endif; ?>
        });
    </script>
</body>
</html>