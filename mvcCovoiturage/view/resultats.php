<?php
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

  // Log search parameters for debugging
  error_log("resultats.php received: depart='$depart', arrivee='$arrivee'");

  if ($depart && $arrivee) {
      // Filtrer les annonces en fonction des lieux de départ et d'arrivée
      $annonces = $controller->searchAnnonces($depart, $arrivee);
  } else {
      // Si aucun filtre n'est fourni, afficher toutes les annonces
      $annonces = $controller->getAllAnnonces();
  }

  // Log number of announcements found
  error_log("resultats.php found " . count($annonces) . " announcements");

  // Get server time for debugging
  $serverTime = (new DateTime())->format('Y-m-d H:i:s');
  error_log("Server time: $serverTime");
} catch (Exception $e) {
  $errorMessages = explode('<br>', $e->getMessage());
  error_log("Error in resultats.php: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Liste des covoiturages disponibles</title>
   <link rel="stylesheet" href="/Projet Web/mvcCovoiturage/public/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet Geocoder pour la recherche d'adresses -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<!-- Leaflet Locate Control pour la géolocalisation -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.css" />
<script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.js"></script>
<!-- Scripts personnalisés -->
<script src="/clickngo/public/js/leaflet-location-picker.js" defer></script>
<script src="/clickngo/public/js/search-form.js" defer></script>

<style>
/* Styles pour le sélecteur de carte */
.fixed {
position: fixed;
}
.inset-0 {
top: 0;
right: 0;
bottom: 0;
left: 0;
}
.z-50 {
z-index: 50;
}
.flex {
display: flex;
}
.items-center {
align-items: center;
}
.justify-center {
justify-content: center;
}
.justify-between {
justify-content: space-between;
}
.justify-end {
justify-content: flex-end;
}
.bg-black\/50 {
background-color: rgba(0, 0, 0, 0.5);
}
.bg-white {
background-color: white;
}
.w-full {
width: 100%;
}
.max-w-3xl {
max-width: 48rem;
}
.max-h-\[90vh\] {
max-height: 90vh;
}
.overflow-hidden {
overflow: hidden;
}
.rounded-lg {
border-radius: 0.5rem;
}
.shadow-xl {
box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
.p-4 {
padding: 1rem;
}
.p-0 {
padding: 0;
}
.p-2 {
padding: 0.5rem;
}
.px-4 {
padding-left: 1rem;
padding-right: 1rem;
}
.py-2 {
padding-top: 0.5rem;
padding-bottom: 0.5rem;
}
.border-b {
border-bottom-width: 1px;
border-bottom-style: solid;
border-bottom-color: #e5e7eb;
}
.border-t {
border-top-width: 1px;
border-top-style: solid;
border-top-color: #e5e7eb;
}
.border {
border-width: 1px;
border-style: solid;
border-color: #e5e7eb;
}
.rounded {
border-radius: 0.25rem;
}
.rounded-full {
border-radius: 9999px;
}
.text-xl {
font-size: 1.25rem;
line-height: 1.75rem;
}
.font-semibold {
font-weight: 600;
}
.gap-2 {
gap: 0.5rem;
}
.flex-1 {
flex: 1 1 0%;
}
.hover\:bg-gray-100:hover {
background-color: #f3f4f6;
}
.bg-gray-200 {
background-color: #e5e7eb;
}
.hover\:bg-gray-300:hover {
background-color: #d1d5db;
}
.text-white {
color: white;
}
.h-\[400px\] {
height: 400px;
}
.logo {
    width: 120px;
    cursor: pointer;
    margin-right: 120px; /* Even more space between logo and Accueil */
    margin-left: -200px; /* Move logo much further to the left */
    position: relative;
    left: -100px; /* Additional left shift */
}
nav {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background-color: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    z-index: 1000;
    padding: 0.5rem 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* This is important to align items properly */
.flex.items-center.justify-center {
    justify-content: center !important; /* Force center alignment */
}

/* This ensures the logo and menu items are in a row */
.flex.space-x-8.text-lg.font-bold.text-black.relative.items-center {
    display: flex;
    align-items: center;
}

</style>
<style>
  /* === FOND D'ÉCRAN === */
  .form-background {
      min-height: calc(100vh - 200px);
  }

  /* === GRADIENT PERSONNALISÉ === */
  .elsa-gradient-primary {
      background: linear-gradient(to right, #be3cf0, #ff50aa);
  }

  .elsa-gradient-primary-hover:hover {
      background: linear-gradient(to left, #be3cf0, #ff50aa);
  }

  /* === STYLE DE LA LISTE === */
  .annonce-container {
      padding: 6rem 2rem 2rem;
      max-width: 1400px;
      margin: 0 auto;
      position: relative;
  }

  .annonce-wrapper {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(10px);
      border-radius: 1.5rem;
      padding: 3rem;
      border: 1px solid rgba(190, 60, 240, 0.3);
      position: relative;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(190, 60, 240, 0.1);
  }

  /* Background Animation */
  .annonce-wrapper::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(190, 60, 240, 0.05) 0%, rgba(255, 80, 170, 0.05) 50%, transparent 70%);
      animation: rotate 20s linear infinite;
      z-index: -1;
  }

  @keyframes rotate {
      0% {
          transform: rotate(0deg);
      }
      100% {
          transform: rotate(360deg);
      }
  }

  .annonce-wrapper h2 {
      text-align: center;
      font-size: 2.25rem;
      font-weight: 700;
      color: #1F2937;
      margin-bottom: 2.5rem;
      position: relative;
      display: inline-block;
      left: 50%;
      transform: translateX(-50%);
  }

  .annonce-wrapper h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 0;
      height: 3px;
      background: linear-gradient(to right, #be3cf0, #ff50aa);
      animation: lineGrow 1.5s ease-out forwards;
  }

  @keyframes lineGrow {
      to {
          width: 100%;
      }
  }

  .annonce-card {
      background: white;
      border-radius: 1rem;
      padding: 1.5rem;
      position: relative;
      transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
      border-left: 4px solid #be3cf0;
      animation: cardEntrance 0.8s ease-out forwards;
      opacity: 0;
      transform: translateY(30px);
      overflow: hidden;
      margin-bottom: 2rem;
  }

  @keyframes cardEntrance {
      to {
          opacity: 1;
          transform: translateY(0);
      }
  }

  .annonce-card:nth-child(1) { animation-delay: 0.1s; }
  .annonce-card:nth-child(2) { animation-delay: 0.2s; }
  .annonce-card:nth-child(3) { animation-delay: 0.3s; }
  .annonce-card:nth-child(4) { animation-delay: 0.4s; }
  .annonce-card:nth-child(5) { animation-delay: 0.5s; }
  .annonce-card:nth-child(6) { animation-delay: 0.6s; }
  .annonce-card:nth-child(7) { animation-delay: 0.7s; }
  .annonce-card:nth-child(8) { animation-delay: 0.8s; }

  .annonce-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  }

  /* Card Glow Effect on Hover */
  .annonce-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      border-radius: 1rem;
      background: linear-gradient(135deg, rgba(190, 60, 240, 0.3), rgba(255, 80, 170, 0.3));
      opacity: 0;
      transition: opacity 0.5s ease;
      pointer-events: none;
      z-index: -1;
  }

  .annonce-card:hover::after {
      opacity: 0.05;
  }

  .annonce-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      border-bottom: 1px solid rgba(190, 60, 240, 0.2);
      padding-bottom: 1rem;
  }

  .annonce-title {
      font-size: 1.4rem;
      font-weight: 600;
      color: #333;
      transition: transform 0.3s ease;
  }

  .annonce-card:hover .annonce-title {
      transform: scale(1.05);
      color: #be3cf0;
  }

  .annonce-price {
      font-size: 1.6rem;
      font-weight: 700;
      color: #be3cf0;
      transition: transform 0.3s ease;
  }

  .annonce-card:hover .annonce-price {
      transform: scale(1.1);
  }

  .annonce-details {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
  }

  .annonce-detail {
      display: flex;
      align-items: center;
      font-size: 0.95rem;
      color: #4B5563;
      transition: transform 0.3s ease;
  }

  .annonce-card:hover .annonce-detail {
      transform: translateX(5px);
  }

  .annonce-detail i {
      margin-right: 0.75rem;
      color: #be3cf0;
      font-size: 1.2rem;
      transition: transform 0.3s ease;
  }

  .annonce-card:hover .annonce-detail i {
      transform: scale(1.2);
  }

  .annonce-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 1rem;
      border-top: 1px solid rgba(190, 60, 240, 0.2);
  }

  .annonce-places {
      font-size: 1rem;
      font-weight: 600;
      color: #333;
  }

  .btn-reserver {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.6rem 1.2rem;
      border-radius: 2rem;
      font-weight: 600;
      font-size: 0.9rem;
      transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
      position: relative;
      overflow: hidden;
      color: white;
      text-decoration: none;
      border: none;
      cursor: pointer;
      background: linear-gradient(to right, #A29BFE, #FFB1D3);
  }

  .btn-reserver::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: all 0.5s ease;
  }

  .btn-reserver:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(190, 60, 240, 0.3);
  }

  .btn-reserver:hover::before {
      left: 100%;
  }

  /* === POPUP STYLES === */
  .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
  }

  .popup-content {
      background: #F5F5F5;
      border-radius: 1rem;
      padding: 2rem;
      text-align: center;
      max-width: 450px;
      width: 90%;
      animation: bounceIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  }

  @keyframes bounceIn {
      0% { transform: scale(0.5); opacity: 0; }
      60% { transform: scale(1.1); opacity: 1; }
      100% { transform: scale(1); }
  }

  .popup-icon {
      font-size: 3.5rem;
      margin-bottom: 1rem;
      animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
      opacity: 0;
      transform: scale(0.5);
  }

  @keyframes popIn {
      to {
          opacity: 1;
          transform: scale(1);
      }
  }

  .popup-success .popup-icon {
      color: #10b981;
  }

  .popup-error .popup-icon {
      color: #ef4444;
  }

  .popup-title {
      font-size: 1.6rem;
      font-weight: 600;
      color: #1F2937;
      margin-bottom: 0.5rem;
  }

  .popup-message {
      color: #4B5563;
      margin-bottom: 1.5rem;
  }

  .popup-button {
      background: linear-gradient(to right, #be3cf0, #ff50aa);
      color: white;
      padding: 0.6rem 2rem;
      border-radius: 2rem;
      font-weight: 600;
      transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
      position: relative;
      overflow: hidden;
      border: none;
      cursor: pointer;
  }

  .popup-button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
      transition: all 0.5s ease;
  }

  .popup-button:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }

  .popup-button:hover::before {
      left: 100%;
  }

  .popup-success .popup-button {
      background: linear-gradient(to right, #059669, #10b981);
  }

  .popup-error .popup-button {
      background: linear-gradient(to right, #dc2626, #ef4444);
  }

  .empty-state {
      text-align: center;
      padding: 4rem;
      animation: fadeInUp 0.8s ease;
  }

  @keyframes fadeInUp {
      from { 
          opacity: 0; 
          transform: translateY(40px); 
      }
      to { 
          opacity: 1; 
          transform: translateY(0); 
      }
  }

  .empty-state i {
      font-size: 5rem;
      color: #be3cf0;
      margin-bottom: 1.5rem;
      animation: float 3s ease-in-out infinite;
  }

  @keyframes float {
      0% {
          transform: translateY(0px);
      }
      50% {
          transform: translateY(-15px);
      }
      100% {
          transform: translateY(0px);
      }
  }

  .empty-state p {
      font-size: 1.5rem;
      color: #4B5563;
  }

  .debug-info {
      background-color: #f8f8f8;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 20px;
      font-size: 14px;
      color: #333;
  }

  .hidden {
      display: none !important;
  }
</style>
</head>
<body>   

<!-- NAVBAR -->
<nav class="absolute top-0 left-0 w-full z-50 p-4">
    <div class="flex items-center justify-center max-w-7xl mx-auto">
        <div class="flex space-x-8 text-lg  text-black relative">
            
                <div class="flex space-x-8 text-lg  text-black relative items-center">

                        <img src="images/logo.png" class="logo"> 

            <a href="/Projet Web/mvcUtilisateur/View/FrontOffice/index.php" class="hover:text-[#be3cf0]">Accueil</a>
            <a href="/Projet Web/mvcact/view/front office/activite.php" class="hover:text-[#be3cf0]">Activités</a>
            <a href="/Projet Web/mvcEvent/View/FrontOffice/evenemant.php" class="hover:text-[#be3cf0]">Événements</a>
            <a href="/Projet Web/mvcProduit/view/front office/produit.php" class="hover:text-[#be3cf0]">Produits</a>


            <div class="group relative">
                <a href="index.php" class="hover:text-[#be3cf0]  text-lg"> 
                    Transport▾
                </a>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                    <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100"></a>
                   <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100 ">Trouver un covoiturage</a>
                  
                </div>


            </div>


                        <a href="/Projet%20Web/mvcSponsor/crud/view/front/index.php" class="hover:text-[#be3cf0]">Sponsors</a>

                </div>
            
            <!-- LISTE DÉROULANTE SERVICES -->
           
    </div>
</nav>
<br>
<br>
<br>
<br><br><br>


<!-- ANNONCES SECTION -->
<div class="form-background">
  <div class="annonce-container">
    <div class="annonce-wrapper">
      <h2>✨ Covoiturages disponibles ✨</h2>
      
      <!-- Display search criteria and server time for debugging -->
      <?php if ($depart && $arrivee): ?>
        <div class="debug-info">
          <p>Recherche pour : <strong><?php echo htmlspecialchars($depart); ?> → <?php echo htmlspecialchars($arrivee); ?></strong></p>
          <p>Heure du serveur : <strong><?php echo htmlspecialchars($serverTime); ?></strong></p>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($errorMessages)): ?>
        <div id="error-popup" class="popup-overlay popup-error">
          <div class="popup-content">
            <div class="popup-icon">
              <i class="fas fa-exclamation-circle"></i>
            </div>
            <h3 class="popup-title">Erreur</h3>
            <div class="popup-message">
              <?php foreach ($errorMessages as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
              <?php endforeach; ?>
            </div>
            <button onclick="hideErrorPopup()" class="popup-button">Fermer</button>
          </div>
        </div>
      <?php endif; ?>

      <?php if (empty($annonces)): ?>
        <div class="empty-state">
          <i class="fas fa-car-side"></i>
          <p>Aucun covoiturage disponible pour ces critères.</p>
        </div>
      <?php else: ?>
        <div class="annonces-list">
          <?php foreach ($annonces as $annonce): ?>
            <div class="annonce-card">
              <div class="annonce-header">
                <div class="annonce-title">
                  <?php echo htmlspecialchars($annonce->getPrenomConducteur() . ' ' . $annonce->getNomConducteur()); ?>
                </div>
                <div class="annonce-price">
                  <?php echo htmlspecialchars(number_format($annonce->getPrixEstime(), 2)) . ' TND'; ?>
                </div>
              </div>
              
              <div class="annonce-details">
                <div class="annonce-detail">
                  <i class="fas fa-map-marker-alt"></i>
                  <span><?php echo htmlspecialchars($annonce->getLieuDepart()); ?></span>
                </div>
                
                <div class="annonce-detail">
                  <i class="fas fa-map-marked-alt"></i>
                  <span><?php echo htmlspecialchars($annonce->getLieuArrivee()); ?></span>
                </div>
                
                <div class="annonce-detail">
                  <i class="fas fa-calendar-alt"></i>
                  <span><?php echo htmlspecialchars($annonce->getDateDepart()->format('d/m/Y H:i')); ?></span>
                </div>
                
                <div class="annonce-detail">
                  <i class="fas fa-car"></i>
                  <span><?php echo htmlspecialchars($annonce->getTypeVoiture()); ?></span>
                </div>
              </div>
              
              <div class="annonce-footer">
                <div class="annonce-places">
                  Places disponibles: <?php echo htmlspecialchars($annonce->getNombrePlaces()); ?>
                </div>
                <?php
                $annonceId = $annonce->getIdConducteur();
                if ($annonceId) {
                  echo '<a href="/clickngo/view/demande_form.php?id=' . htmlspecialchars($annonceId) . '" class="btn-reserver">Réserver</a>';
                } else {
                  echo '<span class="btn-reserver disabled" style="background: grey; cursor: not-allowed;">Réserver (ID manquant)</span>';
                }
                ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Success Popup -->
<div id="success-popup" class="popup-overlay popup-success hidden">
  <div class="popup-content">
    <div class="popup-icon">
      <i class="fas fa-check-circle"></i>
    </div>
    <h3 class="popup-title">Succès!</h3>
    <div class="popup-message" id="success-message"></div>
    <button onclick="hideSuccessPopup()" class="popup-button">Fermer</button>
  </div>
</div>

<!-- Error Popup -->
<div id="error-popup" class="popup-overlay popup-error hidden">
  <div class="popup-content">
    <div class="popup-icon">
      <i class="fas fa-exclamation-circle"></i>
    </div>
    <h3 class="popup-title">Erreur</h3>
    <div class="popup-message" id="error-message"></div>
    <button onclick="hideErrorPopup()" class="popup-button">Fermer</button>
  </div>
</div>

<script>
  function showSuccessPopup(message) {
      document.getElementById('success-message').textContent = message;
      document.getElementById('success-popup').classList.remove('hidden');
  }
  
  function hideSuccessPopup() {
      document.getElementById('success-popup').classList.add('hidden');
  }
  
  function showErrorPopup(message) {
      document.getElementById('error-message').textContent = message;
      document.getElementById('error-popup').classList.remove('hidden');
  }
  
  function hideErrorPopup() {
      document.getElementById('error-popup').classList.add('hidden');
  }
  
  function reserverAnnonce(id) {
      showSuccessPopup('Votre réservation a été enregistrée! Nous vous contacterons bientôt.');
  }

  window.addEventListener('DOMContentLoaded', () => {
      <?php if (!empty($errorMessages)): ?>
          showErrorPopup("<?php echo addslashes(implode('\n', $errorMessages)); ?>");
      <?php endif; ?>
      
      // Apply staggered animation to cards
      const cards = document.querySelectorAll('.annonce-card');
      cards.forEach((card, index) => {
          card.style.animationDelay = `${0.1 * index}s`;
      });
  });
</script>
    
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
                <a href="/Projet%20Web/mvcact/view/front%20office/about.php">À propos</a>

               
                <a href="/Projet%20Web/mvcact/view/front%20office/presse.php">Presse</a>

                <a href="/Projet%20Web/mvcact/view/front%20office/nous-rejoindre.php">Nous rejoindre</a>

            </div>

            <div class="links">
                <p>Liens utiles</p>
             <a href="/Projet Web/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]">Devenir partenaire</a>



                <a href="/Projet Web/mvcact/view/front office/faq.php" class="hover:text-[#be3cf0]">FAQ</a>
               <a href="/Projet Web/mvcact/view/front office/avis.php" class="hover:text-[#be3cf0]">Avis</a>
            </div>
        </div>

        <div class="footer-section">
            <hr>
            <div class="footer-separator"></div>
            <div class="footer-bottom">
                <p>© click'N'go 2025 - tous droits réservés</p>
                <div class="footer-links-bottom">
                   <a href="/Projet Web/mvcact/view/front office/conditions-generales.php" class="hover:text-[#be3cf0]">Conditions générales</a>

<a href="/Projet Web/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]">Devenir partenaire</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
