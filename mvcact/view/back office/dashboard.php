<?php
session_start();
require_once __DIR__ . '/../../controller/ActivityController.php';
require_once __DIR__ . '/../../controller/EnterpriseController.php';

// Fonction utilitaire pour l'affichage d'image (Cloudinary ou locale avec fallback)
if (!function_exists('getImagePath')) {
  function getImagePath($image)
  {
    if (preg_match('/^https?:\/\//', $image)) {
      return $image; // URL Cloudinary
    } elseif (file_exists(__DIR__ . '/../front office/' . $image)) {
      return '../front office/' . $image; // Chemin local existant
    } else {
      return '../front office/images/default.jpg'; // Image par défaut si rien n'existe
    }
  }
}

// Activer le débogage pour les logs
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error_log');
error_reporting(E_ALL);

// Désactiver le cache pour forcer le rafraîchissement des données
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

$activityController = new ActivityController();
$enterpriseController = new EnterpriseController();

// Gérer les actions via l'URL
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;
$category = $_GET['category'] ?? null;

// Correction AJAX calendrier : réponse JSON pure
if ($action === 'getActivitiesBySelectedDate') {
  $activityController->getActivitiesBySelectedDate();
  exit;
}

$data = [];
switch ($action) {
  case 'add':
    $data = $activityController->add();
    break;
  case 'edit':
    if ($id) {
      $data = $activityController->edit($id);
    } else {
      header("Location: dashboard.php");
      exit;
    }
    break;
  case 'delete':
    if ($id) {
      $activityController->delete($id);
    } else {
      header("Location: dashboard.php");
      exit;
    }
    break;
  case 'notifications':
    $data = $activityController->notifications();
    break;
  case 'calendar':
    $data = $activityController->calendar();
    break;
  case 'statistics':
    $data = $activityController->statistics();
    break;

  case 'history':
    $data = $activityController->history();
    break;
  case 'settings':
    $data = $activityController->settings();
    break;
  case 'logout':
    $activityController->logout();
    break;
  case 'enterprise':
    $data = $enterpriseController->index();
    break;
  case 'add_enterprise':
    $data = $enterpriseController->add();
    break;
  case 'edit_enterprise':
    if ($id) {
      $data = $enterpriseController->edit($id);
    } else {
      header("Location: dashboard.php?action=enterprise");
      exit;
    }
    break;
  case 'delete_enterprise':
    if ($id) {
      $enterpriseController->delete($id);
    } else {
      header("Location: dashboard.php?action=enterprise");
      exit;
    }
    break;
  case 'reservations':
    $data = $activityController->reservations();
    break;
  case 'reviews':
    $data = $activityController->reviews();
    break;
  default:
    $data = $activityController->index();
    break;
}

$section = $data['section'] ?? 'control_data';

// Récupération universelle des notifications importantes
$pendingCount = 0;
$reservationsList = [];
// Récupérer les avis en attente
$reviewsData = $activityController->reviews();
if (isset($reviewsData['pendingCount'])) {
  $pendingCount = $reviewsData['pendingCount'];
}
// Récupérer les réservations
$reservationsData = $activityController->reservations();
if (isset($reservationsData['reservations'])) {
  $reservationsList = $reservationsData['reservations'];
}
// ... existing code ...
// Après le PHP d'initialisation, afficher le toast global
?>
<!-- Notification toast pour avis/réservations en attente, affiché partout -->
<?php
$toastHtml = '';
if ($pendingCount > 0) {
  $toastHtml .= '<div id="toast-notif" class="toast-notif">';
  $toastHtml .= '<span>💬 Vous avez <b>' . $pendingCount . '</b> avis en attente d\'approbation.</span>';
  $toastHtml .= '<button onclick="window.location.href=\'dashboard.php?action=reviews&filter=pending\'">Gérer les avis</button>';
  $toastHtml .= '<span class="toast-close" onclick="document.getElementById(\'toast-notif\').style.display=\'none\'">&times;</span>';
  $toastHtml .= '</div>';
}
// Suppression du toast des réservations en attente
// if ($pendingRes > 0) {
//   $toastHtml .= '<div id="toast-notif-res" class="toast-notif">';
//   $toastHtml .= '<span>🎫 Vous avez <b>' . $pendingRes . '</b> réservation(s) en attente de confirmation.</span>';
//   $toastHtml .= '<button onclick="window.location.href=\'dashboard.php?action=reservations&filter=pending\'">Gérer les réservations</button>';
//   $toastHtml .= '<span class="toast-close" onclick="document.getElementById(\'toast-notif-res\').style.display=\'none\'">&times;</span>';
//   $toastHtml .= '</div>';
// }
echo $toastHtml;

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
<style>
  .toast-notif {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #fff6fa;
    color: #8B5CF6;
    border: 1px solid #F7B2D9;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(180, 150, 255, 0.13);
    padding: 18px 28px 18px 18px;
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 18px;
    font-size: 1.1em;
    animation: toastIn 0.7s;
  }

  .toast-notif button {
    background: #8B5CF6;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 7px 18px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
  }

  .toast-notif button:hover {
    background: #6941FF;
  }

  .toast-close {
    font-size: 1.5em;
    cursor: pointer;
    margin-left: 10px;
  }

  @keyframes toastIn {
    from {
      opacity: 0;
      transform: translateY(40px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* --- NAVBAR DASHBOARD STYLE --- */
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
          padding-right: 40px;  
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
          margin-left: 25px;
          padding-left: 25px;
          border-left: 1px solid rgba(151, 104, 209, 0.2);
        }

  .profile-container-navbar {
    position: relative;
    cursor: pointer;
    margin-right: 8px;
    margin-left: 0;
    display: flex;
    align-items: center;
  }

  .profile-container-navbar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.10);
  }

  .profile-dropdown {
    position: absolute;
    top: 50px;
    right: 0;
    width: 240px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 18px rgba(139, 92, 246, 0.18);
    padding: 18px 18px 18px 18px;
    display: none;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
  }

  .profile-container-navbar.active .profile-dropdown {
    display: block;
  }

  .admin-mail {
    color: #666;
    font-size: 15px;
    margin-bottom: 12px;
    padding-bottom: 0;
    text-align: center;
  }

  .logout-btn {
    width: 100%;
    padding: 10px;
    background: linear-gradient(90deg, #e859c0 0%, #bfa2f7 100%);
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 10px;
  }

  .logout-btn:hover {
    background: #d048ac;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @media (max-width: 900px) {


 

    .profile-container-navbar {
      margin-left: 12px;
      margin-right: 4px;
    }
  }

    .sidebar .menu-item {
      display: flex;
      align-items: center;
      padding: 10px 15px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .sidebar .menu-item:hover {
      background: rgba(255, 77, 77, 0.1);
    }

  .sidebar .menu-item.active > a {
    color: #663399 !important;
        transform: translateX(5px);
  }


  .sidebar .menu-item a {
    color: #333333 ;
    text-decoration: none;

  }

menu-item:hover, .menu-item.active {
    color: #663399;
    transform: translateX(5px);
} 


  .sidebar .menu-item {
    margin-bottom: 0px;
  }

  /* Style spécifique pour le logo */
  .logo {
    width: 180px !important;
    height: 180px !important;
    cursor: pointer !important;
    display: block !important;
    margin: 0 auto 0px !important;
    filter: drop-shadow(0 2px 8px rgba(139, 92, 246, 0.2)) !important;
    transition: transform 0.3s ease !important;
  }

  .logo:hover {
    transform: scale(1.05) !important;
  }

  body {
    background:#F4EFFF !important;
    margin: 0;
    font-family: 'Inter', sans-serif;
    display: flex;
  }

  .sidebar {
    background: #F9F7FC !important;
    width: 240px;
    padding: 30px 20px;
    height: 100vh;
    position: fixed;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.05);
  }

  .dashboard {
    margin-left: 280px;
    padding: 30px;
    width: calc(100% - 280px);
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .activity-card,
  .add-activity-form,
  .notifications,
  .history,
  .activity,
  .calendar,
  .stats-section,
  .chart-wrapper,
  .notification-item,
  .upcoming-activities,
  .upcoming-item,
  .history-item,
  .activities-for-date,
  .stat-box,
  .reviews-section,
  .enterprise-section,
  .category-content,
  .add-card {
    background: #F9F7FC !important;
    background-color: #F9F7FC !important;
    border-radius: 18px;
    box-shadow: 0 2px 12px rgba(180, 150, 255, 0.07);
  }

  .activity-image {
    background-color: #FDF6FA !important;
  }

  .calendar {
    background: #FDF6FA !important;
    border-radius: 18px;
    box-shadow: 0 2px 12px rgba(180, 150, 255, 0.07);
    padding: 24px;
  }

  .calendar-grid {
    gap: 10px;
  }

  .calendar-grid div {
    background-color: #FFF !important;
    border-radius: 12px;
    color: #7B4FE0;
    box-shadow: 0 1px 4px rgba(180, 150, 255, 0.06);
    transition: all 0.2s;
    font-weight: 500;
  }

  .calendar-grid div:nth-child(-n+7) {
    background-color: #FDF6FA !important;
    color: #8B5CF6;
    font-weight: 700;
  }

  .calendar-grid div.selected {
    background: #BFA2F7 !important;
    color: #fff !important;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(105, 81, 255, 0.10);
    transform: scale(1.07);
  }

  .calendar-grid div:hover:not(.selected):not(:empty) {
    background-color: #F8D9F0 !important;
    color: #7B4FE0;
    box-shadow: 0 2px 8px rgba(180, 150, 255, 0.10);
    cursor: pointer;
    transform: scale(1.03);
  }

  .add-button,
  .edit-button {
    background: #BFA2F7 !important;
    color: #fff !important;
    border: none;
  }

  .delete-button {
    background: #F7B2D9 !important;
    color: #fff !important;
    border: none;
  }

  h1,
  h2,
  h3,
  h4,
  h5,
  h6,
  .stats-title,
  .stat-content h4,
  .activity-content h3 {
    color: #663399 !important;
  }

  /* Garder les autres styles existants */
  .add-activity-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
  }

  .add-activity-section h3 {
    font-size: 24px;
    color: #333;
    margin: 0;
  }

  .add-button {
    background-color: #6941FF;
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.3s ease;
  }

  .add-button:hover {
    background-color: #5635CC;
  }

  .activities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
  }

  .activity-card {
    background-color: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .activity-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  }

  .activity-image {
    width: 100%;
    height: 150px;
    overflow: hidden;
    background-color: #db92b0;
  }

  .activity-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #666;
    font-size: 14px;
    text-align: center;
    padding: 10px;
  }

  .activity-content {
    padding: 15px;
  }

  .activity-content h3 {
    font-size: 18px;
    margin: 0 0 10px;
    color: #333;
  }

  .activity-content p {
    font-size: 14px;
    color: #666;
    margin: 0 0 15px;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .activity-buttons {
    display: flex;
    gap: 10px;
  }

  .edit-button,
  .delete-button {
    flex: 1;
    text-align: center;
    padding: 8px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: background-color 0.3s ease;
  }

  .edit-button {
    background-color: #6941FF;
    color: white;
  }

  .edit-button:hover {
    background-color: #5635CC;
  }

  .delete-button {
    background-color: #FF5A5A;
    color: white;
  }

  .delete-button:hover {
    background-color: #E04E4E;
  }

  .no-activities {
    text-align: center;
    color: #666;
    font-size: 16px;
    margin-top: 20px;
  }

  /* Styles pour la section Statistiques Générales */
  .charts-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 20px;
    width: 100%;
    min-height: 300px;
  }

  .chart-wrapper {
    flex: 1;
    min-width: 300px;
    height: 400px;
    background-color: #fff;
    border-radius: 10px;
    padding: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }

  #activityChart,
  #categoryChart {
    width: 100% !important;
    height: 350px !important;
  }

  /* Styles pour le calendrier */
  .calendar {
    padding: 20px;
  }

  .calendar h3 {
    margin-bottom: 20px;
    color: #333;
    font-size: 24px;
  }

  .calendar h5 {
    margin: 10px 0;
    color: #666;
    font-size: 16px;
  }

  .calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    margin-bottom: 30px;
  }

  .calendar-grid div {
    padding: 15px 10px;
    text-align: center;
    background-color: rgb(243, 188, 211);
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.2s ease;
  }

  .calendar-grid div:nth-child(-n+7) {
    font-weight: 600;
    background-color: rgb(248, 212, 227);
  }

  .calendar-grid div:nth-child(n+8):hover:not(:empty) {
    background-color: #e9b1d4;
    cursor: pointer;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(231, 10, 131, 0.2);
  }

  .calendar-grid div.selected {
    background-color: #e70a83;
    color: white;
    font-weight: 600;
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(231, 10, 131, 0.2);
  }

  /* Styles pour les activités par date */
  .activities-for-date {
    background-color: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    min-height: 300px;
  }

  .activities-for-date h3 {
    margin-bottom: 20px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    color: #333;
  }

  #selected-date-display {
    color: #e70a83;
    font-weight: 600;
  }

  .activities-placeholder {
    text-align: center;
    padding: 50px 0;
    color: #999;
    font-style: italic;
  }

  .date-activity-item {
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 10px;
    background-color: #f8f9fa;
    border-left: 4px solid #e70a83;
    transition: all 0.2s ease;
  }

  .date-enterprise-item {
    border-left-color: #6941FF;
  }

  .date-activity-item:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }

  .date-activity-item h4 {
    margin: 0 0 5px;
    color: #333;
    font-size: 18px;
  }

  .date-activity-item p {
    margin: 0;
    color: #666;
    font-size: 14px;
  }

  .activity-type-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 15px;
    font-size: 12px;
    margin-top: 8px;
    font-weight: 500;
  }

  .badge-regular {
    background-color: #fce6f2;
    color: #e70a83;
  }

  .badge-enterprise {
    background-color: #ece8ff;
    color: #6941FF;
  }

  .no-activities-message {
    text-align: center;
    padding: 30px;
    color: #666;
    font-size: 16px;
  }

  /* Styles pour les statistiques des avis */
  .reviews-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 20px;
    margin-bottom: 20px;
  }

  .stat-box {
    background-color: white;
    border-radius: 8px;
    padding: 15px;
    min-width: 120px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    text-align: center;
    flex-grow: 1;
  }

  .stat-number {
    font-size: 24px;
    font-weight: 600;
    color: #6941FF;
    margin-bottom: 5px;
  }

  .stat-label {
    font-size: 14px;
    color: #666;
  }

  .panel-header {
    margin-bottom: 20px;
  }

  .panel-header h2 {
    margin-bottom: 10px;
  }

  /* Styles pour l'export Excel */
  .export-section {
    background-color: #FDF6FA;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(180, 150, 255, 0.07);
  }

  .export-section h3 {
    color: #e70a83;
    margin-top: 0;
    font-size: 18px;
    margin-bottom: 15px;
    font-weight: 600;
  }

  .export-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
  }

  .export-btn {
    background: linear-gradient(135deg, #e70a83 0%, #ff5e5e 100%);
    color: white;
    border: none;
    border-radius: 30px;
    padding: 12px 20px;
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 8px rgba(231, 10, 131, 0.15);
    transition: all 0.3s;
  }

  .export-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(231, 10, 131, 0.25);
  }

  .export-btn .icon {
    font-size: 18px;
  }

  .stat-card {
    flex: 1 1 0;
    background: linear-gradient(120deg, #A084E8 0%, #D291BC 100%);
    border-radius: 18px;
    box-shadow: 0 4px 18px rgba(139, 92, 246, 0.10);
    padding: 16px 12px 10px 12px;
    display: flex;
    flex-direction: row;
    align-items: center;
    min-width: 150px;
    min-height: 70px;
    color: #fff;
    position: relative;
    overflow: hidden;
  }

  .stat-icon {
    font-size: 1.5em;
    margin-bottom: 0;
    margin-right: 12px;
    filter: drop-shadow(0 2px 8px rgba(139, 92, 246, 0.13));
  }

  .stat-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }

  .stat-content h4 {
    color: #fff;
    font-size: 1em;
    font-weight: 700;
    margin-bottom: 2px;
    opacity: 0.97;
  }

  .stat-content .count {
    font-size: 1.3em;
    font-weight: 900;
    margin-top: 0;
    letter-spacing: 1px;
    color: #FDF6FA;
  }
</style>
<script>
  setTimeout(() => {
    const toast = document.getElementById('toast-notif');
    if (toast) toast.style.display = 'none';
    const toastRes = document.getElementById('toast-notif-res');
    if (toastRes) toastRes.style.display = 'none';
  }, 10000);

  // Gestion du menu profil (avatar)
  document.addEventListener('DOMContentLoaded', function() {
    const profile = document.getElementById('profileNavbar');
    if (profile) {
      profile.addEventListener('click', function(e) {
        e.stopPropagation();
        profile.classList.toggle('active');
      });
      document.addEventListener('click', function() {
        profile.classList.remove('active');
      });
    }
  });
</script>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Activités</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="dash.css">
  <link rel="stylesheet" href="sidebar-fix.css">
  <link rel="stylesheet" href="stats-fix.css">
  <script src="js/formValidation.js"></script>
  <script src="../front office/js/chart.min.js"></script>
</head>

<body>



  <div class="sidebar">
    <div>
      <img src="../back office/logo.png" alt="Logo" class="logo">
      <div class="menu-items-top">
        <div class="menu-item<?php if ($section === 'notifications') echo ' active'; ?>">
          <a href="dashboard.php?action=notifications">🔔 Notifications</a>
        </div>
        <div class="menu-item<?php if ($section === 'control_data') echo ' active'; ?>">
          <a href="dashboard.php?action=control_data">📋 Contrôle de Données</a>
        </div>
        <div class="menu-item<?php if ($section === 'enterprise') echo ' active'; ?>">
          <a href="dashboard.php?action=enterprise">🏢 Entreprise</a>
        </div>
        <div class="menu-item<?php if ($section === 'reservations') echo ' active'; ?>">
          <a href="dashboard.php?action=reservations">🎫 Réservations</a>
        </div>
        <div class="menu-item<?php if ($section === 'calendar') echo ' active'; ?>">
          <a href="dashboard.php?action=calendar">📅 Calendrier</a>
        </div>
        <div class="menu-item<?php if ($section === 'statistics') echo ' active'; ?>">
          <a href="dashboard.php?action=statistics">📊 Statistiques Générales</a>
        </div>
        <div class="menu-item<?php if ($section === 'reviews') echo ' active'; ?>">
          <a href="dashboard.php?action=reviews">💬 Avis clients</a>
        </div>
      </div>
    </div>
    <div class="menu-items-bottom">

      <div class="menu-item">
        <a href="dashboard.php?action=logout">🚪 Déconnexion</a>
      </div>
    </div>
  </div>

  <div class="dashboard">
    <div class="header">
      <div class="navbar-backoffice-wrapper">
        <nav class="navbar-backoffice">
        <ul>
            <li><a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link">Utilisateurs</a></li>
            <li><a href="/Projet%20Web/mvcact/view/back%20office/dashboard.php" class="nav-link active">Activités</a></li>
            <li><a href="/Projet%20Web/mvcEvent/View/BackOffice/dashboard.php" class="nav-link">Événements</a></li>
            <li><a href="/Projet%20Web/mvcProduit/view/back%20office/indeex.php" class="nav-link">Produits</a></li>
            <li><a href="/Projet%20Web/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link">Transports</a></li>
            <li><a href="/Projet%20Web/mvcSponsor/crud/view/back/back.php" class="nav-link">Sponsors</a></li>
            <li class="profile-container">

        <div class="user-profile">
            <?php if (isset($_SESSION['user'])): ?>
                <?php
                $photoPath = $_SESSION['user']['profile_picture'] ?? '';
                $fullName = $_SESSION['user']['full_name'] ?? 'Utilisateur';
                // Adjust path for back.php's directory
                $photoRelativePath = '../../../mvcUtilisateur/View/FrontOffice/' . $photoPath;
                $absolutePath = realpath(__DIR__ . '/' . $photoRelativePath);
                $showPhoto = !empty($photoPath) && $absolutePath && file_exists($absolutePath);
                ?>
                
                <?php if ($showPhoto): ?>
                    <img src="/Projet%20Web/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
                <?php else: ?>
                    <div class="profile-circle" style="background-color: <?= stringToColor($fullName) ?>;" onclick="toggleDropdown()">
                        <?= strtoupper(substr($fullName, 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="/Projet%20Web/mvcUtilisateur/View/FrontOffice/profile.php">👤 Mon Profil</a>
                    <a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/login/logout.php">🚪 Déconnexion</a>
                </div>
            <?php endif; ?>
        </div>

              <script>
        // Fonction pour ouvrir/fermer le menu
        function toggleDropdown() {
          const menu = document.getElementById('dropdownMenu');
          if (menu.style.display === 'block') {
            menu.style.display = 'none';
          } else {
            menu.style.display = 'block';
          }
        }

        // ✅ Fermer le menu si on clique en dehors
        document.addEventListener('click', function(event) {
          const menu = document.getElementById('dropdownMenu');
          const profile = document.querySelector('.user-profile');
          if (!profile.contains(event.target)) {
            menu.style.display = 'none';
          }
        });
      </script>
      
              <style>
                .user-profile {
                  position: relative;
                  display: inline-block;
                }


                
                .dropdown-menu {
                  position: absolute;
                  top: 45px;
                  right: 0;
                  background-color: white;
                  border: 1px solid #ddd;
                  padding: 10px;
                  display: none;
                  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                  z-index: 100;
                }

                .user-profile:hover .dropdown-menu {
                  display: block;
                }


                .profile-photo {
                  width: 35px;
                  height: 35px;
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

                .dropdown-menu {
                  position: absolute;
                  top: 45px;
                  right: 0;
                  background-color: white;
                  border: 1px solid #ddd;
                  padding: 10px;
                  display: none;
                  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                  z-index: 100;
                }

                .user-profile:hover .dropdown-menu {
                  display: block;
                }

                .search-bar {
                  display: flex;
                  align-items: center;
                  background: rgba(255, 255, 255, 0.1);
                  border: 2px solid var(--spanish-gray);
                  border-radius: 50px;
                  overflow: hidden;
                  margin: 20px auto;
                  max-width: 800px;
                  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                  backdrop-filter: blur(5px);
                  height: 40px;
                  margin-top: 400px !important;
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
        .nav-link[href*="act"] {
          color: #F687B3;
        }


        .nav-link[href*="Utilisateur"] {
          color: #9F7AEA;
        }

        .nav-link[href*="Event"] {
          color: #9F7AEA;
        }

        .nav-link[href*="Produit"] {
          color: #9768D1;
        }

        .nav-link[href*="Covoiturage"] {
          color: #B794F4;
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


              </style>



            </li>
          </ul>





        </nav>


      </div>




    </div>




    <?php if ($section === 'notifications'): ?>
      <div class="notifications">
        <h3>🔔 Notifications</h3>
        <div class="notification-list">
          <?php
          $hasNotif = false;
          $toastHtml = '';
          if (($data['pendingCount'] ?? 0) > 0) {
            $hasNotif = true;
            echo '<div class="notification-item">';
            echo '💬 <b>' . $data['pendingCount'] . '</b> avis client(s) en attente d\'approbation.<br>';
            echo '<button class="notif-action-btn" onclick="window.location.href=\'dashboard.php?action=reviews&filter=pending\'">Gérer les avis</button>';
            echo '</div>';
            $toastHtml .= '<div id="toast-notif" class="toast-notif">';
            $toastHtml .= '<span>💬 Vous avez <b>' . $data['pendingCount'] . '</b> avis en attente d\'approbation.</span>';
            $toastHtml .= '<button onclick="window.location.href=\'dashboard.php?action=reviews&filter=pending\'">Gérer les avis</button>';
            $toastHtml .= '<span class="toast-close" onclick="document.getElementById(\'toast-notif\').style.display=\'none\'">&times;</span>';
            $toastHtml .= '</div>';
          }
          if (($data['reservations'] ?? false) && is_array($data['reservations'])) {
            $pendingRes = 0;
            foreach ($data['reservations'] as $res) {
              if (($res['payment_status'] ?? '') === 'pending') $pendingRes++;
            }
            if ($pendingRes > 0) {
              $hasNotif = true;
              echo '<div class="notification-item">';
              echo '🎫 <b>' . $pendingRes . '</b> réservation(s) en attente de confirmation.<br>';
              echo '<button class="notif-action-btn" onclick="window.location.href=\'dashboard.php?action=reservations&filter=pending\'">Gérer les réservations</button>';
              echo '</div>';
              $toastHtml .= '<div id="toast-notif-res" class="toast-notif">';
              $toastHtml .= '<span>🎫 Vous avez <b>' . $pendingRes . '</b> réservation(s) en attente de confirmation.</span>';
              $toastHtml .= '<button onclick="window.location.href=\'dashboard.php?action=reservations&filter=pending\'">Gérer les réservations</button>';
              $toastHtml .= '<span class="toast-close" onclick="document.getElementById(\'toast-notif-res\').style.display=\'none\'">&times;</span>';
              $toastHtml .= '</div>';
            }
          }
          if (!$hasNotif) {
            echo '<div class="notification-item notification-empty">';
            echo '<span style="font-size:1.3em;">🎉 Aucune notification urgente pour le moment.</span>';
            echo '<ul style="margin-top:10px; color:#8B5CF6;">';
            echo '<li><a href="dashboard.php?action=reservations" style="color:#8B5CF6;text-decoration:underline;">Consulter les réservations récentes</a></li>';
            echo '<li><a href="dashboard.php?action=reviews" style="color:#8B5CF6;text-decoration:underline;">Voir les derniers avis clients</a></li>';
            echo '<li><a href="dashboard.php?action=add" style="color:#8B5CF6;text-decoration:underline;">Ajouter une nouvelle activité</a></li>';
            echo '<li><a href="dashboard.php?action=statistics" style="color:#8B5CF6;text-decoration:underline;">Analyser les statistiques</a></li>';
            echo '</ul>';
            echo '</div>';
          }
          ?>
        </div>
      </div>
      <style>
        .notif-action-btn {
          background: #8B5CF6;
          color: #fff;
          border: none;
          border-radius: 8px;
          padding: 6px 16px;
          font-weight: 600;
          margin-top: 8px;
          cursor: pointer;
          transition: background 0.2s;
        }

        .notif-action-btn:hover {
          background: #6941FF;
        }

        .notification-item {
          background: #fff6fa;
          border: 1px solid #F7B2D9;
          border-radius: 10px;
          padding: 14px 18px;
          margin-bottom: 14px;
          color: #8B5CF6;
          font-size: 1.08em;
        }

        .toast-notif {
          position: fixed;
          bottom: 30px;
          right: 30px;
          background: #fff6fa;
          color: #8B5CF6;
          border: 1px solid #F7B2D9;
          border-radius: 12px;
          box-shadow: 0 4px 16px rgba(180, 150, 255, 0.13);
          padding: 18px 28px 18px 18px;
          z-index: 9999;
          display: flex;
          align-items: center;
          gap: 18px;
          font-size: 1.1em;
          animation: toastIn 0.7s;
        }

        .toast-notif button {
          background: #8B5CF6;
          color: #fff;
          border: none;
          border-radius: 8px;
          padding: 7px 18px;
          font-weight: 600;
          cursor: pointer;
          transition: background 0.2s;
        }

        .toast-notif button:hover {
          background: #6941FF;
        }

        .toast-close {
          font-size: 1.5em;
          cursor: pointer;
          margin-left: 10px;
        }

        @keyframes toastIn {
          from {
            opacity: 0;
            transform: translateY(40px);
          }

          to {
            opacity: 1;
            transform: translateY(0);
          }
        }
      </style>
      <?php if (!empty($toastHtml)): ?>
        <?php echo $toastHtml; ?>
        <script>
          setTimeout(() => {
            const toast = document.getElementById('toast-notif');
            if (toast) toast.style.display = 'none';
            const toastRes = document.getElementById('toast-notif-res');
            if (toastRes) toastRes.style.display = 'none';
          }, 10000);
        </script>
      <?php endif; ?>
      <!-- Suppression de l'historique et des activités à venir dans notifications -->

    <?php elseif ($section === 'control_data'): ?>
      <div class="control-data-section">
        <!-- Section Ajouter une activité -->
        <div class="add-activity-section">
          <h3>
            <form method="GET" action="dashboard.php" class="search-form">
              <input type="text" name="search" class="search" placeholder="Rechercher une activité..." value="<?php echo htmlspecialchars($data['searchTerm'] ?? ''); ?>">
              <button type="submit" style="display: none;">Rechercher</button>
            </form>
          </h3>
          <a href="dashboard.php?action=add" class="add-button">Ajouter une nouvelle activité</a>
        </div>

        <!-- Afficher un message si une recherche est active -->
        <?php if (!empty($data['searchTerm'])): ?>
          <p style="color: #6951FF; margin-bottom: 20px;">
            Résultats de la recherche pour "<?php echo htmlspecialchars($data['searchTerm']); ?>" :
            <?php echo count($data['activities']); ?> activité(s) trouvée(s).
            <a href="dashboard.php" style="color: #6951FF; text-decoration: underline;">Effacer la recherche</a>
          </p>
        <?php endif; ?>

        <!-- Débogage : Vérifier les données récupérées -->
        <?php
        error_log("Données dans control_data : " . print_r($data['activities'], true));
        ?>

        <!-- Grille des cartes d'activités -->
        <div class="activities-grid">
          <?php if (isset($data['activities']) && is_array($data['activities']) && !empty($data['activities'])): ?>
            <?php foreach ($data['activities'] as $activity): ?>
              <div class="activity-card">
                <div class="activity-image">
                  <?php if (!empty($activity['image'])): ?>
                    <?php
                    $image = $activity['image'];
                    // Si c'est une URL (Cloudinary), on l'utilise directement
                    if (filter_var($image, FILTER_VALIDATE_URL)) {
                      $imagePath = $image;
                    } else {
                      $imagePath = "../front office/" . htmlspecialchars($image);
                    }
                    error_log("Chemin de l'image pour l'activité {$activity['id']} : " . $imagePath);
                    ?>
                    <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
                  <?php else: ?>
                    <div class="no-image">Pas d'image disponible</div>
                  <?php endif; ?>
                </div>
                <div class="activity-content">
                  <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                  <p><?php echo htmlspecialchars($activity['description']); ?></p>
                  <div class="activity-buttons">
                    <a href="dashboard.php?action=edit&id=<?php echo htmlspecialchars($activity['id']); ?>" class="edit-button">Modifier</a>
                    <a href="process_activity.php?operation=delete&id=<?php echo htmlspecialchars($activity['id']); ?>" class="delete-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette activité ?')">Supprimer</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="no-activities">
              <?php echo !empty($data['searchTerm']) ? 'Aucune activité trouvée pour cette recherche.' : 'Aucune activité trouvée. Essayez d\'ajouter une nouvelle activité.'; ?>
            </p>
          <?php endif; ?>
        </div>
      </div>

    <?php elseif ($section === 'add_activity'): ?>
      <div class="add-activity-form">
        <h3>📋 Formulaire d'Ajout d'Activité</h3>
        <?php if (isset($data['error'])): ?>
          <p style="color: red;"><?php echo htmlspecialchars($data['error']); ?></p>
        <?php endif; ?>
        <form method="POST" action="process_activity.php" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="operation" value="add">
          <div class="form-group">
            <label for="activityName">Nom de l'activité</label>
            <input type="text" id="activityName" name="name" placeholder="Ex: Yoga du matin" required>
          </div>
          <div class="form-group">
            <label for="activityDescription">Description</label>
            <textarea id="activityDescription" name="description" placeholder="Décrivez l'activité..." rows="5" required></textarea>
          </div>
          <div class="form-group">
            <label for="activityPrice">Prix (en TND)</label>
            <input type="number" id="activityPrice" name="price" placeholder="Ex: 20" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label for="activityLocation">Lieu</label>
            <select id="activityLocation" name="location" required>
              <option value="" disabled selected>Choisir une région</option>
              <option value="Tunis">Tunis</option>
              <option value="Ariana">Ariana</option>
              <option value="Ben Arous">Ben Arous</option>
              <option value="Manouba">Manouba</option>
              <option value="Nabeul">Nabeul</option>
              <option value="Zaghouan">Zaghouan</option>
              <option value="Bizerte">Bizerte</option>
              <option value="Béja">Béja</option>
              <option value="Jendouba">Jendouba</option>
              <option value="Kef">Kef</option>
              <option value="Siliana">Siliana</option>
              <option value="Sousse">Sousse</option>
              <option value="Monastir">Monastir</option>
              <option value="Mahdia">Mahdia</option>
              <option value="Sfax">Sfax</option>
              <option value="Kairouan">Kairouan</option>
              <option value="Kasserine">Kasserine</option>
              <option value="Sidi Bouzid">Sidi Bouzid</option>
              <option value="Gabès">Gabès</option>
              <option value="Medenine">Medenine</option>
              <option value="Tataouine">Tataouine</option>
              <option value="Gafsa">Gafsa</option>
              <option value="Tozeur">Tozeur</option>
              <option value="Kebili">Kebili</option>
            </select>
          </div>
          <div class="form-group">
            <label for="activityDate">Date et Heure</label>
            <input type="datetime-local" id="activityDate" name="date" required>
          </div>
          <div class="form-group">
            <label for="activityCategory">Catégorie</label>
            <select id="activityCategory" name="category" required>
              <option value="" disabled>Choisir une catégorie</option>
              <option value="Ateliers">Ateliers</option>
              <option value="bien-etre">Bien-être</option>
              <option value="Aérien">Aérien</option>
              <option value="Aquatique">Aquatique</option>
              <option value="Terestre">Terrestre</option>
              <option value="Insolite">Insolite</option>
              <option value="culture">Culture</option>
              <option value="Détente">Détente</option>
              <option value="sport">Sport</option>
              <option value="nature">Nature</option>
              <option value="aventure">Aventure</option>
              <option value="Famille">Famille</option>
              <option value="Extreme">Extrême</option>
            </select>
          </div>
          <div class="form-group">
            <label for="activityCapacity">Capacité maximale</label>
            <input type="number" id="activityCapacity" name="capacity" placeholder="Ex: 50" min="1" required>
          </div>
          <div class="form-group">
            <label for="imageFile">Image de l'activité</label>
            <div class="image-input-container">
              <input type="file" id="imageFile" name="image" accept="image/*" required>
            </div>
            <div id="imagePreview" style="margin-top: 10px; display: none;">
              <img id="previewImg" src="" alt="Aperçu de l'image" style="max-width: 100%; max-height: 200px;">
            </div>
          </div>
          <div class="form-buttons">
            <button type="submit" class="submit-button">Ajouter l'activité</button>
            <a href="dashboard.php" class="cancel-button">Annuler</a>
          </div>
        </form>
      </div>

    <?php elseif ($section === 'edit_activity'): ?>
      <div class="add-activity-form">
        <h3>📋 Formulaire de Modification</h3>
        <?php if (isset($data['error'])): ?>
          <p style="color: red;"><?php echo htmlspecialchars($data['error']); ?></p>
        <?php endif; ?>
        <?php if (isset($data['activity']) && is_array($data['activity'])): ?>
          <form method="POST" action="process_activity.php" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="operation" value="edit">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['activity']['id']); ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($data['activity']['image']); ?>">
            <div class="form-group">
              <label for="activityName">Nom de l'activité</label>
              <input type="text" id="activityName" name="name" value="<?php echo htmlspecialchars($data['activity']['name']); ?>" required>
            </div>
            <div class="form-group">
              <label for="activityDescription">Description</label>
              <textarea id="activityDescription" name="description" rows="5" required><?php echo htmlspecialchars($data['activity']['description']); ?></textarea>
            </div>
            <div class="form-group">
              <label for="activityPrice">Prix (en TND)</label>
              <input type="number" id="activityPrice" name="price" value="<?php echo htmlspecialchars($data['activity']['price']); ?>" step="0.01" min="0" required>
            </div>
            <div class="form-group">
              <label for="activityLocation">Lieu</label>
              <select id="activityLocation" name="location" required>
                <option value="" disabled selected>Choisir une région</option>
                <option value="Tunis">Tunis</option>
                <option value="Ariana">Ariana</option>
                <option value="Ben Arous">Ben Arous</option>
                <option value="Manouba">Manouba</option>
                <option value="Nabeul">Nabeul</option>
                <option value="Zaghouan">Zaghouan</option>
                <option value="Bizerte">Bizerte</option>
                <option value="Béja">Béja</option>
                <option value="Jendouba">Jendouba</option>
                <option value="Kef">Kef</option>
                <option value="Siliana">Siliana</option>
                <option value="Sousse">Sousse</option>
                <option value="Monastir">Monastir</option>
                <option value="Mahdia">Mahdia</option>
                <option value="Sfax">Sfax</option>
                <option value="Kairouan">Kairouan</option>
                <option value="Kasserine">Kasserine</option>
                <option value="Sidi Bouzid">Sidi Bouzid</option>
                <option value="Gabès">Gabès</option>
                <option value="Medenine">Medenine</option>
                <option value="Tataouine">Tataouine</option>
                <option value="Gafsa">Gafsa</option>
                <option value="Tozeur">Tozeur</option>
                <option value="Kebili">Kebili</option>
              </select>
            </div>
            <div class="form-group">
              <label for="activityDate">Date et Heure</label>
              <input type="datetime-local" id="activityDate" name="date" value="<?php echo date('Y-m-d\TH:i', strtotime($data['activity']['date'])); ?>" required>
            </div>
            <div class="form-group">
              <label for="activityCategory">Catégorie</label>
              <select id="activityCategory" name="category" required>
                <option value="" disabled>Choisir une catégorie</option>
                <option value="Ateliers" <?php if ($data['activity']['category'] === 'Ateliers') echo 'selected'; ?>>Ateliers</option>
                <option value="bien-etre" <?php if ($data['activity']['category'] === 'bien-etre') echo 'selected'; ?>>Bien-être</option>
                <option value="Aérien" <?php if ($data['activity']['category'] === 'Aérien') echo 'selected'; ?>>Aérien</option>
                <option value="Aquatique" <?php if ($data['activity']['category'] === 'Aquatique') echo 'selected'; ?>>Aquatique</option>
                <option value="Terestre" <?php if ($data['activity']['category'] === 'Terestre') echo 'selected'; ?>>Terrestre</option>
                <option value="Insolite" <?php if ($data['activity']['category'] === 'Insolite') echo 'selected'; ?>>Insolite</option>
                <option value="culture" <?php if ($data['activity']['category'] === 'culture') echo 'selected'; ?>>Culture</option>
                <option value="Détente" <?php if ($data['activity']['category'] === 'Détente') echo 'selected'; ?>>Détente</option>
                <option value="sport" <?php if ($data['activity']['category'] === 'sport') echo 'selected'; ?>>Sport</option>
                <option value="nature" <?php if ($data['activity']['category'] === 'nature') echo 'selected'; ?>>Nature</option>
                <option value="aventure" <?php if ($data['activity']['category'] === 'aventure') echo 'selected'; ?>>Aventure</option>
                <option value="Famille" <?php if ($data['activity']['category'] === 'Famille') echo 'selected'; ?>>Famille</option>
                <option value="Extreme" <?php if ($data['activity']['category'] === 'Extreme') echo 'selected'; ?>>Extrême</option>
              </select>
            </div>
            <div class="form-group">
              <label for="activityCapacity">Capacité maximale</label>
              <input type="number" id="activityCapacity" name="capacity" value="<?php echo htmlspecialchars($data['activity']['capacity']); ?>" min="1" required>
            </div>
            <div class="form-group">
              <label for="imageFile">Image de l'activité</label>
              <?php if (!empty($data['activity']['image'])): ?>
                <div>
                  <img src="<?php echo htmlspecialchars(getImagePath($data['activity']['image'])); ?>" alt="Image actuelle" style="max-width: 100%; max-height: 200px;">
                  <p>Image actuelle</p>
                </div>
              <?php endif; ?>
              <div class="image-input-container">
                <input type="file" id="imageFile" name="image" accept="image/*">
              </div>
              <div id="imagePreview" style="margin-top: 10px; display: none;">
                <img id="previewImg" src="" alt="Aperçu de l'image" style="max-width: 100%; max-height: 200px;">
              </div>
            </div>
            <div class="form-buttons">
              <button type="submit" class="submit-button">Enregistrer les modifications</button>
              <a href="dashboard.php" class="cancel-button">Annuler</a>
            </div>
          </form>
        <?php else: ?>
          <p style="color: red;">Activité non trouvée.</p>
        <?php endif; ?>
      </div>

    <?php elseif ($section === 'calendar'): ?>
      <div class="calendar">
        <h3>📅 Calendrier</h3>
        <div class="calendar-header">
          <button id="prevMonth" class="calendar-nav">&#8592;</button>
          <span id="calendar-month-label"></span>
          <button id="nextMonth" class="calendar-nav">&#8594;</button>
        </div>
        <div class="calendar-grid" id="calendar-grid"></div>
        <div id="selected-date-activities" class="activities-for-date">
          <h3>Activités pour le <span id="selected-date-display">--/--/----</span></h3>
          <div id="date-activities-container">
            <p class="activities-placeholder">Cliquez sur une date pour voir les activités</p>
          </div>
        </div>
      </div>
      <script>
        // Génération dynamique du calendrier
        const calendarGrid = document.getElementById('calendar-grid');
        const monthLabel = document.getElementById('calendar-month-label');
        const prevMonthBtn = document.getElementById('prevMonth');
        const nextMonthBtn = document.getElementById('nextMonth');
        let currentDate = new Date();
        let selectedDate = null;

        function renderCalendar(year, month) {
          calendarGrid.innerHTML = '';
          const daysOfWeek = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
          daysOfWeek.forEach(day => {
            const cell = document.createElement('div');
            cell.textContent = day;
            cell.classList.add('calendar-day-header');
            calendarGrid.appendChild(cell);
          });
          const firstDay = new Date(year, month, 1);
          const lastDay = new Date(year, month + 1, 0);
          let startDay = firstDay.getDay();
          if (startDay === 0) startDay = 7; // Dimanche => 7
          for (let i = 1; i < startDay; i++) {
            const emptyCell = document.createElement('div');
            calendarGrid.appendChild(emptyCell);
          }
          for (let day = 1; day <= lastDay.getDate(); day++) {
            const cell = document.createElement('div');
            cell.textContent = day;
            cell.classList.add('calendar-date-cell');
            cell.addEventListener('click', () => selectDate(year, month, day, cell));
            calendarGrid.appendChild(cell);
          }
          monthLabel.textContent = `${firstDay.toLocaleString('fr-FR', { month: 'long', year: 'numeric' })}`;
        }

        function selectDate(year, month, day, cell) {
          document.querySelectorAll('.calendar-date-cell.selected').forEach(el => el.classList.remove('selected'));
          cell.classList.add('selected');
          selectedDate = new Date(year, month, day);
          const dayStr = String(day).padStart(2, '0');
          const monthStr = String(month + 1).padStart(2, '0');
          const yearStr = year;
          document.getElementById('selected-date-display').textContent = `${dayStr}/${monthStr}/${yearStr}`;
          document.getElementById('date-activities-container').innerHTML = '<p class="activities-placeholder">Chargement des activités...</p>';
          fetch(`dashboard.php?action=getActivitiesBySelectedDate&date=${yearStr}-${monthStr}-${dayStr}`)
            .then(async response => {
              let data;
              try {
                data = await response.json();
              } catch (e) {
                document.getElementById('date-activities-container').innerHTML = '<p class="no-activities-message">Erreur : réponse du serveur invalide (pas du JSON). Vérifiez les erreurs PHP.</p>';
                return;
              }
              const container = document.getElementById('date-activities-container');
              if (data.success) {
                // Combiner les activités régulières et d'entreprise
                const hasActivities = (data.activities && data.activities.length > 0) ||
                  (data.enterpriseActivities && data.enterpriseActivities.length > 0);
                if (hasActivities) {
                  let html = '';
                  // Afficher les activités régulières
                  if (data.activities && data.activities.length > 0) {
                    data.activities.forEach(activity => {
                      html += `
                        <div class="date-activity-item">
                          <h4>${activity.name}</h4>
                          <p>Localisation: ${activity.location}</p>
                          <p>Prix: ${activity.price} DT</p>
                          <span class="activity-type-badge badge-regular">Activité régulière</span>
                        </div>
                      `;
                    });
                  }
                  // Afficher les activités d'entreprise
                  if (data.enterpriseActivities && data.enterpriseActivities.length > 0) {
                    data.enterpriseActivities.forEach(activity => {
                      html += `
                        <div class="date-activity-item date-enterprise-item">
                          <h4>${activity.name}</h4>
                          <p>Catégorie: ${activity.category}</p>
                          <p>Prix: ${activity.price} DT</p>
                          <span class="activity-type-badge badge-enterprise">Activité d'entreprise</span>
                        </div>
                      `;
                    });
                  }
                  container.innerHTML = html;
                } else {
                  container.innerHTML = '<p class="no-activities-message">Aucune activité prévue pour cette date.</p>';
                }
              } else {
                container.innerHTML = `<p class="no-activities-message">Erreur serveur : ${data.message || 'Impossible de récupérer les activités.'}</p>`;
              }
            })
            .catch(error => {
              document.getElementById('date-activities-container').innerHTML =
                `<p class="no-activities-message">Erreur JS ou réseau : ${error.message}</p>`;
            });
        }

        prevMonthBtn.addEventListener('click', () => {
          currentDate.setMonth(currentDate.getMonth() - 1);
          renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
        });
        nextMonthBtn.addEventListener('click', () => {
          currentDate.setMonth(currentDate.getMonth() + 1);
          renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
        });
        // Initialisation
        renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
      </script>

    <?php elseif ($section === 'statistics'): ?>
      <div class="stats-section">
        <h3 class="stats-title">📊 Statistiques Générales</h3>
        <div class="stats-cards">
          <div class="stat-card">
            <div class="stat-icon">🏃‍♀️</div>
            <div class="stat-content">
              <h4>Total des Activités</h4>
              <div class="count" id="count-activities"><?php echo htmlspecialchars($data['stats']['total_activities'] ?? 0); ?></div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
              <h4>Participants</h4>
              <div class="count" id="count-participants"><?php echo htmlspecialchars($data['stats']['total_participants'] ?? 0); ?></div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">📍</div>
            <div class="stat-content">
              <h4>Villes Ciblées</h4>
              <div class="count" id="count-cities"><?php echo htmlspecialchars($data['stats']['total_cities'] ?? 0); ?></div>
            </div>
          </div>
        </div>

        <div class="charts-container">
          <!-- Activités classiques -->
          <div class="chart-wrapper">
            <h3>📈 Participants et Activités par Mois</h3>
            <canvas id="activityChart"></canvas>
          </div>
          <div class="chart-wrapper">
            <h3>📊 Répartition par Catégorie</h3>
            <canvas id="categoryChart"></canvas>
          </div>
          <!-- Activités entreprise -->
          <div class="chart-wrapper">
            <h3>🏢 Répartition Activités Entreprise</h3>
            <canvas id="enterpriseCategoryChart"></canvas>
          </div>
          <div class="chart-wrapper">
            <h3>📅 Activités Entreprise par Mois</h3>
            <canvas id="enterpriseMonthChart"></canvas>
          </div>
          <!-- Réservations -->
          <div class="chart-wrapper">
            <h3>🎫 Réservations par Mois</h3>
            <canvas id="reservationMonthChart"></canvas>
          </div>
          <div class="chart-wrapper">
            <h3>🟣 Statuts des Réservations</h3>
            <canvas id="reservationStatusChart"></canvas>
          </div>
          <!-- Avis -->
          <div class="chart-wrapper">
            <h3>⭐ Répartition des Notes Avis</h3>
            <canvas id="reviewNoteChart"></canvas>
          </div>
          <div class="chart-wrapper">
            <h3>💬 Statuts des Avis</h3>
            <canvas id="reviewStatusChart"></canvas>
          </div>
        </div>
      </div>

      <script>
        // Graphique des participants et activités par mois (Radar)
        const ctxActivity = document.getElementById('activityChart');
        if (ctxActivity) {
          const monthsRaw = <?php echo json_encode(array_column($data['participantsByMonth'] ?? [], 'month')); ?>;
          const participantsDataRaw = <?php echo json_encode(array_column($data['participantsByMonth'] ?? [], 'total_participants')); ?>;
          const activitiesDataRaw = <?php echo json_encode(array_column($data['participantsByMonth'] ?? [], 'total_activities')); ?>;

          if (Array.isArray(monthsRaw) && monthsRaw.length > 0) {
            const months = monthsRaw.map(m => {
              const date = new Date(m + '-01');
              return date.toLocaleString('fr-FR', {
                month: 'short',
                year: 'numeric'
              });
            });
            const participantsData = participantsDataRaw.map(Number);
            const activitiesData = activitiesDataRaw.map(Number);

            new Chart(ctxActivity.getContext('2d'), {
              type: 'radar',
              data: {
                labels: months,
                datasets: [{
                    label: 'Participants',
                    data: participantsData,
                    backgroundColor: 'rgba(105, 81, 255, 0.2)',
                    borderColor: 'rgba(105, 81, 255, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(105, 81, 255, 1)'
                  },
                  {
                    label: 'Activités',
                    data: activitiesData,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)'
                  }
                ]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: {
                    position: 'top'
                  },
                  title: {
                    display: true,
                    text: 'Participants & Activités par Mois (Radar)'
                  }
                }
              }
            });
          } else {
            const context = ctxActivity.getContext('2d');
            context.font = '16px Arial';
            context.fillStyle = '#666';
            context.textAlign = 'center';
            context.fillText('Aucune donnée disponible', ctxActivity.width / 2, ctxActivity.height / 2);
          }
        }

        // Répartition par catégorie (PolarArea)
        const ctxCategory = document.getElementById('categoryChart');
        if (ctxCategory) {
          const categoriesRaw = <?php echo json_encode(array_column($data['activitiesByCategory'] ?? [], 'category')); ?>;
          const categoryCountsRaw = <?php echo json_encode(array_column($data['activitiesByCategory'] ?? [], 'count')); ?>;
          if (Array.isArray(categoriesRaw) && categoriesRaw.length > 0) {
            const categories = categoriesRaw;
            const categoryCounts = categoryCountsRaw.map(Number);
            new Chart(ctxCategory.getContext('2d'), {
              type: 'polarArea',
              data: {
                labels: categories,
                datasets: [{
                  label: 'Activités par Catégorie',
                  data: categoryCounts,
                  backgroundColor: [
                    '#BFA2F7', '#F7B2D9', '#A1A0F7', '#F8D9F0', '#AEE6E6', '#FFD6A5', '#B5EAD7'
                  ]
                }]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: {
                    position: 'top'
                  },
                  title: {
                    display: true,
                    text: 'Répartition des Activités par Catégorie (Polaire)'
                  }
                }
              }
            });
          } else {
            ctxCategory.getContext('2d').fillText('Aucune donnée disponible', 100, 100);
          }
        }

        // Répartition Activités Entreprise (Barres horizontales)
        const ctxEnterpriseCategory = document.getElementById('enterpriseCategoryChart');
        if (ctxEnterpriseCategory) {
          const entCatLabels = <?php echo json_encode(array_column($data['enterpriseByCategory'] ?? [], 'category')); ?>;
          const entCatCounts = <?php echo json_encode(array_column($data['enterpriseByCategory'] ?? [], 'count')); ?>;
          if (Array.isArray(entCatLabels) && entCatLabels.length > 0) {
            new Chart(ctxEnterpriseCategory.getContext('2d'), {
              type: 'bar',
              data: {
                labels: entCatLabels,
                datasets: [{
                  label: 'Activités Entreprise',
                  data: entCatCounts,
                  backgroundColor: '#AEE6E6',
                  borderColor: '#8B5CF6',
                  borderWidth: 1
                }]
              },
              options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                  legend: {
                    display: false
                  },
                  title: {
                    display: true,
                    text: 'Répartition Activités Entreprise (Barres H)'
                  }
                },
                scales: {
                  x: {
                    beginAtZero: true
                  }
                }
              }
            });
          } else {
            ctxEnterpriseCategory.getContext('2d').fillText('Aucune donnée disponible', 100, 100);
          }
        }

        // Activités Entreprise par Mois (Ligne)
        const ctxEnterpriseMonth = document.getElementById('enterpriseMonthChart');
        if (ctxEnterpriseMonth) {
          const entMonthLabels = <?php echo json_encode(array_column($data['enterpriseByMonth'] ?? [], 'month')); ?>;
          const entMonthCounts = <?php echo json_encode(array_column($data['enterpriseByMonth'] ?? [], 'count')); ?>;
          if (Array.isArray(entMonthLabels) && entMonthLabels.length > 0) {
            const months = entMonthLabels.map(m => {
              const date = new Date(m + '-01');
              return date.toLocaleString('fr-FR', {
                month: 'short',
                year: 'numeric'
              });
            });
            new Chart(ctxEnterpriseMonth.getContext('2d'), {
              type: 'line',
              data: {
                labels: months,
                datasets: [{
                  label: 'Activités Entreprise',
                  data: entMonthCounts,
                  backgroundColor: 'rgba(255, 99, 132, 0.2)',
                  borderColor: '#F7B2D9',
                  borderWidth: 2,
                  fill: true
                }]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: {
                    position: 'top'
                  },
                  title: {
                    display: true,
                    text: 'Activités Entreprise par Mois (Ligne)'
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true
                  }
                }
              }
            });
          } else {
            ctxEnterpriseMonth.getContext('2d').fillText('Aucune donnée disponible', 100, 100);
          }
        }

        // Réservations par Mois (Barres)
        const ctxReservationMonth = document.getElementById('reservationMonthChart');
        if (ctxReservationMonth) {
          const resMonthLabels = <?php echo json_encode(array_column($data['reservationsByMonth'] ?? [], 'month')); ?>;
          const resMonthCounts = <?php echo json_encode(array_column($data['reservationsByMonth'] ?? [], 'count')); ?>;
          if (Array.isArray(resMonthLabels) && resMonthLabels.length > 0) {
            const months = resMonthLabels.map(m => {
              const date = new Date(m + '-01');
              return date.toLocaleString('fr-FR', {
                month: 'short',
                year: 'numeric'
              });
            });
            new Chart(ctxReservationMonth.getContext('2d'), {
              type: 'bar',
              data: {
                labels: months,
                datasets: [{
                  label: 'Réservations',
                  data: resMonthCounts,
                  backgroundColor: '#FFD6A5',
                  borderColor: '#F7B2D9',
                  borderWidth: 1
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
                    text: 'Réservations par Mois (Barres)'
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true
                  }
                }
              }
            });
          } else {
            ctxReservationMonth.getContext('2d').fillText('Aucune donnée disponible', 100, 100);
          }
        }

        // Statuts des Réservations (Doughnut)
        const ctxReservationStatus = document.getElementById('reservationStatusChart');
        if (ctxReservationStatus) {
          const resStatusLabels = <?php echo json_encode(array_column($data['reservationsByStatus'] ?? [], 'status')); ?>;
          const resStatusCounts = <?php echo json_encode(array_column($data['reservationsByStatus'] ?? [], 'count')); ?>;
          if (Array.isArray(resStatusLabels) && resStatusLabels.length > 0) {
            new Chart(ctxReservationStatus.getContext('2d'), {
              type: 'doughnut',
              data: {
                labels: resStatusLabels,
                datasets: [{
                  label: 'Statuts Réservations',
                  data: resStatusCounts,
                  backgroundColor: ['#BFA2F7', '#FFD6A5', '#F7B2D9'],
                  borderWidth: 1
                }]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: {
                    position: 'top'
                  },
                  title: {
                    display: true,
                    text: 'Statuts des Réservations (Donut)'
                  }
                }
              }
            });
          } else {
            ctxReservationStatus.getContext('2d').fillText('Aucune donnée disponible', 100, 100);
          }
        }

        // Répartition des Notes Avis (PolarArea)
        const ctxReviewNote = document.getElementById('reviewNoteChart');
        if (ctxReviewNote) {
          const revNoteLabels = <?php echo json_encode(array_column($data['reviewsByNote'] ?? [], 'note')); ?>.map(n => n + ' étoiles');
          const revNoteCounts = <?php echo json_encode(array_column($data['reviewsByNote'] ?? [], 'count')); ?>;
          if (Array.isArray(revNoteLabels) && revNoteLabels.length > 0) {
            new Chart(ctxReviewNote.getContext('2d'), {
              type: 'polarArea',
              data: {
                labels: revNoteLabels,
                datasets: [{
                  label: 'Avis par Note',
                  data: revNoteCounts,
                  backgroundColor: ['#FFD6A5', '#F7B2D9', '#BFA2F7', '#AEE6E6', '#A1A0F7'],
                  borderWidth: 1
                }]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: {
                    position: 'top'
                  },
                  title: {
                    display: true,
                    text: 'Répartition des Notes Avis (Polaire)'
                  }
                }
              }
            });
          } else {
            ctxReviewNote.getContext('2d').fillText('Aucune donnée disponible', 100, 100);
          }
        }

        // Statuts des Avis (Radar)
        const ctxReviewStatus = document.getElementById('reviewStatusChart');
        if (ctxReviewStatus) {
          const revStatusLabels = <?php echo json_encode(array_column($data['reviewsByStatus'] ?? [], 'status')); ?>;
          const revStatusCounts = <?php echo json_encode(array_column($data['reviewsByStatus'] ?? [], 'count')); ?>;
          if (Array.isArray(revStatusLabels) && revStatusLabels.length > 0) {
            new Chart(ctxReviewStatus.getContext('2d'), {
              type: 'radar',
              data: {
                labels: revStatusLabels,
                datasets: [{
                  label: 'Statuts Avis',
                  data: revStatusCounts,
                  backgroundColor: 'rgba(105, 81, 255, 0.2)',
                  borderColor: '#BFA2F7',
                  borderWidth: 2,
                  pointBackgroundColor: '#BFA2F7'
                }]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: {
                    position: 'top'
                  },
                  title: {
                    display: true,
                    text: 'Statuts des Avis (Radar)'
                  }
                }
              }
            });
          } else {
            ctxReviewStatus.getContext('2d').fillText('Aucune donnée disponible', 100, 100);
          }
        }
      </script>



    <?php elseif ($section === 'settings'): ?>
      <div class="settings">
        <h3>⚙️ Paramètres</h3>
        <p>À implémenter : cette section vous permettra de gérer vos paramètres.</p>
      </div>

    <?php elseif ($section === 'reviews'): ?>
      <!-- Section Avis Clients -->
      <div class="reviews-section">
        <div class="panel-header">
          <h2>Gestion des avis clients</h2>

          <!-- Section statistiques des avis -->
          <div class="reviews-stats">
            <div class="stat-box">
              <div class="stat-number"><?php echo $data['totalReviews']; ?></div>
              <div class="stat-label">Total des avis</div>
            </div>
            <div class="stat-box">
              <div class="stat-number"><?php echo $data['pendingCount']; ?></div>
              <div class="stat-label">En attente</div>
            </div>
            <div class="stat-box">
              <div class="stat-number"><?php echo $data['approvedCount']; ?></div>
              <div class="stat-label">Approuvés</div>
            </div>
            <div class="stat-box">
              <div class="stat-number"><?php echo $data['rejectedCount']; ?></div>
              <div class="stat-label">Rejetés</div>
            </div>
            <div class="stat-box">
              <div class="stat-number"><?php echo number_format($data['averageRating'], 1); ?></div>
              <div class="stat-label">Note moyenne</div>
            </div>
          </div>
        </div>

        <!-- Affichage des messages de succès ou d'erreur -->
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-error">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['info'])): ?>
          <div class="alert alert-info">
            <?php echo $_SESSION['info'];
            unset($_SESSION['info']); ?>
          </div>
        <?php endif; ?>

        <div class="filters">
          <div class="filter-group">
            <label for="status-filter">Statut</label>
            <select id="status-filter" onchange="filterReviews()">
              <option value="all">Tous</option>
              <option value="approved">Approuvé</option>
              <option value="pending">En attente</option>
              <option value="rejected">Rejeté</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="rating-filter">Note</label>
            <select id="rating-filter" onchange="filterReviews()">
              <option value="all">Toutes</option>
              <option value="5">5 étoiles</option>
              <option value="4">4 étoiles</option>
              <option value="3">3 étoiles</option>
              <option value="2">2 étoiles</option>
              <option value="1">1 étoile</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="search-reviews">Rechercher</label>
            <input type="text" id="search-reviews" placeholder="Nom, activité..." oninput="filterReviews()">
          </div>
        </div>

        <div class="reviews-table-wrapper">
          <table class="data-table" id="reviews-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Activité</th>
                <th>Client</th>
                <th>Note</th>
                <th>Commentaire</th>
                <th>Date</th>
                <th>Statut</th>

              </tr>
            </thead>
            <tbody>
              <?php if (isset($data['reviews']) && is_array($data['reviews']) && !empty($data['reviews'])): ?>
                <?php foreach ($data['reviews'] as $review):
                  $statusClass = '';
                  switch ($review['status']) {
                    case 'approved':
                      $statusClass = 'status-confirmed';
                      $statusText = 'Approuvé';
                      break;
                    case 'pending':
                      $statusClass = 'status-pending';
                      $statusText = 'En attente';
                      break;
                    case 'rejected':
                      $statusClass = 'status-cancelled';
                      $statusText = 'Rejeté';
                      break;
                  }
                ?>
                  <tr data-status="<?php echo $review['status']; ?>" data-rating="<?php echo $review['rating']; ?>">
                    <td><?php echo $review['id']; ?></td>
                    <td><?php echo htmlspecialchars($review['activity_name']); ?></td>
                    <td><?php echo htmlspecialchars($review['customer_name']); ?><br><small><?php echo !empty($review['customer_email']) ? htmlspecialchars($review['customer_email']) : ''; ?></small></td>
                    <td>
                      <div class="stars-display">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                          <?php if ($i <= $review['rating']): ?>
                            <span class="star filled">★</span>
                          <?php else: ?>
                            <span class="star">☆</span>
                          <?php endif; ?>
                        <?php endfor; ?>
                      </div>
                    </td>
                    <td class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></td>
                    <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                    <td>
                      <?php if ($review['status'] == 'pending'): ?>
                        <a href="process_review.php?action=approve&id=<?php echo $review['id']; ?>" class="btn-confirm" onclick="return confirm('Êtes-vous sûr de vouloir approuver cet avis ?')">Approuver</a>
                        <a href="process_review.php?action=reject&id=<?php echo $review['id']; ?>" class="btn-reject" onclick="return confirm('Êtes-vous sûr de vouloir rejeter cet avis ?')">Rejeter</a>
                      <?php endif; ?>
                      <a href="process_review.php?action=delete&id=<?php echo $review['id']; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis définitivement ?')">Supprimer</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="empty-table">Aucun avis trouvé</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php elseif ($section === 'enterprise'): ?>
      <div class="enterprise-section">
        <h3>🏢 Gestion des Activités d'Entreprise</h3>

        <!-- Tabs de navigation pour les catégories entreprise -->
        <div class="enterprise-tabs">
          <button class="tab-button active" data-category="team-building">Team Building</button>
          <button class="tab-button" data-category="animation">Animation</button>
          <button class="tab-button" data-category="reunion">Réunions</button>
          <button class="tab-button" data-category="soiree">Soirées</button>
          <button class="tab-button" data-category="repas">Repas</button>
          <button class="tab-button" data-category="fundays">Fundays</button>
          <button class="tab-button" data-category="projets-sur-mesure">Projets sur mesure</button>
        </div>

        <!-- Affichage des messages de succès ou d'erreur -->
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-error">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
          </div>
        <?php endif; ?>

        <!-- Section Ajouter une activité d'entreprise -->
        <div class="add-activity-section">
          <a href="dashboard.php?action=add_enterprise" class="add-button">Ajouter une nouvelle activité d'entreprise</a>
        </div>

        <?php
        // Récupérer les données des activités
        $activities = $data['activities'] ?? [];

        // Définir les catégories
        $categories = [
          'team-building' => 'Team Building',
          'animation' => 'Animation',
          'reunion' => 'Réunions',
          'soiree' => 'Soirées',
          'repas' => 'Repas',
          'fundays' => 'Fundays',
          'projets-sur-mesure' => 'Projets sur mesure'
        ];

        // Afficher le contenu pour chaque catégorie
        foreach ($categories as $category_key => $category_name):
          $is_active = ($category_key === 'team-building') ? 'active' : '';
        ?>

          <!-- Content for <?php echo $category_name; ?> -->
          <div class="category-content <?php echo $is_active; ?>" id="<?php echo $category_key; ?>-content">
            <h4>Activités <?php echo $category_name; ?></h4>

            <div class="activities-grid">
              <?php if (isset($activities[$category_key]) && !empty($activities[$category_key])): ?>
                <?php foreach ($activities[$category_key] as $activity): ?>
                  <div class="activity-card">
                    <div class="activity-image">
                      <?php if (!empty($activity['image'])): ?>
                        <?php
                        $image = $activity['image'];
                        // Si c'est une URL (Cloudinary), on l'utilise directement
                        if (filter_var($image, FILTER_VALIDATE_URL)) {
                          $imagePath = $image;
                        } else {
                          $imagePath = "../front office/" . htmlspecialchars($image);
                        }
                        error_log("Chemin de l'image pour l'activité {$activity['id']} : " . $imagePath);
                        ?>
                        <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
                      <?php else: ?>
                        <div class="no-image">Pas d'image disponible</div>
                      <?php endif; ?>
                    </div>
                    <div class="activity-content">
                      <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                      <p><?php echo htmlspecialchars($activity['description']); ?></p>
                      <p class="activity-price"><?php echo htmlspecialchars($activity['price']); ?> <?php echo htmlspecialchars($activity['price_type']); ?></p>
                      <div class="activity-buttons">
                        <a href="dashboard.php?action=edit_enterprise&id=<?php echo $activity['id']; ?>&category=<?php echo $category_key; ?>" class="edit-button">Modifier</a>
                        <a href="dashboard.php?action=delete_enterprise&id=<?php echo $activity['id']; ?>&category=<?php echo $category_key; ?>" class="delete-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette activité ?')">Supprimer</a>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>

              <!-- Card pour ajouter une nouvelle activité -->
              <div class="activity-card add-card">
                <div class="add-content">
                  <span class="add-icon">+</span>
                  <p>Ajouter une nouvelle activité <?php echo $category_name; ?></p>
                  <a href="dashboard.php?action=add_enterprise&category=<?php echo $category_key; ?>" class="add-link">Ajouter</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <style>
        /* Styles pour la section Entreprise */
        .enterprise-section {
          padding-top: 700px;
          padding: 20px;
        }

        .enterprise-section h3 {
          font-size: 24px;
          color: #333;
          margin-bottom: 25px;
        }

        .enterprise-tabs {
          display: flex;
          overflow-x: auto;
          margin-bottom: 30px;
          gap: 10px;
          padding-bottom: 10px;
          border-bottom: 1px solid #e0e0e0;
        }

        .tab-button {
          padding: 10px 20px;
          border: none;
          background-color: #f0f0f0;
          border-radius: 20px;
          cursor: pointer;
          white-space: nowrap;
          transition: all 0.3s ease;
        }

        .tab-button.active {
          background-color: #6941FF;
          color: white;
        }

        .category-content {
          display: none;
        }

        .category-content.active {
          display: block;
        }

        .category-content h4 {
          font-size: 20px;
          color: #444;
          margin-bottom: 20px;
        }

        .add-card {
          display: flex;
          align-items: center;
          justify-content: center;
          min-height: 280px;
          border: 2px dashed #ddd;
          background-color: #f9f9f9;
        }

        .add-content {
          text-align: center;
          padding: 30px;
        }

        .add-icon {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 50px;
          height: 50px;
          border-radius: 50%;
          background-color: #BFA2F7;
          color: white;
          font-size: 24px;
          margin: 0 auto 15px;
        }

        .add-content p {
          color: #666;
          margin-bottom: 15px;
        }

        .add-link {
          display: inline-block;
          background-color: #BFA2F7;
          color: white;
          padding: 8px 25px;
          border-radius: 20px;
          text-decoration: none;
          transition: background-color 0.3s;
        }

        .add-link:hover {
          background-color: #5635CC;
        }

        .activity-price {
          color: #6941FF;
          font-weight: bold;
          margin-bottom: 15px;
        }

        .alert {
          padding: 15px;
          margin-bottom: 20px;
          border-radius: 5px;
        }

        .alert-success {
          background-color: #d4edda;
          color: #155724;
          border: 1px solid #c3e6cb;
        }

        .alert-error {
          background-color: #f8d7da;
          color: #721c24;
          border: 1px solid #f5c6cb;
        }

        .alert-info {
          background-color: #e2f0fb;
          color: #0c5460;
          border: 1px solid #bee5eb;
        }
      </style>

      <script>
        // Script pour les onglets de catégorie entreprise
        document.addEventListener('DOMContentLoaded', function() {
          const tabButtons = document.querySelectorAll('.tab-button');
          const categoryContents = document.querySelectorAll('.category-content');

          tabButtons.forEach(button => {
            button.addEventListener('click', function() {
              // Désactiver tous les onglets
              tabButtons.forEach(btn => btn.classList.remove('active'));
              categoryContents.forEach(content => content.classList.remove('active'));

              // Activer l'onglet cliqué
              this.classList.add('active');
              const category = this.getAttribute('data-category');
              document.getElementById(`${category}-content`).classList.add('active');
            });
          });
        });
      </script>

    <?php elseif ($section === 'add_enterprise'): ?>
      <!-- Section d'ajout d'activité d'entreprise -->
      <div class="enterprise-add-section">
        <div class="header">
          <h2>Ajouter une Activité d'Entreprise</h2>
        </div>

        <div class="add-activity-form">
          <h3>📋 Formulaire d'Ajout d'Activité d'Entreprise</h3>

          <?php
          // Récupérer la catégorie depuis l'URL
          $category = isset($_GET['category']) ? $_GET['category'] : 'team-building';

          // Affichage des messages d'erreur ou de succès
          if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
          }
          if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . $_SESSION['success'] . '</p>';
            unset($_SESSION['success']);
          }

          // Fonction pour convertir les identifiants de catégorie en noms affichables
          function getCategoryDisplayName($categoryId)
          {
            $categories = [
              'team-building' => 'Team Building',
              'animation' => 'Animation',
              'reunion' => 'Réunions',
              'soiree' => 'Soirées',
              'repas' => 'Repas',
              'fundays' => 'Fundays',
              'projets-sur-mesure' => 'Projets sur mesure'
            ];

            return $categories[$categoryId] ?? $categoryId;
          }
          ?>

          <form method="POST" action="process_enterprise.php" enctype="multipart/form-data">
            <input type="hidden" name="operation" value="add">

            <div class="form-group">
              <label for="category">Catégorie</label>
              <select id="category" name="category" required>
                <option value="team-building" <?php echo $category == 'team-building' ? 'selected' : ''; ?>>Team Building</option>
                <option value="animation" <?php echo $category == 'animation' ? 'selected' : ''; ?>>Animation</option>

                <option value="reunion" <?php echo $category == 'reunion' ? 'selected' : ''; ?>>Réunions</option>
                <option value="soiree" <?php echo $category == 'soiree' ? 'selected' : ''; ?>>Soirées</option>
                <option value="repas" <?php echo $category == 'repas' ? 'selected' : ''; ?>>Repas</option>
                <option value="fundays" <?php echo $category == 'fundays' ? 'selected' : ''; ?>>Fundays</option>
                <option value="projets-sur-mesure" <?php echo $category == 'projets-sur-mesure' ? 'selected' : ''; ?>>Projets sur mesure</option>
              </select>
            </div>

            <div class="form-group">
              <label for="activityName">Nom de l'activité</label>
              <input type="text" id="activityName" name="name" placeholder="Nom de l'activité" required>
            </div>

            <div class="form-group">
              <label for="activityDescription">Description</label>
              <textarea id="activityDescription" name="description" rows="5" placeholder="Description détaillée de l'activité" required></textarea>
            </div>

            <div class="form-group">
              <label for="activityPrice">Prix</label>
              <div class="price-container">
                <input type="number" id="activityPrice" name="price" placeholder="Ex: 750" step="0.01" min="0" required>
                <input type="text" id="activityPriceType" name="price_type" placeholder="Ex: DT / groupe" required>
              </div>
              <p class="help-text">Indiquez le prix et le type (ex: DT / groupe, DT / personne)</p>
            </div>

            <div class="form-group">
              <label for="imageFile">Image de l'activité *</label>
              <div class="image-input-container">
                <input type="file" id="imageFile" name="image" accept="image/*" required>
              </div>
              <div id="imagePreview" style="margin-top: 10px; display: none;">
                <img id="previewImg" src="" alt="Aperçu de l'image" style="max-width: 100%; max-height: 200px;">
              </div>
            </div>

            <div class="form-buttons">
              <button type="submit" class="submit-button">Ajouter l'activité</button>
              <a href="dashboard.php?action=enterprise" class="cancel-button">Annuler</a>
            </div>
          </form>
        </div>
      </div>

    <?php elseif ($section === 'edit_enterprise'): ?>
      <!-- Section de modification d'activité d'entreprise -->
      <div class="enterprise-edit-section">
        <div class="header">
          <h2>Modifier une Activité d'Entreprise</h2>
        </div>
        <div class="add-activity-form">
          <h3>📋 Formulaire de Modification d'Activité d'Entreprise</h3>
          <?php
          require_once __DIR__ . '/../../model/EnterpriseModel.php';
          // Récupération et vérification de l'activité
          if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['category']) || empty($_GET['category'])) {
            echo '<p style="color: red;">ID d\'activité ou catégorie non spécifiés</p>';
            echo '<a href="dashboard.php?action=enterprise" class="cancel-button">Retour au tableau de bord</a>';
            exit;
          }
          $id = (int)$_GET['id'];
          $category = $_GET['category'];
          $enterpriseModel = new EnterpriseModel();
          $activity = $enterpriseModel->getEnterpriseActivityById($id, $category);
          if (!$activity) {
            echo '<p style="color: red;">Activité non trouvée</p>';
            echo '<a href="dashboard.php?action=enterprise" class="cancel-button">Retour au tableau de bord</a>';
            exit;
          }
          if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
          }
          if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . $_SESSION['success'] . '</p>';
            unset($_SESSION['success']);
          }
          ?>
          <form method="POST" action="process_enterprise.php" enctype="multipart/form-data">
            <input type="hidden" name="operation" value="edit">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($activity['id']); ?>">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($activity['image'] ?? ''); ?>">
            <div class="form-group">
              <label for="category">Catégorie</label>
              <select id="category" name="category" required>
                <option value="team-building" <?php echo $category == 'team-building' ? 'selected' : ''; ?>>Team Building</option>
                <option value="animation" <?php echo $category == 'animation' ? 'selected' : ''; ?>>Animation</option>
                <option value="reunion" <?php echo $category == 'reunion' ? 'selected' : ''; ?>>Réunions</option>
                <option value="soiree" <?php echo $category == 'soiree' ? 'selected' : ''; ?>>Soirées</option>
                <option value="repas" <?php echo $category == 'repas' ? 'selected' : ''; ?>>Repas</option>
                <option value="fundays" <?php echo $category == 'fundays' ? 'selected' : ''; ?>>Fundays</option>
                <option value="projets-sur-mesure" <?php echo $category == 'projets-sur-mesure' ? 'selected' : ''; ?>>Projets sur mesure</option>
              </select>
            </div>
            <div class="form-group">
              <label for="activityName">Nom de l'activité</label>
              <input type="text" id="activityName" name="name" value="<?php echo htmlspecialchars($activity['name']); ?>" required>
            </div>
            <div class="form-group">
              <label for="activityDescription">Description</label>
              <textarea id="activityDescription" name="description" rows="5" required><?php echo htmlspecialchars($activity['description']); ?></textarea>
            </div>
            <div class="form-group">
              <label for="activityPrice">Prix</label>
              <div class="price-container">
                <input type="number" id="activityPrice" name="price" value="<?php echo htmlspecialchars($activity['price']); ?>" step="0.01" min="0" required>
                <input type="text" id="activityPriceType" name="price_type" value="<?php echo htmlspecialchars($activity['price_type']); ?>" required>
              </div>
              <p class="help-text">Indiquez le prix et le type (ex: DT / groupe, DT / personne)</p>
            </div>
            <div class="form-group">
              <label for="imageFile">Image de l'activité</label>
              <div class="image-input-container">
                <input type="file" id="imageFile" name="image" accept="image/*">
                <p>Laissez vide pour conserver l'image actuelle</p>
              </div>
              <?php if (!empty($activity['image'])): ?>
                <div id="currentImage" style="margin-top: 10px;">
                  <p>Image actuelle:</p>
                  <p style="font-size:12px;color:#888;">Chemin généré : <?php echo htmlspecialchars(getImagePath($activity['image'])); ?></p>
                  <img src="<?php echo htmlspecialchars(getImagePath($activity['image'])); ?>" alt="Image de l'activité" style="max-width: 100%; max-height: 200px;">
                </div>
              <?php else: ?>
                <div id="currentImage" style="margin-top: 10px;">
                  <p style="color:#c00;">Aucune image enregistrée pour cette activité.</p>
                </div>
              <?php endif; ?>
              <div id="imagePreview" style="margin-top: 10px; display: none;">
                <p>Nouvelle image:</p>
                <img id="previewImg" src="" alt="Aperçu de l'image" style="max-width: 100%; max-height: 200px;">
              </div>
            </div>
            <div class="form-buttons" style="display:flex;gap:16px;margin-top:24px;">
              <button type="submit" class="submit-button" style="background:#6941FF;color:#fff;padding:10px 24px;border-radius:8px;font-weight:600;">Mettre à jour l'activité</button>
              <a href="dashboard.php?action=enterprise" class="cancel-button" style="background:#eee;color:#6941FF;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:600;">Annuler</a>
            </div>
          </form>
        </div>
      </div>

    <?php elseif ($section === 'reservations'): ?>
      <!-- Section Réservations -->
      <div class="reservations-section">
        <div class="panel-header">
          <h2>Gestion des réservations</h2>
        </div>

        <!-- Affichage des messages de succès ou d'erreur -->
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert alert-success">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert alert-error">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
          </div>
        <?php endif; ?>

        <!-- Section d'exportation Excel -->
        <div class="export-section">
          <h3>Exporter les réservations</h3>

          <?php
          // Afficher le message d'erreur d'exportation si présent
          if (isset($_GET['error']) && $_GET['error'] === 'export_error') {
            echo '<div class="alert alert-error">Erreur lors de l\'exportation : ';
            echo htmlspecialchars($_GET['message'] ?? 'Problème inconnu');
            echo '</div>';
          }
          ?>

          <div class="export-form">
            <a href="../../export.php" class="export-btn" style="text-decoration: none; display: inline-block; padding: 12px 20px; cursor: pointer;">
              Exporter toutes les réservations <span class="icon">📊</span>
            </a>
          </div>
        </div>

        <div class="filters">
          <div class="filter-group">
            <label for="status-filter">Statut</label>
            <select id="status-filter" onchange="filterReservations()">
              <option value="all">Tous</option>
              <option value="pending">En attente</option>
              <option value="confirmed">Confirmé</option>
              <option value="cancelled">Annulé</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="date-filter">Date</label>
            <input type="date" id="date-filter" onchange="filterReservations()">
          </div>
          <div class="filter-group">
            <label for="search-reservations">Rechercher</label>
            <input type="text" id="search-reservations" placeholder="Nom, email..." oninput="filterReservations()">
          </div>
        </div>

        <div class="reservations-table-wrapper">
          <table class="data-table" id="reservations-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Activité</th>
                <th>Client</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Personnes</th>
                <th>Total</th>
                <th>Statut</th>

              </tr>
            </thead>
            <tbody>
              <?php
              if (isset($data['reservations']) && is_array($data['reservations']) && !empty($data['reservations'])):
                foreach ($data['reservations'] as $reservation):
                  $statusClass = '';
                  switch ($reservation['payment_status']) {
                    case 'confirmed':
                      $statusClass = 'status-confirmed';
                      break;
                    case 'pending':
                      $statusClass = 'status-pending';
                      break;
                    case 'cancelled':
                      $statusClass = 'status-cancelled';
                      break;
                  }

                  $formattedDate = date('Y-m-d', strtotime($reservation['reservation_date']));
              ?>
                  <tr data-status="<?php echo $reservation['payment_status']; ?>" data-date="<?php echo $formattedDate; ?>">
                    <td><?php echo $reservation['id']; ?></td>
                    <td><?php echo htmlspecialchars($reservation['activity_name']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['customer_name']); ?><br><small><?php echo htmlspecialchars($reservation['customer_email']); ?></small></td>
                    <td><?php echo $formattedDate; ?></td>
                    <td><?php echo $reservation['reservation_time']; ?></td>
                    <td><?php echo $reservation['people_count']; ?></td>
                    <td><?php echo $reservation['total_price']; ?> DT</td>
                    <td><span class="status <?php echo $statusClass; ?>"><?php echo ucfirst($reservation['payment_status']); ?></span></td>
                  </tr>
                <?php
                endforeach;
              else:
                ?>
                <tr>
                  <td colspan="8" class="empty-table">Aucune réservation trouvée</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php else: ?>
      <div class="error">
        <h3>Erreur</h3>
        <p>Section non reconnue : <?php echo htmlspecialchars($section); ?></p>
      </div>
    <?php endif; ?>
  </div> <!-- Fin de <div class="dashboard"> -->

  <script>
    // Script pour l'aperçu de l'image dans les formulaires
    const imageFileInput = document.getElementById('imageFile');
    if (imageFileInput) {
      const imagePreview = document.getElementById('imagePreview');
      const previewImg = document.getElementById('previewImg');

      imageFileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            previewImg.src = e.target.result;
            imagePreview.style.display = 'block';
          };
          reader.readAsDataURL(file);
        } else {
          imagePreview.style.display = 'none';
          previewImg.src = '';
        }
      });
    }

    // Script pour le calendrier
    document.querySelectorAll('.calendar-grid div').forEach(dateEl => {
      // Ne pas ajouter d'événements aux cellules vides et aux jours de la semaine
      if (dateEl.innerText && !['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'].includes(dateEl.innerText)) {
        dateEl.addEventListener('click', () => {
          // Désélectionner l'élément précédemment sélectionné
          document.querySelectorAll('.calendar-grid div.selected').forEach(el => {
            el.classList.remove('selected');
          });
          // Sélectionner l'élément actuel
          dateEl.classList.add('selected');
          // Construire la date au format YYYY-MM-DD
          const day = dateEl.innerText.padStart(2, '0');
          const month = '04'; // Avril fixé pour l'exemple (à adapter)
          const year = '2025'; // Année fixée pour l'exemple (à adapter)
          const selectedDate = `${year}-${month}-${day}`;
          // Mettre à jour l'affichage de la date
          document.getElementById('selected-date-display').textContent = `${day}/${month}/${year}`;
          // Afficher le message de chargement
          document.getElementById('date-activities-container').innerHTML = '<p class="activities-placeholder">Chargement des activités...</p>';
          // Faire une requête AJAX pour récupérer les activités de cette date
          fetch(`dashboard.php?action=getActivitiesBySelectedDate&date=${selectedDate}`)
            .then(async response => {
              let data;
              try {
                data = await response.json();
              } catch (e) {
                document.getElementById('date-activities-container').innerHTML = '<p class="no-activities-message">Erreur : réponse du serveur invalide (pas du JSON). Vérifiez les erreurs PHP.</p>';
                return;
              }
              const container = document.getElementById('date-activities-container');
              if (data.success) {
                // Combiner les activités régulières et d'entreprise
                const hasActivities = (data.activities && data.activities.length > 0) ||
                  (data.enterpriseActivities && data.enterpriseActivities.length > 0);
                if (hasActivities) {
                  let html = '';
                  // Afficher les activités régulières
                  if (data.activities && data.activities.length > 0) {
                    data.activities.forEach(activity => {
                      html += `
                        <div class="date-activity-item">
                          <h4>${activity.name}</h4>
                          <p>Localisation: ${activity.location}</p>
                          <p>Prix: ${activity.price} DT</p>
                          <span class="activity-type-badge badge-regular">Activité régulière</span>
                        </div>
                      `;
                    });
                  }
                  // Afficher les activités d'entreprise
                  if (data.enterpriseActivities && data.enterpriseActivities.length > 0) {
                    data.enterpriseActivities.forEach(activity => {
                      html += `
                        <div class="date-activity-item date-enterprise-item">
                          <h4>${activity.name}</h4>
                          <p>Catégorie: ${activity.category}</p>
                          <p>Prix: ${activity.price} DT</p>
                          <span class="activity-type-badge badge-enterprise">Activité d'entreprise</span>
                        </div>
                      `;
                    });
                  }
                  container.innerHTML = html;
                } else {
                  container.innerHTML = '<p class="no-activities-message">Aucune activité prévue pour cette date.</p>';
                }
              } else {
                container.innerHTML = `<p class="no-activities-message">Erreur serveur : ${data.message || 'Impossible de récupérer les activités.'}</p>`;
              }
            })
            .catch(error => {
              document.getElementById('date-activities-container').innerHTML =
                `<p class="no-activities-message">Erreur JS ou réseau : ${error.message}</p>`;
            });
        });
      }
    });

    // Script pour les statistiques (animation des compteurs)
    function animateCounter(id, endValue, speed = 30) {
      let el = document.getElementById(id);
      if (el) {
        let current = 0;
        let increment = Math.ceil(endValue / 50);
        let interval = setInterval(() => {
          current += increment;
          if (current >= endValue) {
            current = endValue;
            clearInterval(interval);
          }
          el.textContent = current;
        }, speed);
      }
    }

    window.addEventListener('DOMContentLoaded', () => {
      animateCounter("count-activities", <?php echo (int)($data['stats']['total_activities'] ?? 0); ?>);
      animateCounter("count-participants", <?php echo (int)($data['stats']['total_participants'] ?? 0); ?>);
      animateCounter("count-cities", <?php echo (int)($data['stats']['total_cities'] ?? 0); ?>);
    });

    // Fonctions pour la gestion des réservations
    function filterReservations() {
      const statusFilter = document.getElementById('status-filter').value;
      const dateFilter = document.getElementById('date-filter').value;
      const searchFilter = document.getElementById('search-reservations').value.toLowerCase();

      const rows = document.querySelectorAll('#reservations-table tbody tr');

      rows.forEach(row => {
        const status = row.getAttribute('data-status');
        const date = row.getAttribute('data-date');
        const textContent = row.textContent.toLowerCase();

        let statusMatch = statusFilter === 'all' || status === statusFilter;
        let dateMatch = !dateFilter || date === dateFilter;
        let searchMatch = !searchFilter || textContent.includes(searchFilter);

        if (statusMatch && dateMatch && searchMatch) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    function confirmReservation(id) {
      if (confirm('Êtes-vous sûr de vouloir confirmer cette réservation ?')) {
        // Ici, vous ajouterez le code AJAX pour confirmer la réservation
        console.log(`Confirmation de la réservation ${id}`);
        // Recharger la page ou mettre à jour l'interface
        alert('Réservation confirmée avec succès !');
        // À adapter selon votre implémentation
      }
    }

    function cancelReservation(id) {
      if (confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
        // Ici, vous ajouterez le code AJAX pour annuler la réservation
        console.log(`Annulation de la réservation ${id}`);
        // Recharger la page ou mettre à jour l'interface
        alert('Réservation annulée avec succès !');
        // À adapter selon votre implémentation
      }
    }

    function filterReviews() {
      const statusFilter = document.getElementById('status-filter').value;
      const ratingFilter = document.getElementById('rating-filter').value;
      const searchTerm = document.getElementById('search-reviews').value.toLowerCase();

      const rows = document.querySelectorAll('#reviews-table tbody tr');

      rows.forEach(row => {
        const status = row.getAttribute('data-status');
        const rating = row.getAttribute('data-rating');
        const client = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        const activity = row.querySelector('td:nth-child(2)').textContent.toLowerCase();

        let statusMatch = statusFilter === 'all' || status === statusFilter;
        let ratingMatch = ratingFilter === 'all' || rating === ratingFilter;
        let searchMatch = searchTerm === '' || client.includes(searchTerm) || activity.includes(searchTerm);

        if (statusMatch && ratingMatch && searchMatch) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    // Fonction pour exporter les réservations en Excel
    function exportReservations() {
      const month = document.getElementById('export-month').value;
      const year = document.getElementById('export-year').value;
      const url = `view/back%20office/export_reservations.php?month=${month}&year=${year}`;
      window.location.href = url;
    }

    // Fonction pour préparer l'export en Excel
    function prepareExport() {
      document.getElementById('export-month-hidden').value = document.getElementById('export-month').value;
      document.getElementById('export-year-hidden').value = document.getElementById('export-year').value;
    }
  </script>

  <style>
    /* Styles pour la section Réservations */
    .reservations-section {
      padding: 20px;
    }

    .filters {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 20px;
    }

    .filter-group {
      display: flex;
      flex-direction: column;
      min-width: 200px;
    }

    .filter-group label {
      margin-bottom: 5px;
      font-size: 0.9em;
      color: #666;
    }

    .filter-group select,
    .filter-group input {
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }

    .reservations-table-wrapper {
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .data-table th,
    .data-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    .data-table th {
      background-color: #f8f9fa;
      font-weight: 600;
      color: #333;
    }

    .data-table tbody tr:hover {
      background-color: #f5f5f5;
    }

    .status {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.9em;
      font-weight: 500;
    }

    .status-confirmed {
      background-color: #e2f8e9;
      color: #0a810a;
    }

    .status-pending {
      background-color: #fff7e6;
      color: #b38600;
    }

    .status-cancelled {
      background-color: #ffe6e6;
      color: #d60000;
    }

    .btn-confirm,
    .btn-cancel {
      display: inline-block;
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      margin: 2px;
      cursor: pointer;
      font-size: 0.9em;
      text-decoration: none;
    }

    .btn-confirm {
      background-color: #28a745;
      color: white;
    }

    .btn-cancel {
      background-color: #dc3545;
      color: white;
    }

    .btn-confirm:hover {
      background-color: #218838;
    }

    .btn-cancel:hover {
      background-color: #c82333;
    }

    .empty-table {
      text-align: center;
      color: #666;
      padding: 40px !important;
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .alert-info {
      background-color: #e2f0fb;
      color: #0c5460;
      border: 1px solid #bee5eb;
    }

    /* Styles pour les étoiles */
    .stars-display {
      display: flex;
    }

    .star {
      color: #ddd;
      font-size: 1.2em;
    }

    .star.filled {
      color: #ffc107;
    }

    /* Styles pour les boutons d'action */
    .btn-confirm,
    .btn-reject,
    .btn-delete {
      display: inline-block;
      padding: 5px 10px;
      margin: 2px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 0.85em;
      font-weight: 500;
      text-align: center;
    }

    .btn-confirm {
      background-color: #e2f8e9;
      color: #0a810a;
    }

    .btn-reject {
      background-color: #ffe6e6;
      color: #d60000;
    }

    .btn-delete {
      background-color: #f8f9fa;
      color: #495057;
    }

    .btn-confirm:hover {
      background-color: #c3e6c7;
    }

    .btn-reject:hover {
      background-color: #ffcccc;
    }

    .btn-delete:hover {
      background-color: #e9ecef;
    }

    /* Limiter la taille des commentaires */
    .review-comment {
      max-width: 300px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .review-comment:hover {
      white-space: normal;
      word-wrap: break-word;
    }
  </style>
</body>

</html>