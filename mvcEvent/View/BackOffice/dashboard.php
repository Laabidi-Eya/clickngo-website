<?php
require_once '../../Controller/EventController.php';

$controller = new EventController();
$editId = $_POST['edit_id'] ?? null;

// Check for server-side errors
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Récupérer user_id pour filtrage optionnel
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);
if (isset($_SESSION['form_data'])) {
    $formData = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    error_log('POST request received with action: ' . $action . ', data: ' . json_encode($_POST));

    try {
        switch ($action) {
            case 'add':
                $event = new Event(
                    $_POST['category'],
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['duration'],
                    $_POST['date'],
                    $_POST['longitude'],
                    $_POST['latitude'],
                    $_POST['place_name'],
                    $_POST['imageUrl'],
                    $_POST['totalSeats'],
                    $_POST['reservedSeats'] ?? 0
                );
                $eventId = $controller->addEvent($event);
                error_log('Event added successfully, redirecting...');
                break;

            case 'supp':
                $id = $_POST['id'];
                $controller->deleteEvent($id);
                break;

            case 'modif':
                $event = new Event(
                    $_POST['category'],
                    $_POST['name'],
                    $_POST['description'],
                    $_POST['price'],
                    $_POST['duration'],
                    $_POST['date'],
                    $_POST['longitude'],
                    $_POST['latitude'],
                    $_POST['place_name'],
                    $_POST['imageUrl'],
                    $_POST['totalSeats'],
                    $_POST['reservedSeats'] ?? 0
                );
                $controller->updateEvent($event, $_POST['id']);
                break;
        }

        if ($action !== 'edit') {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } catch (Exception $e) {
        error_log('Error handling POST request: ' . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de l'opération : " . $e->getMessage();
        $_SESSION['form_data'] = $_POST; // Save form data

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
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
    <title>Gestion des événements</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="add.css">
    <link rel="stylesheet" href="dashboard.css">
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css' rel='stylesheet' />
    <style>

/* Sidebar Styles from Products Dashboard */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 240px;
  height: 100vh;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98));
  box-shadow: 0 8px 32px rgba(151, 104, 209, 0.1);
  backdrop-filter: blur(10px);
  border-right: 1px solid rgba(151, 104, 209, 0.1);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 20px 0;
  z-index: 100;
  overflow-y: auto;
      border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
}

.sidebar-logo {
  width: 200px;
  height: auto;
  display: block;
  margin: 0 auto 20px;
  transition: transform 0.3s ease;
  padding: 10px 20px;
}

.sidebar-logo:hover {
  transform: scale(1.1);
}

.sidebar-logo img {
  width: 100%;
  height: auto;
}

.menu-list {
  display: flex;
  flex-direction: column;
  gap: 5px;
  padding: 0 15px;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
color: #333333;
  font-weight: 500;
  font-size: 15px;
}

.menu-item:hover {
  background: rgba(246, 135, 179, 0.1);
  transform: translateY(-1px);
}

.menu-item.active {
  font-weight: 600;

}

.menu-item .icon {
  margin-right: 12px;
  font-size: 18px;
}

.sidebar-bottom {
  padding: 0 15px;
  margin-top: auto;
}

.menu-item.settings {
  margin-top: 5px;
  margin-bottom: 5px;
}

.menu-item.logout {
  margin-top: 5px;
}

/* Active menu item styling */


/* Notification badge styling if needed */
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

        .dashboard {
            padding: 20px;
            margin-left: 260px;
            min-height: 100vh;
        }

        /* Stats Cards Styles */
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(247, 178, 217, 0.9), rgba(102, 51, 153, 0.8));
            padding: 20px;
            border-radius: 15px;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: white;
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
        }

        /* Charts Section */
        .charts-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .chart-container {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <div class="dashboard">
<nav class="top-navbar">
    <div class="nav-links-container">
        <a href="/Projet Web/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link">Utilisateurs</a>
        <a href="/Projet Web/mvcact/view/back office/dashboard.php" class="nav-link" data-section="activites">Activités</a>
        <a href="/Projet Web/mvcEvent/View/BackOffice/dashboard.php" class="nav-link active" data-section="evenements">Événements</a>
        <a href="/Projet Web/mvcProduit/view/back office/indeex.php" class="nav-link" data-section="produits">Produits</a>
        <a href="/Projet Web/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link" data-section="transports">Transports</a>
        <a href="/Projet Web/mvcSponsor/crud/view/back/back.php" class="nav-link" data-section="sponsors">Sponsors</a>
    </div>

    <div class="profile-container">

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

    <style>

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
          /* Réduit légèrement l'espacement entre les éléments */
          margin: 0;
          padding: 0;
          list-style: none;
        }  .sidebar h1 {
    font-size: 24px;
    margin-bottom: 40px;
    color: #663399;
  }

        .top-navbar {
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98));
          padding: 0px 25px;
          border-radius: 25px;
          box-shadow: 0 8px 32px rgba(151, 104, 209, 0.1);
          position: fixed;
          top: 30px;
          left: 55%;
          transform: translateX(-50%);
          z-index: 1000;
          width: fit-content;
          min-width: 800px;
          backdrop-filter: blur(10px);
          border: 1px solid rgba(151, 104, 209, 0.1);
        }

        .top-navbar ul {
          display: flex;
          justify-content: center;
          align-items: center;
          gap: 30px;
          /* Réduit légèrement l'espacement entre les éléments */
          margin: 0;
          padding: 0;
          list-style: none;
          padding-top: 1000px;
        }
        
        .nav-links-container {
            display: flex;
            gap: 30px;
            margin: 12px 12px;
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
          color: #F687B3;
        }

        .nav-link[href*="Produit"] {
          color: #9F7AEA;
        }

        .nav-link[href*="Covoiturage"] {
          color: #9F7AEA;
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


        .user-profile {
            position: relative;
            margin-left: auto;
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

        .profile-photo,
        .profile-circle {
        display: flex;
          width: 35px;
          height: 35px;
          border-radius: 50%;
          cursor: pointer;
          border: 2px solid white;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
  body::after {
    content: '✨';
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 24px;
    opacity: 0.5;
    z-index: 1;
  }
        
        .profile-container {
          display: flex;
          align-items: center;
          gap: 20px;
          margin-left: 35px;
          padding-left: 25px;
          border-left: 1px solid rgba(151, 104, 209, 0.2);
          padding-right: 40px;
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-width: 160px;
            z-index: 1001;
        }
        
        .dropdown-menu a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }
        
        .dropdown-menu a:hover {
            background-color: #f5f5f5;
        }


    </style>

    <script>
        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('dropdownMenu');
            const profile = document.querySelector('.user-profile');
            if (!profile.contains(event.target)) {
                menu.style.display = 'none';
            }
        });
    </script>

    </div>
</nav>

    <div class="sidebar">
        <div>
            <div class="sidebar-logo">
                <img src="../FrontOffice/images/logo.png" alt="Logo" />
            </div>
            <div class="menu-list">
                <div class="menu-item" data-section="evenements">
                    <span class="icon">📅</span>Événements
                </div>
                <div class="menu-item section-stat" data-section="statistiques">
                    <span class="icon">📊</span>Statistiques Générales
                </div>
                <div class="menu-item section-chaises" data-section="chaises">
                    <span class="icon">💺</span>Gestion des chaises
                </div>
            </div>
        </div>
        <div class="sidebar-bottom">
            <div class="menu-item logout">
                <span class="icon">🚪</span>Déconnexion
            </div>
        </div>
    </div>

    <!-- Contenu des sections -->
    <div id="section-evenements">
        <div class="header">
            <h2>Gestion des événements</h2>
            <div style="display: flex; gap: 15px;">
                <div class="search">
                    <input type="text" id="searchInput" placeholder="Rechercher..." style="border: none; background: transparent; width: 100%;">
                </div>
                <select id="sortSelect" style="padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                    <option value="date_asc">Trier par date (croissant)</option>
                    <option value="date_desc">Trier par date (décroissant)</option>
                    <option value="name_asc">Trier par nom (A-Z)</option>
                    <option value="name_desc">Trier par nom (Z-A)</option>
                    <option value="price_asc">Trier par prix (croissant)</option>
                    <option value="price_desc">Trier par prix (décroissant)</option>
                </select>
                <button class="open-panel-btn" id="openPanel">+ Ajouter un événement</button>
            </div>
        </div>

        <?php if ($errorMessage): ?>
            <div class="error-message-display">
                <p><?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php endif; ?>

        <div class="cardS">
            <button id="toggleTableBtn" class="toggle-table-btn">
                <i>▼</i> Réduire le tableau
            </button>

            <div class="table-responsive">
                <div class="collapsible-table" id="eventTable">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Catégorie</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Prix</th>
                                <th>Durée</th>
                                <th>Date</th>
                                <th>Lieu</th>
                                <th>Image</th>
                                <th>Places totales</th>
                                <th>Places réservées</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $events = $controller->getEvents();
                            foreach ($events as $event) {
                                echo "<tr>";
                                echo "<td>{$event['id']}</td>";

                                if ($editId == $event['id']) {
                                    echo "<form method='post'>";
                                    echo "<input type='hidden' name='id' value='{$event['id']}'>";

                                    echo "<td><input type='text' name='category' value='" . htmlspecialchars($event['category']) . "' 
                                              required minlength='3'></td>";
                                    echo "<td><input type='text' name='name' value='" . htmlspecialchars($event['name']) . "' 
                                              required minlength='3'></td>";
                                    echo "<td><input type='text' name='description' value='" . htmlspecialchars($event['description']) . "' 
                                              required></td>";
                                    echo "<td><input type='number' step='0.01' name='price' value='{$event['price']}' 
                                              required min='0'></td>";
                                    echo "<td><input type='number' name='duration' value='{$event['duration']}' 
                                              required min='1' max='8'></td>";
                                    echo "<td><input type='date' name='date' value='{$event['date']}' 
                                              required min='" . date('Y-m-d') . "'></td>";
                                    echo "<td>
                                              <div class='location-display'>
                                                  <input type='text' id='edit_place_name_display_{$event['id']}' value='" . htmlspecialchars($event['place_name']) . "' readonly>
                                                  <button type='button' class='edit-location-btn' data-event-id='{$event['id']}'>Modifier le lieu</button>
                                              </div>
                                              <div id='edit_location_container_{$event['id']}' class='location-edit-container' style='display: none;'>
                                                  <div id='map-{$event['id']}' style='height: 200px;'></div>
                                                  <div id='edit_geocoder_{$event['id']}' class='geocoder'></div>
                                                  <span id='edit_locationError_{$event['id']}' class='error-message'></span>
                                              </div>
                                              <input type='hidden' name='longitude' id='edit_longitude_{$event['id']}' value='{$event['longitude']}'>
                                              <input type='hidden' name='latitude' id='edit_latitude_{$event['id']}' value='{$event['latitude']}'>
                                              <input type='hidden' name='place_name' id='edit_place_name_{$event['id']}' value='" . htmlspecialchars($event['place_name']) . "'>
                                          </td>";
                                    echo "<td>
                                        <input type='text' name='imageUrl' value='" . htmlspecialchars($event['imageUrl']) . "' 
                                             minlength='3' pattern='.*/.*'>
                                        " . (!empty($event['imageUrl']) ?
                                        "<img src='" . htmlspecialchars($event['imageUrl']) . "' alt='Preview' style='max-width: 60px; margin-top: 5px;'>"
                                        : "") . "
                                      </td>";
                                    echo "<td><input type='number' name='totalSeats' value='{$event['totalSeats']}' 
                                              required min='20' max='50'></td>";
                                    echo "<td><input type='number' name='reservedSeats' value='{$event['reservedSeats']}' 
                                              required min='0'></td>";
                                    echo "<td><button type='submit' name='action' value='modif' class='btn btn-primary'>Enregistrer</button></td>";
                                    echo "</form>";
                                } else {
                                    echo "<td>{$event['category']}</td>";
                                    echo "<td>{$event['name']}</td>";
                                    echo "<td>{$event['description']}</td>";
                                    echo "<td>{$event['price']}</td>";
                                    echo "<td>{$event['duration']}</td>";
                                    echo "<td>{$event['date']}</td>";
                                    echo "<td>" . htmlspecialchars($event['place_name']) . "</td>";
                                    echo "<td>";
                                    if (!empty($event['imageUrl'])) {
                                        echo "<img src='{$event['imageUrl']}' alt='Image événement' style='max-width: 100px; max-height: 60px; object-fit: cover;'>";
                                    } else {
                                        echo "Aucune image";
                                    }
                                    echo "</td>";
                                    echo "<td>{$event['totalSeats']}</td>";
                                    echo "<td>{$event['reservedSeats']}</td>";
                                    echo "<td class='action-buttons'>
                                        <form method='post'>
                                            <input type='hidden' name='edit_id' value='{$event['id']}'>
                                            <button type='submit' name='action' value='edit' class='btn-modifier'>Modifier</button>
                                        </form>
                                        <form method='post'>
                                            <input type='hidden' name='id' value='{$event['id']}'>
                                            <button type='submit' name='action' value='supp' class='btn-supprimer'>Supprimer</button>
                                        </form>
<button onclick=\"showSeatSectionForEvent({$event['id']})\" class='btn-voir-chaises'>Voir chaises</button>
</td>";
                                }
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="section-statistiques" style="display:none; padding-top:100px;">

        <div class="stats-overview">
            <div class="stat-card">
                <h3>Total des Événements</h3>
                <div class="number">
                    <?php
                    $totalEvents = count($events);
                    echo $totalEvents;
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Places Réservées</h3>
                <div class="number">
                    <?php
                    $totalReservedSeats = array_reduce($events, function ($carry, $event) {
                        return $carry + intval($event['reservedSeats']);
                    }, 0);
                    echo $totalReservedSeats;
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Revenus Totaux (€)</h3>
                <div class="number">
                    <?php
                    $totalRevenue = array_reduce($events, function ($carry, $event) {
                        return $carry + (floatval($event['price']) * intval($event['reservedSeats']));
                    }, 0);
                    echo number_format($totalRevenue, 2, ',', ' ');
                    ?>
                </div>
            </div>
        </div>

        <div class="charts-section">
            <h2>Statistiques des événements</h2>
            <div class="charts-container">
                <div class="chart-container">
                    <canvas id="eventsByCategoryChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="seatStatusChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="priceDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div id="section-chaises" style="display:non; padding-top:100px;">
        <div class="seat-management">
            <h2>Gestion des chaises</h2>
            <div class="seat-controls">
                <select id="eventFilter">
                    <option value="">Sélectionner un événement</option>
                    <?php
                    $events = $controller->getEvents();
                    foreach ($events as $event) {
                        echo "<option value='{$event['id']}'>" . htmlspecialchars($event['name']) . "</option>";
                    }
                    ?>
                </select>
                <select id="statusFilter">
                    <option value="all">Tous les statuts</option>
                    <option value="libre">Libre</option>
                    <option value="reserve">Réservé</option>
                </select>
                <div class="seat-stats" id="seatStats"></div>
            </div>
            <div class="seat-grid-container">
                <div class="seat-grid" id="seatGrid"></div>
            </div>
        </div>

        <div id="seatModal" class="modal">
            <div class="modal-content">
                <span class="close">×</span>
                <h3>Détails de la chaise</h3>
                <div id="seatDetails"></div>
            </div>
        </div>
    </div>
    </div>

    <div class="add-panel" id="addPanel">
        <button class="close-panel" id="closePanel">×</button>
        <h3>Ajouter un événement</h3>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="eventForm">
            <div class="form-group">
                <label for="category">Catégorie :</label>
                <select id="category" name="category" required>
                    <option value="">Sélectionnez...</option>
                    <option value="sportif">Événements sportifs</option>
                    <option value="culturel">Festivals culturels</option>
                    <option value="culinaire">Festivals culinaires</option>
                    <option value="musique">Festivals de musique</option>
                    <option value="charite">Galas de charité</option>
                </select>
            </div>
            <div class="form-group">
                <label for="name">Nom :</label>
                <input type="text" name="name" id="name" required minlength="3">
                <span id="nameError" class="error-message"></span>
            </div>
            <div class="form-group">
                <label for="description">Description :</label>
                <textarea name="description" id="description" required maxlength="3000"></textarea>
                <span id="descriptionError" class="error-message"></span>
            </div>
            <div class="form-group">
                <label for="price">Prix :</label>
                <input type="number" step="0.01" name="price" id="price" required min="0">
                <span id="priceError" class="error-message"></span>
            </div>
            <div class="form-group">
                <label for="duration">Durée :</label>
                <input type="number" name="duration" id="duration" required min="1" max="8">
                <span id="durationError" class="error-message"></span>
            </div>
            <div class="form-group">
                <label for="date">Date :</label>
                <input type="date" name="date" id="date" required>
                <span id="dateError" class="error-message"></span>
            </div>
            <div class="form-group">
                <label for="location">Lieu :</label>
                <div id="map" style="height: 300px;"></div>
                <div id="geocoder" class="geocoder"></div>
                <input type="hidden" name="longitude" id="longitude">
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="place_name" id="place_name">
                <span id="locationError" class="error-message"></span>
            </div>
            <div class="form-group">
                <label for="imageUrl">URL de l'image :</label>
                <input type="text" name="imageUrl" id="imageUrl" minlength="3" pattern=".*/.*">
                <span id="imageUrlError" class="error-message"></span>
            </div>
            <div class="form-group">
                <label for="totalSeats">Places totales :</label>
                <input type="number" name="totalSeats" id="totalSeats" required min="20" max="50">
                <span id="totalSeatsError" class="error-message"></span>
            </div>
            <button type="submit" name="action" value="add" class="btn btn-primary" id="submitEventBtn" disabled>Enregistrer</button>
        </form>
    </div>

    <div class="overlay" id="overlay"></div>

    <div class="modal" id="descriptionModal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Description complète</h3>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion du sidebar
            const menuItems = document.querySelectorAll('.menu-list .menu-item');

            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Retirer la classe active de tous les items du menu
                    menuItems.forEach(i => i.classList.remove('active'));
                    // Ajouter la classe active à l'item cliqué
                    this.classList.add('active');

                    // Gérer l'affichage des sections
                    const section = this.getAttribute('data-section');
                    document.getElementById('section-evenements').style.display = (section === 'evenements') ? 'block' : 'none';
                    document.getElementById('section-statistiques').style.display = (section === 'statistiques') ? 'block' : 'none';
                    document.getElementById('section-chaises').style.display = (section === 'chaises') ? 'block' : 'none';
                });
            });

            // Gestion du menu utilisateur dans la navbar
            const userAvatar = document.querySelector('.user-avatar');
            const userInfo = document.querySelector('.user-info');

            if (userAvatar && userInfo) {
                userAvatar.addEventListener('click', function() {
                    userInfo.classList.toggle('active');
                });

                // Fermer le menu utilisateur quand on clique ailleurs
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.user-profile')) {
                        userInfo.classList.remove('active');
                    }
                });
            }

            // Gestion du bouton de déconnexion
            const logoutBtn = document.querySelector('.logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function() {
                    window.location.href = 'logout.php';
                });
            }

            // Activer la section événements par défaut dans le sidebar
            const defaultMenuItem = document.querySelector('.menu-item[data-section="evenements"]');
            if (defaultMenuItem) {
                defaultMenuItem.classList.add('active');
            }
        });
    </script>

    <script src='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js'></script>
    <script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js'></script>
    <script>
        // Mapbox access token
        mapboxgl.accessToken = '<?php echo "pk.eyJ1IjoiaGFvYXVzMDEiLCJhIjoiY21hOHhqcGttMWJ5NjJtczg3eGJxazM0MiJ9.lm0YeqM7TkpDT4r6_Pf6aw"; ?>';

        // Ajouter user_id pour les appels à get_chaises.php
        const USER_ID = <?php echo json_encode($user_id); ?>;
        // Event data from PHP
        const eventsData = <?php echo json_encode($events); ?>;

        // Utility functions
        function showLoading(element) {
            element.innerHTML = `
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100px;">
            <div class="loading-spinner" style="
                border: 4px solid rgba(52, 152, 219, 0.1);
                border-radius: 50%;
                border-top: 4px solid #3498db;
                width: 40px;
                height: 40px;
                animation: spin 1s linear infinite;
            "></div>
            <p style="margin-top: 10px; color: #3498db;">Chargement...</p>
        </div>
    `;
        }

        function showError(element, message) {
            element.textContent = message;
            element.classList.add("error");
            element.classList.remove("valid");
        }

        function showValid(element) {
            element.textContent = "✓ Valide";
            element.classList.add("valid");
            element.classList.remove("error");
        }

        // Seat management variables
        let currentChaises = [];
        let currentChaiseId = null;
        let selectedSeat = null;

        // Seat management functions
        function loadSeatsForEvent(eventId) {
            const grid = document.getElementById('seatGrid');
            const stats = document.getElementById('seatStats');
            if (!grid || !stats) {
                console.error('Seat grid or stats element not found');
                return;
            }
            if (!eventId) {
                grid.innerHTML = '<p class="no-seats">Sélectionnez un événement</p>';
                stats.innerHTML = '';
                console.log('No event ID provided');
                updateSeatStatusChart({
                    total: 0,
                    reserved: 0
                });
                return;
            }
            showLoading(grid);
            console.log('Fetching seats for event ID:', eventId);
            // Ajouter user_id à l'URL si disponible
            const url = USER_ID ? `/Projet%20Web/mvcEvent/get_chaises.php?event_id=${eventId}&user_id=${USER_ID}` : `/Projet%20Web/mvcEvent/get_chaises.php?event_id=${eventId}`;
            fetch(url)
                .then(response => {
                    console.log('Fetch response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Fetch data received:', data);
                    if (data.status === 'success') {
                        currentChaises = data.chaises || [];
                        updateSeatGrid();
                        updateSeatStats(data.stats || {
                            total: 0,
                            reserved: 0
                        });
                        updateSeatStatusChart(data.stats || {
                            total: 0,
                            reserved: 0
                        });
                        animateSeats();
                    } else {
                        throw new Error(data.message || 'Erreur serveur');
                    }
                })
                .catch(error => {
                    console.error('Error loading seats:', error);
                    grid.innerHTML = `<p class="error-message">Erreur : ${error.message}</p>`;
                    stats.innerHTML = '';
                    updateSeatStatusChart({
                        total: 0,
                        reserved: 0
                    });
                });
        }

        function updateSeatGrid() {
            const grid = document.getElementById('seatGrid');
            const statusFilter = document.getElementById('statusFilter')?.value || 'all';

            grid.innerHTML = '';

            if (!currentChaises.length) {
                grid.innerHTML = '<p class="no-seats">Aucune chaise disponible pour cet événement</p>';
                console.log('No chaises available');
                return;
            }

            currentChaises.forEach(chaise => {
                if (statusFilter === 'all' || chaise.statut === statusFilter) {
                    const seatEl = createSeatElement(chaise);
                    grid.appendChild(seatEl);
                }
            });
            console.log('Seat grid updated with', currentChaises.length, 'chaises');
        }

        function createSeatElement(chaise) {
            const seatEl = document.createElement('div');
            seatEl.className = `seat ${chaise.statut}`;
            seatEl.textContent = chaise.numero;
            seatEl.dataset.id = chaise.id;

            seatEl.title = `Chaise #${chaise.numero} - ${chaise.statut === 'libre' ? 'Disponible' : 'Réservée'}`;

            seatEl.addEventListener('click', () => {
                if (selectedSeat) {
                    selectedSeat.classList.remove('selected');
                }
                selectedSeat = seatEl;
                selectedSeat.classList.add('selected');
                showSeatDetails(chaise);
            });

            return seatEl;
        }

        function animateSeats() {
            setTimeout(() => {
                document.querySelectorAll('.seat').forEach((seat, index) => {
                    seat.style.opacity = '0';
                    seat.style.transform = 'translateY(20px)';
                    seat.style.transition = 'none';

                    setTimeout(() => {
                        seat.style.transition = 'opacity 0.3s, transform 0.3s';
                        seat.style.transitionDelay = `${index * 0.05}s`;
                        seat.style.opacity = '1';
                        seat.style.transform = 'translateY(0)';
                    }, 50);
                });
            }, 100);
        }

        function updateSeatStats(stats) {
            const statsElement = document.getElementById('seatStats');
            if (!statsElement) {
                console.error('Seat stats element not found');
                return;
            }

            const total = stats.total || 0;
            const reserved = stats.reserved || 0;
            const free = total - reserved;
            const percentage = total > 0 ? Math.round((reserved / total) * 100) : 0;

            statsElement.innerHTML = `
        <div class="stats-container">
            <div class="stat-item">
                <span class="stat-value">${total}</span>
                <span class="stat-label">Total</span>
            </div>
            <div class="stat-item">
                <span class="stat-value" style="color: #e74c3c">${reserved}</span>
                <span class="stat-label">Réservées</span>
            </div>
            <div class="stat-item">
                <span class="stat-value" style="color: #2ecc71">${free}</span>
                <span class="stat-label">Libres</span>
            </div>
            <div class="progress-container">
                <div class="progress-bar" style="width: ${percentage}%"></div>
            </div>
            <span class="percentage">${percentage}% réservé</span>
        </div>
    `;
            console.log('Seat stats updated:', {
                total,
                reserved,
                free,
                percentage
            });
        }

        function filterSeats() {
            console.log('Filtering seats by status:', document.getElementById('statusFilter').value);
            updateSeatGrid();
        }

        function showSeatDetails(chaise) {
            currentChaiseId = chaise.id;
            const modal = document.getElementById('seatModal');
            const details = document.getElementById('seatDetails');

            if (!modal || !details) return;

            details.innerHTML = `
        <p><strong>ID:</strong> <span>${chaise.id}</span></p>
        <p><strong>Numéro:</strong> <span>${chaise.numero}</span></p>
        <p><strong>Statut:</strong> <span class="status-${chaise.statut}">${chaise.statut === 'libre' ? 'Disponible' : 'Réservée'}</span></p>
        <p><strong>Utilisateur:</strong> <span>${chaise.id_user || 'Aucun'}</span></p>
    `;

            modal.classList.add('active'); // Utilise la classe active pour afficher
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('seatModal');
            if (!modal) return;

            modal.classList.remove('active'); // Retire la classe active pour cacher
            document.body.style.overflow = '';
        }

        // Ajouter des gestionnaires d'événements pour fermer la modal
        document.addEventListener('DOMContentLoaded', function() {
            const closeButtons = document.querySelectorAll('.close');
            closeButtons.forEach(button => {
                button.addEventListener('click', closeModal);
            });

            // Fermer la modal en cliquant en dehors
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('seatModal');
                if (event.target === modal) {
                    closeModal();
                }
            });
        });

        function initializeSeatManagement() {
            const style = document.createElement('style');
            style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .seat { cursor: pointer; padding: 10px; margin: 5px; border-radius: 5px; }
        .seat.libre { background: #2ecc71; color: white; }
        .seat.reserve { background: #e74c3c; color: white; }
        .seat.selected { border: 2px solidrgb(137, 65, 224); }
        .status-libre { color: #2ecc71; font-weight: bold; }
        .status-reserve { color: #e74c3c; font-weight: bold; }
        .error-message { color: #e74c3c; text-align: center; padding: 20px; }
        .no-seats { text-align: center; color: #7f8c8d; padding: 20px; }
        .stats-container { display: flex; gap: 15px; flex-wrap: wrap; align-items: center; }
        .stat-item { text-align: center; padding: 5px 10px; }
        .stat-value { font-size: 1.2rem; font-weight: bold; display: block; }
        .stat-label { font-size: 0.8rem; color: #7f8c8d; }
        .progress-container {
            flex-grow: 1;
            height: 8px;
            background: #ecf0f1;
            border-radius: 4px;
            overflow: hidden;
            margin: 0 10px;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #e74c3c, #3498db);
            transition: width 0.5s;
        }
        .percentage { font-size: 0.9rem; color: #7f8c8d; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-10px); }
            40%, 80% { transform: translateX(10px); }
        }
    `;
            document.head.appendChild(style);
        }

        // Form validation and other dashboard functionality
        document.addEventListener("DOMContentLoaded", function() {
            console.log('Initializing dashboard...');
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date').min = today;

            let isLocationSelected = false;

            // Initialize map for add event form
            let map, geocoder;
            try {
                map = new mapboxgl.Map({
                    container: 'map',
                    style: 'mapbox://styles/mapbox/streets-v11',
                    center: [10.1815, 36.8065],
                    zoom: 10
                });
                geocoder = new MapboxGeocoder({
                    accessToken: mapboxgl.accessToken,
                    mapboxgl: mapboxgl,
                    placeholder: 'Rechercher un lieu...',
                    marker: {
                        color: '#C83EFC'
                    }
                });
                document.getElementById('geocoder').appendChild(geocoder.onAdd(map));

                map.on('styleimagemissing', (e) => {
                    const imageId = e.id;
                    console.warn(`Missing image: ${imageId}`);
                    map.addImage(imageId, {
                        width: 20,
                        height: 20,
                        data: new Uint8Array(20 * 20 * 4)
                    });
                });

                geocoder.on('result', function(e) {
                    const coords = e.result.geometry.coordinates;
                    document.getElementById('longitude').value = coords[0];
                    document.getElementById('latitude').value = coords[1];
                    document.getElementById('place_name').value = e.result.place_name;
                    isLocationSelected = true;
                    console.log('Geocoder result:', {
                        longitude: coords[0],
                        latitude: coords[1],
                        place_name: e.result.place_name
                    });
                    validateLocation();
                    updateSubmitButton();
                });

                geocoder.on('error', function(error) {
                    console.error('Geocoder error:', error);
                    document.getElementById('locationError').textContent = 'Erreur lors de la recherche du lieu. Veuillez réessayer.';
                    isLocationSelected = false;
                    updateSubmitButton();
                });
            } catch (error) {
                console.error('Mapbox initialization failed:', error);
                document.getElementById('locationError').textContent = 'Erreur de chargement de la carte. Vérifiez votre connexion réseau ou la clé API Mapbox.';
            }

            // Form validation functions
            function validateName() {
                const name = document.getElementById('name').value.trim();
                const errorElement = document.getElementById('nameError');

                if (name.length < 3) {
                    showError(errorElement, "Le nom doit contenir au moins 3 caractères");
                    return false;
                } else {
                    showValid(errorElement);
                    return true;
                }
            }

            function validatePrice() {
                const price = parseFloat(document.getElementById('price').value);
                const errorElement = document.getElementById('priceError');

                if (isNaN(price) || price < 0) {
                    showError(errorElement, "Le prix doit être positif");
                    return false;
                } else {
                    showValid(errorElement);
                    return true;
                }
            }

            function validateImageUrl() {
                const imageUrl = document.getElementById('imageUrl').value.trim();
                const errorElement = document.getElementById('imageUrlError');

                if (imageUrl && (imageUrl.length < 3 || !imageUrl.includes('/'))) {
                    showError(errorElement, "L'image doit contenir au moins 3 caractères et un /");
                    return false;
                } else {
                    showValid(errorElement);
                    return true;
                }
            }

            function validateDuration() {
                const duration = parseInt(document.getElementById('duration').value);
                const errorElement = document.getElementById('durationError');

                if (isNaN(duration) || duration <= 0 || duration > 8) {
                    showError(errorElement, "La durée doit être positive et ≤ 8 heures");
                    return false;
                } else {
                    showValid(errorElement);
                    return true;
                }
            }

            function validateLocation() {
                const longitude = document.getElementById('longitude').value;
                const latitude = document.getElementById('latitude').value;
                const placeName = document.getElementById('place_name').value;
                const errorElement = document.getElementById('locationError');

                if (!isLocationSelected) {
                    showError(errorElement, "Veuillez sélectionner un lieu à l'aide de la recherche.");
                    return false;
                }

                if (!longitude || !latitude || !placeName) {
                    showError(errorElement, "Les coordonnées du lieu sont manquantes.");
                    return false;
                }

                if (isNaN(parseFloat(longitude)) || isNaN(parseFloat(latitude))) {
                    showError(errorElement, "Les coordonnées GPS sont invalides.");
                    return false;
                }

                if (placeName.length < 3) {
                    showError(errorElement, "Le nom du lieu doit contenir au moins 3 caractères.");
                    return false;
                }

                showValid(errorElement);
                return true;
            }

            function validateDate() {
                const dateInput = document.getElementById('date').value;
                const selectedDate = new Date(dateInput);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const errorElement = document.getElementById('dateError');

                if (!dateInput || selectedDate <= today) {
                    showError(errorElement, "La date doit être dans le futur");
                    return false;
                } else {
                    showValid(errorElement);
                    return true;
                }
            }

            function validateTotalSeats() {
                const seats = parseInt(document.getElementById('totalSeats').value);
                const errorElement = document.getElementById('totalSeatsError');

                if (isNaN(seats) || seats < 20 || seats > 50) {
                    showError(errorElement, "Le nombre de places doit être entre 20 et 50");
                    return false;
                } else {
                    showValid(errorElement);
                    return true;
                }
            }

            function validateDescription() {
                const description = document.getElementById('description').value.trim();
                const words = description.split(/\s+/).filter(word => word.length > 0);
                const errorElement = document.getElementById('descriptionError');

                if (words.length > 300) {
                    showError(errorElement, "La description ne doit pas dépasser 300 mots");
                    return false;
                } else {
                    showValid(errorElement);
                    return true;
                }
            }

            function validateCategory() {
                const category = document.getElementById('category').value;
                return category !== "";
            }

            function updateSubmitButton() {
                const submitButton = document.getElementById('submitEventBtn');
                const isValid = validateName() && validatePrice() && validateImageUrl() &&
                    validateDuration() && validateLocation() && validateDate() &&
                    validateTotalSeats() && validateDescription() && validateCategory();
                submitButton.disabled = !isValid;
            }

            // Add event listeners for form inputs
            document.getElementById('name').addEventListener('input', () => {
                validateName();
                updateSubmitButton();
            });
            document.getElementById('price').addEventListener('input', () => {
                validatePrice();
                updateSubmitButton();
            });
            document.getElementById('imageUrl').addEventListener('input', () => {
                validateImageUrl();
                updateSubmitButton();
            });
            document.getElementById('duration').addEventListener('input', () => {
                validateDuration();
                updateSubmitButton();
            });
            document.getElementById('date').addEventListener('change', () => {
                validateDate();
                updateSubmitButton();
            });
            document.getElementById('totalSeats').addEventListener('input', () => {
                validateTotalSeats();
                updateSubmitButton();
            });
            document.getElementById('description').addEventListener('input', () => {
                validateDescription();
                updateSubmitButton();
            });
            document.getElementById('category').addEventListener('change', () => {
                updateSubmitButton();
            });

            // Form submission validation
            document.getElementById('eventForm').addEventListener('submit', function(e) {
                console.log('Form submission attempt:', Object.fromEntries(new FormData(this)));
                const isValid = validateName() && validatePrice() && validateImageUrl() &&
                    validateDuration() && validateLocation() && validateDate() &&
                    validateTotalSeats() && validateDescription() && validateCategory();

                if (!isValid) {
                    e.preventDefault();
                    console.error('Form validation failed:', {
                        name: validateName(),
                        price: validatePrice(),
                        imageUrl: validateImageUrl(),
                        duration: validateDuration(),
                        location: validateLocation(),
                        date: validateDate(),
                        totalSeats: validateTotalSeats(),
                        description: validateDescription(),
                        category: validateCategory()
                    });
                }
            });

            // Initialize map and geocoder for edit forms
            // Gestion du clic sur le bouton "Modifier le lieu"
            document.querySelectorAll('.edit-location-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const eventId = this.dataset.eventId;
                    const locationContainer = document.getElementById(`edit_location_container_${eventId}`);

                    if (locationContainer) {
                        locationContainer.style.display = 'block';
                        this.style.display = 'none';

                        // Initialiser la carte uniquement si elle n'existe pas déjà
                        if (!window[`editMap_${eventId}`]) {
                            initializeEditMap(eventId);
                        }
                    }
                });
            });

            // Fonction pour initialiser la carte et le géocodeur
            function initializeEditMap(eventId) {
                const mapContainer = document.getElementById(`map-${eventId}`);
                if (!mapContainer) return;

                try {
                    const longitude = parseFloat(document.getElementById(`edit_longitude_${eventId}`).value) || 10.1815;
                    const latitude = parseFloat(document.getElementById(`edit_latitude_${eventId}`).value) || 36.8065;

                    const editMap = new mapboxgl.Map({
                        container: `map-${eventId}`,
                        style: 'mapbox://styles/mapbox/streets-v11',
                        center: [longitude, latitude],
                        zoom: 12
                    });

                    const editGeocoder = new MapboxGeocoder({
                        accessToken: mapboxgl.accessToken,
                        mapboxgl: mapboxgl,
                        placeholder: 'Rechercher un lieu...',
                        marker: {
                            color: '#C83EFC'
                        }
                    });

                    document.getElementById(`edit_geocoder_${eventId}`).appendChild(editGeocoder.onAdd(editMap));

                    // Stocker la référence de la carte dans l'objet window
                    window[`editMap_${eventId}`] = editMap;

                    editGeocoder.on('result', function(e) {
                        const coords = e.result.geometry.coordinates;
                        document.getElementById(`edit_longitude_${eventId}`).value = coords[0];
                        document.getElementById(`edit_latitude_${eventId}`).value = coords[1];
                        document.getElementById(`edit_place_name_${eventId}`).value = e.result.place_name;
                        document.getElementById(`edit_place_name_display_${eventId}`).value = e.result.place_name;
                    });

                    editGeocoder.on('error', function(error) {
                        console.error(`Geocoder error for event ${eventId}:`, error);
                        document.getElementById(`edit_locationError_${eventId}`).textContent =
                            'Erreur lors de la recherche du lieu. Veuillez réessayer.';
                    });

                    // Ajouter un marqueur pour le lieu existant
                    if (!isNaN(longitude) && !isNaN(latitude)) {
                        new mapboxgl.Marker({
                                color: '#C83EFC'
                            })
                            .setLngLat([longitude, latitude])
                            .addTo(editMap);
                    }

                } catch (error) {
                    console.error(`Failed to initialize map for event ${eventId}:`, error);
                    document.getElementById(`edit_locationError_${eventId}`).textContent =
                        'Erreur de chargement de la carte. Vérifiez votre connexion réseau.';
                }
            }
            // Panel controls
            const openPanelBtn = document.getElementById('openPanel');
            const closePanelBtn = document.getElementById('closePanel');
            const addPanel = document.getElementById('addPanel');
            const overlay = document.getElementById('overlay');

            openPanelBtn.addEventListener('click', () => {
                addPanel.classList.add('active');
                overlay.classList.add('active');
            });

            closePanelBtn.addEventListener('click', () => {
                addPanel.classList.remove('active');
                overlay.classList.remove('active');
            });

            overlay.addEventListener('click', () => {
                addPanel.classList.remove('active');
                overlay.classList.remove('active');
            });

            // Table collapse
            const toggleBtn = document.getElementById('toggleTableBtn');
            const eventTable = document.getElementById('eventTable');

            if (toggleBtn && eventTable) {
                toggleBtn.addEventListener('click', function() {
                    eventTable.classList.toggle('collapsed');
                    const icon = this.querySelector('i');
                    if (eventTable.classList.contains('collapsed')) {
                        icon.textContent = '▲';
                        this.innerHTML = '<i>▲</i> Étendre le tableau';
                    } else {
                        icon.textContent = '▼';
                        this.innerHTML = '<i>▼</i> Réduire le tableau';
                    }
                });
            }

            // Seat management initialization
            function setupEventListeners() {
                const modalClose = document.querySelector('.modal .close');
                if (modalClose) {
                    modalClose.addEventListener('click', closeModal);
                } else {
                    console.error('Modal close button not found');
                }

                window.addEventListener('click', (event) => {
                    if (event.target === document.getElementById('seatModal')) {
                        closeModal();
                    }
                });

                const eventFilter = document.getElementById('eventFilter');
                if (eventFilter) {
                    eventFilter.addEventListener('change', function() {
                        console.log('Event filter changed to:', this.value);
                        loadSeatsForEvent(this.value);
                    });
                } else {
                    console.error('Event filter element not found');
                }

                const statusFilter = document.getElementById('statusFilter');
                if (statusFilter) {
                    statusFilter.addEventListener('change', filterSeats);
                } else {
                    console.error('Status filter element not found');
                }
            }

            // Search and sort
            const searchInput = document.getElementById('searchInput');
            const sortSelect = document.getElementById('sortSelect');

            if (searchInput) {
                searchInput.addEventListener('input', filterAndSortEvents);
            } else {
                console.error('Search input element not found');
            }

            if (sortSelect) {
                sortSelect.addEventListener('change', filterAndSortEvents);
            } else {
                console.error('Sort select element not found');
            }

            function filterAndSortEvents() {
                const searchInput = document.getElementById('searchInput');
                const sortSelect = document.getElementById('sortSelect');
                const searchTerm = searchInput?.value.toLowerCase() || '';
                const sortValue = sortSelect?.value || '';
                const rows = document.querySelectorAll('#eventTable tbody tr');

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (!cells || cells.length < 4) {
                        console.warn('Row skipped due to missing cells:', row);
                        return;
                    }

                    const name = cells[2].textContent.toLowerCase();
                    const description = cells[3].textContent.toLowerCase();

                    const matchesSearch = name.includes(searchTerm) ||
                        description.includes(searchTerm) ||
                        cells[1].textContent.toLowerCase().includes(searchTerm);

                    row.style.display = matchesSearch ? '' : 'none';
                });

                const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');

                visibleRows.sort((a, b) => {
                    const aCells = a.querySelectorAll('td');
                    const bCells = b.querySelectorAll('td');

                    if (!aCells || !bCells || aCells.length < 7 || bCells.length < 7) {
                        console.warn('Rows skipped during sorting due to missing cells:', a, b);
                        return 0;
                    }

                    switch (sortValue) {
                        case 'date_asc':
                            return new Date(aCells[6].textContent) - new Date(bCells[6].textContent);
                        case 'date_desc':
                            return new Date(bCells[6].textContent) - new Date(aCells[6].textContent);
                        case 'name_asc':
                            return aCells[2].textContent.localeCompare(bCells[2].textContent);
                        case 'name_desc':
                            return bCells[2].textContent.localeCompare(aCells[2].textContent);
                        case 'price_asc':
                            return parseFloat(aCells[4].textContent) - parseFloat(bCells[4].textContent);
                        case 'price_desc':
                            return parseFloat(bCells[4].textContent) - parseFloat(aCells[4].textContent);
                        default:
                            return 0;
                    }
                });

                const tbody = document.querySelector('#eventTable tbody');
                visibleRows.forEach(row => tbody.appendChild(row));
                console.log('Events filtered and sorted:', {
                    searchTerm,
                    sortValue
                });
            }

            // Initialize seat management
            initializeSeatManagement();
            initializeCharts();
            setupEventListeners();

            const eventFilter = document.getElementById('eventFilter');
            if (eventFilter && eventFilter.options.length > 1) {
                eventFilter.value = eventFilter.options[1].value;
                loadSeatsForEvent(eventFilter.value);
            } else {
                console.error('Event filter not found or has insufficient options');
                const seatGrid = document.getElementById('seatGrid');
                if (seatGrid) {
                    seatGrid.innerHTML = '<p class="error-message">Erreur : Aucun événement disponible.</p>';
                }
            }

            // Global error handler
            window.onerror = function(message, source, lineno, colno, error) {
                console.error(`Global error: ${message} at ${source}:${lineno}:${colno}`, error);

                const existingError = document.querySelector('.error-message-display');
                if (existingError) {
                    existingError.remove();
                }

                document.body.insertAdjacentHTML('beforeend', `
            <div class="error-message-display">
                <p>Une erreur s'est produite. Veuillez réessayer ou contacter le support.</p>
            </div>
        `);
            };
        });
        // Chart initialization functions
        function initializeCharts() {
            const chartColors = {
                // Couleurs pastel
                pink: '#FFE1E9',
                lavender: '#E1E1FF',
                mint: '#D1FFE4',
                peach: '#FFE4D6',
                lemon: '#FFF3D6',
                skyBlue: '#D6F3FF',
                lilac: '#F2E6FF',
                coral: '#FFD6D6',
                // Couleurs pour les bordures
                pinkBorder: '#FFB5C5',
                lavenderBorder: '#B5B5FF',
                mintBorder: '#98FFB3',
                peachBorder: '#FFB59E',
                lemonBorder: '#FFE08C',
                skyBlueBorder: '#9EDFFF',
                lilacBorder: '#E0B3FF',
                coralBorder: '#FFB3B3'
            };

            // Events by Category (Bar Chart)
            const categoryCounts = eventsData.reduce((acc, event) => {
                acc[event.category] = (acc[event.category] || 0) + 1;
                return acc;
            }, {});
            const categoryLabels = Object.keys(categoryCounts);
            const categoryData = Object.values(categoryCounts);

            eventsByCategoryChart = new Chart(document.getElementById('eventsByCategoryChart'), {
                type: 'bar',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        label: 'Nombre d\'événements',
                        data: categoryData,
                        backgroundColor: [
                            chartColors.pink,
                            chartColors.lavender,
                            chartColors.mint,
                            chartColors.peach,
                            chartColors.lemon,
                            chartColors.skyBlue,
                            chartColors.lilac,
                            chartColors.coral
                        ],
                        borderColor: [
                            chartColors.pinkBorder,
                            chartColors.lavenderBorder,
                            chartColors.mintBorder,
                            chartColors.peachBorder,
                            chartColors.lemonBorder,
                            chartColors.skyBlueBorder,
                            chartColors.lilacBorder,
                            chartColors.coralBorder
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Événements par catégorie',
                            color: '#663399',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(102, 51, 153, 0.1)'
                            },
                            ticks: {
                                color: '#663399',
                                font: {
                                    weight: '500'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#663399',
                                font: {
                                    weight: '500'
                                }
                            }
                        }
                    }
                }
            });

            // Seat Status (Pie Chart)
            seatStatusChart = new Chart(document.getElementById('seatStatusChart'), {
                type: 'pie',
                data: {
                    labels: ['Libres', 'Réservées'],
                    datasets: [{
                        data: [0, 0],
                        backgroundColor: [chartColors.mint, chartColors.lavender],
                        borderColor: [chartColors.mintBorder, chartColors.lavenderBorder],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#663399',
                                font: {
                                    weight: '500'
                                },
                                padding: 20
                            }
                        },
                        title: {
                            display: true,
                            text: 'Statut des places',
                            color: '#663399',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    }
                }
            });

            // Price Distribution (Line Chart)
            const priceRanges = [0, 10, 20, 50, 100, Infinity];
            const priceLabels = ['0-10', '10-20', '20-50', '50-100', '100+'];
            const priceData = priceRanges.slice(0, -1).map((range, index) => {
                return eventsData.filter(event => {
                    const price = parseFloat(event.price);
                    return price >= range && price < priceRanges[index + 1];
                }).length;
            });

            priceDistributionChart = new Chart(document.getElementById('priceDistributionChart'), {
                type: 'line',
                data: {
                    labels: priceLabels,
                    datasets: [{
                        label: 'Nombre d\'événements',
                        data: priceData,
                        fill: true,
                        backgroundColor: 'rgba(225, 225, 255, 0.5)',
                        borderColor: chartColors.lavenderBorder,
                        tension: 0.4,
                        pointBackgroundColor: chartColors.pink,
                        pointBorderColor: chartColors.pinkBorder,
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Distribution des prix',
                            color: '#663399',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(102, 51, 153, 0.1)'
                            },
                            ticks: {
                                color: '#663399',
                                font: {
                                    weight: '500'
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(102, 51, 153, 0.1)'
                            },
                            ticks: {
                                color: '#663399',
                                font: {
                                    weight: '500'
                                }
                            }
                        }
                    }
                }
            });
        }

        function updateSeatStatusChart(stats) {
            const total = stats.total || 0;
            const reserved = stats.reserved || 0;
            const free = total - reserved;

            seatStatusChart.data.datasets[0].data = [free, reserved];
            seatStatusChart.options.plugins.title.text = `Statut des places${total > 0 ? ' (Événement sélectionné)' : ' (Sélectionnez un événement)'}`;
            seatStatusChart.update();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Modal pour la description -->
    <div class="modal" id="descriptionModal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Description complète</h3>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        // Gestion de la modale de description
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('descriptionModal');
            const modalContent = document.getElementById('modalContent');
            const closeBtn = modal.querySelector('.close');

            // Ajouter des écouteurs de clic sur toutes les cellules de description
            document.querySelectorAll('table td:nth-child(4)').forEach(cell => {
                cell.addEventListener('click', function() {
                    modalContent.textContent = this.textContent;
                    modal.classList.add('active');
                });
            });

            // Fermer la modale
            closeBtn.addEventListener('click', function() {
                modal.classList.remove('active');
            });

            // Fermer la modale en cliquant en dehors
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });
    </script>

    <script>
        /*document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la navigation*/
        //const navLinks = document.querySelectorAll('.nav-link');
        /*const sections = {
        utilisateurs: document.getElementById('section-utilisateurs'),
        activites: document.getElementById('section-activites'),
        evenements: document.getElementById('section-evenements'),
        produits: document.getElementById('section-produits'),
        transports: document.getElementById('section-transports'),
        sponsors: document.getElementById('section-sponsors')
    };

    // Fonction pour afficher une section
    function showSection(sectionName) {
        // Cacher toutes les sections
        Object.values(sections).forEach(section => {
            if (section) section.style.display = 'none';
        });
        
        // Afficher la section sélectionnée
        const selectedSection = sections[sectionName];
        if (selectedSection) selectedSection.style.display = 'block';
    }

    // Gestion des clics sur les liens
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Mise à jour des classes active
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Afficher la section correspondante
            const sectionName = this.getAttribute('data-section');
            showSection(sectionName);
        });
    });

    // Afficher la section événements par défaut
    showSection('evenements');
    document.querySelector('[data-section="evenements"]').classList.add('active');

    // Gestion du menu utilisateur
    const userAvatar = document.querySelector('.user-avatar');
    const userInfo = document.querySelector('.user-info');
    
    userAvatar.addEventListener('click', function() {
        userInfo.classList.toggle('active');
    });

    // Fermer le menu utilisateur quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.user-profile')) {
            userInfo.classList.remove('active');
        }
    });

    // Gestion du bouton de déconnexion
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            window.location.href = 'logout.php';
        });
    }
});*/

        function showSeatSectionForEvent(eventId) {
            // Activer la section chaises dans le sidebar
            const menuItems = document.querySelectorAll('.menu-list .menu-item');
            menuItems.forEach(item => item.classList.remove('active'));
            document.querySelector('.menu-item.section-chaises').classList.add('active');

            // Afficher la section chaises et masquer les autres
            document.getElementById('section-evenements').style.display = 'none';
            document.getElementById('section-statistiques').style.display = 'none';
            document.getElementById('section-chaises').style.display = 'block';

            // Sélectionner l'événement dans le filtre
            const eventFilter = document.getElementById('eventFilter');
            if (eventFilter) {
                eventFilter.value = eventId;
                // Charger les chaises pour cet événement
                loadSeatsForEvent(eventId);
            } else {
                console.error('Event filter element not found');
            }

            // Faire défiler vers la section chaises
            document.getElementById('section-chaises').scrollIntoView({
                behavior: 'smooth'
            });
        }
    </script>
</body>

</html>