<?php
require_once '../../config.php';
require_once '../../Controller/DemandeCovoiturageController.php';

// Connexion DB
try {
    $pdo = new PDO("mysql:host=localhost;dbname=clickngo_db", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur DB: ".$e->getMessage());
}

$controller = new DemandeCovoiturageController($pdo);

// Fonction pour récupérer le trajet par conducteur
function getTrajetByConducteur($pdo, $id_conducteur) {
    if (!$id_conducteur) return ['lieu_depart'=>'', 'lieu_arrivee'=>''];
    $stmt = $pdo->prepare("SELECT lieu_depart, lieu_arrivee FROM annonce_covoiturage WHERE id_conducteur=? ORDER BY date_depart DESC LIMIT 1");
    $stmt->execute([$id_conducteur]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['lieu_depart'=>'', 'lieu_arrivee'=>''];
}

// Fonction pour récupérer les infos passager
function getPassagerInfo($pdo, $id_passager) {
    if (!$id_passager) return ['prenom_passager'=>'Inconnu', 'nom_passager'=>''];
    $stmt = $pdo->prepare("SELECT prenom_passager, nom_passager FROM demande_covoiturage WHERE id_passager=?");
    $stmt->execute([$id_passager]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['prenom_passager'=>'Inconnu', 'nom_passager'=>''];
}

// Filtre et tri
$status_filter = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'date_desc';

// Fonction pour normaliser les statuts
function normalizeStatus($status) {
    $status = strtolower(trim($status));
    if (strpos($status, 'approuv') !== false) return 'approuvée';
    if (strpos($status, 'rejet') !== false) return 'rejetée';
    return 'en cours';
}

// Export Excel
if (isset($_GET['export'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="demandes.xls"');
    
    $demandes = $controller->getAllDemandes();
    
    echo "<table border='1'>";
    echo "<tr>
            <th>ID Passager</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Départ</th>
            <th>Arrivée</th>
            <th>Date/Heure</th>
            <th>Places</th>
            <th>Statut</th>
          </tr>";
    
    foreach ($demandes as $d) {
        $current_status = normalizeStatus($d['status_demande'] ?? 'en cours');
        
        if ($status_filter !== 'all' && $current_status !== normalizeStatus($status_filter)) {
            continue;
        }
        
        $trajet = getTrajetByConducteur($pdo, $d['id_conducteur'] ?? null);
        $passager = getPassagerInfo($pdo, $d['id_passager'] ?? null);
        
        echo "<tr>
                <td>".htmlspecialchars($d['id_passager'] ?? '')."</td>
                <td>".htmlspecialchars($passager['prenom_passager'] ?? '')."</td>
                <td>".htmlspecialchars($passager['nom_passager'] ?? '')."</td>
                <td>".htmlspecialchars($trajet['lieu_depart'] ?? '')."</td>
                <td>".htmlspecialchars($trajet['lieu_arrivee'] ?? '')."</td>
                <td>".(isset($d['date_demande']) ? date('d/m/Y H:i', strtotime($d['date_demande'])) : '')."</td>
                <td>".htmlspecialchars($d['nbr_places_reservees'] ?? '')."</td>
                <td>".htmlspecialchars($current_status)."</td>
              </tr>";
    }
    
    echo "</table>";
    exit();
}

// Récupération et tri des données
$all_demandes = $controller->getAllDemandes();

// Fonction de comparaison pour le tri
function compareDemandes($a, $b, $sort) {
    switch ($sort) {
        case 'status_asc':
            return strcmp(normalizeStatus($a['status_demande']), normalizeStatus($b['status_demande']));
        case 'status_desc':
            return strcmp(normalizeStatus($b['status_demande']), normalizeStatus($a['status_demande']));
        case 'date_asc':
            return strtotime($a['date_demande']) <=> strtotime($b['date_demande']);
        case 'date_desc':
        default:
            return strtotime($b['date_demande']) <=> strtotime($a['date_demande']);
    }
}

// Appliquer le tri
usort($all_demandes, function($a, $b) use ($sort) {
    return compareDemandes($a, $b, $sort);
});

$demandes_a_afficher = [];
foreach ($all_demandes as $d) {
    $current_status = normalizeStatus($d['status_demande'] ?? 'en cours');
    
    if ($status_filter !== 'all' && $current_status !== normalizeStatus($status_filter)) {
        continue;
    }
    
    $trajet = getTrajetByConducteur($pdo, $d['id_conducteur'] ?? null);
    $passager = getPassagerInfo($pdo, $d['id_passager'] ?? null);
    
    $demandes_a_afficher[] = [
        'id_passager' => $d['id_passager'] ?? '',
        'prenom' => $passager['prenom_passager'] ?? '',
        'nom' => $passager['nom_passager'] ?? '',
        'depart' => $trajet['lieu_depart'] ?? '',
        'arrivee' => $trajet['lieu_arrivee'] ?? '',
        'date' => isset($d['date_demande']) ? date('d/m/Y H:i', strtotime($d['date_demande'])) : '',
        'places' => $d['nbr_places_reservees'] ?? '',
        'statut' => $current_status
    ];
}

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
    <title>Demandes - Click'N'Go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #F1EBFF;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar styles from dashboard - EXACT COPY */
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
            border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
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
            margin: 5px 0;
            font-size: 16px;
            color: #333;
            gap: 10px;
            transition: color 0.3s ease, transform 0.2s ease;
        }

        .menu-item:hover {
            background: rgba(255, 77, 77, 0.1);
            color: #663399;
            transform: translateX(5px);
        }

        .menu-item.active {

            color: #663399;
            transform: translateX(5px);
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

        .sidebar h1 {
            font-size: 24px;
            margin-bottom: 40px;
            color: #663399;
        }

        /* Logo style */
        .logo {
            height: 70px;
            margin-bottom: 20px;
            margin-top: 50px;
            margin-left: 40px;
        }

        /* Main Content */
        .main-content {
            margin-top: 100px;
            margin-left: 250px;
            padding: 40px;
            width: calc(100% - 250px);
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .page-header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            color: #c63dc9;
            font-family: 'Inter', sans-serif;
            font-size: 32px;
        }

        /* Filtres et tri */
        .filter-sort-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-box, .sort-box {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 200px;
        }

        .filter-box h3, .sort-box h3 {
            margin-bottom: 10px;
            color: #c63dc9;
            font-size: 16px;
        }

        select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #d9e4ff;
            font-size: 14px;
        }

        /* Table */
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: linear-gradient(145deg, #ff8acb, #a7bfff);
            color: white;
            position: relative;
        }

        th.sortable:hover {
            cursor: pointer;
            background: linear-gradient(145deg, #e67ab5, #95a5e6);
        }

        .sort-arrow {
            margin-left: 5px;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        /* Export Excel */
        .export-btn {
            background:#c63dc9;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            margin-top: 30px;
            float: right;
            text-decoration: none;
            transition: all 0.3s;
        }

        .export-btn:hover {
            background:rgb(245, 169, 245);
            transform: translateY(-2px);
        }

        .export-btn i {
            margin-right: 8px;
        }

        /* Badges */
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-approved {
            background-color: #4CAF50;
            color: white;
        }

        .badge-rejected {
            background-color: #f44336;
            color: white;
        }

        .badge-pending {
            background-color:rgb(138, 195, 248);
            color: #000;
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

        /* Navbar styles */
        .navbar-backoffice-wrapper {
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98));
          padding: 15px 30px;
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
          padding-left: 25px;
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

        .user-profile {
          position: relative;
          display: inline-block;
          margin-right: 30px;
        }
        
        .user-profile:hover .dropdown-menu {
          display: block;
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

        .dropdown-menu {
          position: absolute;
          top: 50px;
          right: 0;
          background: white;
          border-radius: 15px;
          box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
          padding: 8px 0;
          min-width: 180px;
          border: 1px solid rgba(151, 104, 209, 0.1);
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
    </style>
</head>
<body>

<nav>
  <div class="navbar-backoffice-wrapper">
        <div class="profile-container1">
    <!-- Liens de navigation -->
    <a href="/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link">Utilisateurs</a>
    <a href="/mvcact/view/back office/dashboard.php" class="nav-link" data-section="activites">Activités</a>
    <a href="/mvcEvent/View/BackOffice/dashboard.php" class="nav-link" data-section="evenements">Événements</a>
    <a href="/mvcProduit/view/back office/indeex.php" class="nav-link" data-section="produits">Produits</a>
    <a href="/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link active" data-section="transports">Transports</a>
    <a href="/mvcSponsor/crud/view/back/back.php" class="nav-link" data-section="sponsors">Sponsors</a>
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
            <img src="/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>"
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
            <a href="/mvcUtilisateur/View/FrontOffice/profile.php">👤 Mon Profil</a>
            <a href="/mvcUtilisateur/View/BackOffice/login/logout.php">🚪 Déconnexion</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

    <!-- Sidebar from dashboard - EXACT COPY -->
    <div class="sidebar">
        <div>
            <img src="/mvcProduit/view/back office/logo.png" alt="Logo" class="logo">

            <div class="menu-item" data-section="overview" onclick="window.location.href='dashboard.php?page=1'"><span class="icon">🏠</span>Vue Générale</div>
            <div class="menu-item" data-section="annonces" onclick="window.location.href='annonces.php'"><span class="icon">📢</span> Annonces</div>
            <div class="menu-item active" data-section="demandes" onclick="window.location.href='demande_list.php'"><span class="icon">📋</span> Demandes</div>
           
        </div>
        <div>
            <div class="menu-item logout" onclick="window.location.href='logout.php'"><span class="icon">🚪</span> Déconnexion</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Liste des Demandes</h1>
        </div>

        <!-- Filtres et tri -->
        <div class="filter-sort-container">
            <div class="filter-box">
                <h3>Filtrer par statut</h3>
                <form method="GET">
                    <select name="status" onchange="this.form.submit()">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Tous les statuts</option>
                        <option value="approuvée" <?= $status_filter === 'approuvée' ? 'selected' : '' ?>>Approuvée</option>
                        <option value="rejetée" <?= $status_filter === 'rejetée' ? 'selected' : '' ?>>Rejetée</option>
                        <option value="en cours" <?= $status_filter === 'en cours' ? 'selected' : '' ?>>En attente</option>
                    </select>
                    <input type="hidden" name="sort" value="<?= $sort ?>">
                </form>
            </div>
            
            <div class="sort-box">
                <h3>Trier par</h3>
                <form method="GET">
                    <select name="sort" onchange="this.form.submit()">
                        <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Date (récent)</option>
                        <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Date (ancien)</option>
                        <option value="status_asc" <?= $sort === 'status_asc' ? 'selected' : '' ?>>Statut (A-Z)</option>
                        <option value="status_desc" <?= $sort === 'status_desc' ? 'selected' : '' ?>>Statut (Z-A)</option>
                    </select>
                    <input type="hidden" name="status" value="<?= $status_filter ?>">
                </form>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Passager</th>
                        <th>Prénom</th>
                        <th>Nom</th>
                        <th>Départ</th>
                        <th>Arrivée</th>
                        <th class="sortable" onclick="window.location='?status=<?= $status_filter ?>&sort=<?= $sort === 'date_desc' ? 'date_asc' : 'date_desc' ?>'">
                            Date/Heure
                            <span class="sort-arrow">
                                <?php if ($sort === 'date_desc'): ?>↓<?php elseif ($sort === 'date_asc'): ?>↑<?php endif; ?>
                            </span>
                        </th>
                        <th>Places</th>
                        <th class="sortable" onclick="window.location='?status=<?= $status_filter ?>&sort=<?= $sort === 'status_desc' ? 'status_asc' : 'status_desc' ?>'">
                            Statut
                            <span class="sort-arrow">
                                <?php if ($sort === 'status_desc'): ?>↓<?php elseif ($sort === 'status_asc'): ?>↑<?php endif; ?>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes_a_afficher)): ?>
                        <tr><td colspan="8" style="text-align:center;">Aucune demande trouvée</td></tr>
                    <?php else: ?>
                        <?php foreach ($demandes_a_afficher as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['id_passager']) ?></td>
                            <td><?= htmlspecialchars($d['prenom']) ?></td>
                            <td><?= htmlspecialchars($d['nom']) ?></td>
                            <td><?= htmlspecialchars($d['depart']) ?></td>
                            <td><?= htmlspecialchars($d['arrivee']) ?></td>
                            <td><?= htmlspecialchars($d['date']) ?></td>
                            <td><?= htmlspecialchars($d['places']) ?></td>
                            <td>
                                <?php if ($d['statut'] === 'approuvée'): ?>
                                    <span class="badge badge-approved">Approuvée</span>
                                <?php elseif ($d['statut'] === 'rejetée'): ?>
                                    <span class="badge badge-rejected">Rejetée</span>
                                <?php else: ?>
                                    <span class="badge badge-pending">En attente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <a href="?status=<?= urlencode($status_filter) ?>&sort=<?= urlencode($sort) ?>&export=1" class="export-btn">
                <i class="fas fa-file-excel"></i> Exporter en Excel
            </a>
        </div>
    </div>

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

        // Confirmation avant actions
        const confirmAction = (e) => {
            if (!confirm(e.target.getAttribute('data-confirm') || 'Êtes-vous sûr ?')) {
                e.preventDefault();
            }
        };
        
        document.querySelectorAll('.btn-action').forEach(btn => {
            btn.addEventListener('click', confirmAction);
        });
    </script>
</body>
</html>