<?php

session_start(); // Must be at the top of the file



// Fonction pour générer une couleur basée sur le nom de l'utilisateur
function stringToColor($str)
{
  // Liste de couleurs inspirées du thème Funbooker (rose, violet, orange, etc.)
  $Colors = [
    '#FF6B6B', // Rose vif
    '#FF8E53', // Orange clair
    '#6B5B95', // Violet moyen
    '#88B04B', // Vert doux
    '#F7CAC9', // Rose pâle
    '#92A8D1', // Bleu pastel
    '#955251', // Rouge bordeaux
    '#B565A7', // Violet rose
    '#DD4124', // Rouge-orange vif
    '#D65076', // Rose foncé
  ];

  // Générer un index déterministe basé sur la chaîne
  $hash = 0;
  for ($i = 0; $i < strlen($str); $i++) {
    $hash = ord($str[$i]) + (($hash << 5) - $hash);
  }

  // Sélectionner une couleur du tableau
  $index = abs($hash) % count($Colors);
  return $Colors[$index];
}


require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$errorMessages = [];
$annonces = [];

try {
    $pdo = config::getConnexion();
    $controller = new AnnonceCovoiturageController($pdo);

    // Récupérer les paramètres de recherche
    $depart = isset($_GET['depart']) ? trim($_GET['depart']) : '';
    $arrivee = isset($_GET['arrivee']) ? trim($_GET['arrivee']) : '';

    // Log the received parameters
    error_log("resultats.php received: depart='$depart', arrivee='$arrivee'");

    if ($depart && $arrivee) {
        // Filtrer les annonces en fonction des lieux de départ et d'arrivée
        $annonces = $controller-> searchAnnonces($depart, $arrivee);
    } else {
        // Si aucun filtre n'est fourni, afficher toutes les annonces
        $annonces = $controller->getAllAnnonces();
    }

    // Log the number of announcements found
    error_log("resultats.php found " . count($annonces) . " announcements");
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
}
try {
    // Query to fetch top 6 drivers (using a placeholder for rating)
    $stmt = $pdo->prepare("SELECT prenom_conducteur, nom_conducteur FROM annonce_covoiturage ORDER BY date_depart DESC LIMIT 6");
    $stmt->execute();
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Simulate rating (replace with actual rating logic if available)
    foreach ($drivers as &$driver) {
        // Placeholder rating (e.g., 4.0 as default; replace with real logic)
        $driver['rating'] = 4.0; // Example: Could use AVG from a reviews table
       
    }
    unset($driver); // Unset reference
} catch (Exception $e) {
    $drivers = [];
    echo '<div class="text-center text-red-500">Erreur lors du chargement des conducteurs: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>click'N'go</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

 
    
    
    <!-- Leaflet CSS et JS pour OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }
.header {
    min-height: 100vh;
    width: 100%;
    /* Overlay très léger (0.1 au lieu de 0.4 et 0.6) */
    background: linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.2)), url('../view/5668988_58246.jpg') center/cover no-repeat;
    color: white;
    position: relative;
    z-index: 1;
}

    /* Fallback si l'image ne se charge pas */
    .header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
       
        z-index: -1;
    }

    /* TOP NAVIGATION */
    nav {
        display: flex;
        align-items: center;
        padding: 40px 7%; /* Increased top padding to create more space */
        z-index: 10;
        position: relative;
    }

    .nav-links-wrapper {
        flex: 1;
        display: flex;
        justify-content: center;
    }

    .nav-links {
        display: flex;
        gap: 25px;
    }

    .nav-links li {
        list-style: none;
    }

    .nav-links a {
        color: white;
        text-decoration: none;
        font-size: 18px;
        position: relative;
        padding-bottom: 5px;
        transition: color 0.3s ease;
    }

    .nav-links a::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #ffa4d6;
        transform: scaleX(0);
        transition: transform 0.3s;
    }

    .nav-links a:hover::after {
        transform: scaleX(1);
    }

    /* LOGO */
    .logo {
        width: 180px;
        cursor: pointer;
    }

    /* MOBILE NAV */
    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        color: white;
        font-size: 26px;
        cursor: pointer;
        z-index: 1001;
    }

    @media (max-width: 768px) {
        .nav-links {
            display: none;
        }
        .mobile-menu-btn {
            display: block;
        }
    }

    /* COVOITURAGE FORM STYLES */
    .covoiturage-section {
        position: absolute;
        top: 60%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        z-index: 5;
        width: 90%;
        max-width: 800px;
        padding-top: 80px; /* Added padding to move the section down */
    }

    .covoiturage-title {
        font-size: 3rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: white;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        letter-spacing: 3px;
    }

    .covoiturage-subtitle {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 2rem;
        color: #ff69b4;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        letter-spacing: 2px;
        font-style: italic; /* Added to match the style in the image */
    }

    .covoiturage-form {
        display: flex;
        gap: 1rem;
        justify-content: center;
        align-items: center;
    }

    .form-input-group {
        display: flex;
        align-items: center;
        background: white;
        border-radius: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: all 0.3s ease;
        min-width: 200px;
    }

    .form-input-group:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }

    .form-input-group .map-icon {
        padding: 12px 15px;
        cursor: pointer;
        color: #be3cf0;
        font-size: 18px;
        transition: all 0.3s ease;
    }

    .form-input-group .map-icon:hover {
        color: #ff6666;
        transform: scale(1.1);
    }

    .form-input-group.arrival .map-icon {
        color: #ff6666;
    }

    .form-input-group.arrival .map-icon:hover {
        color: #be3cf0;
    }

    .form-input-group input {
        border: none;
        padding: 12px 8px;
        font-size: 16px;
        outline: none;
        flex: 1;
        background: transparent;
        color: #333;
        font-weight: 500;
    }

    .form-input-group input::placeholder {
        color: #666;
        font-weight: 400;
    }

    .form-input-group .plane-icon {
        padding: 12px 15px;
        color: #999;
        font-size: 16px;
    }
.search-btn {
    background: linear-gradient(to right, #A29BFE, #FFB1D3);
    color: #1F2937;
    border: none;
    border-radius: 2rem;
    padding: 0.6rem 1.8rem;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 140px;
}

.search-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: all 0.5s ease;
}

.search-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.search-btn:hover::before {
    left: 100%;
}

.search-btn:active {
    transform: scale(1.05);
}

    /* MAP MODAL STYLES */
    #loadingSpinner {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10000;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #be3cf0;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    #mapModal {
        position: fixed;
        top: 10%;
        left: 10%;
        width: 80%;
        height: 80%;
        z-index: 9999;
        background: white;
        border: 2px solid #ccc;
        border-radius: 15px;
        display: none;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }

    .close-map-button {
        background: #ff6666;
        color: white;
        width: 30px;
        height: 30px;
        text-align: center;
        line-height: 30px;
        font-size: 20px;
        font-weight: bold;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 0 5px rgba(0,0,0,0.3);
        z-index: 1000;
        margin-right: 10px;
        margin-top: 10px;
    }

    .close-map-button:hover {
        background: #e55a5a;
        transform: scale(1.1);
        transition: all 0.2s ease;
    }

    #mapSearchInput {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 250px;
        padding: 8px 12px;
        border: 2px solid #be3cf0;
        border-radius: 20px;
        z-index: 1000;
        outline: none;
        font-size: 14px;
    }

    #mapSearchInput:focus {
        border-color: #ff6666;
        box-shadow: 0 0 10px rgba(190, 60, 240, 0.3);
    }

    #mapSearchSuggestions {
        position: absolute;
        top: 45px;
        left: 10px;
        width: 250px;
        max-height: 200px;
        overflow-y: auto;
        background: white;
        border: 1px solid #ddd;
        border-radius: 10px;
        z-index: 1000;
        display: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    #mapSearchSuggestions div {
        border-bottom: 1px solid #eee;
        padding: 10px 12px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #mapSearchSuggestions div:hover {
        background: #f8f9fa;
        color: #be3cf0;
    }

    #mapSearchSuggestions div:last-child {
        border-bottom: none;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .covoiturage-section {
            padding-top: 60px; /* Adjusted padding for smaller screens */
        }

        .covoiturage-title {
            font-size: 2.2rem;
        }
        
        .covoiturage-subtitle {
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
        }
        
        .covoiturage-form {
            flex-direction: column;
            width: 100%;
            gap: 1rem;
        }
        
       .form-input-group {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 2rem;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    min-width: 200px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.form-input-group input {
    border: none;
    padding: 0.6rem 1.8rem 0.6rem 45px;
    font-size: 16px;
    outline: none;
    flex: 1;
    background: transparent;
    color: #333;
    font-weight: 500;
}

.form-input-group .map-icon {
    padding: 0.6rem 15px;
    cursor: pointer;
    color: #be3cf0;
    font-size: 18px;
    transition: all 0.3s ease;
}

.form-input-group .plane-icon {
    padding: 0.6rem 15px;
    color: #999;
    font-size: 16px;
}

        .search-btn {
            width: 100%;
            max-width: 300px;
            justify-content: center;
        }

        #mapModal {
            top: 5%;
            left: 5%;
            width: 90%;
            height: 90%;
        }
    }

    @media (max-width: 480px) {
        .covoiturage-section {
            padding-top: 40px; /* Further adjusted padding for smaller screens */
        }

        .covoiturage-title {
            font-size: 1.8rem;
        }
        
        .covoiturage-subtitle {
            font-size: 1.8rem;
        }

        .form-input-group {
            max-width: 280px;
        }

        .search-btn {
            max-width: 280px;
        }
    }

    /* Debug styles pour vérifier si l'image se charge */
    .header.debug {
        border: 3px solid red;
    }
    
    .header.debug::after {
        content: 'Image de fond chargée !';
        position: absolute;
        top: 10px;
        right: 10px;
        background: green;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
    }
.font-pacifico {
    font-family: 'Pacifico', cursive;
}
.age-section {
    width: 100%;
    max-width: 1024px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.age-title {
    font-size: 1.25rem;
    font-weight: bold;
    color: #1f2937;
    margin-bottom: 1rem;
    text-align: center;
}

.age-cards-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 1rem;
}

.age-card {
    background-color: #f3f4f6;
    border-radius: 0.5rem;
    padding: 1rem;
    width: 6rem;
    text-align: center;
    transform: scale(1);
    transition: transform 0.3s ease, background-color 0.3s ease;
    position: relative;
    cursor: pointer;
}

.age-card:hover {
    transform: scale(1.05);
    background-color: #fce7f3;
}

.age-number {
    color: #ec4899;
    font-size: 2.25rem;
    font-family: 'Pacifico', cursive;
    margin: 0;
    line-height: 1;
}

.age-text {
    color: #374151;
    margin: 0;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Font Pacifico */
.font-pacifico {
    font-family: 'Pacifico', cursive;
}

/* Responsive */
@media (max-width: 768px) {
    .age-section {
        margin: 1rem auto;
    }
    
    .age-card {
        width: 5rem;
        padding: 0.75rem;
    }
    
    .age-number {
        font-size: 1.875rem;
    }
}


/* Section Nos trajets récents */
.recent-trips-section {
    max-width: 1200px;
    margin: 3rem auto;
    padding: 0 2rem;
}

.trips-title {
    font-size: 2rem;
    font-weight: 600;
    color: #be3cf0;
    text-align: center;
    margin-bottom: 2rem;
}

.trip-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    max-width: 350px;
    margin: 0 auto;
    transition: transform 0.3s ease;
}

.trip-card:hover {
    transform: translateY(-5px);
}

.trip-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.trip-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.trip-info {
    padding: 1.5rem;
}

.trip-route {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.trip-details {
    color: #666;
    font-size: 0.9rem;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .recent-trips-section {
        padding: 0 1rem;
        margin: 2rem auto;
    }
    
    .trips-title {
        font-size: 1.5rem;
    }
    
    .trip-card {
        max-width: 100%;
    }
}
/* Import de la police Pacifico */
@import url("https://fonts.googleapis.com/css2?family=Pacifico&display=swap");

/* Style unifié pour TOUS les h2 */
h2,
.age-title,
.trips-title,
.chatbot-title,
#top-conducteurs h2,
#trajets h2 {
  font-family: "Pacifico", cursive !important;
  font-size: 2.25rem !important;
  font-weight: normal !important;
  color:rgb(223, 167, 255) !important;
  text-align: center !important;
  margin-bottom: 2rem !important;
  line-height: 1.2 !important;
}

/* Animation pour les titres (optionnelle) */
h2.animate-title,
.chatbot-title {
  opacity: 0;
  transform: translateY(-20px);
  animation: bounceIn 0.8s ease-out forwards;
}

@keyframes bounceIn {
  0% {
    opacity: 0;
    transform: translateY(-20px);
  }
  60% {
    opacity: 1;
    transform: translateY(5px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive pour mobile */
@media (max-width: 768px) {
  h2,
  .age-title,
  .trips-title,
  .chatbot-title,
  #top-conducteurs h2,
  #trajets h2 {
    font-size: 1.875rem !important;
  }
}

@media (max-width: 480px) {
  h2,
  .age-title,
  .trips-title,
  .chatbot-title,
  #top-conducteurs h2,
  #trajets h2 {
    font-size: 1.5rem !important;
  }

}/* Menu déroulant */
.dropdown {
    position: relative;
}

.dropdown-toggle {
    display: flex;
    align-items: center;
    gap: 5px;
}

.dropdown-toggle i {
    font-size: 12px;
    transition: transform 0.3s ease;
}

.dropdown:hover .dropdown-toggle i {
    transform: rotate(180deg);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    border-radius: 12px;
    min-width: 250px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-15px);
    transition: all 0.4s ease;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    list-style: none;
    padding: 15px 0;
    margin: 0;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.dropdown:hover .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-menu li {
    margin: 0;
    width: 100%;
}

.dropdown-menu a {
    display: block;
    padding: 15px 25px;
    color: rgba(255, 255, 255, 0.9) !important;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    font-weight: 500;
    letter-spacing: 0.5px;
    width: 100%;
    box-sizing: border-box;
}

.dropdown-menu a:hover {
    background: rgba(138, 43, 226, 0.3);
    border-left: 3px solid #8a2be2;
    padding-left: 30px;
    color: #ffffff !important;
    transform: translateX(5px);
}

.dropdown-menu a::after {
    display: none;
}

/* Effet de brillance au survol */
.dropdown-menu a:hover::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(138, 43, 226, 0.1), transparent);
    animation: shine 0.6s ease-in-out;
}

@keyframes shine {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
</style>

<body>




     <script>


  function hideMessage() {
    const botMessage = document.getElementById("bot-message");
    botMessage.style.display = "none";
  }

  const navMenuLinks = document.querySelectorAll(".nav-links a");

  navMenuLinks.forEach(link => {
    link.addEventListener("mouseenter", () => {
      const section = link.textContent.trim().toLowerCase();
      let message = "";

      switch (section) {
        case "accueil":
          message = "Bienvenue chez ClickNgo 🏠";
          break;
        case "activités":
          message = "Prêt pour l’aventure ? Découvre nos activités ! 🧗";
          break;
        case "événements":
          message = "Ne rate pas nos événements exclusifs 🎉";
          break;
        case "produits":
          message = "Regarde ce qu'on a en boutique ! 🛍️";
          break;
        case "transports":
          message = "Besoin d’un covoiturage ? On a ce qu’il faut 🚗";
          break;
        case "sponsors":
          message = "Un grand merci à nos sponsors 🤝";
          break;
        default:
          message = `Tu veux explorer "${section}" ? 🤖`;
      }

      showMessage(message);
    });

    link.addEventListener("mouseleave", hideMessage);

    link.addEventListener("click", (e) => {
      e.preventDefault();
      const url = link.getAttribute("href");
      showMessage("Super choix ! On y va... 🚀");

      setTimeout(() => {
        window.location.href = url;
      }, 1000);
    });
  });
</script>

  
  <!-- Face-api.js pour la détection des émotions -->
  <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

  <!-- Spline pour le robot 3D -->
  <script type="module" src="https://unpkg.com/@splinetool/viewer@1.10.2/build/spline-viewer.js"></script>

  <!-- STYLES -->
  <style>

    spline-viewer {
      position: fixed;
      bottom: 20px;
      left: 20px;
      width: 300px;
      height: 300px;
      pointer-events: none;
      z-index: 1000;
      background: transparent !important;
    }

    #search1, #exploreBtn {
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
      font-size: 16px;
      z-index: 2000;
      border-radius: 25px;
    }

    #search1 {
      top: 40px;
      padding: 12px 20px;
      border: 1px solid #ccc;
      width: 300px;
    }

    #exploreBtn {
      top: 100px;
      padding: 12px 24px;
      background: #ff66cc;
      color: white;
      border: none;
      cursor: pointer;
    }

    .bubble {
      position: fixed;
      bottom: 160px;
      left: 150px;
      background: #fff;
      padding: 14px 20px;
      border-radius: 22px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      font-size: 16px;
      max-width: 260px;
      opacity: 0;
      transition: opacity 0.3s ease;
      z-index: 1100;
      color: #333;
      font-weight: 500;
    }

    .bubble.show {
      opacity: 1;
      animation: bubblePop 0.3s ease;
    }

    @keyframes bubblePop {
      0% { transform: scale(0.8); opacity: 0; }
      60% { transform: scale(1.05); opacity: 1; }
      100% { transform: scale(1); }
    }

    .bubble::after {
      content: '';
      position: absolute;
      left: 30px;
      bottom: -5px;
      border: 15px solid transparent;
      border-top-color: #fff;
      border-bottom: 0;
      rotate: 160deg;
    }

    #webcam {
      position: fixed;
      top: 20px;
      right: 20px;
      width: 160px;
      height: 120px;
      border: 2px solid #ccc;
      border-radius: 8px;
      z-index: 1500;
      display: none;
    }

    #scanMoodBtn, #toggleVoice {
      position: fixed;
      z-index: 1500;
      font-size: 14px;
      font-weight: bold;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    #scanMoodBtn {
      bottom: 40px;
      left: 160px;
      padding: 10px 18px;
      background: linear-gradient(135deg, #d36fff, #ff66cc);
      color: white;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    #scanMoodBtn:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 16px rgba(0,0,0,0.3);
    }

    #toggleVoice {
      bottom: 40px;
      left: 30px;
      background: white;
      border: 2px solid #ff66cc;
      color: #ff66cc;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
    }

    #toggleVoice.active {
      background: #ff66cc;
      color: white;
    }

    #toggleVoice:hover {
      transform: scale(1.1);
    }
  </style>

  <!-- Interface utilisateur -->
<input type="text" id="search1" placeholder="Recherche..." style="display: none;">
<button id="exploreBtn" style="display: none;">Voir les activités</button>

<!-- Interface utilisateur -->
<div style="text-align: center;">
  <button id="scanMoodBtn">Scanner mon humeur</button>

</div>

<script>
  let hasShownScrollMessage = false;

  window.addEventListener("scroll", () => {
    if (!hasShownScrollMessage && window.scrollY > 100) {
      showMessage("Tu explores notre monde ? Fais-moi signe si tu veux de l’aide 🤖📜");
      hasShownScrollMessage = true;

      setTimeout(hideMessage, 6000); // Masquer après 6 secondes
    }
  });
</script>


<script>




  const moodBtn = document.getElementById('scanMoodBtn');

  moodBtn.addEventListener('mouseenter', () => {
    showMessage("Envie de connaître ton humeur ? Clique ici ! 😊");
  });

  moodBtn.addEventListener('mouseleave', hideMessage);

  moodBtn.addEventListener('click', () => {
    showMessage("Analyse de ton humeur en cours... 🤖");

    // Affiche la vidéo si ce n'est pas déjà visible
    const video = document.getElementById('videoInput');
    video.style.display = 'block';

    // Lance la détection
    startCameraAndDetect(); // Cette fonction doit être définie ailleurs
  });
</script>




  <video id="webcam" autoplay muted></video>
<script>
window.addEventListener("DOMContentLoaded", () => {
  // Vérifie si le message de bienvenue a déjà été montré
  if (!sessionStorage.getItem("welcomeShown")) {
    const welcomeMessage = "👋 Bienvenue chez Click’n’Go ! Je suis ClickBot, ton assistant virtuel 🤖";
    showMessage(welcomeMessage);

    // Cache le message après 5 secondes
    setTimeout(hideMessage, 5000);

    // Marque comme déjà affiché
    sessionStorage.setItem("welcomeShown", "true");
  }
});
</script>


  <!-- Robot 3D -->
  <spline-viewer id="robot" url="https://prod.spline.design/IWzKfSYNM8KCQbZm/scene.splinecode" alpha="true"></spline-viewer>

  <!-- Message bulle -->
  <div class="bubble" id="robotMessage">Tu cherches quoi ? 😊</div>

  <!-- Bouton voix -->
  <button id="toggleVoice" title="Activer/désactiver la voix">🔊</button>

  <script>
  const voiceBtn = document.getElementById('toggleVoice');

  voiceBtn.addEventListener('mouseenter', () => {
    showMessage("Clique pour activer ou désactiver la voix 🔈🗣️");
  });

  voiceBtn.addEventListener('mouseleave', hideMessage);

  voiceBtn.addEventListener('click', () => {
    showMessage("Changement de l'état de la voix... 🎧");
    toggleVoice(); // Appelle ta fonction pour activer/désactiver la voix
  });
</script>


  <!-- SCRIPT JS -->
  <script>
    let voiceEnabled = false;
    const toggleVoiceBtn = document.getElementById('toggleVoice');

    toggleVoiceBtn.addEventListener('click', () => {
      voiceEnabled = !voiceEnabled;
      toggleVoiceBtn.classList.toggle('active');
    });

    function speak(text) {
      if (!voiceEnabled || !window.speechSynthesis) return;
      const utterance = new SpeechSynthesisUtterance(text);
      utterance.lang = 'fr-FR';
      utterance.rate = 1;
      utterance.pitch = 1;
      window.speechSynthesis.cancel();
      window.speechSynthesis.speak(utterance);
    }

    const viewer = document.getElementById('robot');
    const message = document.getElementById('robotMessage');
    const searchInput = document.getElementById('search1');
    const exploreBtn = document.getElementById('exploreBtn');
    const webcam = document.getElementById('webcam');
    const scanMoodBtn = document.getElementById('scanMoodBtn');

    let currentMood = null;
    let detectionInterval = null;
    let mediaStream = null;

    // Bouton arrêt du scan
    const stopBtn = document.createElement('button');
    stopBtn.textContent = "Arrêter le scan";
    Object.assign(stopBtn.style, {
      position: "fixed",
      bottom: "40px",
      left: "160px",
      padding: "10px 18px",
      background: "linear-gradient(135deg, #ff4d6d, #ff66cc)",
      color: "white",
      fontWeight: "bold",
      border: "none",
      borderRadius: "20px",
      boxShadow: "0 4px 12px rgba(0,0,0,0.2)",
      cursor: "pointer",
      zIndex: "1500",
      fontSize: "14px",
      transition: "transform 0.2s, box-shadow 0.2s",
      display: "none"
    });

    stopBtn.addEventListener("mouseenter", () => {
      stopBtn.style.transform = "scale(1.05)";
      stopBtn.style.boxShadow = "0 6px 16px rgba(0,0,0,0.3)";
    });
    stopBtn.addEventListener("mouseleave", () => {
      stopBtn.style.transform = "scale(1)";
      stopBtn.style.boxShadow = "0 4px 12px rgba(0,0,0,0.2)";
    });

    document.body.appendChild(stopBtn);

    viewer.addEventListener('load', () => {
      let head = null;
      try { head = viewer.scene.getObjectByName('Head'); } catch {}
      window.addEventListener('mousemove', (e) => {
        if (!head) return;
        const x = (e.clientX / window.innerWidth - 0.5) * 2;
        const y = (e.clientY / window.innerHeight - 0.5) * -2;
        head.rotation.y = x * 0.5;
        head.rotation.x = y * 0.5;
      });
    });

function showMessage(txt) {
  message.textContent = txt;
  message.classList.add("show");
  speak(txt);

  // Supprimer le message après 3 secondes (3000 millisecondes)
  setTimeout(() => {
    message.classList.remove("show");
  }, 4000);
}


    function hideMessage() {
      message.classList.remove("show");
    }

    searchInput.addEventListener('focus', () => showMessage("Dis-moi ce que tu veux trouver 😉"));
    searchInput.addEventListener('input', () => {
      const val = searchInput.value.trim();
      showMessage(val ? `Hmm… "${val}" ? 🔍` : "Tape quelque chose 👀");
    });
    searchInput.addEventListener('blur', hideMessage);

    exploreBtn.addEventListener('mouseenter', () => showMessage(`Tu veux "${exploreBtn.textContent}" ? 😎`));
    exploreBtn.addEventListener('mouseleave', hideMessage);
    exploreBtn.addEventListener('click', () => {
      showMessage("D'accord, je t'y emmène ! 🛸");
    });

    scanMoodBtn.addEventListener('click', async () => {
      try {
        scanMoodBtn.style.display = 'none';
        stopBtn.style.display = 'block';
        await initFaceDetection();
      } catch {
        showMessage("Je ne peux pas accéder à ta caméra 😢");
        scanMoodBtn.style.display = 'block';
        stopBtn.style.display = 'none';
      }
    });

    stopBtn.addEventListener('click', () => {
      if (mediaStream) mediaStream.getTracks().forEach(track => track.stop());
      clearInterval(detectionInterval);
      webcam.style.display = 'none';
      scanMoodBtn.style.display = 'block';
      stopBtn.style.display = 'none';
      currentMood = null;
      showMessage("Scan arrêté 👋");
      setTimeout(hideMessage, 3000);
    });

    async function initFaceDetection() {
      try {
        mediaStream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
        webcam.srcObject = mediaStream;
        webcam.style.display = 'block';

        await faceapi.nets.tinyFaceDetector.loadFromUri('https://justadudewhohacks.github.io/face-api.js/models');
        await faceapi.nets.faceExpressionNet.loadFromUri('https://justadudewhohacks.github.io/face-api.js/models');

        detectExpression();
      } catch (error) {
        showMessage("Permission refusée 😢 Active ta caméra !");
        throw error;
      }
    }

    function detectExpression() {
      detectionInterval = setInterval(async () => {
        if (!webcam.srcObject) return;
        const detection = await faceapi.detectSingleFace(webcam, new faceapi.TinyFaceDetectorOptions()).withFaceExpressions();
        if (!detection) {
          showMessage("On dirait que tu es metghachech 😶 Retire le cache pour que je t'aide !");
          return;
        }

        const sorted = Object.entries(detection.expressions).sort((a, b) => b[1] - a[1]);
        const expr = sorted[0][0];
        if (expr !== currentMood) {
          currentMood = expr;
          reactToEmotion(expr);
        }
      }, 3500);
    }

    function reactToEmotion(expr) {
      const activities = {
        happy: "🎉 Viens essayer notre atelier de danse ou un concert live !",
        sad: "🧘‍♂️ Essaie une session de méditation ou une balade en forêt",
        angry: "🥊 Viens faire un peu de boxe ou du sport pour te défouler",
        fearful: "🛶 Et si on faisait un tour en kayak paisible ?",
        surprised: "🎭 Tu vas adorer notre pièce de théâtre comique !",
        disgusted: "🍫 Viens goûter nos desserts maison pour te changer les idées 😋",
        neutral: "🎨 Atelier créatif ou expo photo ? Tu choisis !"
      };

      const reaction = {
        happy: "Tu souris ! On va faire la fête ? 🎉",
        sad: "Hmm... tu as l'air triste. Je te fais un câlin virtuel 🤗",
        neutral: "Tu as l'air concentré. Besoin d'aide ? 🤔",
        surprised: "Oh, surpris ? Viens découvrir une surprise ! 🎁",
        angry: "Prends une grande respiration... tout va bien se passer 🌿",
        disgusted: "Quelque chose te dégoûte ? Changeons de sujet ! 🌈",
        fearful: "N'aie pas peur, je suis là pour toi 🛡️"
      };

      showMessage(reaction[expr] + "\n\n" + activities[expr]);
      setTimeout(hideMessage, 7000);
    }
  </script>









    <!-- Indicateur de chargement -->
    <div id="loadingSpinner"></div>

    <div class="header">
        <nav>
            <img src="images/logo.png" class="logo">
            <div class="nav-links-wrapper">
<style>
/* Style de base */
.nav-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 20px;
}

.nav-links li {
    position: relative;
}

.nav-links a {
    display: block;
    padding: 10px 15px;
    text-decoration: none;
    color: #333;
}

/* Menu déroulant */
.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background-color: transparent;
    min-width: 180px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

.dropdown-menu li a {
    padding: 10px;
    color: #333;
}

.dropdown:hover .dropdown-menu {
    display: block;
}

/* Optionnel : flèche avec Font Awesome */
.dropdown-toggle i {
    margin-left: 5px;
}
</style>

<ul class="nav-links">
    <li><a href="/mvcUtilisateur/View/FrontOffice/index.php">Accueil</a></li>
    <li><a href="/mvcact/view/front office/activite.php">Activités</a></li>
    <li><a href="/mvcEvent/View/FrontOffice/evenemant.php">Événements</a></li>
    <li><a href="/mvcProduit/view/front office/produit.php">Produits</a></li>
    
    <li class="dropdown">
        <a href="#" class="dropdown-toggle">
            Transports <i class="fas fa-chevron-down"></i>
        </a>
        <ul class="dropdown-menu">
            <li><a href="AjouterConducteur.php">Proposer un covoiturage</a></li>
            <li><a href="DisplayConducteur.php">Trouver un covoiturage</a></li>
            <li><a href="#top-conducteurs">Top Conducteurs</a></li>
            <li><a href="#faq">Chatbot</a></li>
        </ul>
    </li>

    <li><a href="/mvcSponsor/crud/view/front/index.php">Sponsors</a></li>
</ul>


                

                
      <script>
        function hideMessage() {
          const botMessage = document.getElementById("bot-message");
          botMessage.style.display = "none";
        }

        const menuLinks = document.querySelectorAll(".nav-links a");

        menuLinks.forEach(link => {
          link.addEventListener("mouseenter", () => {
            const section = link.textContent.trim().toLowerCase();
            let message = "";

            switch (section) {
              case "accueil":
                message = "Bienvenue chez ClickNgo 🏠";
                break;
              case "activités":
                message = "Prêt pour l’aventure ? Découvre nos activités ! 🧗";
                break;
              case "événements":
                message = "Ne rate pas nos événements exclusifs 🎉";
                break;
              case "produits":
                message = "Regarde ce qu'on a en boutique ! 🛍️";
                break;
              case "transports":
                message = "Besoin d’un covoiturage ? On a ce qu’il faut 🚗";
                break;
              case "sponsors":
                message = "Un grand merci à nos sponsors 🤝";
                break;
              default:
                message = `Tu veux explorer "${section}" ? 🤖`;
            }

            showMessage(message);
          });

          link.addEventListener("mouseleave", hideMessage);

          link.addEventListener("click", (e) => {
            e.preventDefault();
            const url = link.getAttribute("href");
            showMessage("Super choix ! On y va... 🚀");

            setTimeout(() => {
              window.location.href = url;
            }, 1000);
          });
        });
      </script>


            </div>

                                <!-- Vérification de l'état de connexion -->
<?php if (!isset($_SESSION['user'])): ?>
  <!-- 🔒 Utilisateur non connecté : bouton vers login -->
  <a href="/mvcUtilisateur/View/BackOffice/login/login.php" class="register-btn" title="Connexion/Inscription">
    <i class="fas fa-user"></i>
  </a>
<?php else: ?>
  <!-- 👤 Utilisateur connecté -->
  <div class="user-profile" style="position: relative; display: inline-block;">
    <?php
    $user = $_SESSION['user'];
    $fullName = $user['full_name'] ?? 'U';
    $initial = strtoupper(substr($fullName, 0, 1));
    $profilePicture = $user['profile_picture'] ?? '';
    $verified = isset($user['is_verified']) && $user['is_verified'] == 1;
    ?>

    <?php if (!empty($profilePicture) && file_exists($profilePicture)): ?>
      <img src="<?= htmlspecialchars($profilePicture) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
    <?php else: ?>
      <div class="profile-circle"
        style="background-color: <?= stringToColor($fullName) ?>;"
        onclick="toggleDropdown()">
        <?= $initial ?>
      </div>
    <?php endif; ?>

    <!-- ✅ Badge vérification -->
    <div class="verification-status" style="position: absolute; bottom: -5px; right: -5px;">
      <?php if ($verified): ?>
        <img src="/mvcUtilisateur/assets/icons/verified.png"
          alt="Compte vérifié"
          title="Compte Vérifié"
          style="width: 20px; height: 20px;">
      <?php else: ?>
        <img src="/mvcUtilisateur/assets/icons/not_verified.png"
          alt="Compte non vérifié"
          title="Compte Non Vérifié"
          style="width: 20px; height: 20px; cursor: pointer;"
          onclick="showVerificationPopup()">
      <?php endif; ?>
    </div>

    <!-- Menu déroulant -->
    <div class="dropdown-menu" id="dropdownMenu" style="display: none; position: absolute; top: 120%; right: 0; background-color: white; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 100;">
      <a href="/mvcUtilisateur/View/FrontOffice/profile.php" style="display: block; padding: 10px;">👤 Mon Profil</a>
      <a href="/mvcUtilisateur/View/BackOffice/login/logout.php" style="display: block; padding: 10px;">🚪 Déconnexion</a>
    </div>
  </div>
<?php endif; ?>

        </nav>







<script>
  function toggleDropdown() {
    const menu = document.getElementById("dropdownMenu");
    if (menu) {
      menu.style.display = menu.style.display === "block" ? "none" : "block";
    }
  }

  function showVerificationPopup() {
    Swal.fire({
      title: 'Vérification requise',
      text: 'Veuillez vérifier votre compte via l’email que vous avez reçu.',
      icon: 'info',
      confirmButtonText: 'OK',
      confirmButtonColor: '#6c63ff'
    });
  }

  // Fermer dropdown quand on clique en dehors
  document.addEventListener("click", function (event) {
    const dropdown = document.getElementById("dropdownMenu");
    const profile = document.querySelector(".user-profile");
    if (dropdown && profile && !profile.contains(event.target)) {
      dropdown.style.display = "none";
    }
  });
</script>
<style>
    .user-profile {
      position: relative;
      display: inline-block;
    }

    .profile-photo {
      width: 55px;
      height: 55px;
      border-radius: 50%;
      object-fit: cover;
      cursor: pointer;
      border: 2px solid purple;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    .profile-circle {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
    }



    .user-profile:hover .dropdown-menu {
      display: block;
    }

  </style>























        <!-- COVOITURAGE SECTION -->
        <div class="covoiturage-section">
            <h1 class="covoiturage-title">TROUVEZ</h1>
            <h3 class="covoiturage-subtitle" style="font-style: italic; color: white; text-transform: uppercase; letter-spacing: 2px;">✨ un covoiturage ✨</h3>

            <form action="resultats.php" method="GET" class="covoiturage-form">
                <!-- Adresse de départ -->
                <div class="form-input-group">
                    <span onclick="showMap('depart')" class="map-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </span>
                    <input id="depart" name="depart" placeholder="Départ" type="text" required/>
                    <input id="depart_coords" name="depart_coords" type="hidden"/>
                    <span class="plane-icon">
                        <i class="fas fa-paper-plane"></i>
                    </span>
                </div>

                <!-- Adresse d'arrivée -->
                <div class="form-input-group arrival">
                    <span onclick="showMap('arrivee')" class="map-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </span>
                    <input id="arrivee" name="arrivee" placeholder="Arrivée" type="text" required/>
                    <input id="arrivee_coords" name="arrivee_coords" type="hidden"/>
                    <span class="plane-icon">
                        <i class="fas fa-paper-plane"></i>
                    </span>
                </div>

                <!-- Bouton de recherche -->
                <button type="submit" class="search-btn">
                    <span>Rechercher</span>
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Modal carte -->
    <div id="mapModal">
        <input id="mapSearchInput" type="text" placeholder="Rechercher une ville en Tunisie...">
        <div id="mapSearchSuggestions"></div>
        <div id="map" style="width:100%; height:100%; border-radius:15px;"></div>
    </div>

    <script>
        let map;
        let marker;
        let locationMarker;
        let currentInputId = null;

        // Vérifier si l'image de fond se charge correctement
        window.addEventListener('load', function() {
            const header = document.querySelector('.header');
            const img = new Image();
            img.onload = function() {
                console.log('Image de fond chargée avec succès !');
                header.classList.add('debug');
            };
            img.onerror = function() {
                console.error('Erreur lors du chargement de l\'image de fond');
                console.log('Vérifiez que le fichier existe à : ./view/5668988_58246.jpg');
            };
            img.src = './view/5668988_58246.jpg';
        });

        // Fonction pour initialiser la carte
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

            // List of Tunisian cities and towns
            const tunisianLocations = [
    // Tunis Governorate
    {name: "Tunis", lat: 36.8065, lng: 10.1815},
    {name: "La Marsa", lat: 36.8782, lng: 10.3247},
    {name: "Sidi Bou Saïd", lat: 36.8687, lng: 10.3416},
    {name: "Carthage", lat: 36.8545, lng: 10.3306},
    {name: "Le Bardo", lat: 36.8092, lng: 10.1333},
    {name: "La Goulette", lat: 36.8185, lng: 10.3052},
    {name: "Hammam-Lif", lat: 36.7333, lng: 10.3333},
    {name: "Radès", lat: 36.7667, lng: 10.2833},
    {name: "El Omrane", lat: 36.8167, lng: 10.1667},
    {name: "El Kabaria", lat: 36.8333, lng: 10.2000},
    {name: "Sidi Hassine", lat: 36.8000, lng: 10.1000},
    {name: "Ezzouhour", lat: 36.8167, lng: 10.1333},
    {name: "Sebkhet Sejoumi", lat: 36.7833, lng: 10.1333},

    // Ariana Governorate
    {name: "Ariana", lat: 36.8665, lng: 10.1647},
    {name: "Raoued", lat: 36.9333, lng: 10.1500},
    {name: "Kalâat el-Andalous", lat: 37.0667, lng: 10.1167},
    {name: "Sidi Thabet", lat: 36.9000, lng: 10.0333},
    {name: "La Soukra", lat: 36.8833, lng: 10.2500},
    {name: "Ettadhamen", lat: 36.8500, lng: 10.1000},
    {name: "Mnihla", lat: 36.8500, lng: 10.1667},

    // Ben Arous Governorate
    {name: "Ben Arous", lat: 36.7435, lng: 10.2319},
    {name: "Boumhel", lat: 36.7333, lng: 10.2833},
    {name: "El Mourouj", lat: 36.7333, lng: 10.2000},
    {name: "Ezzahra", lat: 36.7333, lng: 10.2500},
    {name: "Hammam Chott", lat: 36.7000, lng: 10.3000},
    {name: "Mégrine", lat: 36.7667, lng: 10.2333},
    {name: "Mohamedia", lat: 36.6833, lng: 10.1500},
    {name: "Mornag", lat: 36.6833, lng: 10.2833},
    
    // Manouba Governorate
    {name: "Manouba", lat: 36.8081, lng: 10.0972},
    {name: "Borj El Amri", lat: 36.7167, lng: 9.9333},
    {name: "Den Den", lat: 36.8667, lng: 10.0167},
    {name: "Douar Hicher", lat: 36.8333, lng: 10.0500},
    {name: "El Battan", lat: 36.8000, lng: 9.8333},
    {name: "Jedaida", lat: 36.8500, lng: 10.0167},
    {name: "Mornaguia", lat: 36.7500, lng: 10.0167},
    {name: "Oued Ellil", lat: 36.8333, lng: 10.0333},
    {name: "Tebourba", lat: 36.8167, lng: 9.8500},

    // Bizerte Governorate
    {name: "Bizerte", lat: 37.2744, lng: 9.8739},
    {name: "Menzel Bourguiba", lat: 37.1536, lng: 9.7878},
    {name: "Menzel Jemil", lat: 37.2333, lng: 9.9167},
    {name: "Mateur", lat: 37.0500, lng: 9.6667},
    {name: "Ras Jebel", lat: 37.2167, lng: 10.1167},
    {name: "Sejnane", lat: 37.0500, lng: 9.2333},
    {name: "Tinja", lat: 37.1667, lng: 9.7500},
    {name: "Ghar El Melh", lat: 37.1667, lng: 10.1833},
    {name: "Utique", lat: 37.0500, lng: 10.0500},
    {name: "El Alia", lat: 37.1667, lng: 10.0333},
    {name: "Raf Raf", lat: 37.1833, lng: 10.1833},

    // Nabeul Governorate
    {name: "Nabeul", lat: 36.4561, lng: 10.7376},
    {name: "Hammamet", lat: 36.4000, lng: 10.6167},
    {name: "Kelibia", lat: 36.8475, lng: 11.0939},
    {name: "Korba", lat: 36.5786, lng: 10.8586},
    {name: "Menzel Temime", lat: 36.7833, lng: 10.9833},
    {name: "Beni Khalled", lat: 36.6500, lng: 10.6000},
    {name: "Beni Khiar", lat: 36.4667, lng: 10.7833},
    {name: "Bou Argoub", lat: 36.5333, lng: 10.5500},
    {name: "Dar Chaabane", lat: 36.4667, lng: 10.7500},
    {name: "El Haouaria", lat: 37.0500, lng: 11.0167},
    {name: "Soliman", lat: 36.7000, lng: 10.4833},
    {name: "Takelsa", lat: 36.7833, lng: 10.6333},
    {name: "Grombalia", lat: 36.6000, lng: 10.5000},

    // Béja Governorate
    {name: "Béja", lat: 36.7256, lng: 9.1817},
    {name: "Amdoun", lat: 36.8167, lng: 9.1167},
    {name: "Goubellat", lat: 36.5333, lng: 9.6667},
    {name: "Medjez el-Bab", lat: 36.6500, lng: 9.6000},
    {name: "Nefza", lat: 36.9333, lng: 9.0500},
    {name: "Téboursouk", lat: 36.4500, lng: 9.2500},
    {name: "Testour", lat: 36.5500, lng: 9.4500},
    {name: "Thibar", lat: 36.5000, lng: 9.1333},

    // Jendouba Governorate
    {name: "Jendouba", lat: 36.5012, lng: 8.7804},
    {name: "Ain Draham", lat: 36.7667, lng: 8.6833},
    {name: "Balta-Bou Aouane", lat: 36.5833, lng: 8.5000},
    {name: "Bou Salem", lat: 36.6167, lng: 8.9667},
    {name: "Fernana", lat: 36.6500, lng: 8.7000},
    {name: "Ghardimaou", lat: 36.4500, lng: 8.4333},
    {name: "Oued Mliz", lat: 36.4667, lng: 8.5667},
    {name: "Tabarka", lat: 36.9544, lng: 8.7581},

    // Zaghouan Governorate
    {name: "Zaghouan", lat: 36.4029, lng: 10.1429},
    {name: "Bir Mcherga", lat: 36.5000, lng: 10.0667},
    {name: "El Fahs", lat: 36.3667, lng: 9.9000},
    {name: "Nadhour", lat: 36.3167, lng: 10.1500},
    {name: "Saouaf", lat: 36.2833, lng: 10.1167},
    {name: "Zriba", lat: 36.3333, lng: 10.0833},

    // Siliana Governorate
    {name: "Siliana", lat: 36.0849, lng: 9.3708},
    {name: "Bargou", lat: 36.0833, lng: 9.6167},
    {name: "Bou Arada", lat: 36.3500, lng: 9.6167},
    {name: "El Aroussa", lat: 36.2667, lng: 9.4667},
    {name: "Gaâfour", lat: 36.3333, lng: 9.3167},
    {name: "Kesra", lat: 35.8167, lng: 9.3667},
    {name: "Makthar", lat: 35.8500, lng: 9.2000},
    {name: "Rouhia", lat: 35.7667, lng: 9.2833},

    // Le Kef Governorate
    {name: "El Kef", lat: 36.1822, lng: 8.7147},
    {name: "Dahmani", lat: 35.9500, lng: 8.8333},
    {name: "Jérissa", lat: 35.9167, lng: 8.5833},
    {name: "El Ksour", lat: 35.8833, lng: 8.8833},
    {name: "Kalâat Khasba", lat: 35.8167, lng: 8.6667},
    {name: "Kalâat Senan", lat: 35.8167, lng: 8.4667},
    {name: "Nebeur", lat: 36.2833, lng: 8.7667},
    {name: "Sakiet Sidi Youssef", lat: 36.2167, lng: 8.3500},
    {name: "Tajerouine", lat: 35.8833, lng: 8.5500},

    // Sousse Governorate
    {name: "Sousse", lat: 35.8254, lng: 10.6360},
    {name: "Akouda", lat: 35.8667, lng: 10.5667},
    {name: "Bouficha", lat: 36.2667, lng: 10.4500},
    {name: "Enfidha", lat: 36.1333, lng: 10.3833},
    {name: "Hammam Sousse", lat: 35.8500, lng: 10.5833},
    {name: "Hergla", lat: 36.0333, lng: 10.5000},
    {name: "Kalâa Kebira", lat: 35.8667, lng: 10.5333},
    {name: "Kalâa Seghira", lat: 35.8167, lng: 10.5667},
    {name: "Kondar", lat: 35.8833, lng: 10.5833},
    {name: "Msaken", lat: 35.7333, lng: 10.5833},
    {name: "Sidi Bou Ali", lat: 35.9500, lng: 10.4167},
    {name: "Sidi El Hani", lat: 35.6667, lng: 10.3167},

    // Monastir Governorate
    {name: "Monastir", lat: 35.7643, lng: 10.8113},
    {name: "Bekalta", lat: 35.6167, lng: 10.9833},
    {name: "Bembla", lat: 35.6833, lng: 10.8000},
    {name: "Beni Hassen", lat: 35.5667, lng: 10.8167},
    {name: "Jemmal", lat: 35.6333, lng: 10.7667},
    {name: "Ksar Hellal", lat: 35.6500, lng: 10.9000},
    {name: "Ksibet el-Médiouni", lat: 35.6833, lng: 10.8500},
    {name: "Moknine", lat: 35.6333, lng: 10.9667},
    {name: "Ouerdanine", lat: 35.6667, lng: 10.6667},
    {name: "Sayada", lat: 35.6667, lng: 10.8833},
    {name: "Téboulba", lat: 35.6333, lng: 10.9333},
    {name: "Zéramdine", lat: 35.5667, lng: 10.7333},

    // Mahdia Governorate
    {name: "Mahdia", lat: 35.5047, lng: 11.0622},
    {name: "Bou Merdes", lat: 35.3833, lng: 10.9833},
    {name: "Chebba", lat: 35.2333, lng: 11.1167},
    {name: "Chorbane", lat: 35.2833, lng: 10.3833},
    {name: "El Jem", lat: 35.3000, lng: 10.7167},
    {name: "Hbira", lat: 35.5000, lng: 11.0333},
    {name: "Ksour Essef", lat: 35.4167, lng: 10.9833},
    {name: "Melloulèche", lat: 35.1667, lng: 11.0333},
    {name: "Ouled Chamekh", lat: 35.3667, lng: 10.3333},
    {name: "Rejiche", lat: 35.4333, lng: 10.9167},
    {name: "Sidi Alouane", lat: 35.3833, lng: 10.9333},

    // Kairouan Governorate
    {name: "Kairouan", lat: 35.6712, lng: 10.1006},
    {name: "Bou Hajla", lat: 35.6333, lng: 10.1333},
    {name: "Chebika", lat: 35.7667, lng: 9.9667},
    {name: "Echrarda", lat: 35.6333, lng: 9.7667},
    {name: "Haffouz", lat: 35.6333, lng: 9.6667},
    {name: "Hajeb El Ayoun", lat: 35.6833, lng: 9.8000},
    {name: "Nasrallah", lat: 35.3667, lng: 9.8667},
    {name: "Oueslatia", lat: 35.8667, lng: 9.5333},
    {name: "Sbikha", lat: 35.9333, lng: 10.0167},

    // Kasserine Governorate
    {name: "Kasserine", lat: 35.1676, lng: 8.8365},
    {name: "Fériana", lat: 34.9500, lng: 8.5667},
    {name: "Foussana", lat: 35.3333, lng: 8.6167},
    {name: "Haïdra", lat: 35.5667, lng: 8.4667},
    {name: "Jedelienne", lat: 35.2000, lng: 8.7500},
    {name: "Majel Bel Abbès", lat: 35.0833, lng: 8.7500},
    {name: "Sbeïtla", lat: 35.2333, lng: 9.1167},
    {name: "Sbiba", lat: 35.5333, lng: 9.0667},
    {name: "Thala", lat: 35.5667, lng: 8.6667},

    // Sidi Bouzid Governorate
    {name: "Sidi Bouzid", lat: 35.0383, lng: 9.4848},
    {name: "Bir El Hafey", lat: 34.9333, lng: 9.2000},
    {name: "Cebbala", lat: 35.2500, lng: 9.2500},
    {name: "Jilma", lat: 35.2667, lng: 9.4167},
    {name: "Meknassy", lat: 34.9833, lng: 9.5667},
    {name: "Menzel Bouzaiane", lat: 35.1667, lng: 9.4833},
    {name: "Mezzouna", lat: 34.5833, lng: 9.8333},
    {name: "Ouled Haffouz", lat: 35.1667, lng: 9.6333},
    {name: "Regueb", lat: 34.8500, lng: 9.7833},
    {name: "Sidi Ali Ben Aoun", lat: 35.0167, lng: 9.5667},

    // Sfax Governorate
    {name: "Sfax", lat: 34.7406, lng: 10.7603},
    {name: "Agareb", lat: 34.7333, lng: 10.5333},
    {name: "Bir Ali Ben Khalifa", lat: 34.7333, lng: 10.0833},
    {name: "El Amra", lat: 34.6667, lng: 10.5833},
    {name: "El Hencha", lat: 34.4667, lng: 10.4500},
    {name: "Graïba", lat: 34.6500, lng: 10.5000},
    {name: "Jebiniana", lat: 34.6333, lng: 10.7500},
    {name: "Kerkennah Islands", lat: 34.7000, lng: 11.2000},
    {name: "Mahres", lat: 34.5333, lng: 10.5000},
    {name: "Sakiet Eddaier", lat: 34.7667, lng: 10.6833},
    {name: "Sakiet Ezzit", lat: 34.7500, lng: 10.7500},
    {name: "Skhira", lat: 34.3000, lng: 10.0667},
    {name: "Thyna", lat: 34.6667, lng: 10.7000},

    // Gabès Governorate
    {name: "Gabès", lat: 33.8815, lng: 10.0983},
    {name: "Ghannouch", lat: 33.9333, lng: 10.0667},
    {name: "El Hamma", lat: 33.8917, lng: 9.7967},
    {name: "Matmata", lat: 33.5500, lng: 9.9667},
    {name: "Métouia", lat: 33.9667, lng: 10.0000},
    {name: "Nouvelle Matmata", lat: 33.8833, lng: 9.8500},
    {name: "Oudhref", lat: 33.8167, lng: 10.0333},

    // Medenine Governorate
    {name: "Medenine", lat: 33.3549, lng: 10.5055},
    {name: "Ajim", lat: 33.7167, lng: 10.7500},
    {name: "Ben Gardane", lat: 33.1333, lng: 11.2167},
    {name: "Beni Khedache", lat: 33.2500, lng: 10.2000},
    {name: "Houmt Souk", lat: 33.8667, lng: 10.8500},
    {name: "Midoun", lat: 33.8167, lng: 10.9833},
    {name: "Zarzis", lat: 33.5000, lng: 11.1167},
    {name: "Sidi Makhlouf", lat: 33.3500, lng: 10.4833},

    // Tataouine Governorate
    {name: "Tataouine", lat: 32.9297, lng: 10.4510},
    {name: "Bir Lahmar", lat: 32.8000, lng: 10.6333},
    {name: "Dehiba", lat: 32.0167, lng: 10.7000},
    {name: "Ghomrassen", lat: 33.0667, lng: 10.3333},
    {name: "Remada", lat: 32.3167, lng: 10.4000},
    {name: "Smâr", lat: 33.2333, lng: 10.5000},

    // Gafsa Governorate
    {name: "Gafsa", lat: 34.4229, lng: 8.7841},
    {name: "El Guettar", lat: 34.3333, lng: 8.9500},
    {name: "El Ksar", lat: 34.4167, lng: 8.8000},
    {name: "Mdhilla", lat: 34.2833, lng: 8.7500},
    {name: "Métlaoui", lat: 34.3333, lng: 8.4000},
    {name: "Moulares", lat: 34.3167, lng: 8.2667},
    {name: "Redeyef", lat: 34.3833, lng: 8.1500},
    {name: "Sened", lat: 34.9333, lng: 10.2833},

    // Tozeur Governorate
    {name: "Tozeur", lat: 33.9197, lng: 8.1335},
    {name: "Degache", lat: 33.9833, lng: 8.2167},
    {name: "Hazoua", lat: 33.9333, lng: 7.8667},
    {name: "Nefta", lat: 33.8667, lng: 7.8833},
    {name: "Tamerza", lat: 34.2167, lng: 7.9333},

    // Kebili Governorate
    {name: "Kebili", lat: 33.7000, lng: 8.9667},
    {name: "Douz", lat: 33.4667, lng: 9.0167},
    {name: "Faouar", lat: 33.6833, lng: 9.0167},
    {name: "Souk Lahad", lat: 33.8333, lng: 9.0167}
];

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

                // Add click event to select location
                marker.on('click', function() {
                    // Set the input field value
                    document.getElementById(currentInputId).value = location.name;
                    // Set coordinates
                    document.getElementById(currentInputId + '_coords').value = `${location.lat.toFixed(5)}, ${location.lng.toFixed(5)}`;
                    // Close the map
                    document.getElementById("mapModal").style.display = "none";
                    document.getElementById("loadingSpinner").style.display = "none";
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
                    alert("Please select a point within Tunisia.");
                    loadingSpinner.style.display = "none";
                }
            });

            // Fit map to show all of Tunisia
            map.fitBounds([
                [30.2, 7.5],
                [37.4, 11.6]
            ]);
        }

        function showMap(inputId) {
            console.log('showMap called with inputId:', inputId);

            if (!['depart', 'arrivee'].includes(inputId)) {
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
                console.log('Map already initialized, hiding spinner');
                loadingSpinner.style.display = "none";
            }

            // Obtenir la localisation actuelle
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        console.log('Geolocation success: lat:', lat, 'lng:', lng);

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
                            console.log('Geolocation outside Tunisia, using default center');
                            map.setView([36.8065, 10.1815], 7);
                        }
                        loadingSpinner.style.display = "none";
                    },
                    (error) => {
                        console.error('Geolocation error:', error.message);
                        map.setView([36.8065, 10.1815], 7);
                        loadingSpinner.style.display = "none";
                        alert("Impossible d'obtenir votre localisation. Centrage sur Tunis.");
                    }
                );
            } else {
                console.log('Geolocation not supported');
                map.setView([36.8065, 10.1815], 7);
                loadingSpinner.style.display = "none";
            }

            // Masquer le spinner après 2 secondes (secours)
            setTimeout(() => {
                console.log('Timeout: hiding spinner');
                loadingSpinner.style.display = "none";
            }, 2000);
        }

        function reverseGeocode(lat, lng, inputId) {
            console.log('reverseGeocode called with inputId:', inputId, 'lat:', lat, 'lng:', lng);

            if (!['depart', 'arrivee'].includes(inputId)) {
                console.error('Invalid inputId in reverseGeocode:', inputId);
                document.getElementById("loadingSpinner").style.display = "none";
                return;
            }

            const inputElement = document.getElementById(inputId);
            const coordsElement = document.getElementById(inputId + '_coords');
            const loadingSpinner = document.getElementById("loadingSpinner");

            if (!inputElement || !coordsElement) {
                console.error('DOM elements not found for inputId:', inputId, { inputElement, coordsElement });
                loadingSpinner.style.display = "none";
                return;
            }

            // Recherche inversée pour trouver la ville la plus proche
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=10&accept-language=fr`, {
                headers: {
                    'User-Agent': 'ClickNGo/1.0 (contact@yourdomain.com)'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Nominatim response for', inputId, ':', data);
                if (data && data.address && data.address.city) {
                    inputElement.value = data.address.city;
                    coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                } else if (data && data.address && data.address.town) {
                    inputElement.value = data.address.town;
                    coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                } else {
                    inputElement.value = 'Ville non trouvée';
                    coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                }
                document.getElementById("mapModal").style.display = "none";
                loadingSpinner.style.display = "none";
                console.log('Updated:', { inputId, inputValue: inputElement.value, coordsValue: coordsElement.value });
            })
            .catch(error => {
                console.error('Error during reverse geocoding:', error);
                inputElement.value = 'Erreur de recherche';
                coordsElement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                loadingSpinner.style.display = "none";
                console.log('Updated (error):', { inputId, inputValue: inputElement.value, coordsValue: coordsElement.value });
            });
        }

        // Fonction de recherche avec autocomplétion
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
                        console.log('Search results:', data);
                        suggestions.innerHTML = '';
                        data.forEach(item => {
                            if (item.type === 'city' || item.type === 'town') {
                                const div = document.createElement('div');
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
                                });
                                suggestions.appendChild(div);
                            }
                        });
                        if (suggestions.innerHTML === '') {
                            suggestions.innerHTML = '<div style="padding: 10px 12px; color: #666;">Aucune ville trouvée</div>';
                        }
                        suggestions.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        suggestions.innerHTML = '<div style="padding: 10px 12px; color: red;">Erreur lors de la recherche</div>';
                        suggestions.style.display = 'block';
                    });
                }, 300);
            });

            // Masquer les suggestions lors de la perte de focus
            searchInput.addEventListener('blur', () => {
                setTimeout(() => {
                    suggestions.style.display = 'none';
                    suggestions.innerHTML = '';
                }, 200);
            });

            // Afficher les suggestions au focus
            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim().length >= 3) {
                    suggestions.style.display = 'block';
                }
            });
        }

        // Initialiser la recherche après le chargement du DOM
        document.addEventListener('DOMContentLoaded', () => {
            setupSearch();
        });
    </script>
<div class="age-section">
    <h2 class="age-title">Nos sélections par âge</h2>
    <div class="age-cards-container">
        <div class="age-card" data-age="18">
            <p class="age-number">18</p>
            <p class="age-text">ans</p>
        </div>
        <div class="age-card" data-age="20">
            <p class="age-number">20</p>
            <p class="age-text">ans</p>
        </div>
        <div class="age-card" data-age="30">
            <p class="age-number">30</p>
            <p class="age-text">ans</p>
        </div>
        <div class="age-card" data-age="40">
            <p class="age-number">40</p>
            <p class="age-text">ans</p>
        </div>
        <div class="age-card" data-age="50">
            <p class="age-number">50</p>
            <p class="age-text">ans</p>
        </div>
        <div class="age-card" data-age="60">
            <p class="age-number">60</p>
            <p class="age-text">ans</p>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const cards = document.querySelectorAll(".age-card");
        const buttons = document.getElementById("role-buttons");

        cards.forEach(card => {
            card.addEventListener("mouseenter", () => {
                const rect = card.getBoundingClientRect();
                buttons.style.left = `${rect.left + rect.width / 2}px`;
                buttons.style.top = `${rect.bottom + window.scrollY + 5}px`;
                buttons.style.transform = "translateX(-50%)";
                buttons.classList.remove("hidden");
            });

            card.addEventListener("mouseleave", () => {
                setTimeout(() => {
                    if (!buttons.matches(":hover")) {
                        buttons.classList.add("hidden");
                    }
                }, 200);
            });
        });

        buttons.addEventListener("mouseleave", () => {
            buttons.classList.add("hidden");
        });
    });
</script> <br><br><br>

    <section id="trajets" class="bg-[#f9f9fb] py-12 px-4 sm:px-8 lg:px-16">
    <h2 class="text-3xl font-bold text-center text-[#be3cf0] mb-10 animate-title">Nos trajets récents</h2>
    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4" id="trajets-grid">
        <?php 
        require_once '../Model/ImageGenerator.php';
        
        // Clear session indices for fresh images on each page load
        $_SESSION['used_indices'] = [];
        $usedIndices = [];
        $imageGenerator = new ImageGenerator($usedIndices);
        
        try {
            $recentAnnonces = annonce_covoiturage::getRecentAnnonces(8);
            
            if (empty($recentAnnonces)) {
                echo '<div class="col-span-full text-center text-gray-600">Aucun trajet récent disponible pour le moment.</div>';
            } else {
                foreach ($recentAnnonces as $index => $annonce) {
                    $imageUrl = $imageGenerator->getImageForLocation($annonce->getLieuArrivee(), $usedIndices);
                    $_SESSION['used_indices'] = $usedIndices;
                    
                    setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
                    $dateFormatted = strftime('%d %B %Y', $annonce->getDateDepart()->getTimestamp());
                    
                    echo '
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden transition-all duration-300 animate-sway" data-location="' . htmlspecialchars($annonce->getLieuArrivee()) . '" data-index="' . $index . '">
                        <img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($annonce->getLieuDepart() . ' vers ' . $annonce->getLieuArrivee()) . '" class="w-full h-48 object-cover trajet-image" loading="lazy" onload="this.style.opacity=1" style="opacity:0;transition:opacity 0.5s">
                        <div class="p-4">
                            <h3 class="text-xl font-semibold text-gray-800 transition-colors duration-300">' . 
                                htmlspecialchars($annonce->getLieuDepart()) . ' ➝ ' . htmlspecialchars($annonce->getLieuArrivee()) . 
                            '</h3>
                            <p class="text-gray-600 mt-2 transition-colors duration-300 text-sm">' . 
                                htmlspecialchars($annonce->getTypeVoiture()) . ' · ' . 
                                htmlspecialchars($annonce->getNombrePlaces()) . ' personnes · ' . 
                                htmlspecialchars($dateFormatted) . 
                            '</p>
                        </div>
                    </div>';
                }
            }
        } catch (Exception $e) {
            echo '<div class="col-span-full text-center text-red-500">Erreur lors du chargement des trajets récents: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>

    <style>
        /* Section Nos trajets récents */
#trajets {
    background-color: #f9f9fb;
    padding: 3rem 1rem;
}

@media (min-width: 640px) {
    #trajets {
        padding: 3rem 2rem;
    }
}

@media (min-width: 1024px) {
    #trajets {
        padding: 3rem 4rem;
    }
}

/* Titre de la section */
#trajets h2 {
    font-size: 1.875rem;
    font-weight: 700;
    text-align: center;
    color: #be3cf0;
    margin-bottom: 2.5rem;
}

/* Grille des trajets */
#trajets-grid {
    display: grid;
    gap: 2rem;
    grid-template-columns: 1fr;
}

@media (min-width: 640px) {
    #trajets-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    #trajets-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Cartes de trajets */
#trajets-grid > div {
    background-color: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    transition: all 0.3s ease;
}

/* Images des trajets */
.trajet-image {
    width: 100%;
    height: 12rem;
    object-fit: cover;
    opacity: 0;
    transition: opacity 0.5s ease;
}

/* Contenu des cartes */
#trajets-grid .p-4 {
    padding: 1rem;
}

/* Titres des trajets */
#trajets-grid h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    transition: color 0.3s ease;
}

/* Détails des trajets */
#trajets-grid p {
    color: #4b5563;
    margin-top: 0.5rem;
    transition: color 0.3s ease;
    font-size: 0.875rem;
}

/* Messages d'erreur/vide */
.col-span-full {
    grid-column: 1 / -1;
    text-align: center;
}

.text-gray-600 {
    color: #4b5563;
}

.text-red-500 {
    color: #ef4444;
}

/* Animations */
.animate-title {
    opacity: 0;
    transform: translateY(-20px);
    animation: bounceIn 0.8s ease-out forwards;
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    60% {
        opacity: 1;
        transform: translateY(5px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Animation de balancement pour les cartes */
.animate-sway {
    animation: sway 4s ease-in-out infinite;
    transform-origin: center;
}

@keyframes sway {
    0%, 100% {
        transform: translateX(0) rotate(0deg);
    }
    25% {
        transform: translateX(5px) rotate(1deg);
    }
    75% {
        transform: translateX(-5px) rotate(-1deg);
    }
}

/* Délais d'animation échelonnés */
.animate-sway[data-index="0"] { animation-delay: 0s; }
.animate-sway[data-index="1"] { animation-delay: 0.2s; }
.animate-sway[data-index="2"] { animation-delay: 0.4s; }
.animate-sway[data-index="3"] { animation-delay: 0.6s; }
.animate-sway[data-index="4"] { animation-delay: 0.8s; }
.animate-sway[data-index="5"] { animation-delay: 1s; }
.animate-sway[data-index="6"] { animation-delay: 1.2s; }
.animate-sway[data-index="7"] { animation-delay: 1.4s; }

/* Effet de survol */
.animate-sway:hover {
    animation-play-state: paused;
    transform: rotate(3deg) scale(1.05);
    box-shadow: 0 8px 20px rgba(190, 60, 240, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Transition fluide des images */
.trajet-image {
    transition: opacity 0.5s ease;
}

/* Section Top Conducteurs - Conversion complète des classes Tailwind */

/* Section principale */
#top-conducteurs {
  margin-top: 5rem;
  scroll-margin-top: 5rem;
  margin-left: auto;
  margin-right: auto;
  max-width: 80rem;
}

/* Titre */
#top-conducteurs h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 2rem;
  text-align: center;
  color: #1f2937;
}

/* Container des cartes */
#top-conducteurs > div {
  display: flex;
  flex-wrap: nowrap;
  justify-content: center;
  gap: 1.5rem;
  padding-left: 1rem;
  padding-right: 1rem;
  overflow-x: auto;
}

/* Messages d'erreur/vide */
.text-center {
  text-align: center;
}

.text-gray-600 {
  color: #4b5563;
}

.text-red-500 {
  color: #ef4444;
}

/* Cartes des conducteurs */
.top-driver {
  position: relative;
  padding: 1rem;
  background: linear-gradient(135deg, #fff0f5, #f3e8ff);
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  overflow: visible;
  width: 200px;
  text-align: center;
  flex-shrink: 0;
}

.top-driver:hover {
  transform: translateY(-8px);
  box-shadow: 0 10px 25px rgba(255, 105, 180, 0.4);
}

/* Classes Tailwind converties */
.relative {
  position: relative;
}

.group {
  /* Groupe pour les effets hover */
}

.flex-shrink-0 {
  flex-shrink: 0;
}

.w-fit {
  width: fit-content;
}

.mx-auto {
  margin-left: auto;
  margin-right: auto;
}

.rounded-full {
  border-radius: 9999px;
}

.w-20 {
  width: 5rem;
}

.h-20 {
  height: 5rem;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.duration-300 {
  transition-duration: 300ms;
}

.absolute {
  position: absolute;
}

.inset-0 {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}

.bg-gradient-to-b {
  background-image: linear-gradient(to bottom, var(--tw-gradient-stops));
}

.from-black\/60 {
  --tw-gradient-from: rgba(0, 0, 0, 0.6);
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(0, 0, 0, 0));
}

.to-transparent {
  --tw-gradient-to: transparent;
}

.text-white {
  color: #ffffff;
}

.text-sm {
  font-size: 0.875rem;
  line-height: 1.25rem;
}

.flex {
  display: flex;
}

.flex-col {
  flex-direction: column;
}

.justify-center {
  justify-content: center;
}

.items-center {
  align-items: center;
}

.opacity-0 {
  opacity: 0;
}

.group:hover .group-hover\:opacity-100 {
  opacity: 1;
}

.transition {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow,
    transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.gap-1 {
  gap: 0.25rem;
}

.text-base {
  font-size: 1rem;
  line-height: 1.5rem;
}

.text-lg {
  font-size: 1.125rem;
  line-height: 1.75rem;
}

.font-semibold {
  font-weight: 600;
}

.text-gray-800 {
  color: #1f2937;
}

.gap-3 {
  gap: 0.75rem;
}

.mt-2 {
  margin-top: 0.5rem;
}

.text-yellow-400 {
  color: #fbbf24;
}

.text-pink-400 {
  color: #f472b6;
}

.hover\:text-\[\#ff69b4\]:hover {
  color: #ff69b4;
}

.text-xl {
  font-size: 1.25rem;
  line-height: 1.75rem;
}

.fas {
  font-family: "Font Awesome 6 Free";
  font-weight: 900;
}

.far {
  font-family: "Font Awesome 6 Free";
  font-weight: 400;
}

.fab {
  font-family: "Font Awesome 6 Brands";
  font-weight: 400;
}

.fa-star:before {
  content: "\f005";
}

.text-gray-200 {
  color: #e5e7eb;
}

/* Boutons d'étoiles */
.star-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 2px;
  transition: transform 0.2s ease;
}

.star-btn:hover {
  transform: scale(1.2);
}

.star-btn:disabled {
  cursor: not-allowed;
}

/* Animation d'entrée en cascade */
.animate-cascade-drop {
  opacity: 0;
  transform: translateY(-100px) scale(0.9);
  animation: cascadeDrop 0.7s ease-out forwards;
}

@keyframes cascadeDrop {
  0% {
    opacity: 0;
    transform: translateY(-100px) scale(0.9);
  }
  60% {
    opacity: 0.8;
    transform: translateY(10px) scale(1.05);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Effet de particules au survol */
.particle-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: -1;
}

.top-driver:hover .particle-container::before,
.top-driver:hover .particle-container::after {
  content: "";
  position: absolute;
  width: 8px;
  height: 8px;
  background: radial-gradient(circle, #ff69b4, transparent);
  border-radius: 50%;
  opacity: 0;
  animation: particleSwirl 1.5s ease-in-out infinite;
}

.top-driver:hover .particle-container::before {
  top: 10%;
  left: 10%;
  animation-delay: 0s;
}

.top-driver:hover .particle-container::after {
  bottom: 10%;
  right: 10%;
  animation-delay: 0.5s;
}

@keyframes particleSwirl {
  0% {
    opacity: 0.8;
    transform: translate(0, 0) scale(1);
  }
  50% {
    opacity: 1;
    transform: translate(20px, -20px) scale(1.5);
  }
  100% {
    opacity: 0;
    transform: translate(-20px, 20px) scale(0.5);
  }
}

/* Effet d'étincelle pour les étoiles */
.animate-sparkle {
  position: relative;
  animation: sparkle 1.5s infinite ease-in-out;
}

.animate-sparkle::after {
  content: "\f0c3";
  font-family: "Font Awesome 6 Free";
  font-weight: 900;
  position: absolute;
  top: -4px;
  right: -4px;
  font-size: 8px;
  color: #ffd700;
  opacity: 0;
  animation: sparkleBlink 1.5s infinite ease-in-out;
  animation-delay: 0.3s;
}

@keyframes sparkle {
  0%,
  100% {
    transform: scale(1);
    opacity: 0.9;
  }
  50% {
    transform: scale(1.2);
    opacity: 1;
  }
}

@keyframes sparkleBlink {
  0%,
  100% {
    opacity: 0;
  }
  50% {
    opacity: 1;
  }
}

/* Responsive Design */
@media (min-width: 640px) {
  .sm\:w-24 {
    width: 6rem;
  }

  .sm\:h-24 {
    height: 6rem;
  }

  .sm\:text-lg {
    font-size: 1.125rem;
    line-height: 1.75rem;
  }

  .sm\:text-base {
    font-size: 1rem;
    line-height: 1.5rem;
  }

  .sm\:text-xl {
    font-size: 1.25rem;
    line-height: 1.75rem;
  }
}

@media (max-width: 640px) {
  .top-driver {
    padding: 0.75rem;
    width: 160px;
  }

  .top-driver img {
    width: 18vw !important;
    height: 18vw !important;
  }

  .top-driver h3 {
    font-size: 0.9rem;
  }

  .top-driver .text-yellow-400 {
    font-size: 0.8rem;
  }

  .top-driver .fab {
    font-size: 1rem;
  }
}

@media (min-width: 641px) and (max-width: 1024px) {
  .top-driver {
    padding: 0.875rem;
    width: 180px;
  }

  .top-driver img {
    width: 22vw !important;
    height: 22vw !important;
  }
}

    </style>

    <script>
        // Function to refresh images
        function refreshImages() {
            const trajets = document.querySelectorAll('#trajets-grid > div');
            if (trajets.length === 0) return;
            const locations = Array.from(trajets).map(trajet => trajet.dataset.location);

            fetch('fetch_images.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(locations),
            })
            .then(response => response.json())
            .then(data => {
                trajets.forEach(trajet => {
                    const location = trajet.dataset.location;
                    const img = trajet.querySelector('.trajet-image');
                    if (data[location] && img.src !== data[location]) {
                        img.style.opacity = 0;
                        img.src = data[location];
                        img.onload = () => img.style.opacity = 1;
                    }
                });
            })
            .catch(error => console.error('Error refreshing images:', error));
        }

        // Initial refresh to ensure all images load
        window.addEventListener('load', refreshImages);
    </script>
</section>

<section id="top-conducteurs" class="mt-20 scroll-mt-20 mx-auto max-w-7xl">
    <h2 class="text-2xl font-bold mb-8 text-center text-gray-800">Top Conducteurs</h2>
    <div class="flex flex-nowrap justify-center gap-6 px-4 overflow-x-auto">
        <?php 
        // Include config file
        require_once dirname(__DIR__) . '/config.php';

        // Get PDO connection
        $pdo = config::getConnexion();

        // Get user ratings from session
        if (!isset($_SESSION['user_ratings'])) {
            $_SESSION['user_ratings'] = [];
        }
        $userRatings = $_SESSION['user_ratings'];

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    prenom_conducteur, 
                    nom_conducteur, 
                    likes AS average_rating, 
                    dislikes AS vote_count
                FROM 
                    annonce_covoiturage 
                ORDER BY 
                    likes DESC, 
                    date_depart DESC 
                LIMIT 6
            ");
            $stmt->execute();
            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($drivers)) {
                echo '<div class="text-center text-gray-600">Aucun conducteur disponible pour le moment.</div>';
            } else {
                $index = 0;
                foreach ($drivers as $driver) {
                    $fullName = htmlspecialchars(trim($driver['prenom_conducteur'] . ' ' . $driver['nom_conducteur']));
                    $driverId = htmlspecialchars($driver['prenom_conducteur'] . '_' . $driver['nom_conducteur']);
                    
                    // Use identicon style for avatars
                    $avatarUrl = 'https://api.dicebear.com/7.x/identicon/svg?seed=' . urlencode($fullName) . '&backgroundColor=f7c7d7';
                    
                    $averageRating = isset($driver['average_rating']) ? (float)$driver['average_rating'] : 0;
                    $voteCount = isset($driver['vote_count']) ? (int)$driver['vote_count'] : 0;
                    
                    $userHasRated = isset($userRatings[$driverId]);
                    $userRating = $userRatings[$driverId] ?? null;

                    // Add delay for cascading effect
                    $delay = $index * 0.15;
                    echo '
                    <div class="top-driver text-center relative group animate-cascade-drop flex-shrink-0" style="animation-delay: ' . $delay . 's;">
                        <div class="relative w-fit mx-auto">
                            <img src="' . $avatarUrl . '" alt="' . $fullName . '" class="rounded-full w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-2 transition-transform duration-300">
                            <div class="absolute inset-0 bg-gradient-to-b from-black/60 to-transparent text-white text-sm flex flex-col justify-center items-center opacity-0 group-hover:opacity-100 transition duration-300 rounded-full">
                                <div class="flex gap-1 rating-stars" data-driver-id="' . $driverId . '">';
                                    for ($i = 1; $i <= 5; $i++) {
                                        $starClass = $userHasRated && $i <= $userRating ? 'fas fa-star text-yellow-400 animate-sparkle' : 'far fa-star text-gray-200 animate-sparkle';
                                        echo '<button class="star-btn ' . $starClass . '" data-rating="' . $i . '" ' . ($userHasRated ? 'disabled' : '') . '></button>';
                                    }
                    echo '
                                </div>
                            </div>
                        </div>
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800">' . $fullName . '</h3>
                        <div class="flex justify-center gap-3 mt-2">
                            <span class="text-yellow-400 text-sm sm:text-base">' . number_format($averageRating, 1) . ' / 5 (' . $voteCount . ' votes)</span>
                        </div>
                        <div class="flex justify-center gap-3 mt-2">
                           
                        </div>
                        <div class="particle-container"></div>
                    </div>';
                    $index++;
                }
            }
        } catch (Exception $e) {
            echo '<div class="text-center text-red-500">Erreur lors du chargement des conducteurs: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        document.querySelectorAll('.star-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (this.disabled) return;

                const driverId = this.closest('.rating-stars').dataset.driverId;
                const rating = parseInt(this.dataset.rating);
                const container = this.closest('.top-driver');
                const stars = container.querySelectorAll('.star-btn');
                const ratingDisplay = container.querySelector('.text-yellow-400');

                fetch('handle_rating.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ driver_id: driverId, rating: rating }),
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network error: HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        stars.forEach((star, index) => {
                            star.className = index < rating ? 'star-btn fas fa-star text-yellow-400 animate-sparkle' : 'star-btn far fa-star text-gray-200 animate-sparkle';
                            star.disabled = true;
                        });
                        ratingDisplay.textContent = `${data.average_rating.toFixed(1)} / 5 (${data.vote_count} votes)`;
                    } else {
                        console.error('Server error:', data.error);
                        alert('Erreur: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error.message);
                    alert('Erreur réseau. Veuillez réessayer. Détails: ' + error.message);
                });
            });
        });
    </script>
</section><br><br><br>


<!-- Chatbot FAQ Section -->
<section id="faq" class="chatbot-section">
    <h2 class="chatbot-title">Parlez avec Click'N'Go</h2>
    <div class="chatbot-container">
        <div class="chat-window">
            <!-- Chat Header -->
            <div class="chat-header">
                <i class="fas fa-robot"></i>
                <h3>Click'N'Go Bot</h3>
                <div class="chat-status">
                    <span class="status-dot"></span>
                    <span>En ligne</span>
                </div>
            </div>
            <!-- Chat Messages -->
            <div id="chat-messages" class="chat-messages">
                <div class="bot-message" data-message-id="welcome">
                    <div class="message-content">
                        Salut ! Je suis le bot Click'N'Go, ton guide pour des aventures épiques en Tunisie ! 🌴 Pose une question, partage une photo ou utilise les options créatives ! ✨
                    </div>
                    <div class="message-reactions">
                        <button class="reaction-btn" data-reaction="👍">👍</button>
                        <button class="reaction-btn" data-reaction="❤️">❤️</button>
                        <button class="reaction-btn" data-reaction="😊">😊</button>
                        <button class="reaction-btn" data-reaction="🔥">🔥</button>
                    </div>
                </div>
            </div>

            <!-- Suggestions rapides -->
            <div class="chat-suggestions">
                <div class="suggestion-title">Questions populaires:</div>
                <div class="suggestions-container">
                    <button class="suggestion-btn" data-query="Où faire du karting ?">
                        <i class="fas fa-flag-checkered"></i>
                        <span>Karting</span>
                    </button>
                    <button class="suggestion-btn" data-query="Quels produits puis-je trouver ?">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Produits</span>
                    </button>
                    <button class="suggestion-btn" data-query="Quels sont les sponsors ?">
                        <i class="fas fa-handshake"></i>
                        <span>Sponsors</span>
                    </button>
                    <button class="suggestion-btn" data-query="Où trouver des raquettes ?">
                        <i class="fas fa-table-tennis"></i>
                        <span>Raquettes</span>
                    </button>
                </div>
            </div>
            
            <!-- Chat Input -->
            <div class="chat-input-container">
                <div class="input-wrapper">
                    <input id="chat-input" type="text" placeholder="Karting, shopping, raquettes, sponsors... pose ta question !" aria-label="Posez une question sur Click'N'Go">
                    
                    <!-- Boutons créatifs -->
                    <div class="creative-buttons">
                        <button id="photo-btn" class="creative-btn" title="Partager une photo">
                            <i class="fas fa-camera"></i>
                        </button>
                        <button id="location-btn" class="creative-btn" title="Partager ma localisation">
                            <i class="fas fa-map-marker-alt"></i>
                        </button>
                        <button id="emoji-btn" class="creative-btn" title="Réactions rapides">
                            <i class="fas fa-smile"></i>
                        </button>
                        <button id="surprise-btn" class="creative-btn" title="Surprise moi !">
                            <i class="fas fa-gift"></i>
                        </button>
                    </div>
                    
                    <button id="chat-send" aria-label="Envoyer la question">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                
                <!-- Input caché pour les photos -->
                <input type="file" id="photo-input" accept="image/*" style="display: none;">
                
                <!-- Panel d'emojis -->
                <div id="emoji-panel" class="emoji-panel" style="display: none;">
                    <div class="emoji-grid">
                        <button class="emoji-option" data-emoji="🏖️">🏖️ Plage</button>
                        <button class="emoji-option" data-emoji="🏎️">🏎️ Karting</button>
                        <button class="emoji-option" data-emoji="🛍️">🛍️ Shopping</button>
                        <button class="emoji-option" data-emoji="🎨">🎨 Art</button>
                        <button class="emoji-option" data-emoji="🍕">🍕 Food</button>
                        <button class="emoji-option" data-emoji="🎵">🎵 Musique</button>
                        <button class="emoji-option" data-emoji="⚽">⚽ Sport</button>
                        <button class="emoji-option" data-emoji="🌟">🌟 Aventure</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let messageCounter = 0;

        // Réponses créatives pour les différentes interactions
        const creativeResponses = {
            photo: [
                "Superbe photo ! 📸 Ça me donne envie d'aventure ! Pour des expériences similaires, check nos activités ou trouve un covoiturage ! 🚗",
                "Magnifique ! 🌟 Cette photo me rappelle les merveilles de la Tunisie ! Besoin d'infos sur des lieux similaires ? 🏖️",
                "Wow ! 😍 Quelle belle image ! Ça m'inspire pour te proposer des activités géniales en Tunisie ! ✨",
                "Incroyable photo ! 🔥 Tu as l'œil ! Veux-tu découvrir des endroits similaires ou des activités fun ? 🎯"
            ],
            location: [
                "Position reçue ! 📍 Parfait ! Je peux te suggérer des activités près de toi ou des covoiturages disponibles ! 🚗",
                "Super ! 🗺️ Avec ta localisation, je peux te proposer les meilleures aventures à proximité ! Karting ? Paintball ? 🏎️",
                "Localisation capturée ! ✨ Prêt pour des suggestions d'activités locales ou des trajets vers l'aventure ? 🌟",
                "Génial ! 📌 Maintenant je peux te guider vers les meilleurs spots d'aventure près de chez toi ! 🎯"
            ],
            surprise: [
                "🎁 SURPRISE ! Savais-tu qu'à Djerba tu peux faire du quad dans le désert ET du surf sur la côte le même jour ? Épique ! 🏄‍♂️",
                "✨ SURPRISE ! Le karting de Monastir a une piste de 1,2km - la plus longue de Tunisie ! Fonce-y ! 🏎️",
                "🌟 SURPRISE ! À Sidi Bou Saïd, tu peux peindre ton propre tableau dans des ateliers avec vue sur la mer ! 🎨",
                "🎯 SURPRISE ! Nos sponsors Ooredoo offrent parfois des réductions sur les activités ! Check régulièrement ! 💰",
                "🔥 SURPRISE ! Le paintball de Hammamet a des scénarios de jeu inspirés de films d'action ! Immersif ! 🎬"
            ]
        };

        // Expanded FAQ data stored client-side
        const faqs = [
            {
                question: "Qu'est-ce que Click'N'Go ?",
                answer: "Click'N'Go, c'est ta plateforme de loisirs ultime en Tunisie ! Trouve des trajets pour des aventures palpitantes à Djerba, des festivals à Sousse, ou du karting à Tunis. Shop des produits fun et embarque pour l'épopée ! 🌟",
                keywords: ["platforme", "clickngo", "loisirs", "aventure"]
            },
            {
                question: "C'est quoi le covoiturage ?",
                answer: "Le covoiturage, c'est partager une voiture pour des destinations vibrantes comme des plages ou des festivals. Économique, écolo, et super convivial ! 🚗",
                keywords: ["covoiturage", "définition", "partage"]
            },
            {
                question: "Quels sont les avantages du covoiturage ?",
                answer: "Le covoiturage, c'est génial ! Économise des dinars, réduis ton empreinte carbone, et fais des rencontres fun. Parfait pour tes escapades à Hammamet ou Kairouan ! 🌍",
                keywords: ["avantages", "covoiturage", "écolo", "économique", "bénéfices"]
            },
            {
                question: "Pourquoi utiliser le covoiturage ?",
                answer: "Pour des trajets abordables et éco-chic ! Connecte avec des aventuriers et explore Tunis ou Zaghouan. Voyage malin avec Click'N'Go ! 🎉",
                keywords: ["pourquoi", "covoiturage", "utiliser", "raison"]
            },
            {
                question: "Comment réserver un trajet ?",
                answer: "Facile ! Dans la navbar, clique sur 'Nos trajets' pour réserver ton aventure. Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🚀",
                keywords: ["réserver", "trajet", "navbar", "nos trajets"]
            },
            {
                question: "Comment être conducteur ?",
                answer: "Rejoins l'aventure ! Dans la navbar, clique sur 'Proposer un covoiturage' pour transporter des voyageurs vers des plages ou festivals. Gagne des dinars et des sourires ! 😎",
                keywords: ["conducteur", "devenir", "proposer", "navbar"]
            },
            {
                question: "Est-ce que les trajets sont sûrs ?",
                answer: "Oui, 100% ! Conducteurs vérifiés et avis transparents. Pars à Sidi Bou Saïd ou Tozeur l'esprit léger ! 🛡️",
                keywords: ["sûr", "sécurité", "avis", "vérifié"]
            },
            {
                question: "Y a-t-il du shopping ici ?",
                answer: "Click'N'Go est parfait pour toi ! Visite notre partie produits, y'a un choix divers pour tous tes besoins ! Shop des raquettes, puzzles, et plus encore ! 🛍️",
                keywords: ["shopping", "shop", "produits", "achat"]
            },
            {
                question: "Quels produits puis-je trouver ?",
                answer: "Tu trouves tout sur Click'N'Go dans la section produits ! Ajoute au panier : raquette avec balle de tennis, ballons, corde à sauter, haltères réglables, planche de surf, lunettes de natation, chaussures de plage, bouteille d'eau de sport, combinaison de wingsuit, veste coupe-vent, leggings et brassière, chaussures de randonnée, caméra instantanée, casque VR, montre connectée, table inclinable, smoothie shaker, lampe loupe, support de dessin, carnet de dessin, crayons de couleurs, cube de Rubik, jeux de table, puzzle, jeu de mémoire visuelle, BrainBox. Tout pour tes aventures ! 🛒",
                keywords: ["produits", "articles", "acheter", "catalogue"]
            },
            {
                question: "Où faire du karting en Tunisie ?",
                answer: "Fonce au Karting Park à Monastir, près de l'aéroport ! Courses endiablées sur piste pro, combinaisons fournies, dès 20 TND pour 10 min. Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🏎️",
                keywords: ["karting", "course", "monastir"]
            },
            {
                question: "Où jouer au paintball ?",
                answer: "Rends-toi au Zizou Paintball Club à Yasmine Hammamet ! Équipe-toi pour des batailles stratégiques, dès 30 TND par personne. Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🔫",
                keywords: ["paintball", "zizou", "hammamet"]
            },
            {
                question: "Quels sont les parcs d'attractions en Tunisie ?",
                answer: "Visite le Centre Venizia à Hammamet pour manèges et karting, ou Happy Land Park à Tunis pour des jeux familiaux. Entrée dès 10 TND. Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🎢",
                keywords: ["parc", "attractions", "venizia", "happy land"]
            },
            {
                question: "Où trouver des parcs aquatiques ?",
                answer: "Plonge à l'Aqua Land à Yasmine Hammamet ! Toboggans et piscines pour tous, entrée environ 25 TND. Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🏊",
                keywords: ["parc aquatique", "aqua land", "toboggans"]
            },
            {
                question: "Où participer à des ateliers de peinture ?",
                answer: "Essaie les ateliers à Tunis (La Marsa) ou Sousse, souvent dans des galeries comme Nizar. Crée tes œuvres pour 50-100 TND par session. Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🎨",
                keywords: ["peinture", "atelier", "art"]
            },
            {
                question: "Où trouver des ateliers de musique ?",
                answer: "Participe à des cours de oud ou darbouka à Tunis (Médina) ou Djerba, dès 40 TND par session. Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🎶",
                keywords: ["musique", "atelier", "oud", "darbouka"]
            },
            {
                question: "Où surfer en Tunisie ?",
                answer: "Ride les vagues à Bizerte ou Raf Raf ! Locations de planches dès 30 TND/jour. Shop ta planche sur Click'N'Go ! Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🏄",
                keywords: ["surfer", "surf", "bizerte", "raf raf"]
            },
            {
                question: "Où faire de la randonnée ?",
                answer: "Explore les sentiers de Zaghouan ou Ichkeul, guidés pour 100-150 TND/jour. Shop des chaussures de randonnée sur Click'N'Go ! Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🥾",
                keywords: ["randonnée", "hiking", "zaghouan", "ichkeul"]
            },
            {
                question: "Où faire un safari ?",
                answer: "Pars à Douz pour un safari désertique avec chameaux, dès 150 TND/jour. Shop une veste coupe-vent sur Click'N'Go ! Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🐪",
                keywords: ["safari", "désert", "douz"]
            },
            {
                question: "Où explorer les souks ?",
                answer: "Flâne dans les souks de Tunis ou Nabeul pour des trésors artisanaux, entrée gratuite. Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🛍️",
                keywords: ["souk", "marché", "artisanat"]
            },
            {
                question: "Où voir les étoiles ?",
                answer: "Admire les étoiles à Tataouine ou dans le Sahara, soirées guidées dès 80 TND. Shop une caméra instantanée sur Click'N'Go ! Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🌠",
                keywords: ["étoiles", "stargazing", "tataouine"]
            },
            {
                question: "Où faire du trampoline ?",
                answer: "Saute au Trampoline Park à Tunis (Les Berges du Lac) ! Sessions fun pour 15-20 TND/heure. Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🤸",
                keywords: ["trampoline", "sauter", "tunis"]
            },
            {
                question: "Où jouer à des jeux ninja ?",
                answer: "Tente le Ninja Warrior Course à Sousse, obstacles et défis pour 25 TND par session. Pas de transport ? Visite notre partie services 'Nos trajets' for a covoiturage ! 💪",
                keywords: ["ninja", "warrior", "sousse"]
            },
            {
                question: "Où apprendre la cuisine tunisienne ?",
                answer: "Participe à des ateliers culinaires à Tunis (Médina) ou Testour, dès 50 TND. Prépare du couscous et plus encore ! Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🍲",
                keywords: ["cuisine", "tunisienne", "atelier"]
            },
            {
                question: "Où faire du quad ?",
                answer: "Fonce dans le désert à Tozeur ou Douz pour des virées en quad, dès 100 TND/heure. Shop une veste coupe-vent sur Click'N'Go ! Pas de transport ? Visite notre partie services 'Nos trajets' pour un covoiturage ! 🏍️",
                keywords: ["quad", "désert", "tozeur"]
            },
            {
                question: "Où trouver des raquettes de tennis ou padel ?",
                answer: "Trouve des raquettes avec balle de tennis ou padel dans notre section produits ! Parfait pour jouer à Tunis ou Hammamet. Ajoute au panier ! 🏸",
                keywords: ["raquette", "tennis", "padel"]
            },
            {
                question: "Où acheter des ballons ?",
                answer: "Des ballons pour foot ou basket sont dans notre section produits ! Idéal pour les parcs à Sousse. Ajoute au panier ! ⚽",
                keywords: ["ballon", "foot", "basket"]
            },
            {
                question: "Où trouver des pulls pour le sport ?",
                answer: "Découvre des pulls stylés dans notre section produits, parfaits pour les randos ou soirées fraîches. Ajoute au panier ! 🧥",
                keywords: ["pull", "pulls", "sport"]
            },
            {
                question: "Où acheter une corde à sauter ?",
                answer: "La corde à sauter est dans notre section produits, idéale pour s'entraîner partout ! Ajoute au panier ! 🏋️",
                keywords: ["corde", "sauter", "fitness"]
            },
            {
                question: "Où trouver des haltères réglables ?",
                answer: "Shop des haltères réglables dans notre section produits pour booster ton training. Ajoute au panier ! 🏋️",
                keywords: ["haltères", "poids", "training"]
            },
            {
                question: "Où acheter une planche de surf ?",
                answer: "Trouve une planche de surf dans notre section produits pour rider à Bizerte ! Ajoute au panier ! 🏄",
                keywords: ["planche", "surf", "plage"]
            },
            {
                question: "Où trouver des lunettes de natation ?",
                answer: "Les lunettes de natation sont dans notre section produits, parfaites pour les piscines de Tunis. Ajoute au panier ! 🏊",
                keywords: ["lunettes", "natation", "piscine"]
            },
            {
                question: "Où acheter des chaussures de plage ?",
                answer: "Shop des chaussures de plage dans notre section produits pour Djerba ou Monastir. Confort garanti ! 🩴",
                keywords: ["chaussures", "plage", "sandales"]
            },
            {
                question: "Où trouver des fournitures d'art ?",
                answer: "Carnet de dessin, crayons de couleurs, support de dessin, lampe loupe : tout est dans notre section produits ! Crée à fond ! 🎨",
                keywords: ["art", "peinture", "dessin", "crayons"]
            },
            {
                question: "Où acheter des jeux de réflexion ?",
                answer: "Cube de Rubik, puzzle, jeu de mémoire visuelle, BrainBox : shop dans notre section produits pour des soirées fun ! Ajoute au panier ! 🧩",
                keywords: ["jeux", "puzzle", "rubik", "brainbox"]
            },
            {
                question: "Combien coûte un trajet ?",
                answer: "Le coût dépend du trajet ! Pour plus de détails, visitez 'Nos trajets' ou 'Nos trajets récents'. En général, les prix varient de 2 TND à 40 TND. 🌟",
                keywords: ["coût", "prix", "trajet", "tarif"]
            },
            {
                question: "Puis-je voyager en groupe ?",
                answer: "Oui ! Filtre par places pour une virée entre amis à Monastir ou le Sahara. Plus on est, plus c'est fun ! 🎈",
                keywords: ["groupe", "amis", "places"]
            },
            {
                question: "Comment payer mon trajet ?",
                answer: "Paye le conducteur en cash ou via Flouci. Bientôt des paiements en ligne pour tes escapades ! 💳",
                keywords: ["payer", "paiement", "espèces"]
            },
            {
                question: "Click'N'Go est-il éco-friendly ?",
                answer: "Oui ! Moins de CO2 grâce au covoiturage. Explore Tabarka ou El Jem éco-chic ! 🌍",
                keywords: ["éco", "environnement", "green"]
            },
            {
                question: "Puis-je emmener mon animal ?",
                answer: "Certains conducteurs disent oui ! Vérifie les détails du trajet pour ton aventure avec ton compagnon ! 🐶",
                keywords: ["animal", "chien", "chat"]
            },
            {
                question: "Quels sont les sponsors de ce site ?",
                answer: "Nos sponsors sont Ooredoo, Saida, Pathé, Dabchy, Vitalait, TravelTodo et Lella, qui soutiennent tes aventures loisirs en Tunisie ! 🌟",
                keywords: ["sponsors", "partenaires", "soutien"]
            },
            {
                question: "Quels sont les avantages des sponsors ?",
                answer: "Nos sponsors bénéficient d'une visibilité premium sur notre plateforme, d'un accès à notre communauté d'aventuriers, et de partenariats exclusifs pour des événements. Ils contribuent à rendre tes aventures encore plus accessibles ! 🌟",
                keywords: ["avantages", "sponsors", "bénéfices"]
            },
            {
                question: "Comment devenir sponsor ?",
                answer: "Pour devenir sponsor de Click'N'Go, contacte notre équipe via la section Contact ou envoie un email à sponsors@clickngo.tn. Nous créerons ensemble un partenariat sur mesure pour ta marque ! 🤝",
                keywords: ["devenir", "sponsor", "partenaire"]
            }
        ];

        // Greetings and special responses
        const greetings = {
            "bonjour": "Salut ! Prêt pour une aventure en Tunisie ? Demande-moi sur le karting, shopping, sponsors, ou produits ! 🌟",
            "salut": "Hey ! Envie d'une escapade ? Pose une question sur les plages, raquettes, ou sponsors ! 🚗",
            "merci": "De rien ! 😊 Quelle aventure ou produit veux-tu explorer maintenant ?",
            "hello": "Hi ! Ready for Tunisian fun? Ask about paintball, shopping, or sponsors! 🌴"
        };

        // Activity-related keywords
        const activityKeywords = [
            "karting", "paintball", "parc", "aquatique", "peinture", "musique", 
            "surf", "randonnée", "hiking", "safari", "souk", "étoiles", 
            "stargazing", "cuisine", "atelier", "plongée", "diving", "camping", 
            "quad", "parapente", "pêche", "voile", "concert", "exposition", 
            "théâtre", "jeux", "manèges", "trampoline", "ninja"
        ];

        // Handle chat input
        const chatInput = document.getElementById('chat-input');
        const chatSend = document.getElementById('chat-send');
        const chatMessages = document.getElementById('chat-messages');
        
        // Boutons créatifs
        const photoBtn = document.getElementById('photo-btn');
        const locationBtn = document.getElementById('location-btn');
        const emojiBtn = document.getElementById('emoji-btn');
        const surpriseBtn = document.getElementById('surprise-btn');
        const photoInput = document.getElementById('photo-input');
        const emojiPanel = document.getElementById('emoji-panel');

        // Initialiser les événements
        chatSend.addEventListener('click', () => {
            const userQuery = chatInput.value.trim();
            if (userQuery) {
                handleUserQuery(userQuery);
                chatInput.value = '';
            }
        });

        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && chatInput.value.trim()) {
                handleUserQuery(chatInput.value.trim());
                chatInput.value = '';
            }
        });

        // Gestion des boutons créatifs
        photoBtn.addEventListener('click', () => {
            photoInput.click();
        });

        photoInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                handlePhotoUpload(file);
            }
        });

        locationBtn.addEventListener('click', () => {
            handleLocationShare();
        });

        emojiBtn.addEventListener('click', () => {
            emojiPanel.style.display = emojiPanel.style.display === 'none' ? 'block' : 'none';
        });

        surpriseBtn.addEventListener('click', () => {
            handleSurprise();
        });

        // Gestion des emojis
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('emoji-option')) {
                const emoji = e.target.getAttribute('data-emoji');
                const text = e.target.textContent;
                handleEmojiSelect(emoji, text);
                emojiPanel.style.display = 'none';
            }
        });

        // Fermer le panel d'emojis si on clique ailleurs
        document.addEventListener('click', (e) => {
            if (!emojiBtn.contains(e.target) && !emojiPanel.contains(e.target)) {
                emojiPanel.style.display = 'none';
            }
        });

        // Fonction pour gérer l'upload de photo
        function handlePhotoUpload(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageUrl = e.target.result;
                addPhotoMessage(imageUrl, file.name);
                
                // Réponse du bot
                setTimeout(() => {
                    const response = creativeResponses.photo[Math.floor(Math.random() * creativeResponses.photo.length)];
                    addBotMessage(response);
                }, 1500);
            };
            reader.readAsDataURL(file);
        }

        // Fonction pour partager la localisation
        function handleLocationShare() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        addLocationMessage(lat, lng);
                        
                        // Réponse du bot
                        setTimeout(() => {
                            const response = creativeResponses.location[Math.floor(Math.random() * creativeResponses.location.length)];
                            addBotMessage(response);
                        }, 1500);
                    },
                    (error) => {
                        addMessage("Partager ma localisation", "Impossible d'accéder à votre localisation. Vérifiez les permissions de votre navigateur. 📍");
                    }
                );
            } else {
                addMessage("Partager ma localisation", "La géolocalisation n'est pas supportée par votre navigateur. 😔");
            }
        }

        // Fonction pour gérer la sélection d'emoji
        function handleEmojiSelect(emoji, text) {
            addMessage(text, `${emoji} Super choix ! Voici ce que je peux te dire sur ${text.split(' ')[1]} en Tunisie ! ✨`);
        }

        // Fonction surprise
        function handleSurprise() {
            const response = creativeResponses.surprise[Math.floor(Math.random() * creativeResponses.surprise.length)];
            addMessage("Surprise moi !", response);
        }

        // Ajouter un message photo
        function addPhotoMessage(imageUrl, fileName) {
            messageCounter++;
            const messageId = `photo_${messageCounter}`;
            
            const userMsg = document.createElement('div');
            userMsg.className = 'user-message photo-message';
            userMsg.setAttribute('data-message-id', messageId);
            
            userMsg.innerHTML = `
                <div class="message-content">
                    <div class="photo-container">
                        <img src="${imageUrl}" alt="${fileName}" class="shared-photo">
                        <div class="photo-overlay">
                            <i class="fas fa-camera"></i>
                            <span>${fileName}</span>
                        </div>
                    </div>
                </div>
                <div class="message-reactions">
                    <button class="reaction-btn" data-reaction="👍">👍</button>
                    <button class="reaction-btn" data-reaction="❤️">❤️</button>
                    <button class="reaction-btn" data-reaction="😊">😊</button>
                    <button class="reaction-btn" data-reaction="🔥">🔥</button>
                </div>
            `;
            
            chatMessages.appendChild(userMsg);
            addReactionEvents(userMsg);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Ajouter un message de localisation
        function addLocationMessage(lat, lng) {
            messageCounter++;
            const messageId = `location_${messageCounter}`;
            
            const userMsg = document.createElement('div');
            userMsg.className = 'user-message location-message';
            userMsg.setAttribute('data-message-id', messageId);
            
            userMsg.innerHTML = `
                <div class="message-content">
                    <div class="location-container">
                        <div class="location-info">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="location-details">
                                <span class="location-title">Ma position</span>
                                <span class="location-coords">Lat: ${lat.toFixed(4)}, Lng: ${lng.toFixed(4)}</span>
                            </div>
                        </div>
                        <div class="location-map">
                            <div class="map-placeholder">
                                <i class="fas fa-map"></i>
                                <span>Carte</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="message-reactions">
                    <button class="reaction-btn" data-reaction="👍">👍</button>
                    <button class="reaction-btn" data-reaction="❤️">❤️</button>
                    <button class="reaction-btn" data-reaction="😊">😊</button>
                    <button class="reaction-btn" data-reaction="🔥">🔥</button>
                </div>
            `;
            
            chatMessages.appendChild(userMsg);
            addReactionEvents(userMsg);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Add message to chat
        function addMessage(question, answer) {
            messageCounter++;
            const userMessageId = `user_${messageCounter}`;
            const botMessageId = `bot_${messageCounter}`;
            
            // Add user question
            const userMsg = document.createElement('div');
            userMsg.className = 'user-message';
            userMsg.setAttribute('data-message-id', userMessageId);
            userMsg.innerHTML = `
                <div class="message-content">${question}</div>
                <div class="message-reactions">
                    <button class="reaction-btn" data-reaction="👍">👍</button>
                    <button class="reaction-btn" data-reaction="❤️">❤️</button>
                    <button class="reaction-btn" data-reaction="😊">😊</button>
                    <button class="reaction-btn" data-reaction="🔥">🔥</button>
                </div>
            `;
            chatMessages.appendChild(userMsg);
            addReactionEvents(userMsg);

            // Add typing animation
            const typingMsg = document.createElement('div');
            typingMsg.className = 'typing';
            typingMsg.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
            chatMessages.appendChild(typingMsg);

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Show answer after 3s
            setTimeout(() => {
                typingMsg.remove();
                addBotMessage(answer);
            }, 3000);
        }

        // Ajouter seulement un message du bot
        function addBotMessage(answer) {
            messageCounter++;
            const botMessageId = `bot_${messageCounter}`;
            
            const botMsg = document.createElement('div');
            botMsg.className = 'bot-message';
            botMsg.setAttribute('data-message-id', botMessageId);
            botMsg.innerHTML = `
                <div class="message-content">${answer}</div>
                <div class="message-reactions">
                    <button class="reaction-btn" data-reaction="👍">👍</button>
                    <button class="reaction-btn" data-reaction="❤️">❤️</button>
                    <button class="reaction-btn" data-reaction="😊">😊</button>
                    <button class="reaction-btn" data-reaction="🔥">🔥</button>
                </div>
            `;
            chatMessages.appendChild(botMsg);
            addReactionEvents(botMsg);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Ajouter les événements de réaction aux messages
        function addReactionEvents(messageElement) {
            const reactionBtns = messageElement.querySelectorAll('.reaction-btn');
            reactionBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const reaction = this.getAttribute('data-reaction');
                    const messageId = messageElement.getAttribute('data-message-id');
                    addReaction(messageId, reaction, this);
                });
            });
        }

        // Ajouter une réaction à un message
        function addReaction(messageId, reaction, buttonElement) {
            // Vérifier si l'utilisateur a déjà réagi avec cette émotion
            if (buttonElement.classList.contains('reacted')) {
                // Retirer la réaction
                buttonElement.classList.remove('reacted');
                const count = buttonElement.querySelector('.reaction-count');
                if (count) {
                    const currentCount = parseInt(count.textContent) - 1;
                    if (currentCount <= 0) {
                        count.remove();
                    } else {
                        count.textContent = currentCount;
                    }
                }
            } else {
                // Ajouter la réaction
                buttonElement.classList.add('reacted');
                let count = buttonElement.querySelector('.reaction-count');
                if (!count) {
                    count = document.createElement('span');
                    count.className = 'reaction-count';
                    count.textContent = '1';
                    buttonElement.appendChild(count);
                } else {
                    count.textContent = parseInt(count.textContent) + 1;
                }
                
                // Animation de la réaction
                buttonElement.style.transform = 'scale(1.3)';
                setTimeout(() => {
                    buttonElement.style.transform = 'scale(1)';
                }, 200);
            }
        }

        // Handle typed user query with improved matching
        function handleUserQuery(query) {
            const lowerQuery = query.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

            // Check for greetings
            for (const [greet, response] of Object.entries(greetings)) {
                if (lowerQuery.includes(greet)) {
                    addMessage(query, response);
                    return;
                }
            }

            // Check for activity-related queries
            if (activityKeywords.some(keyword => lowerQuery.includes(keyword))) {
                addMessage(query, "Visitez notre partie activité, vous trouverez ce que vous recherchez par détails ! Besoin d'un covoiturage pour y aller ? Visite notre partie services 'Nos trajets' ! 🚗");
                return;
            }

            // Find the best FAQ match based on keyword scoring
            let bestMatch = null;
            let highestScore = 0;

            faqs.forEach(faq => {
                let score = 0;
                faq.keywords.forEach(keyword => {
                    if (lowerQuery.includes(keyword.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''))) {
                        score++;
                    }
                });
                // Additional score for question similarity
                if (faq.question.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').includes(lowerQuery)) {
                    score += 2; // Give higher weight to question similarity
                }
                if (score > highestScore) {
                    highestScore = score;
                    bestMatch = faq;
                }
            });

            if (bestMatch) {
                addMessage(query, bestMatch.answer);
            } else {
                addMessage(query, "Oups, je n'ai pas compris ! Tente des mots comme 'karting', 'shopping', 'sponsors' ou 'raquettes'. Quelle aventure ou produit veux-tu explorer ? 😊");
            }
        }

        // Gérer les clics sur les suggestions
        document.addEventListener('DOMContentLoaded', function() {
            const suggestionBtns = document.querySelectorAll('.suggestion-btn');
            
            suggestionBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const query = this.getAttribute('data-query');
                    chatInput.value = query;
                    handleUserQuery(query);
                    chatInput.value = '';
                    
                    // Générer de nouvelles suggestions basées sur la catégorie
                    generateNewSuggestions(this.querySelector('span').textContent.toLowerCase());
                });
            });

            // Ajouter les événements de réaction au message de bienvenue
            const welcomeMessage = document.querySelector('[data-message-id="welcome"]');
            if (welcomeMessage) {
                addReactionEvents(welcomeMessage);
            }
        });

        // Générer de nouvelles suggestions basées sur la catégorie
        function generateNewSuggestions(category) {
            const suggestionsContainer = document.querySelector('.suggestions-container');
            suggestionsContainer.innerHTML = '';
            
            let newSuggestions = [];
            
            switch(category) {
                case 'karting':
                    newSuggestions = [
                        {icon: 'fas fa-car-side', text: 'Paintball', query: 'Où jouer au paintball ?'},
                        {icon: 'fas fa-water', text: 'Parcs aquatiques', query: 'Où trouver des parcs aquatiques ?'},
                        {icon: 'fas fa-motorcycle', text: 'Quad', query: 'Où faire du quad ?'},
                        {icon: 'fas fa-undo', text: 'Retour', query: 'retour_menu_principal'}
                    ];
                    break;
                case 'produits':
                    newSuggestions = [
                        {icon: 'fas fa-table-tennis', text: 'Raquettes', query: 'Où trouver des raquettes ?'},
                        {icon: 'fas fa-futbol', text: 'Ballons', query: 'Où acheter des ballons ?'},
                        {icon: 'fas fa-tshirt', text: 'Vêtements', query: 'Où trouver des pulls pour le sport ?'},
                        {icon: 'fas fa-undo', text: 'Retour', query: 'retour_menu_principal'}
                    ];
                    break;
                case 'sponsors':
                    newSuggestions = [
                        {icon: 'fas fa-info-circle', text: 'Plus d\'infos', query: 'Quels sont les sponsors de ce site ?'},
                        {icon: 'fas fa-question-circle', text: 'Avantages', query: 'Quels sont les avantages des sponsors ?'},
                        {icon: 'fas fa-handshake', text: 'Partenariats', query: 'Comment devenir sponsor ?'},
                        {icon: 'fas fa-undo', text: 'Retour', query: 'retour_menu_principal'}
                    ];
                    break;
                case 'raquettes':
                    newSuggestions = [
                        {icon: 'fas fa-table-tennis', text: 'Tennis', query: 'Où trouver des raquettes de tennis ?'},
                        {icon: 'fas fa-table-tennis', text: 'Padel', query: 'Où trouver des raquettes de padel ?'},
                        {icon: 'fas fa-shopping-cart', text: 'Acheter', query: 'Comment acheter des raquettes ?'},
                        {icon: 'fas fa-undo', text: 'Retour', query: 'retour_menu_principal'}
                    ];
                    break;
                default:
                    newSuggestions = [
                        {icon: 'fas fa-flag-checkered', text: 'Karting', query: 'Où faire du karting ?'},
                        {icon: 'fas fa-shopping-bag', text: 'Produits', query: 'Quels produits puis-je trouver ?'},
                        {icon: 'fas fa-handshake', text: 'Sponsors', query: 'Quels sont les sponsors ?'},
                        {icon: 'fas fa-table-tennis', text: 'Raquettes', query: 'Où trouver des raquettes ?'}
                    ];
            }
            
            newSuggestions.forEach(suggestion => {
                const btn = document.createElement('button');
                btn.className = 'suggestion-btn';
                if (suggestion.query === 'retour_menu_principal') {
                    btn.setAttribute('data-query', 'menu principal');
                } else {
                    btn.setAttribute('data-query', suggestion.query);
                }
                
                const icon = document.createElement('i');
                icon.className = suggestion.icon;
                
                const span = document.createElement('span');
                span.textContent = suggestion.text;
                
                btn.appendChild(icon);
                btn.appendChild(span);
                
                btn.addEventListener('click', function() {
                    const query = this.getAttribute('data-query');
                    if (query === 'menu principal') {
                        generateNewSuggestions('default');
                        addMessage('Menu principal', 'Voici les options principales. Que voudrais-tu explorer ? 😊');
                    } else {
                        chatInput.value = query;
                        handleUserQuery(query);
                        chatInput.value = '';
                        
                        // Générer de nouvelles suggestions basées sur la catégorie
                        generateNewSuggestions(this.querySelector('span').textContent.toLowerCase());
                    }
                });
                
                suggestionsContainer.appendChild(btn);
            });
            
            // Animation des nouvelles suggestions
            const btns = suggestionsContainer.querySelectorAll('.suggestion-btn');
            btns.forEach((btn, index) => {
                btn.style.animationDelay = `${index * 0.1}s`;
                btn.classList.add('pop-in');
            });
        }
    </script>
    <style>
        /* Section Chatbot avec nouveau dégradé */
.chatbot-section {
  background-color:rgb(244, 244, 244); /* Couleur de fond */
  padding: 3rem 1rem;
}

@media (min-width: 640px) {
  .chatbot-section {
    padding: 3rem 2rem;
  }
}

@media (min-width: 1024px) {
  .chatbot-section {
    padding: 3rem 4rem;
  }
}

/* Titre */
.chatbot-title {
  font-size: 2.5rem;
  font-weight: 700;
  text-align: center;
  color: #a64dff;
  margin-bottom: 2.5rem;
  opacity: 0;
  transform: translateY(-20px);
  animation: bounceIn 0.8s ease-out forwards;
}

/* Container principal */
.chatbot-container {
  max-width: 42rem;
  margin: 0 auto;
}

/* Fenêtre de chat */
.chat-window {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border: 2px solid #a29bfe;
  opacity: 0;
  transform: scale(0.95);
  animation: scaleIn 0.5s ease-out forwards;
  animation-delay: 0.3s;
}

/* En-tête du chat */
.chat-header {
  background: linear-gradient(to right, #a29bfe, #ffb1d3);
  color: white;
  padding: 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.chat-header i {
  font-size: 1.25rem;
}

.chat-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  margin: 0;
  flex-grow: 1;
}

.chat-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.status-dot {
  width: 8px;
  height: 8px;
  background: #4ade80;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

/* Zone des messages */
.chat-messages {
  padding: 1rem;
  height: 16rem;
  overflow-y: auto;
  background: linear-gradient(135deg, #f8f4ff, #fff0f8);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* Messages du bot */
.bot-message {
  background: linear-gradient(135deg, #a29bfe, #9b8bfe);
  color: white;
  padding: 0.75rem;
  border-radius: 15px 15px 15px 5px;
  max-width: 80%;
  align-self: flex-start;
  opacity: 0;
  transform: translateY(10px);
  animation: fadeInUp 0.3s ease-out forwards;
  box-shadow: 0 2px 8px rgba(162, 155, 254, 0.3);
  position: relative;
}

/* Messages de l'utilisateur */
.user-message {
  background: linear-gradient(135deg, #ffb1d3, #ff9ac7);
  color: white;
  padding: 0.75rem;
  border-radius: 15px 15px 5px 15px;
  max-width: 80%;
  align-self: flex-end;
  opacity: 0;
  transform: translateY(10px);
  animation: fadeInUp 0.3s ease-out forwards;
  box-shadow: 0 2px 8px rgba(255, 177, 211, 0.3);
  position: relative;
}

/* Contenu des messages */
.message-content {
  margin-bottom: 0.5rem;
}

/* Réactions aux messages */
.message-reactions {
  display: flex;
  gap: 0.25rem;
  margin-top: 0.5rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.bot-message:hover .message-reactions,
.user-message:hover .message-reactions {
  opacity: 1;
}

.reaction-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.75rem;
  position: relative;
}

.reaction-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.reaction-btn.reacted {
  background: rgba(255, 255, 255, 0.4);
  transform: scale(1.1);
}

.reaction-count {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #ff6b6b;
  color: white;
  border-radius: 50%;
  width: 16px;
  height: 16px;
  font-size: 0.6rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

/* Messages avec photos */
.photo-message .message-content {
  padding: 0;
}

.photo-container {
  position: relative;
  border-radius: 10px;
  overflow: hidden;
  max-width: 250px;
}

.shared-photo {
  width: 100%;
  height: auto;
  display: block;
  border-radius: 10px;
}

.photo-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
  color: white;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
}

.photo-overlay i {
  font-size: 1rem;
}

/* Messages de localisation */
.location-message .message-content {
  padding: 0;
}

.location-container {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.location-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.location-info i {
  font-size: 1.5rem;
  color: #ff6b6b;
}

.location-details {
  display: flex;
  flex-direction: column;
}

.location-title {
  font-weight: 600;
  font-size: 0.9rem;
}

.location-coords {
  font-size: 0.75rem;
  opacity: 0.8;
}

.location-map {
  margin-top: 0.5rem;
}

.map-placeholder {
  background: rgba(255, 255, 255, 0.1);
  border: 2px dashed rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: rgba(255, 255, 255, 0.8);
}

.map-placeholder i {
  font-size: 1.5rem;
}

/* Animation de frappe */
.typing {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 10px;
  background: linear-gradient(135deg, #a29bfe, #9b8bfe);
  color: white;
  border-radius: 15px 15px 15px 5px;
  max-width: 80%;
  align-self: flex-start;
  box-shadow: 0 2px 8px rgba(162, 155, 254, 0.3);
}

.typing-dot {
  width: 8px;
  height: 8px;
  background: white;
  border-radius: 50%;
  animation: typingDot 1s infinite;
}

.typing-dot:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-dot:nth-child(3) {
  animation-delay: 0.4s;
}

/* Container de saisie */
.chat-input-container {
  padding: 1rem;
  background: white;
  border-top: 1px solid #a29bfe;
}

.input-wrapper {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

/* Champ de saisie */
#chat-input {
  flex: 1;
  padding: 0.75rem;
  border: 2px solid #a29bfe;
  border-radius: 0.5rem;
  outline: none;
  transition: border-color 0.3s ease;
  animation: pulseBorder 2s ease-in-out infinite;
  min-width: 200px;
}

#chat-input:focus {
  border-color: #ffb1d3;
  animation: none;
}

/* Boutons créatifs */
.creative-buttons {
  display: flex;
  gap: 0.25rem;
  flex-wrap: wrap;
}

.creative-btn {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 50%;
  background: linear-gradient(135deg, #a29bfe, #ffb1d3);
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 5px rgba(162, 155, 254, 0.3);
}

.creative-btn:hover {
  background: linear-gradient(135deg, #ffb1d3, #a29bfe);
  transform: translateY(-2px) scale(1.1);
  box-shadow: 0 4px 8px rgba(162, 155, 254, 0.4);
}

.creative-btn:active {
  transform: translateY(0) scale(0.95);
}

/* Bouton d'envoi */
#chat-send {
  padding: 0.75rem;
  background: linear-gradient(135deg, #a29bfe, #ffb1d3);
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(162, 155, 254, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
}

#chat-send:hover {
  background: linear-gradient(135deg, #ffb1d3, #a29bfe);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(162, 155, 254, 0.4);
}

/* Panel d'emojis */
.emoji-panel {
  position: absolute;
  bottom: 100%;
  right: 0;
  background: white;
  border: 2px solid #a29bfe;
  border-radius: 1rem;
  padding: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  margin-bottom: 0.5rem;
}

.emoji-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
  min-width: 200px;
}

.emoji-option {
  background: linear-gradient(135deg, #a29bfe, #ffb1d3);
  color: white;
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.85rem;
  text-align: left;
}

.emoji-option:hover {
  background: linear-gradient(135deg, #ffb1d3, #a29bfe);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(162, 155, 254, 0.3);
}

/* Scrollbar personnalisée */
.chat-messages::-webkit-scrollbar {
  width: 8px;
}

.chat-messages::-webkit-scrollbar-track {
  background: #f8f4ff;
}

.chat-messages::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #a29bfe, #ffb1d3);
  border-radius: 4px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #9b8bfe, #ff9ac7);
}

/* Animations */
@keyframes bounceIn {
  0% {
    opacity: 0;
    transform: translateY(-20px);
  }
  60% {
    opacity: 1;
    transform: translateY(5px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulseBorder {
  0%,
  100% {
    border-color: #a29bfe;
  }
  50% {
    border-color: #ffb1d3;
  }
}

@keyframes typingDot {
  0%,
  20% {
    transform: translateY(0);
    opacity: 1;
  }
  50% {
    transform: translateY(-5px);
    opacity: 0.5;
  }
  80%,
  100% {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Responsive */
@media (max-width: 640px) {
  .chatbot-title {
    font-size: 1.5rem;
  }

  .chat-messages {
    height: 14rem;
  }

  .bot-message,
  .user-message {
    max-width: 90%;
  }

  .message-reactions {
    opacity: 1;
  }

  .input-wrapper {
    flex-direction: column;
    gap: 0.75rem;
  }

  #chat-input {
    width: 100%;
    min-width: auto;
  }

  .creative-buttons {
    justify-content: center;
    width: 100%;
  }

  #chat-send {
    width: 100%;
    justify-content: center;
  }

  .emoji-panel {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    right: auto;
    width: 90%;
    max-width: 300px;
  }

  .emoji-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .photo-container {
    max-width: 200px;
  }

  .creative-btn {
    width: 35px;
    height: 35px;
  }
}

/* Suggestions de chat */
.chat-suggestions {
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, #f8f4ff, #fff0f8);
  border-top: 1px solid rgba(162, 155, 254, 0.3);
}

.suggestion-title {
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.5rem;
  font-style: italic;
}

.suggestions-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.suggestion-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: linear-gradient(135deg, #a29bfe, #ffb1d3);
  color: white;
  border: none;
  border-radius: 2rem;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 5px rgba(162, 155, 254, 0.3);
  flex-grow: 1;
  justify-content: center;
  max-width: calc(50% - 0.25rem);
}

.suggestion-btn:hover {
  background: linear-gradient(135deg, #ffb1d3, #a29bfe);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(162, 155, 254, 0.4);
}

.suggestion-btn i {
  font-size: 0.9rem;
}

/* Animation pour les suggestions */
.pop-in {
  animation: popIn 0.5s cubic-bezier(0.26, 0.53, 0.74, 1.48) forwards;
  opacity: 0;
  transform: scale(0.8);
}

@keyframes popIn {
  0% {
    opacity: 0;
    transform: scale(0.8);
  }
  60% {
    opacity: 1;
    transform: scale(1.05);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

/* Responsive pour les suggestions */
@media (max-width: 640px) {
  .suggestion-btn {
    max-width: 100%;
  }
}

/* Animations spéciales pour les boutons créatifs */
@keyframes photoFlash {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.2);
    background: linear-gradient(135deg, #ff6b6b, #ffa500);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes locationPing {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.2);
    background: linear-gradient(135deg, #4ecdc4, #44a08d);
  }
  100% {
    transform: scale(1);
  }
}

@keyframes surpriseSparkle {
  0% {
    transform: scale(1) rotate(0deg);
  }
  50% {
    transform: scale(1.2) rotate(180deg);
    background: linear-gradient(135deg, #ffd700, #ff6b6b);
  }
  100% {
    transform: scale(1) rotate(360deg);
  }
}

.creative-btn:active {
  animation-duration: 0.3s;
}

#photo-btn:active {
  animation: photoFlash 0.3s ease;
}

#location-btn:active {
  animation: locationPing 0.3s ease;
}

#surprise-btn:active {
  animation: surpriseSparkle 0.3s ease;
}

    </style>
</section>

    <section class="carpooling-home">
        <div class="carpooling-container">
            <div class="carpooling-content">
                <h1>Votre sécurité est notre priorité</h1> 
                <ul class="carpooling-features">
                    <p>Chez Click'N'Go, nous nous sommes fixé comme objectif de construire une communauté de covoiturage fiable et digne de confiance à travers le monde.
                    Rendez-vous sur notre page Confiance et sécurité pour explorer les différentes fonctionnalités disponibles pour covoiturer sereinement.</p>
                </ul>
                <button class="carpooling-btn">En savoir plus</button>
            </div>
            <div class="carpooling-image">
                <img src="/mvcCovoiturage/public/images/cov.webp" alt="">
            </div>
        </div>
    </section>


    
    
    <div class="footer-wrapper">
       
        <div class="newsletter">
            <div class="newsletter-left">
                <h3>Abonnez-vous à notre</h3>
                <h1>Click'N'Go</h1>
            </div>
            <div class="newsletter-right">
                <div class="newsletter-input">
                    <input type="text" placeholder="Entrez votre adresse e-mail" />
                    <button class="fotter-btn">Valider</button>
                </div>
            </div>
        </div>

        <div class="footer-content">
            <div class="footer-main">
                <div class="footer-brand">
                    <img src="images/logo.png" alt="click'N'go Logo" class="footer-logo">
                </div>
                <p>Rejoignez nous aussi sur :</p>
                <div class="social-icons">
                    <a href="#" style="--color: #0072b1" class="icon"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="#" style="--color: #E1306C" class="icon"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" style="--color: #FF0050" class="icon"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="#" style="--color: #4267B2" class="icon"><i class="fa-brands fa-facebook"></i></a>
                </div>
            </div>

            <div class="links">
                <p>Moyens de paiement</p>
                <div class="payment-methods">
                    <img src="images/visa.webp" alt="Visa">
                    <img src="images/mastercard-v2.webp" alt="Mastercard">
                    <img src="images/logo-cb.webp" alt="CB" class="cb-logo">
                    <img src="images/paypal.webp" alt="Paypal" class="paypal">
                </div>
            </div>

            <div class="links">
                <p>À propos</p>
                <a href="/mvcact/view/front%20office/about.php">À propos</a>

               
                <a href="/mvcact/view/front%20office/presse.php">Presse</a>

                <a href="/mvcact/view/front%20office/nous-rejoindre.php">Nous rejoindre</a>

            </div>

            <div class="links">
                <p>Liens utiles</p>
             <a href="/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]">Devenir partenaire</a>



                <a href="/mvcact/view/front office/faq.php" class="hover:text-[#be3cf0]">FAQ</a>
               <a href="/mvcact/view/front office/avis.php" class="hover:text-[#be3cf0]">Avis</a>
            </div>
        </div>

        <div class="footer-section">
            <hr>
            <div class="footer-separator"></div>
            <div class="footer-bottom">
                <p>© click'N'go 2025 - tous droits réservés</p>
                <div class="footer-links-bottom">
                   <a href="/mvcact/view/front office/conditions-generales.php" class="hover:text-[#be3cf0]">Conditions générales</a>

<a href="/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]">Devenir partenaire</a>
                </div>
            </div>
        </div>
    </div>
<script>
        // Static global variable for user ID
        const id_user = 12345; // You can set any value you want here
    </script>


<style>

    .carpooling-home {
    padding: 60px 7%;
    text-align: center;
}
.fotter-btn {
     background: linear-gradient(to right, #A29BFE, #FFB1D3);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: bold;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
}
.carpooling-container {
    background: linear-gradient(to right, #2d2f5f, #6b248f, #0a6d9b);
    border-radius: 15px;
    padding: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
}

.carpooling-content {
    color: white;
    margin-bottom: 30px;
    text-align: center;
}

.carpooling-content h2 {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 20px;
}

.carpooling-features {
    list-style: none;
    margin-bottom: 25px;
}

.feature-item {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    font-size: 1.1rem;
}

.feature-icon {
    width: 30px;
    margin-right: 15px;
}

.carpooling-btn {
    display: block;
    margin: 0 auto;
    background: linear-gradient(to right, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: bold;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
    text-decoration: none;
    text-align: center;
    width: fit-content; /* Pour un meilleur ajustement */
}

.carpooling-btn:hover {
    background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.carpooling-image img {
    width: 100%;
    max-width: 400px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

@media (min-width: 768px) {
    .carpooling-container {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 40px;
    }
    
    .carpooling-content {
        text-align: left;
        margin-bottom: 0;
        margin-right: 40px;
        flex: 1;
    }
    
    .carpooling-image {
        flex-shrink: 0;
    }
}


.footer-wrapper {
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    background-color: #f5f5f5;
    padding: 3rem 0;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    margin-top: 50px;
    background-attachment: fixed;
    background-position: center;
    background-size: cover;
    color: #333;
}

/* ===== FOOTER STYLES ===== */

.footer-wrapper {
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    background-color: white;
    background-attachment: fixed;
    background-position: center;
    background-size: cover;
    color: #333;
}

.footer-logo {
    width: 200px;
    margin: 0 auto;
    display: flex;
    justify-content: center;
    align-items: center;
}

.newsletter {
    display: flex;
    width: 100%;
    position: relative;
    top: 60px;
    max-width: 1000px;
    margin: auto;
    background-color: #303035;
    justify-content: space-around;
    align-items: center;
    padding: 20px 15px;
    border-radius: 10px;
}

.newsletter-left h2 {
    color: #ffffff;
    text-transform: uppercase;
    font-size: 1rem;
    opacity: 0.5;
    letter-spacing: 1px;
}

.newsletter-left h1 {
    color: #ffffff;
    text-transform: uppercase;
    font-size: 1.5rem;
}

.newsletter-right {
    width: 500px;
}

.newsletter-input {
    background-color: #ffffff;
    padding: 5px;
    border-radius: 20px;
    display: flex;
    justify-content: space-between;
}

.newsletter-input input {
    border: none;
    outline: none;
    background: transparent;
    width: 80%;
    padding-left: 10px;
    font-weight: 600;
}

.newsletter-input button {
    background-color: #201e1e;
    padding: 9px 15px;
    border-radius: 15px;
    color: #ffffff;
    cursor: pointer;
    border: none;
}

.newsletter-input button:hover {
    background-color: #3a3939;
}

.footer-content {
    background-color:  #f4f4f4;
    padding: 100px 40px 40px;
    width: 100%;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

.footer-main {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    margin-bottom: 20px;
}

.footer-main h2 {
    color: #ffffff;
    font-size: 1.6rem;
}

.footer-main p {
    color: #1c3f50;
    font-size: 0.8rem;
    line-height: 1.3rem;
}

.footer-bottom {
    background-color:  #f4f4f4;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    color: #666;
    padding: 20px 40px 20px;
}

.footer-links-bottom a {
    margin-left: 20px;
    color: #666;
    text-decoration: none;
}

.footer-links-bottom a:hover {
    text-decoration: underline;
}

.payment-methods {
    display: flex;
    align-items: center;
    gap: 12px;
}

.payment-methods img {
    width: 50px;
    height: auto;
    margin-right: 10px;
    vertical-align: middle;
    object-fit: contain;
}

.cb-logo {
    transform: translateY(2px);
    width: 50px;
}

.social-links {
    margin: 15px 0px;
    display: flex;
    gap: 8px;
}

.social-links a {
    padding: 5px;
    background-color: black;
    border-radius: 5px;
    transition: 0.5s;
    text-decoration: none;
}

.social-links a:hover {
    opacity: 0.7;
}

.social-links a i {
    margin: 2px;
    font-size: 1.1rem;
    color: #201e1e;
}

.links {
    display: flex;
    flex-direction: column;
    width: 200px;
    margin: 40px 20px;
}

.links p {
    color: #1c3f50;
    font-size: 1.1rem;
    margin-bottom: 10px;
    font-weight: bold;
}

.links a {
    color: #1c3f50;
    text-decoration: none;
    margin: 5px 0;
    opacity: 0.7;
    font-size: 0.9rem;
}

.links a:hover {
    opacity: 1;
}

.social-icons {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 10px;
}

footer {
    background-color: #f8f8f8;
    padding: 3rem 0;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
}

footer .footer-container {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 3rem;
}

footer .footer-section h3 {
    font-size: 1.25rem;
    font-weight: bold;
    margin-bottom: 1.5rem;
}

footer .footer-section ul {
    list-style: none;
    padding: 0;
}

footer .footer-section ul li {
    margin-bottom: 0.5rem;
}

footer .footer-section .social-media a {
    font-size: 1.25rem;
    margin: 0 0.5rem;
    color: #333;
    transition: color 0.3s;
}

footer .footer-section .social-media a:hover {
    color: #ff50aa;
}


.footer-logo {
    width: 150px;
    margin-bottom: 10px;
    display: block;
}

.newsletter {
    display: flex;
    width: 100%;
    position: relative;
    top: 60px;
    max-width: 1000px;
    margin: auto;
    background-color: #303035;
    justify-content: space-around;
    align-items: center;
    padding: 20px 15px;
    border-radius: 10px;
}

.newsletter-left h2 {
    color: #ffffff;
    text-transform: uppercase;
    font-size: 1rem;
    opacity: 0.5;
    letter-spacing: 1px;
}

.newsletter-left h1 {
    color: #ffffff;
    text-transform: uppercase;
    font-size: 1.5rem;
}

.newsletter-right {
    width: 500px;
}

.newsletter-input {
    background-color: #ffffff;
    padding: 5px;
    border-radius: 20px;
    display: flex;
    justify-content: space-between;
}

.newsletter-input input {
    border: none;
    outline: none;
    background: transparent;
    width: 80%;
    padding-left: 10px;
    font-weight: 600;
}

.newsletter-input button {
    background-color: #201e1e;
    padding: 9px 15px;
    border-radius: 15px;
    color: #ffffff;
    cursor: pointer;
    border: none;
}

.newsletter-input button:hover {
    background-color: #3a3939;
}

.footer-content {
    background-color:  #f4f4f4;
    padding: 100px 40px 40px;
    width: 100%;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

.footer-main {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    margin-bottom: 20px;
}

.footer-main h2 {
    color: #ffffff;
    font-size: 1.6rem;
}

.footer-main p {
    color: #1c3f50;
    font-size: 0.8rem;
    line-height: 1.3rem;
}

.social-links {
    margin: 15px 0px;
    display: flex;
    gap: 8px;
}

.social-links a {
    padding: 5px;
    background-color: black;
    border-radius: 5px;
    transition: 0.5s;
    text-decoration: none;
}

.social-links a:hover {
    opacity: 0.7;
}

.social-links a i {
    margin: 2px;
    font-size: 1.1rem;
    color: #201e1e;
}

.links {
    display: flex;
    flex-direction: column;
    width: 200px;
    margin: 40px 20px;
}

.links p {
    color: #1c3f50;
    font-size: 1.1rem;
    margin-bottom: 10px;
    font-weight: bold;
}

.links a {
    color: #1c3f50;
    text-decoration: none;
    margin: 5px 0;
    opacity: 0.7;
    font-size: 0.9rem;
}

.links a:hover {
    opacity: 1;
}

.social-icons {
    display: flex;
    flex-direction: row; /* ✅ forcer l'affichage en ligne */
    flex-wrap: nowrap;   /* ✅ pas de retour à la ligne */
    justify-content: center; /* ✅ centrer les icônes horizontalement */
    align-items: center;
    gap: 15px;
    margin-top: 10px;
}


@import url(https://use.fontawesome.com/releases/v5.0.8/css/all.css);

.icon {
    margin: 0 10px;
    margin-bottom: 30px;
    border-radius: 50%;
    box-sizing: border-box;
    background: transparent;
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    text-decoration: none !important;
    transition: 0.5s;
    color: var(--color);
    font-size: 2.5em;
    -webkit-box-reflect: below 5px linear-gradient(to bottom, rgba(0, 0, 0, 0),rgba(0, 0, 0, 0.2));
}

.icon i {
    color: var(--color);
}

.icon:hover {
    background: var(--color);
    box-shadow: 0 0 5px var(--color),
                0 0 25px var(--color), 
                0 0 50px var(--color),
                0 0 200px var(--color);
}

/* ✅ changer la couleur de l’icône en noir au survol */
.icon:hover i {
    color: #050801;
}
.payment-icons img {
    height: 20px;
    margin-right: 20px;
}
/* Add this to your CSS file */
.animated-text {
    animation: fadeInUp 1.5s ease-in-out;
}

@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

</body>
</html>
