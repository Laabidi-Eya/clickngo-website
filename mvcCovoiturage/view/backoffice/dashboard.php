<?php
$originalDir = getcwd();

chdir('../../Controller');

require_once '../config.php'; // Resolves to C:\xampp\htdocs\clickngoooo\config.php
require_once 'AnnonceCovoiturageController.php';
require_once 'DemandeCovoiturageController.php';

chdir($originalDir);

// Initialize controllers
$pdo = config::getConnexion();
$annonceController = new AnnonceCovoiturageController($pdo);
$demandeController = new DemandeCovoiturageController($pdo); // Pass PDO instance

// Fetch all data
$allAnnonces = $annonceController->getAllAnnonces();
$allDemandes = $demandeController->getAllDemandes();

// Get total counts
$totalAnnonces = count($allAnnonces);
$totalDemandes = count($allDemandes);
$totalAvis = 0;

// Fetch destination statistics - lieux d'arrivée les plus utilisés (top 5)
try {
    $stmt = $pdo->query("
        SELECT 
            lieu_arrivee as destination, 
            COUNT(*) as destination_count
        FROM annonce_covoiturage
        GROUP BY lieu_arrivee
        ORDER BY destination_count DESC
        LIMIT 5
    ");
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $destinations = [];
    error_log("Error fetching destination stats: " . $e->getMessage());
}

// Encode the destinations data as JSON for JavaScript to use
$destinationsJson = json_encode(['destinations' => $destinations]);


// Fonction pour générer une couleur à partir du nom
function stringToColor($str)
{
  $Colors = [
    '#FF6B6B',
    '#FF8E53',
    '#6B5B95',
    '#88B04B',
    '#F7CAC9',
    '#92A8D1',
    '#955251',
    '#B565A7',
    '#DD4124',
    '#D65076'
  ];
  $hash = 0;
  for ($i = 0; $i < strlen($str); $i++) {
    $hash = ord($str[$i]) + (($hash << 5) - $hash);
  }
  return $Colors[abs($hash) % count($Colors)];
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Click'N'go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'inter', sans-serif;
            background: #F1EBFF;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar styles from product dashboard */
        .sidebar {
            width: 250px;
            background: #fff;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar > div:first-child {
            position: relative;
            top: 0px;
            padding-top: 10px;
        }

        .sidebar .logo {
            width: 200px;
            height: auto;
            display: block;
            margin: 0 auto -20px;
            transition: transform 0.3s ease;
        }

        .sidebar .logo:hover {
            transform: scale(1.1);
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .menu-item:hover {
            background: rgba(255, 77, 77, 0.1);
        }


        .menu-item .icon {
            margin-right: 10px;
            font-size: 18px;
        }

        .menu-item.settings {
            margin-top: 5px !important;
            margin-bottom: 5px !important;
        }

        .menu-item.logout {
            margin-top: 5px !important;
        }

        .notification-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            background: #ff4d4d;
            color: #fff;
            font-size: 12px;
            font-weight: bold;
            border-radius: 10px;
            margin-left: auto;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .notification-badge:hover {
            transform: scale(1.1);
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 40px;
            width: calc(100% - 250px);
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .dashboard-header {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            margin-top: 100px;
        }

        .dashboard-header h1 {
            font-family: 'Inter', sans-serif;
            font-size: 32px;
            color: #2d2d2d;
            text-align: center;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
            margin-top: 100px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '✨';
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            opacity: 0.5;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            font-family: 'Inter', sans-serif;
            font-size: 24px;
            color: #ff8fa3;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 18px;
            color: #666;
        }

        .card i {
            font-size: 40px;
            color: #ff8fa3;
            margin-bottom: 10px;
        }

        /* Statistics Section */
        .stats-section {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .stats-section h3 {
            font-family: 'Inter', sans-serif;
            font-size: 28px;
            color: #ff8fa3;
            text-align: center;
            margin-bottom: 20px;
        }

        .stats-section .chart-container {
            max-width: 500px;
            margin: 0 auto;
            position: relative;
            height: 400px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            border-radius: 15px;
        }

        .stats-section .stats-details {
            text-align: center;
            margin-top: 20px;
        }

        .stats-section .stats-details p {
            font-size: 18px;
            color: #666;
            margin: 5px 0;
        }

        .stats-section .stats-details p span:first-child {
            font-weight: 500;
            color: #333;
        }

        .stats-section .no-data {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-top: 20px;
        }

        /* Navigation Buttons Styles */
        .navigation-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .navigation-buttons a,
        .navigation-buttons button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            text-decoration: none;
            color: #fff;
            font-size: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            border: none;
            cursor: pointer;
        }

        .navigation-buttons a:hover,
        .navigation-buttons button:hover {
            transform: scale(1.1);
        }

        .nav-annonces {
            background: linear-gradient(145deg, #ff8acb, #a7bfff);
        }

        .nav-archiver {
            background: linear-gradient(145deg, #ff6f61, #ff9a8b);
        }

        .nav-restaurer {
            background: linear-gradient(145deg, #6b7280, #9ca3af);
        }

        .nav-demandes {
            background: linear-gradient(145deg, #60a5fa, #93c5fd);
        }

        .nav-previous {
            background: linear-gradient(145deg, #a1a1aa, #d4d4d8);
        }

        /* Popup Styles */
        
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
            z-index: 1000;
        }

       .popup {
    background: linear-gradient(145deg, #ff8acb, #a7bfff);
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    width: 250px;
    position: absolute;
    bottom: 20px;  /* Changed from top: 100px */
    right: 20px;
    text-align: center;
    color: #fff;
    animation: popupFadeIn 0.5s ease-out;
    cursor: pointer;
}
        @keyframes popupFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .popup h3 {
            font-family: 'Inter', sans-serif;
            font-size: 20px;
            margin-bottom: 8px;
        }

        .popup p {
            font-size: 14px;
            margin-bottom: 0;
        }

        .popup .close-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #fff;
            color: #ff8acb;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .popup .close-btn:hover {
            background: #ff8acb;
            color: #fff;
            transform: rotate(90deg);
        }
        
.sidebar h1 {
    font-size: 24px;
    margin-bottom: 40px;
    color: #663399;
  }
  .menu-item {
    margin: 5px 0;
    font-size: 16px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    transition: color 0.3s ease, transform 0.2s ease;
  }
  .menu-item:hover, .menu-item.active {
    color: #663399;
    transform: translateX(5px);
  }
        .popup .close-btn {
            cursor: pointer;
        }

        /* Navbar styles */
        .navbar-backoffice-wrapper {
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98));
          padding: 15px 25px;
          border-radius: 30px;
          box-shadow: 0 8px 32px rgba(151, 104, 209, 0.1);
          position: fixed;
          top: 40px;
          left: 58%;
          transform: translateX(-50%);
          z-index: 1000;
          width: fit-content;
          min-width: 800px;
          backdrop-filter: blur(10px);
          border: 1px solid rgba(151, 104, 209, 0.1);
        }

        .profile-container {
          display: flex;
          align-items: center;
          gap: 20px;
          margin-left: 25px;
          padding-left: 25px;
          border-left: 1px solid rgba(151, 104, 209, 0.2);
        }
        
        .profile-container1 {
          display: flex;
          align-items: center;
          gap: 20px;
          margin-left: 25px;
        }

        .nav-link {
          text-decoration: none;
          font-weight: 500;
          font-size: 15px;
          transition: all 0.3s ease;
          padding: 10px 20px;
          border-radius: 20px;
          white-space: nowrap;
          position: relative;
        }

        /* Couleurs spécifiques pour chaque lien */
        .nav-link[href*="Utilisateur"] {
          color: #9F7AEA;
        }

        .nav-link[href*="act"] {
          color: #9F7AEA;
        }

        .nav-link[href*="Event"] {
          color: #9F7AEA;
        }

        .nav-link[href*="Produit"] {
          color: #9F7AEA;
        }

        .nav-link[href*="Covoiturage"] {
          color: #F687B3;
        }

        .nav-link[href*="Sponsor"] {
          color: #9F7AEA;
        }

        .nav-link:hover {
          background: rgba(246, 135, 179, 0.1);
          transform: translateY(-1px);
        }

        .nav-link.active {
          background: rgba(246, 135, 179, 0.15);
          color: #F687B3;
        }

        .nav-link.active::after {
          content: '';
          position: absolute;
          bottom: -5px;
          left: 50%;
          transform: translateX(-50%);
          width: 20px;
          height: 3px;
          background: #F687B3;
          border-radius: 10px;
        }

        /* User profile styles */
        .user-profile {
            position: relative;
            margin-right: 50px;
        }
.sidebar {
    width: 240px;
    background: linear-gradient(180deg, #ffffff, #f5f0ff);
    border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
    padding: 30px 20px;
    box-shadow: 5px 0 15px rgba(102, 51, 153, 0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
  }
        .profile-photo,
        .profile-circle {
          width: 35px;
          height: 35px;
          border-radius: 50%;
          cursor: pointer;
          border: 2px solid white;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .profile-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .dropdown-menu {
          display: none;
          position: absolute;
          top: 50px;
          right: 0;
          background: white;
          border-radius: 15px;
          box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
          padding: 8px 0;
          min-width: 180px;
          border: 1px solid rgba(151, 104, 209, 0.1);
          z-index: 1000;
        }

        .dropdown-menu a {
          display: block;
          padding: 10px 20px;
          color: #666;
          text-decoration: none;
          font-size: 14px;
          transition: all 0.3s ease;
        }

        .dropdown-menu a:hover {
          background: rgba(247, 243, 255, 0.95);
          color: #9768D1;
        }

        /* Logo style */
        .logo {
          height: 70px;
          margin-bottom: 20px;
          margin-top: 50px;
          margin-left: 40px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }

            .stats-section .chart-container {
                height: 350px;
            }

            .stats-section .stats-details p,
            .stats-section .no-data {
                font-size: 16px;
            }

            .navigation-buttons a,
            .navigation-buttons button {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .popup {
                width: 200px;
                top: 10px;
                right: 10px;
            }

            .popup h3 {
                font-size: 18px;
            }

            .popup p {
                font-size: 12px;
            }

            .popup .close-btn {
                width: 20px;
                height: 20px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .stats-section .chart-container {
                height: 300px;
            }

            .stats-section .stats-details p,
            .stats-section .no-data {
                font-size: 14px;
            }

            .navigation-buttons a,
            .navigation-buttons button {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }

            .popup {
                width: 180px;
                top: 5px;
                right: 5px;
            }

            .popup h3 {
                font-size: 16px;
            }

            .popup p {
                font-size: 10px;
            }

            .popup .close-btn {
                width: 18px;
                height: 18px;
                font-size: 10px;
            }
        }
    </style>
</head>

<body>
    <nav>
      <div class="navbar-backoffice-wrapper">
        <div class="profile-container1">
          <!-- Liens de navigation -->
          <a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link">Utilisateurs</a>
          <a href="/Projet Web/mvcact/view/back office/dashboard.php" class="nav-link" data-section="activites">Activités</a>
          <a href="/Projet Web/mvcEvent/View/BackOffice/dashboard.php" class="nav-link" data-section="evenements">Événements</a>
          <a href="/Projet Web/mvcProduit/view/back office/indeex.php" class="nav-link" data-section="produits">Produits</a>
          <a href="/Projet Web/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link active" data-section="transports">Transports</a>
          <a href="/Projet Web/mvcSponsor/crud/view/back/back.php" class="nav-link" data-section="sponsors">Sponsors</a>
          <div class="profile-container">
            <!-- Profil à droite -->
            <div class="user-profile">
              <?php if (isset($_SESSION['user'])): ?>
                <?php
                $photoPath = $_SESSION['user']['profile_picture'] ?? '';
                $fullName = $_SESSION['user']['full_name'] ?? 'Utilisateur';
                $photoRelativePath = '../../../mvcUtilisateur/View/FrontOffice/' . $photoPath;
                $absolutePath = realpath(__DIR__ . '/' . $photoRelativePath);
                $showPhoto = !empty($photoPath) && $absolutePath && file_exists($absolutePath);
                ?>
                
                <?php if ($showPhoto): ?>
                  <img src="/Projet Web/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>" 
                    alt="Photo de profil" 
                    class="profile-photo" 
                    onclick="toggleDropdown()">
                <?php else: ?>
                  <div class="profile-circle" 
                    style="background-color: <?= function_exists('stringToColor') ? stringToColor($fullName) : '#999' ?>;" 
                    onclick="toggleDropdown()">
                    <?= strtoupper(htmlspecialchars(substr($fullName, 0, 1))) ?>
                  </div>
                <?php endif; ?>
                
                <div class="dropdown-menu" id="dropdownMenu">
                  <a href="/Projet Web/mvcUtilisateur/View/FrontOffice/profile.php">👤 Mon Profil</a>
                  <a href="/Projet Web/mvcUtilisateur/View/BackOffice/login/logout.php">🚪 Déconnexion</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Popup Notification -->
    <div class="popup-overlay" id="popupOverlay">
        <div class="popup" onclick="redirectToAnnonces(event)">
            <button class="close-btn" onclick="closePopup(event)">✖</button>
            <h3>Rappel 📢</h3>
          <p>Rappel : Voir les annonces - <?php echo htmlspecialchars($totalAnnonces); ?> annonces à consulter.</p>
        </div>
    </div>

    <!-- Sidebar from product dashboard -->
    <div class="sidebar">
        <div>
            <img src="/Projet Web/mvcProduit/view/back office/logo.png" alt="Logo" class="logo">

            <div class="menu-item active" data-section="overview" onclick="window.location.href='dashboard.php?page=1'"><span class="icon">🏠</span>Vue Générale</div>
            <div class="menu-item" data-section="annonces" onclick="window.location.href='annonces.php'"><span class="icon">📢</span> Annonces</div>
            <div class="menu-item" data-section="demandes" onclick="window.location.href='demande_list.php'"><span class="icon">📋</span> Demandes</div>
           
        </div>
        <div>
            <div class="menu-item logout" onclick="window.location.href='logout.php'"><span class="icon">🚪</span> Déconnexion</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">


        <div class="dashboard-cards">
            <div class="card">
                <i class="fas fa-box"></i>
                <h3>Annonces</h3>
                <p><?php echo htmlspecialchars($totalAnnonces); ?> annonces</p>
            </div>
            <div class="card">
                <i class="fas fa-list"></i>
                <h3>Demandes</h3>
                <p><?php echo htmlspecialchars($totalDemandes); ?> demandes</p>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="chart-container">
                <canvas id="destinationStatsChart"></canvas>
            </div>
            <div class="stats-details" id="statsDetails">
                <!-- Details will be populated by JavaScript -->
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <a href="annonces.php" class="nav-annonces" title="Annonces"><i class="fas fa-box"></i></a>
            <a href="demande_list.php" class="nav-demandes" title="Demandes"><i class="fas fa-list"></i></a>
            <button onclick="window.history.back()" class="nav-previous" title="Précédent"><i class="fas fa-arrow-left"></i></button>
        </div>
    </div>

    <!-- Embed the destination stats as JSON -->
    <script>
        const destinationStats = <?php echo $destinationsJson; ?>;
    </script>

    <!-- JavaScript to Display Statistics, Handle Submenu, and Control Popup -->
    <script>
        // Toggle dropdown for user profile
        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            if (menu.style.display === 'block') {
                menu.style.display = 'none';
            } else {
                menu.style.display = 'block';
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('dropdownMenu');
            const profile = document.querySelector('.user-profile');
            if (profile && !profile.contains(event.target)) {
                if (menu) menu.style.display = 'none';
            }
        });
document.addEventListener('DOMContentLoaded', function() {
    // Show the popup permanently and make it non-blocking
    const popupOverlay = document.getElementById('popupOverlay');
    if (popupOverlay) {
        // Remove overlay behavior and make it always visible
        popupOverlay.style.display = 'block';
        popupOverlay.style.position = 'fixed';
        popupOverlay.style.pointerEvents = 'none'; // Allow clicks to pass through the overlay
        
        // Make the actual popup content clickable
        const popupContent = popupOverlay.querySelector('.popup-content, .popup, .modal-content');
        if (popupContent) {
            popupContent.style.pointerEvents = 'auto'; // Re-enable clicks on popup content
        }
    }
});

        // Function to close the popup
        function closePopup(event) {
            event.stopPropagation();
            const popupOverlay = document.getElementById('popupOverlay');
            if (popupOverlay) popupOverlay.style.display = 'none';
        }

        // Function to redirect to annonces.php when clicking the popup
        function redirectToAnnonces(event) {
            if (event.target.classList.contains('close-btn')) {
                return;
            }
            window.location.href = 'annonces.php';
        }

        // Access the embedded JSON data
        const data = destinationStats;

        const statsDetails = document.getElementById('statsDetails');
        const chartContainer = document.querySelector('.chart-container');

        // Check if data is empty
        if (!data.destinations || data.destinations.length === 0) {
            chartContainer.style.display = 'none';
            statsDetails.innerHTML = '<p class="no-data">Aucune donnée disponible</p>';
        } else {
            // Prepare data for the chart
            const labels = data.destinations.map(destination => destination.destination);
            const destinationCounts = data.destinations.map(destination => destination.destination_count);

            // Define gradient colors for the doughnut chart
            const generateGradients = (ctx) => {
                const gradients = [];
                const colors = [{
                        start: '#ff8acb',
                        end: '#a7bfff'
                    },
                    {
                        start: '#ff8acb',
                        end: '#d9e4ff'
                    },
                    {
                        start: '#ffa8da',
                        end: '#98b8ff'
                    },
                    {
                        start: '#ffbce3',
                        end: '#c4d6ff'
                    },
                    {
                        start: '#ff9ad2',
                        end: '#8aaeff'
                    }
                ];

                colors.forEach(color => {
                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, color.start);
                    gradient.addColorStop(1, color.end);
                    gradients.push(gradient);
                });

                return gradients;
            };

            // Create the doughnut chart using Chart.js
            const ctx = document.getElementById('destinationStatsChart').getContext('2d');
            const gradients = generateGradients(ctx);

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: destinationCounts,
                        backgroundColor: gradients,
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 14,
                                    family: 'Inter, sans-serif'
                                },
                                color: '#333',
                                padding: 20
                            }
                        },
                        title: {
                            display: true,
                            text: 'Les destinations les plus demandées',
                            font: {
                                size: 18,
                                family: 'Inter, sans-serif',
                                weight: 'bold'
                            },
                            color: '#ff8acb',
                            padding: {
                                bottom: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} trajets (${percentage}%)`;
                                }
                            },
                            titleFont: {
                                family: 'Inter, sans-serif',
                                size: 14
                            },
                            bodyFont: {
                                family: 'Inter, sans-serif',
                                size: 14
                            },
                            padding: 12,
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#ff8acb',
                            bodyColor: '#333',
                            borderColor: '#ffeaf2',
                            borderWidth: 1
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 2000,
                        easing: 'easeOutCirc'
                    }
                }
            });

            // Display the destination statistics details with more styling
            if (data.destinations.length > 0) {
                const mostPopularDestination = data.destinations[0];
                const totalTrips = data.destinations.reduce((sum, dest) => sum + parseInt(dest.destination_count), 0);

                statsDetails.innerHTML = `
                    <div style="margin-top: 30px; padding: 15px; background: linear-gradient(145deg, #ff8acb, #a7bfff); border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05); color: white;">
                        <p><span style="font-weight: 500; color: white;">Destination la Plus Populaire:</span> <span style="color: white;">${mostPopularDestination.destination}</span></p>
                        <p><span style="font-weight: 500; color: white;">Nombre de Trajets:</span> <span style="color: white;">${mostPopularDestination.destination_count} sur un total de ${totalTrips} trajets</span></p>
                        <p><span style="font-weight: 500; color: white;">Pourcentage:</span> <span style="color: white;">${Math.round((mostPopularDestination.destination_count/totalTrips)*100)}% des trajets</span></p>
                    </div>
                `;
            }
        }
    </script>
</body>

</html>
