<?php
// clickngo/view/EditAnnonce.php
session_start();

require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$errorMessages = [];
$successMessage = '';
$annonce = null;
$showSuccessPopup = false;
$showErrorPopup = false;

try {
    $pdo = config::getConnexion();
    $controller = new AnnonceCovoiturageController($pdo);

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id > 0) {
        $annonce = $controller->getAnnonceById($id);
        if (!$annonce) {
            throw new Exception("Annonce non trouvée");
        }
    } else {
        throw new Exception("ID d'annonce invalide");
    }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    error_log("Données POST reçues : " . print_r($_POST, true));

    // Filtrer et reformater les données
    $filteredData = [
        'id_conducteur' => (int)($_POST['id_conducteur'] ?? $id),
        'prenom_conducteur' => $_POST['prenom_conducteur'] ?? '',
        'nom_conducteur' => $_POST['nom_conducteur'] ?? '',
        'tel_conducteur' => $_POST['tel_conducteur'] ?? '',
        'date_depart' => date('Y-m-d H:i:s', strtotime($_POST['date_depart'] ?? '')),
        'lieu_depart' => $_POST['lieu_depart'] ?? '',
        'lieu_arrivee' => $_POST['lieu_arrivee'] ?? '',
        'nombre_places' => (int)($_POST['nombre_places'] ?? 0),
        'type_voiture' => $_POST['type_voiture'] ?? '',
        'prix_estime' => (float)($_POST['prix_estime'] ?? 0),
        'description' => $_POST['description'] ?? ''
    ];
    error_log("Données filtrées : " . print_r($filteredData, true));

    // Vérifier si id_conducteur est valide
    if ($filteredData['id_conducteur'] <= 0) {
        error_log("ID Conducteur invalide dans POST : " . $filteredData['id_conducteur']);
        throw new Exception("ID de l'annonce invalide");
    }

    try {
        $success = $controller->updateAnnonce($filteredData);
        if ($success) {
            $_SESSION['success_message'] = "Annonce mise à jour avec succès!";
            $showSuccessPopup = true;
            header('Location: ListConducteurs.php');
            exit();
        } else {
            throw new Exception("Échec de la mise à jour de l'annonce");
        }
    } catch (Exception $e) {
        $errorMessages = explode('<br>', $e->getMessage());
        $showErrorPopup = true;
        error_log("Erreur dans updateAnnonce : " . $e->getMessage() . "\nTrace : " . $e->getTraceAsString());
    }
}
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
    $showErrorPopup = true;
    error_log("Erreur générale : " . $e->getMessage() . "\nTrace : " . $e->getTraceAsString());
}

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
    <title>Modifier une annonce - Click'N'go</title>
    <!-- Leaflet CSS and JS for OpenStreetMap -->
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

        /* Main content */
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

        .preview img {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            z-index: 1;
            border: 2px solid #fff;
        }

        .preview img:hover {
            transform: scale(1.08) rotate(1deg);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
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
        .location-form input[type="datetime-local"],
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
        }

        .register-btn {
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: 0.5s;
        }

        .register-btn:hover::before {
            left: 100%;
        }

        .register-btn:hover {
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

        /* Popup Styles */
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

        /* Map Modal Styles */
        #mapModal {
            position: fixed;
            top: 10%;
            left: 10%;
            width: 80%;
            height: 80%;
            z-index: 9999;
            background: white;
            border: 2px solid #ccc;
            border-radius: 8px;
            display: none;
        }

        #map {
            width: 100%;
            height: 100%;
            border-radius: 8px;
        }

        #mapSearchInput {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 200px;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            z-index: 1000;
        }

        #mapSearchSuggestions {
            position: absolute;
            top: 30px;
            left: 8px;
            width: 200px;
            max-height: 16px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            z-index: 1000;
            display: none;
        }

        #mapSearchSuggestions div {
            border-bottom: 1px solid #eee;
            padding: 6px;
            cursor: pointer;
        }

        #mapSearchSuggestions div:hover {
            background: #f0f0f0;
        }

        #mapSearchSuggestions div:last-child {
            border-bottom: none;
        }

        #mapSearchInput:focus + #mapSearchSuggestions,
        #mapSearchSuggestions:hover {
            display: block;
        }

        #loadingSpinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10000;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #ff8fa3;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .close-map-button {
            background: #ff6666;
            color: white;
            width: 25px;
            height: 25px;
            text-align: center;
            line-height: 25px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 0 4px rgba(0,0,0,0.3);
            z-index: 1000;
            margin-right: 8px;
            margin-top: 8px;
        }

        .close-map-button:hover {
            background: #e55a5a;
            transform: scale(1.1);
            transition: all 0.2s ease;
        }

        .location-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .location-icon {
            position: absolute;
            right: 8px;
            cursor: pointer;
            font-size: 18px;
            color: #ff8fa3;
        }

        .price-suggestion {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .comfort-option {
            display: flex;
            align-items: center;
            margin-top: 8px;
        }

        .comfort-option label {
            margin: 0 0 0 8px;
            font-size: 14px;
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

            .preview img {
                width: 150px;
                height: 150px;
            }

            #mapModal {
                top: 5%;
                left: 5%;
                width: 90%;
                height: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <div class="preview">
                <img src="369239694_e90328ae-f885-42c1-a65b-731ecc49cc7d.jpg" alt="Car Image">
                <h3>Aperçu de l'annonce</h3>
                <div class="price" id="preview-prix_estime"><?php echo $annonce ? htmlspecialchars($annonce->getPrixEstime()) . ' TND' : '-'; ?></div>
                <link rel="stylesheet" href="/clickngo/public/css/style.css">
            </div>
            <div class="location-form">
                <h2>Modifier un covoiturage</h2>
                <?php if ($annonce): ?>
                <form method="post" action="" id="conducteurForm">
                    <input type="hidden" name="id_conducteur" value="<?php echo htmlspecialchars($annonce->getIdConducteur()); ?>">
                    
                    <label for="prenom_conducteur">Prénom Conducteur</label>
                    <input type="text" name="prenom_conducteur" id="prenom_conducteur" value="<?php echo htmlspecialchars($annonce->getPrenomConducteur()); ?>" required>
                    <div class="error" id="prenom_conducteur-error">Prénom invalide (minimum 3 caractères alphabétiques)</div>

                    <label for="nom_conducteur">Nom Conducteur</label>
                    <input type="text" name="nom_conducteur" id="nom_conducteur" value="<?php echo htmlspecialchars($annonce->getNomConducteur()); ?>" required>
                    <div class="error" id="nom_conducteur-error">Nom invalide (minimum 3 caractères alphabétiques)</div>

                    <label for="tel_conducteur">Téléphone Conducteur</label>
                    <input type="tel" name="tel_conducteur" id="tel_conducteur" value="<?php echo htmlspecialchars($annonce->getTelConducteur()); ?>" required>
                    <div class="error" id="tel_conducteur-error">Numéro de téléphone invalide (8 chiffres requis)</div>

                    <label for="type_voiture">Type de Voiture</label>
                    <input type="text" name="type_voiture" id="type_voiture" value="<?php echo htmlspecialchars($annonce->getTypeVoiture()); ?>" required>
                    <div class="error" id="type_voiture-error">Type de voiture invalide (minimum 2 caractères alphabétiques)</div>

                    <label for="lieu_depart">Lieu de Départ</label>
                    <div class="location-input-container">
                        <input type="text" name="lieu_depart" id="lieu_depart" value="<?php echo htmlspecialchars($annonce->getLieuDepart()); ?>" required readonly>
                        <input type="hidden" name="lieu_depart_coords" id="lieu_depart_coords" value="<?php echo method_exists($annonce, 'getLieuDepartCoords') ? htmlspecialchars($annonce->getLieuDepartCoords()) : ''; ?>">
                        <span class="location-icon" onclick="showMap('lieu_depart')">📍</span>
                    </div>
                    <div class="error" id="lieu_depart-error">Le lieu de départ est requis (minimum 2 caractères)</div>

                    <label for="lieu_arrivee">Lieu d'Arrivée</label>
                    <div class="location-input-container">
                        <input type="text" name="lieu_arrivee" id="lieu_arrivee" value="<?php echo htmlspecialchars($annonce->getLieuArrivee()); ?>" required readonly>
                        <input type="hidden" name="lieu_arrivee_coords" id="lieu_arrivee_coords" value="<?php echo method_exists($annonce, 'getLieuArriveeCoords') ? htmlspecialchars($annonce->getLieuArriveeCoords()) : ''; ?>">
                        <span class="location-icon" onclick="showMap('lieu_arrivee')">📍</span>
                    </div>
                    <div class="error" id="lieu_arrivee-error">Le lieu d'arrivée est requis (minimum 2 caractères)</div>

                    <label for="date_depart">Date de Départ</label>
                    <input type="datetime-local" name="date_depart" id="date_depart" value="<?php echo htmlspecialchars($annonce->getDateDepart()->format('Y-m-d\TH:i')); ?>" required>
                    <div class="error" id="date_depart-error">La date de départ doit être dans le futur</div>

                    <label for="nombre_places">Nombre de Places</label>
                    <input type="number" name="nombre_places" id="nombre_places" min="1" max="4" value="<?php echo htmlspecialchars($annonce->getNombrePlaces()); ?>" required>
                    <div class="error" id="nombre_places-error">Nombre de places invalide (1-4)</div>

                    <label for="prix_estime">Prix Estimé (TND)</label>
                    <input type="number" name="prix_estime" id="prix_estime" step="0.01" value="<?php echo htmlspecialchars($annonce->getPrixEstime()); ?>" required>
                    <div class="error" id="prix_estime-error">Prix estimé invalide (doit être positif)</div>
                    <div class="price-suggestion" id="price-suggestion">Prix suggéré : - TND</div>

                    <div class="comfort-option">
                        <input type="checkbox" name="comfort_extra" id="comfort_extra" <?php echo method_exists($annonce, 'getComfortExtra') && $annonce->getComfortExtra() ? 'checked' : ''; ?>>
                        <label for="comfort_extra">Confort supplémentaire (+5 TND)</label>
                    </div>

                    <label for="description">Description</label>
                    <textarea name="description" id="description" placeholder="Description (optionnel)"><?php echo htmlspecialchars($annonce->getDescription()); ?></textarea>

                    <button type="submit" name="submit" class="register-btn">Mettre à jour</button>
                    
                    <a href="ListConducteurs.php" style="display: block; text-align: center; margin-top: 20px; color: #c084fc; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    


    <!-- Success Popup -->
    <div class="popup" id="successPopup">
        <div class="icon">✔</div>
        <h3>Succès!</h3>
        <p id="successMessage"><?php echo htmlspecialchars($successMessage); ?></p>
        <button onclick="redirectToDisplay()">Fermer</button>
    </div>

    <!-- Error Popup -->
    <div class="popup error" id="errorPopup">
        <div class="icon">❌</div>
        <h3>Erreur</h3>
        <p id="errorMessage"><?php echo !empty($errorMessages) ? htmlspecialchars(implode('<br>', $errorMessages)) : ''; ?></p>
        <button onclick="closePopup()">Fermer</button>
    </div>

    <!-- Map Modal -->
    <div id="mapModal">
        <input id="mapSearchInput" type="text" placeholder="Rechercher une ville en Tunisie">
        <div id="mapSearchSuggestions"></div>
        <div id="map"></div>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner"></div>

    <script>
        let map;
        let marker;
        let locationMarker;
        let currentInputId = null;

        // Initialize the map
        function initializeMap() {
            const tunisiaBounds = [
                [30.2, 7.5], // Southwest corner
                [37.4, 11.6]  // Northeast corner
            ];

            map = L.map('map', {
                center: [36.8065, 10.1815], // Tunis by default
                zoom: 7,
                maxBounds: tunisiaBounds,
                maxBoundsViscosity: 1.0,
                minZoom: 6,
                maxZoom: 12
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Custom red pin icon
            const redPin = L.icon({
                iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                shadowSize: [41, 41]
            });

            // Comprehensive list of Tunisian cities and towns
            const tunisianLocations = [
                {name: "Tunis", lat: 36.8065, lng: 10.1815},
                {name: "La Marsa", lat: 36.8782, lng: 10.3247},
                {name: "Sidi Bou Saïd", lat: 36.8687, lng: 10.3416},
                {name: "Carthage", lat: 36.8545, lng: 10.3306},
                {name: "Le Bardo", lat: 36.8092, lng: 10.1333},
                {name: "El Mourouj", lat: 36.7178, lng: 10.2247},
                {name: "Ezzahra", lat: 36.6976, lng: 10.3081},
                {name: "Hammam Lif", lat: 36.7333, lng: 10.3333},
                {name: "Radès", lat: 36.7667, lng: 10.2833},
                {name: "Kram", lat: 36.8376, lng: 10.3173},
                {name: "La Goulette", lat: 36.8181, lng: 10.3050},
                {name: "Mégrine", lat: 36.7709, lng: 10.2369},
                {name: "Ben Arous", lat: 36.7435, lng: 10.2319},
                {name: "Ariana", lat: 36.8665, lng: 10.1647},
                {name: "Raoued", lat: 36.9535, lng: 10.1919},
                {name: "Mnihla", lat: 36.8791, lng: 10.1196},
                {name: "Kalaat el-Andalous", lat: 37.0631, lng: 10.1117},
                {name: "Manouba", lat: 36.8081, lng: 10.0972},
                {name: "Den Den", lat: 36.8028, lng: 10.1167},
                {name: "Oued Ellil", lat: 36.8333, lng: 10.0667},
                {name: "Tebourba", lat: 36.8333, lng: 9.8333},
                {name: "Mornag", lat: 36.6833, lng: 10.2833},
                {name: "Khalidia", lat: 36.7167, lng: 10.2667},
                {name: "Fouchana", lat: 36.7000, lng: 10.1667},
                {name: "Hammam Chott", lat: 36.7167, lng: 10.3667},
                {name: "Sfax", lat: 34.7406, lng: 10.7603},
                {name: "Sakiet Ezzit", lat: 34.8000, lng: 10.7667},
                {name: "Sakiet Eddaïer", lat: 34.7833, lng: 10.7833},
                {name: "Thyna", lat: 34.6833, lng: 10.6833},
                {name: "Gremda", lat: 34.7500, lng: 10.7167},
                {name: "Chaffar", lat: 34.6167, lng: 10.6667},
                {name: "Sousse", lat: 35.8254, lng: 10.6360},
                {name: "Hammam Sousse", lat: 35.8912, lng: 10.5938},
                {name: "Akouda", lat: 35.8667, lng: 10.5667},
                {name: "Kalaâ Kebira", lat: 35.8333, lng: 10.5333},
                {name: "Kalaâ Seghira", lat: 35.8167, lng: 10.6167},
                {name: "Msaken", lat: 35.7333, lng: 10.5833},
                {name: "Ezzouhour", lat: 35.8167, lng: 10.6000},
                {name: "Monastir", lat: 35.7643, lng: 10.8113},
                {name: "Jemmal", lat: 35.6333, lng: 10.7667},
                {name: "Ksar Hellal", lat: 35.6500, lng: 10.9000},
                {name: "Moknine", lat: 35.6333, lng: 10.9667},
                {name: "Sayada", lat: 35.6667, lng: 10.9167},
                {name: "Lamta", lat: 35.6833, lng: 10.8833},
                {name: "Bembla", lat: 35.6667, lng: 10.7833},
                {name: "Mahdia", lat: 35.5047, lng: 11.0622},
                {name: "Chebba", lat: 35.2333, lng: 11.1167},
                {name: "Melloulèche", lat: 35.4667, lng: 11.0333},
                {name: "Ksour Essef", lat: 35.4167, lng: 10.9833},
                {name: "El Jem", lat: 35.3000, lng: 10.7167},
                {name: "Sidi Alouane", lat: 35.3833, lng: 10.9333},
                {name: "Kairouan", lat: 35.6712, lng: 10.1006},
                {name: "Haffouz", lat: 35.6333, lng: 9.6667},
                {name: "Chebika", lat: 35.6167, lng: 9.9333},
                {name: "Sbikha", lat: 35.9333, lng: 10.0167},
                {name: "Oueslatia", lat: 35.8333, lng: 9.5833},
                {name: "Nasrallah", lat: 35.6833, lng: 9.8667},
                {name: "Bizerte", lat: 37.2744, lng: 9.8739},
                {name: "Menzel Bourguiba", lat: 37.1536, lng: 9.7878},
                {name: "Mateur", lat: 37.0405, lng: 9.6655},
                {name: "Ras Jebel", lat: 37.2167, lng: 10.1167},
                {name: "Sejnane", lat: 37.0667, lng: 9.2333},
                {name: "Ghar El Melh", lat: 37.1667, lng: 10.1833},
                {name: "Utique", lat: 37.0833, lng: 10.0333},
                {name: "Nabeul", lat: 36.4561, lng: 10.7376},
                {name: "Hammamet", lat: 36.4000, lng: 10.6167},
                {name: "Korba", lat: 36.5786, lng: 10.8586},
                {name: "Kelibia", lat: 36.8475, lng: 11.0939},
                {name: "Dar Chaabane", lat: 36.4667, lng: 10.7500},
                {name: "Menzel Temime", lat: 36.7833, lng: 10.9833},
                {name: "Grombalia", lat: 36.6000, lng: 10.5000},
                {name: "Soliman", lat: 36.7000, lng: 10.4833},
                {name: "Zaghouan", lat: 36.4029, lng: 10.1429},
                {name: "Zriba", lat: 36.3333, lng: 10.2167},
                {name: "Bir Mcherga", lat: 36.4833, lng: 10.0167},
                {name: "El Fahs", lat: 36.3667, lng: 9.9333},
                {name: "Gabès", lat: 33.8815, lng: 10.0983},
                {name: "El Hamma", lat: 33.8917, lng: 9.7967},
                {name: "Matmata", lat: 33.5333, lng: 9.9667},
                {name: "Métouia", lat: 33.9667, lng: 10.0000},
                {name: "Nouvelle Matmata", lat: 33.5333, lng: 10.0333},
                {name: "Medenine", lat: 33.3549, lng: 10.5055},
                {name: "Zarzis", lat: 33.5040, lng: 11.1122},
                {name: "Ben Gardane", lat: 33.1417, lng: 11.2167},
                {name: "Beni Khedache", lat: 33.2500, lng: 10.2000},
                {name: "Houmt Souk (Djerba)", lat: 33.8667, lng: 10.8500},
                {name: "Midoun (Djerba)", lat: 33.8167, lng: 11.0333},
                {name: "Ajim (Djerba)", lat: 33.7167, lng: 10.9833},
                {name: "Tataouine", lat: 32.9297, lng: 10.4510},
                {name: "Ghomrassen", lat: 33.0333, lng: 10.3167},
                {name: "Remada", lat: 32.3167, lng: 10.4000},
                {name: "Bir Lahmar", lat: 33.1167, lng: 10.4333},
                {name: "Gafsa", lat: 34.4229, lng: 8.7841},
                {name: "Metlaoui", lat: 34.3167, lng: 8.4000},
                {name: "Redeyef", lat: 34.3833, lng: 8.1500},
                {name: "El Ksar", lat: 34.4000, lng: 8.8167},
                {name: "Mdhilla", lat: 34.2667, lng: 8.5667},
                {name: "Tozeur", lat: 33.9197, lng: 8.1335},
                {name: "Nefta", lat: 33.8667, lng: 7.8833},
                {name: "Degache", lat: 33.9833, lng: 8.2000},
                {name: "Tamerza", lat: 34.3833, lng: 7.9500},
                {name: "Kebili", lat: 33.7019, lng: 8.9750},
                {name: "Douz", lat: 33.4667, lng: 9.0167},
                {name: "Souk Lahad", lat: 33.7833, lng: 8.8833},
                {name: "Jemna", lat: 33.5667, lng: 9.0000},
                {name: "Béja", lat: 36.7256, lng: 9.1817},
                {name: "Nefza", lat: 36.9833, lng: 9.0833},
                {name: "Testour", lat: 36.5500, lng: 9.4333},
                {name: "Goubellat", lat: 36.4667, lng: 9.6667},
                {name: "Jendouba", lat: 36.5012, lng: 8.7804},
                {name: "Tabarka", lat: 36.9544, lng: 8.7581},
                {name: "Aïn Draham", lat: 36.7667, lng: 8.7000},
                {name: "Fernana", lat: 36.6667, lng: 8.7000},
                {name: "Boussalem", lat: 36.6167, lng: 8.9667},
                {name: "El Kef", lat: 36.1822, lng: 8.7147},
                {name: "Dahmani", lat: 35.9333, lng: 8.8333},
                {name: "Sakiet Sidi Youssef", lat: 36.2167, lng: 8.6000},
                {name: "Nebeur", lat: 36.2833, lng: 8.7667},
                {name: "Siliana", lat: 36.0849, lng: 9.3708},
                {name: "Makthar", lat: 35.8667, lng: 9.2000},
                {name: "Bou Arada", lat: 36.3500, lng: 9.6167},
                {name: "Gaâfour", lat: 36.3167, lng: 9.7167},
                {name: "Kasserine", lat: 35.1676, lng: 8.8365},
                {name: "Sbeitla", lat: 35.2333, lng: 9.1333},
                {name: "Feriana", lat: 35.0333, lng: 8.5667},
                {name: "Thala", lat: 35.5667, lng: 8.6667},
                {name: "Haidra", lat: 35.5667, lng: 8.3167},
                {name: "Sidi Bouzid", lat: 35.0383, lng: 9.4848},
                {name: "Regueb", lat: 34.8667, lng: 9.7833},
                {name: "Jilma", lat: 35.2667, lng: 9.4333},
                {name: "Menzel Bouzaiane", lat: 34.9333, lng: 9.4167},
                {name: "Mezzouna", lat: 34.5833, lng: 9.8333}
            ];

            // Add close button to map
            const closeButton = L.control({position: 'topright'});
            closeButton.onAdd = function() {
                const div = L.DomUtil.create('div', 'close-map-button');
                div.innerHTML = '×';
                div.title = 'Close map';
                div.onclick = function() {
                    document.getElementById("mapModal").style.display = "none";
                    document.getElementById("loadingSpinner").style.display = "none";
                };
                return div;
            };
            closeButton.addTo(map);

            // Add red pins for all locations
            tunisianLocations.forEach(location => {
                const marker = L.marker([location.lat, location.lng], {
                    icon: redPin,
                    title: location.name
                }).addTo(map);

                marker.on('click', function() {
                    document.getElementById(currentInputId).value = location.name;
                    document.getElementById(currentInputId + '_coords').value = `${location.lat.toFixed(5)}, ${location.lng.toFixed(5)}`;
                    document.getElementById("mapModal").style.display = "none";
                    document.getElementById("loadingSpinner").style.display = "none";
                    suggestPrice();
                });
            });

            const loadingSpinner = document.getElementById("loadingSpinner");
            map.on('tilesloaded', function() {
                loadingSpinner.style.display = "none";
            });

            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                if (lat >= 30.2 && lat <= 37.4 && lng >= 7.5 && lng <= 11.6) {
                    if (marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng], {icon: redPin}).addTo(map);
                    reverseGeocode(lat, lng, currentInputId);
                    document.getElementById("mapModal").style.display = "none";
                    loadingSpinner.style.display = "none";
                } else {
                    alert("Veuillez sélectionner un point en Tunisie.");
                    loadingSpinner.style.display = "none";
                }
            });

            map.fitBounds(tunisiaBounds);
        }

        // Show map for location selection
        function showMap(inputId) {
            if (!['lieu_depart', 'lieu_arrivee'].includes(inputId)) {
                console.error('Invalid inputId:', inputId);
                document.getElementById("loadingSpinner").style.display = "none";
                return;
            }

            currentInputId = inputId;
            const mapModal = document.getElementById("mapModal");
            const loadingSpinner = document.getElementById("loadingSpinner");
            mapModal.style.display = "block";
            loadingSpinner.style.display = "block";

            if (!map) {
                initializeMap();
            } else {
                loadingSpinner.style.display = "none";
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        if (lat >= 30.2 && lat <= 37.4 && lng >= 7.5 && lng <= 11.6) {
                            map.setView([lat, lng], 10);
                            if (locationMarker) map.removeLayer(locationMarker);
                            locationMarker = L.marker([lat, lng], {
                                icon: L.divIcon({
                                    className: 'location-icon',
                                    html: '<i class="fas fa-location-dot fa-2x" style="color:blue;"></i>',
                                    iconSize: [24, 24],
                                    iconAnchor: [12, 24]
                                })
                            }).addTo(map);
                        } else {
                            map.setView([36.8065, 10.1815], 7);
                        }
                        loadingSpinner.style.display = "none";
                    },
                    (error) => {
                        map.setView([36.8065, 10.1815], 7);
                        loadingSpinner.style.display = "none";
                        alert("Impossible d'obtenir votre localisation. Centrage sur Tunis.");
                    }
                );
            } else {
                map.setView([36.8065, 10.1815], 7);
                loadingSpinner.style.display = "none";
            }

            setTimeout(() => {
                loadingSpinner.style.display = "none";
            }, 2000);
        }

        // Reverse geocode to get city name
        function reverseGeocode(lat, lng, inputId) {
            if (!['lieu_depart', 'lieu_arrivee'].includes(inputId)) {
                console.error('Invalid inputId in reverseGeocode:', inputId);
                document.getElementById("loadingSpinner").style.display = "none";
                return;
            }

            const inputElement = document.getElementById(inputId);
            const coordsElement = document.getElementById(inputId + '_coords');
            const loadingSpinner = document.getElementById("loadingSpinner");

            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=10&accept-language=fr`, {
                headers: {
                    'User-Agent': 'ClickNGo/1.0 (contact@yourdomain.com)'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.address && (data.address.city || data.address.town)) {
                    inputElement.value = data.address.city || data.address.town;
                    coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                } else {
                    inputElement.value = 'Ville non trouvée';
                    coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                }
                document.getElementById("mapModal").style.display = "none";
                loadingSpinner.style.display = "none";
                suggestPrice();
            })
            .catch(error => {
                inputElement.value = 'Erreur de recherche';
                coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                loadingSpinner.style.display = "none";
                suggestPrice();
            });
        }

        // Setup autocomplete search
        function setupSearch() {
            const searchInput = document.getElementById('mapSearchInput');
            const suggestions = document.getElementById('mapSearchSuggestions');
            let debounceTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimeout);
                const query = this.value.trim();
                suggestions.innerHTML = '';

                if (query.length < 3) return;

                debounceTimeout = setTimeout(() => {
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=tn&q=${encodeURIComponent(query)}&place=city&accept-language=fr`, {
                        headers: {
                            'User-Agent': 'ClickNGo/1.0 (contact@yourdomain.com)'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        suggestions.innerHTML = '';
                        data.forEach(item => {
                            if (item.type === 'city' || item.type === 'town') {
                                const div = document.createElement('div');
                                div.className = 'p-2 hover:bg-gray-200 cursor-pointer';
                                div.textContent = item.display_name;
                                div.addEventListener('click', () => {
                                    const lat = parseFloat(item.lat);
                                    const lng = parseFloat(item.lon);
                                    map.setView([lat, lng], 10);
                                    if (marker) map.removeLayer(marker);
                                    marker = L.marker([lat, lng]).addTo(map);
                                    reverseGeocode(lat, lng, currentInputId);
                                    document.getElementById("mapModal").style.display = "none";
                                    document.getElementById("loadingSpinner").style.display = "none";
                                    suggestions.innerHTML = '';
                                    searchInput.value = '';
                                    suggestPrice();
                                });
                                suggestions.appendChild(div);
                            }
                        });
                        if (suggestions.innerHTML === '') {
                            suggestions.innerHTML = '<div class="p-2 text-gray-500">Aucune ville trouvée</div>';
                        }
                    })
                    .catch(error => {
                        suggestions.innerHTML = '<div class="p-2 text-red-500">Erreur lors de la recherche</div>';
                    });
                }, 300);
            });

            searchInput.addEventListener('blur', () => {
                setTimeout(() => suggestions.innerHTML = '', 200);
            });

            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim().length >= 3) {
                    suggestions.style.display = 'block';
                }
            });
        }

        // Form elements
        const prixInput = document.getElementById('prix_estime');
        const prixPreview = document.getElementById('preview-prix_estime');
        const successPopup = document.getElementById('successPopup');
        const errorPopup = document.getElementById('errorPopup');
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');

        prixInput.addEventListener('input', () => {
            prixPreview.textContent = prixInput.value ? `${prixInput.value} TND` : '-';
        });

        // Price suggestion logic
        function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
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

        function suggestPrice() {
            const lieuDepart = document.getElementById('lieu_depart').value;
            const lieuArrivee = document.getElementById('lieu_arrivee').value;
            const departCoords = document.getElementById('lieu_depart_coords').value;
            const arriveeCoords = document.getElementById('lieu_arrivee_coords').value;
            const comfortExtra = document.getElementById('comfort_extra').checked;
            const priceSuggestion = document.getElementById('price-suggestion');
            const prixPreview = document.getElementById('preview-prix_estime');

            if (!lieuDepart || !lieuArrivee || !departCoords || !arriveeCoords) {
                priceSuggestion.textContent = 'Prix suggéré : - TND';
                prixPreview.textContent = '-';
                return;
            }

            const [departLat, departLng] = departCoords.split(',').map(coord => parseFloat(coord.trim()));
            const [arriveeLat, arriveeLng] = arriveeCoords.split(',').map(coord => parseFloat(coord.trim()));

            if (isNaN(departLat) || isNaN(departLng) || isNaN(arriveeLat) || isNaN(arriveeLng)) {
                priceSuggestion.textContent = 'Prix suggéré : - TND (coordonnées invalides)';
                prixPreview.textContent = '-';
                return;
            }

            const distanceKm = calculateHaversineDistance(departLat, departLng, arriveeLat, arriveeLng);
            const basePrice = 5.00;
            const perKmRate = 0.10;
            let totalPrice = basePrice + (distanceKm * perKmRate);

            if (comfortExtra) {
                totalPrice += 5.00;
            }

            totalPrice = Math.max(totalPrice, 5.00).toFixed(2);
            priceSuggestion.textContent = `Prix suggéré : ${totalPrice} TND (~${Math.round(distanceKm)} km)`;
            prixPreview.textContent = `${totalPrice} TND`;
            document.getElementById('prix_estime').value = totalPrice;
        }

        // Add event listeners for price suggestion updates
        document.getElementById('lieu_depart').addEventListener('input', suggestPrice);
        document.getElementById('lieu_arrivee').addEventListener('input', suggestPrice);
        document.getElementById('comfort_extra').addEventListener('change', suggestPrice);

        function showSuccessPopup() {
            successPopup.classList.add('show');
            setTimeout(() => {
                redirectToDisplay();
            }, 2000);
        }

        function showErrorPopup() {
            errorPopup.classList.add('show');
        }

        function closePopup() {
            successPopup.classList.remove('show');
            errorPopup.classList.remove('show');
        }

        function redirectToDisplay() {
            window.location.href = 'ListConducteurs.php';
        }

        window.addEventListener('DOMContentLoaded', () => {
            setupSearch();
            
            // Only show popups if they have content and are explicitly set to show
            <?php if (!empty($successMessage) && $showSuccessPopup): ?>
                showSuccessPopup();
            <?php endif; ?>
            
            <?php if (!empty($errorMessages) && $showErrorPopup): ?>
                showErrorPopup();
            <?php endif; ?>
            
            // Initialize price suggestion on page load
            suggestPrice();
        });
    </script>
</body>
</html>