<?php
session_start(); // Add this at the very top
require_once '../../config.php';
require_once '../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Gestion des messages
if (isset($_GET['success'])) {
    echo '<div class="alert" style="background:#dff0d8;color:#3c763d;padding:15px;margin:20px;border-radius:5px">L\'annonce a été archivée avec succès!</div>';
}
if (isset($_GET['error'])) {
    echo '<div class="alert" style="background:#f2dede;color:#a94442;padding:15px;margin:20px;border-radius:5px">Erreur: '.htmlspecialchars($_GET['error']).'</div>';
}

// Paramètres
$filter = $_GET['filter'] ?? 'active';
$search = $_GET['search'] ?? '';
$order = $_GET['order'] ?? 'date_depart DESC';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$itemsPerPage = 10;

try {
    $pdo = config::getConnexion();
    
    $query = "SELECT * FROM annonce_covoiturage WHERE ";
    $query .= ($filter === 'active') ? "(statut = 'active' OR statut IS NULL)" : "statut = 'archivée'";
    
    if (!empty($search)) {
        $query .= " AND (CONCAT(prenom_conducteur,' ',nom_conducteur) LIKE :search OR tel_conducteur LIKE :search)";
    }
    
    $query .= " ORDER BY $order";
    
    // Pagination
    $offset = ($page - 1) * $itemsPerPage;
    $query .= " LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam);
    }
    
    $stmt->execute();
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total count for pagination
    $countQuery = "SELECT COUNT(*) FROM annonce_covoiturage WHERE ";
    $countQuery .= ($filter === 'active') ? "(statut = 'active' OR statut IS NULL)" : "statut = 'archivée'";
    if (!empty($search)) {
        $countQuery .= " AND (CONCAT(prenom_conducteur,' ',nom_conducteur) LIKE :search OR tel_conducteur LIKE :search)";
    }
    $countStmt = $pdo->prepare($countQuery);
    if (!empty($search)) {
        $countStmt->bindParam(':search', $searchParam);
    }
    $countStmt->execute();
    $totalItems = $countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $itemsPerPage);

    // Export PDF
    if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        
        $html = '<h1 style="text-align:center;color:#c63dc9;font-family:Playfair Display">Liste des Annonces '.($filter === 'active' ? 'Actives' : 'Archivées').'</h1>';
        $html .= '<table border="1" cellpadding="8" style="width:100%;border-collapse:collapse">';
        $html .= '<tr style="background:#9c27b0;color:white">
                    <th>ID</th><th>Conducteur</th><th>Téléphone</th>
                    <th>Date Départ</th><th>Trajet</th>
                    <th>Places</th><th>Prix</th><th>Statut</th>
                 </tr>';
        
        foreach ($annonces as $annonce) {
            $html .= '<tr>
                        <td>'.$annonce['id_conducteur'].'</td>
                        <td>'.$annonce['prenom_conducteur'].' '.$annonce['nom_conducteur'].'</td>
                        <td>'.$annonce['tel_conducteur'].'</td>
                        <td>'.$annonce['date_depart'].'</td>
                        <td>'.$annonce['lieu_depart'].' → '.$annonce['lieu_arrivee'].'</td>
                        <td>'.$annonce['nombre_places'].'</td>
                        <td>'.$annonce['prix_estime'].' TND</td>
                        <td style="color:'.(($annonce['statut'] ?? 'active') === 'active' ? '#4CAF50' : '#F44336').'">
                            '.($annonce['statut'] ?? 'active').'
                        </td>
                     </tr>';
        }
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("annonces_".date('Y-m-d').".pdf");
        exit;
    }

} catch (PDOException $e) {
    echo '<div class="alert" style="background:#f2dede;color:#a94442;padding:15px;margin:20px;border-radius:5px">Erreur: '.$e->getMessage().'</div>';
    exit;
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
    <title>Click'N'Go - Annonces</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: #f5f0ff;
            display: flex;
            min-height: 100vh;
        }

        /* Navbar styles from dashboard */
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

        .navbar-backoffice ul {
          display: flex;
          justify-content: center;
          align-items: center;
          gap: 30px;
          margin: 0;
          padding: 0;
          list-style: none;
          padding-right: 40px;
        }

        .profile-container {
          display: flex;
          align-items: center;
          gap: 20px;
          margin-left: 25px;
          padding-left: 25px;
          border-left: 1px solid rgba(151, 104, 209, 0.2);
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
          color: #9768D1;
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
          font-weight: 600;
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

        /* User profile styling */
        .user-profile {
          position: relative;
          display: inline-block;
        }

        .profile-photo {
          width: 40px;
          height: 40px;
          border-radius: 50%;
          object-fit: cover;
          cursor: pointer;
          border: 2px solid rgba(246, 135, 179, 0.3);
          box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
          transition: all 0.3s ease;
        }

        .profile-photo:hover {
          transform: scale(1.05);
          border-color: #F687B3;
        }

        .profile-circle {
          width: 40px;
          height: 40px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          font-size: 16px;
          cursor: pointer;
          border: 2px solid rgba(246, 135, 179, 0.3);
          box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
          transition: all 0.3s ease;
        }

        .profile-circle:hover {
          transform: scale(1.05);
          border-color: #F687B3;
        }

        .dropdown-menu {
          position: absolute;
          top: 50px;
          right: 0;
          background-color: white;
          border: 1px solid rgba(151, 104, 209, 0.2);
          padding: 10px 0;
          display: none;
          box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
          z-index: 1001;
          border-radius: 12px;
          min-width: 180px;
          backdrop-filter: blur(10px);
        }

        .dropdown-menu a {
          display: block;
          padding: 12px 20px;
          text-decoration: none;
          color: #333;
          font-size: 14px;
          border-radius: 8px;
          margin: 0 8px;
          transition: all 0.3s ease;
        }

        .dropdown-menu a:hover {
          background: rgba(246, 135, 179, 0.1);
          color: #F687B3;
        }

        /* Sidebar styles - exactly like dashboard */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 0 15px 15px 0;
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

        .sidebar-logo h1 {
            font-size: 28px;
            margin-top: 5px;
            font-weight: 700;
        }

        .sidebar-logo h1 span {
            color: #ff69b4;
        }

        .sidebar-logo p {
            font-size: 14px;
            opacity: 0.8;
            margin-top: -5px;
        }

        .sidebar-menu {
            flex-grow: 1;
            padding: 0 15px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
            color: #666;
            text-decoration: none;
        }

        .menu-item:hover {
            background-color: #fce4ec;
        }

        .menu-item.active {
            color: #663399;
            font-weight: 500;
        }

        .menu-item .icon {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 0 15px;
        }

        /* Sous-menu styles */
        .has-submenu {
            position: relative;
        }
        
        .submenu-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            cursor: pointer;
        }
        
        .dropdown-icon {
            transition: transform 0.3s;
            font-size: 14px;
            margin-left: auto;
        }
        
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s;
            padding-left: 20px;
            margin-top: 5px;
        }
        
        .submenu a {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 5px;
            text-decoration: none;
            color: #666;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        
        .submenu a:hover {
            background-color: #fce4ec;
        }
        
        .submenu a.active {
            color: #663399;
            font-weight: 500;
        }
        
        .submenu a i {
            margin-right: 8px;
            font-size: 12px;
        }

        /* Main content styles */
        .main-content {
            margin-left: 240px;
            width: calc(100% - 240px);
            padding: 20px;
            margin-top: 120px;
        }

        /* Page header */
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
            font-size: 32px;
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
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        /* Boutons */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            margin-right: 5px;
            font-size: 14px;
        }

        .btn-archive {
            background: #9c27b0;
            color: white;
        }

        .btn-archive:hover {
            background: #7b1fa2;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-restore {
            background: #4CAF50;
            color: white;
        }

        .btn-restore:hover {
            background: #3e8e41;
            transform: translateY(-2px);
        }

        /* Barre de recherche */
        .search-box {
            margin-bottom: 20px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 25px;
            border: 1px solid #d9e4ff;
            font-size: 16px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #c63dc9;
        }

        /* Export PDF */
        .export-btn {
            background: #c63dc9;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            margin-top: 30px;
            float: right;
            text-decoration: none;
        }

        .export-btn i {
            margin-right: 8px;
        }

        /* Tri */
        .sort-arrows {
            margin-left: 5px;
        }

        .sort-arrows a {
            color: white;
            margin: 0 2px;
        }

        /* Badges */
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-success {
            background-color: #4CAF50;
            color: white;
        }

        .badge-danger {
            background-color: #f44336;
            color: white;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a, .pagination span {
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #d9e4ff;
            transform: translateY(-2px);
        }

        .pagination .active {
            background: #ff8acb;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 15px 0;
            }

            .sidebar-logo h1, 
            .sidebar-logo p,
            .menu-item span {
                display: none;
            }

            .menu-item {
                justify-content: center;
                padding: 12px;
            }

            .menu-item .icon {
                margin-right: 0;
                font-size: 20px;
            }

            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }

            .navbar-backoffice-wrapper {
                min-width: 600px;
                left: 50%;
            }

            .profile-container {
                margin-left: 12px;
                margin-right: 4px;
            }
        }
    </style>
</head>
<body>
    <!-- New Navbar from Dashboard -->
    <nav>
      <div class="navbar-backoffice-wrapper">
        <nav class="navbar-backoffice">
          <ul>
            <li><a href="/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link">Utilisateurs</a></li>
            <li><a href="/mvcact/view/back%20office/dashboard.php" class="nav-link">Activités</a></li>
            <li><a href="/mvcEvent/View/BackOffice/dashboard.php" class="nav-link">Événements</a></li>
            <li><a href="/mvcProduit/view/back%20office/indeex.php" class="nav-link">Produits</a></li>
            <li><a href="/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link active">Transports</a></li>
            <li><a href="/mvcSponsor/crud/view/back/back.php" class="nav-link">Sponsors</a></li>
            <li class="profile-container">
              <div class="user-profile">
                <?php 
                // Mock user data for testing - replace with your actual session data
                $user = $_SESSION['user'] ?? [
                    'profile_picture' => 'uploads/profiles/default-avatar.png', // Default path
                    'full_name' => 'Admin User',
                    'prenom' => 'Admin',
                    'nom' => 'User'
                ];
                
                $photoPath = $user['profile_picture'] ?? '';
                $fullName = $user['full_name'] ?? ($user['prenom'] ?? 'A') . ' ' . ($user['nom'] ?? 'User');
                
                // Try different possible paths for the profile picture
                $possiblePaths = [
                    "/mvcUtilisateur/View/FrontOffice/" . $photoPath,
                    "/uploads/profiles/" . basename($photoPath),
                    $photoPath
                ];
                
                $showPhoto = false;
                $finalPhotoPath = '';
                
                foreach ($possiblePaths as $path) {
                    if (!empty($photoPath) && $photoPath !== 'uploads/profiles/default-avatar.png') {
                        $showPhoto = true;
                        $finalPhotoPath = $path;
                        break;
                    }
                }
                ?>
                
                <?php if ($showPhoto): ?>
                  <img src="<?= htmlspecialchars($finalPhotoPath) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div class="profile-circle" style="background-color: <?= stringToColor($fullName) ?>; display: none;" onclick="toggleDropdown()">
                    <?= strtoupper(substr($fullName, 0, 1)) ?>
                  </div>
                <?php else: ?>
                  <div class="profile-circle" style="background-color: <?= stringToColor($fullName) ?>;" onclick="toggleDropdown()">
                    <?= strtoupper(substr($fullName, 0, 1)) ?>
                  </div>
                <?php endif; ?>
                
                <div class="dropdown-menu" id="dropdownMenu">
                  <a href="/mvcUtilisateur/View/FrontOffice/profile.php">👤 Mon Profil</a>
                  <a href="/mvcUtilisateur/View/BackOffice/login/logout.php">🚪 Déconnexion</a>
                </div>
              </div>
            </li>
          </ul>
        </nav>
      </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <div class="sidebar-logo">
                <img src="/mvcProduit/view/back office/logo.png" alt="Logo" class="logo">
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="menu-item">
                    <span class="icon">🏠</span>
                    <span>Vue Générale</span>
                </a>
                <div class="has-submenu">
                    <div class="menu-item submenu-toggle <?= strpos($_SERVER['PHP_SELF'], 'annonces.php') !== false ? 'active' : '' ?>">
                        <span class="icon">📢</span>
                        <span>Annonces</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </div>
                    <div class="submenu">
                        <a href="annonces.php?filter=active" class="<?= $filter === 'active' ? 'active' : '' ?>">
                            <i class="fas fa-circle" style="color:#4CAF50;"></i> Actives
                        </a>
                        <a href="annonces.php?filter=archived" class="<?= $filter === 'archived' ? 'active' : '' ?>">
                            <i class="fas fa-circle" style="color:#F44336;"></i> Archivées
                        </a>
                    </div>
                </div>
                <a href="demande_list.php" class="menu-item">
                    <span class="icon">📋</span>
                    <span>Demandes</span>
                </a>
            </div>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="menu-item">
                <span class="icon">🚪</span>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Liste des Annonces <?= $filter === 'active' ? 'Actives' : 'Archivées' ?></h1>
        </div>

        <!-- Barre de recherche -->
        <div class="search-box">
            <i class="fas fa-search"></i>
            <form method="GET">
                <input type="hidden" name="filter" value="<?= $filter ?>">
                <input type="text" name="search" placeholder="Rechercher par conducteur ou téléphone..." 
                       value="<?= htmlspecialchars($search) ?>" onkeypress="if(event.keyCode==13) this.form.submit()">
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Conducteur</th>
                        <th>Téléphone</th>
                        <th>
                            Date Départ
                            <span class="sort-arrows">
                                <a href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&order=date_depart ASC" title="Croissant">↑</a>
                                <a href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&order=date_depart DESC" title="Décroissant">↓</a>
                            </span>
                        </th>
                        <th>Trajet</th>
                        <th>Places</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($annonces)): ?>
                        <tr>
                            <td colspan="9" style="text-align:center;">Aucune annonce trouvée</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($annonces as $annonce): ?>
                        <tr>
                            <td><?= $annonce['id_conducteur'] ?></td>
                            <td><?= htmlspecialchars($annonce['prenom_conducteur'].' '.$annonce['nom_conducteur']) ?></td>
                            <td><?= $annonce['tel_conducteur'] ?></td>
                            <td><?= $annonce['date_depart'] ?></td>
                            <td><?= htmlspecialchars($annonce['lieu_depart'].' → '.$annonce['lieu_arrivee']) ?></td>
                            <td><?= $annonce['nombre_places'] ?></td>
                            <td><?= $annonce['prix_estime'] ?> TND</td>
                            <td>
                                <span class="badge <?= ($annonce['statut'] ?? 'active') === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $annonce['statut'] ?? 'active' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (($annonce['statut'] ?? 'active') === 'active'): ?>
                                    <a href="archiver.php?id_conducteur=<?= $annonce['id_conducteur'] ?>" class="btn btn-archive" onclick="return confirm('Archiver cette annonce?')">
                                        <i class="fas fa-archive"></i> Archiver
                                    </a>
                                <?php else: ?>
                                    <a href="restaurer.php?id_conducteur=<?= $annonce['id_conducteur'] ?>" class="btn btn-restore" onclick="return confirm('Restaurer cette annonce?')">
                                        <i class="fas fa-undo"></i> Restaurer
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Bouton Export PDF -->
            <br>
            <a href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&order=<?= urlencode($order) ?>&export=pdf" class="export-btn">
                <i class="fas fa-file-pdf"></i> Exporter en PDF
            </a>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&order=<?= urlencode($order) ?>&page=<?= $page - 1 ?>">Précédent</a>
                <?php else: ?>
                    <span>Précédent</span>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&order=<?= urlencode($order) ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?filter=<?= $filter ?>&search=<?= urlencode($search) ?>&order=<?= urlencode($order) ?>&page=<?= $page + 1 ?>">Suivant</a>
                <?php else: ?>
                    <span>Suivant</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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

        // Gestion du sous-menu
        document.querySelector('.submenu-toggle').addEventListener('click', function() {
            const submenu = this.closest('.has-submenu').querySelector('.submenu');
            const icon = this.querySelector('.dropdown-icon');
            
            if (submenu.style.maxHeight) {
                submenu.style.maxHeight = null;
                icon.style.transform = 'rotate(0deg)';
            } else {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
                icon.style.transform = 'rotate(180deg)';
            }
        });
        
        // Ouvrir automatiquement si on est dans Annonces
        if (window.location.href.includes('annonces.php')) {
            const submenu = document.querySelector('.submenu');
            const icon = document.querySelector('.dropdown-icon');
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
            icon.style.transform = 'rotate(180deg)';
        }
    </script>
</body>
</html>