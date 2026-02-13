<?php
session_start();
require_once '../../Controller/produitcontroller.php';
require_once '../../Controller/AvisController.php'; // Include AvisController

$pdo = new PDO("mysql:host=localhost;dbname=clickngo_db", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$controller = new ProductController();
$response = $controller->getAllProducts();
$products = $response['success'] ? $response['products'] : [];
$avisController = new AvisController();
$pendingReviews = $avisController->getPendingReviewsCount();
$pendingCount = $pendingReviews['success'] ? $pendingReviews['pending_count'] : 0;

// Trier les produits par stock (du plus petit au plus grand)
usort($products, function ($a, $b) {
  return $a['stock'] - $b['stock'];
});

$categoryStats = $controller->getCategoryStats();
$stats = $categoryStats['success'] ? $categoryStats['stats'] : [];
$globalStats = $controller->getGlobalStats();
$orderStats = $controller->getOrdersStats();
$period = isset($_GET['period']) ? $_GET['period'] : 'today';
$topProductsData = $controller->getTopProducts($period);
$topProducts = $topProductsData['success'] ? $topProductsData['products'] : [];
$recentActivities = $controller->getRecentActivities();
$outOfStockData = $controller->getOutOfStockCount();
// Fetch all reviews
$avisResponse = $avisController->getAllAvis();
$avis = $avisResponse['success'] ? $avisResponse['avis'] : [];

// Calculate stats for stat card and satisfaction overview
$reviewCount = count($avis);
$averageRating = $reviewCount > 0 ? round(array_sum(array_column($avis, 'stars')) / $reviewCount, 1) : 0;

// Récupérer les commandes d'achat par jour (7 derniers jours)
$achatStats = $pdo->query("
    SELECT DATE(date_commande) as date, COUNT(*) as total
    FROM commandes
    GROUP BY DATE(date_commande)
    ORDER BY date DESC
    LIMIT 7
")->fetchAll(PDO::FETCH_ASSOC);
// Récupérer les locations par jour (7 derniers jours)
$locationStats = $pdo->query("
    SELECT DATE(date_location) as date, COUNT(*) as total
    FROM louer
    GROUP BY DATE(date_location)
    ORDER BY date DESC
    LIMIT 7
")->fetchAll(PDO::FETCH_ASSOC);

// Déterminer la section active
$active_section = isset($_GET['section']) ? $_GET['section'] : 'overview';

// Compter les commandes en attente
$pending_purchases = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut_commande = 'en_attente'")->fetchColumn();
$pending_rentals = $pdo->query("SELECT COUNT(*) FROM louer WHERE statut_location = 'en_attente'")->fetchColumn();
$total_pending = $pending_purchases + $pending_rentals;
?>

<?php
// Configuration de session sécurisée
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    session_start();
}

// Vérifier si utilisateur connecté
if (!isset($_SESSION['user']['id_user'])) {
    header('Location: /login.php'); // Adjust the login path as needed
    exit();
}


$conn = config::getConnexion();

// Function to generate color from string (from indeex.php)
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
  <title>Dashboard Gestion de Produits</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
  <link rel="stylesheet" href="styles.css">
  <style>
    .btn:disabled {
      background-color: #cccccc;
      color: #666666;
      cursor: not-allowed;
      opacity: 0.6;
    }

    /* Styles existants pour les cartes de produits (inchangés) */
    .product-cards {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      padding: 20px;
    }

    .card {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      height: 400px;
      width: 100%;
      max-width: 250px;
      margin: 0 auto;
    }

    .product-image {
      width: 100%;
      height: 150px;
      overflow: hidden;
    }

    .product-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .product-details {
      padding: 15px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      overflow: hidden;
    }

    .product-details h3 {
      font-size: 16px;
      margin: 0 0 8px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .product-details p {
      font-size: 12px;
      margin: 4px 0;
      word-break: break-word;
    }

    .progress-bar {
      width: 100%;
      height: 8px;
      background: #e0e0e0;
      border-radius: 5px;
      margin: 8px 0;
    }

    .progress-bar-fill {
      height: 100%;
      background: #4CAF50;
      border-radius: 5px;
      transition: width 0.3s ease;
    }

    /* Style pour le conteneur des boutons dans les cartes */
    .card-buttons {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }

    /* Style de base pour tous les boutons */
    .btn {
      padding: 8px;
      font-size: 12px;
      border-radius: 4px;
      cursor: pointer;
      text-align: center;
      color: white;
      border: none;
      transition: background-color 0.3s ease;
    }

    /* Styles spécifiques pour les boutons dans les cartes */
    .card-buttons .btn {
      flex: 1;
      /* Les boutons dans les cartes occupent tout l'espace disponible */
    }

    /* Nouveau style pour les boutons Modifier et Supprimer - avec couleur lilas #BFA2F7 */
    .edit-button,
    .btn.edit-button,
    button.edit-button {
      background: #BFA2F7 !important;
      /* Couleur lilas demandée */
      color: white !important;
      border: none !important;
      padding: 10px 15px !important;
      border-radius: 25px !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      transition: transform 0.3s ease, box-shadow 0.3s ease !important;
      box-shadow: 0 3px 6px rgba(191, 162, 247, 0.2) !important;
    }

    /* Style spécifique du bouton Supprimer avec la couleur rose demandée */
    .delete-button,
    .btn.delete-button,
    button.delete-button {
      background: #F7B2D9 !important;
      /* Couleur rose demandée */
      color: white !important;
      border: none !important;
      padding: 10px 15px !important;
      border-radius: 25px !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      transition: transform 0.3s ease, box-shadow 0.3s ease !important;
      box-shadow: 0 3px 6px rgba(247, 178, 217, 0.2) !important;
    }

    .edit-button:hover,
    .btn.edit-button:hover,
    button.edit-button:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 5px 12px rgba(191, 162, 247, 0.4) !important;
      background: #AD8DF3 !important;
      /* Version plus foncée au survol */
    }

    /* Effet de survol spécifique pour le bouton Supprimer */
    .delete-button:hover,
    .btn.delete-button:hover,
    button.delete-button:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 5px 12px rgba(247, 178, 217, 0.4) !important;
      background: #F094C3 !important;
      /* Version plus foncée du rose au survol */
    }

    /* Style pour le conteneur du bouton Ajouter */
    .add-product-nav {
      display: flex;
      justify-content: center;
      margin: 20px 0;
    }

    /* Style pour le bouton Ajouter */
    .add-button {
      background-color: #B19CD9;
      /* Pastel purple */
    }

    .add-button:hover {
      background-color: #9F84CF;
      /* Slightly darker pastel purple on hover */
    }

    /* Styles pour le modal (inchangés) */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
      background-color: #fefefe;
      margin: 5% auto;
      padding: 0;
      border: 1px solid #888;
      width: 80%;
      max-width: 600px;
      max-height: 80vh;
      display: flex;
      flex-direction: column;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
      padding: 16px;
      background-color: #f5f5f5;
      border-bottom: 1px solid #ddd;
    }

    .modal-body {
      padding: 16px;
      overflow-y: auto;
      flex-grow: 1;
    }

    .modal-footer {
      padding: 16px;
      background-color: #f5f5f5;
      border-top: 1px solid #ddd;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: #B19CD9;
      /* Pastel purple */
      outline: none;
    }

    .error-message {
      color: #d32f2f;
      font-size: 0.8em;
      display: block;
      margin-top: 5px;
      min-height: 18px;
    }

    input:invalid,
    select:invalid {
      border-color: #ff4444;
    }

    input:valid,
    select:valid {
      border-color: #00C851;
    }

    .close-btn {
      padding: 8px 16px;
      background-color: #f44336;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .save-btn {
      padding: 8px 16px;
      background-color: #B19CD9;
      /* Pastel purple */
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .close-btn:hover {
      background-color: #d32f2f;
    }

    .save-btn:hover {
      background-color: #9F84CF;
      /* Slightly darker pastel purple on hover */
    }

    /* Styles pour la section Commandes */
    .orders-container {
      padding: 20px;
    }

    .orders-section {
      margin-bottom: 40px;
    }

    .orders-section h2 {
      font-size: 20px;
      margin-bottom: 15px;
      padding-bottom: 5px;
    }

    .orders-section.purchase-orders h2 {
      color: #D81B60;
      /* Rose foncé pour Commandes d'achat */
      border-bottom: 2px solid #D81B60;
    }

    .orders-section.rental-orders h2 {
      color: #0288D1;
      /* Bleu pour Locations */
      border-bottom: 2px solid #0288D1;
    }

    .order-cards {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .order-card {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      padding: 20px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 220px;
      border: 1px solid #e0e0e0;
    }

    .order-details h3 {
      font-size: 16px;
      margin: 0 0 12px;
      color: #333;
      /* Gris foncé pour ID Commande et ID Location */
    }

    .order-details p {
      font-size: 14px;
      margin: 6px 0;
      color: #000;
      /* Noir pour tous les champs */
    }

    .order-actions {
      margin-top: 15px;
      padding-top: 10px;
      border-top: 1px solid #e0e0e0;
    }

    .order-actions form {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
    }

    .order-actions select {
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
      flex: 1;
      min-width: 120px;
    }

    .update-button {
      background: #BFA2F7 !important;
      /* Couleur lilas */
      color: white !important;
      border: none !important;
      padding: 10px 15px !important;
      border-radius: 25px !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      transition: transform 0.3s ease, box-shadow 0.3s ease !important;
      box-shadow: 0 3px 6px rgba(191, 162, 247, 0.2) !important;
    }

    .update-button:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 5px 12px rgba(191, 162, 247, 0.4) !important;
      background: #AD8DF3 !important;
      /* Version plus foncée au survol */
    }

    .delete-button {
      background-color: #f44336;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }

    .delete-button:hover {
      background-color: #d32f2f;
    }

    .no-orders {
      text-align: center;
      color: #888;
      font-size: 16px;
      margin: 20px 0;
      padding: 20px;
      background: #f9f9f9;
      border-radius: 8px;
    }

    /* Styles spécifiques pour la section Tableau de Bord */
    #overview .header h2 {
      color: #E91E63;
      /* Rose pour le titre principal */
    }

    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 15px;
      padding: 15px;
    }

    .stat-card {
      background: #FCE4EC;
      border: 1px solid #E91E63;
      border-radius: 8px;
      padding: 15px;
      text-align: center;
      transition: transform 0.3s ease;
      height: 180px;
    }

    .stat-card:hover {
      transform: translateY(-5px);
    }

    .stat-icon {
      font-size: 95px;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 10px auto;
    }

    .stat-card h3 {
      font-size: 16px;
      margin: 0 0 5px;
      color: #333;
    }

    .stat-value {
      font-size: 24px;
      font-weight: 700;
      color: #E91E63;
      margin: 5px 0;
    }

    .stat-details {
      font-size: 12px;
      color: #666;
      margin-top: 3px;
    }

    .charts-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      padding: 20px;
    }

    .chart-card {
      background: #fff;
      border: 1px solid #E91E63;
      /* Bordure rose */
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      height: 400px;
      /* Augmentation de la hauteur */
      display: flex;
      flex-direction: column;
    }

    .chart-card h3 {
      font-size: 16px;
      margin: 0 0 15px;
      color: #9C27B0;
      /* Mauve pour les titres des graphiques */
    }

    .chart-card canvas {
      flex: 1;
      min-height: 300px;
      /* Hauteur minimale pour le canvas */
    }

    .stock-alert {
      background: linear-gradient(135deg, #FFF5F5, #FFE2EC);
      border-radius: 15px;
      padding: 15px 25px;
      margin: 20px 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 5px 15px rgba(255, 105, 180, 0.15);
      border-left: 5px solid #FF69B4;
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
      animation: alertPulse 2s infinite alternate;
    }

    .stock-alert:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(255, 105, 180, 0.25);
    }

    .stock-alert p {
      margin: 0;
      font-size: 16px;
      color: #333;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .alert-icon {
      font-size: 24px;
      display: inline-block;
      animation: shakeIcon 1.5s ease infinite;
    }

    .stock-alert strong {
      color: #E91E63;
      font-weight: 600;
    }

    .alert-btn {
      background: linear-gradient(135deg, #FF69B4, #B19CD9);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 12px;
      /* Même border-radius que la notification */
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      box-shadow: 0 4px 15px rgba(177, 156, 217, 0.3);
      /* Même ombre que la notification */
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      letter-spacing: 0.5px;
    }

    .alert-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(255, 105, 180, 0.5);
      background: linear-gradient(135deg, #FF5CAD, #9F84CF);
      /* Version plus foncée au survol */
    }

    .alert-btn:active {
      transform: translateY(0);
      box-shadow: 0 3px 8px rgba(177, 156, 217, 0.3);
    }

    .alert-btn::after {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: rgba(255, 255, 255, 0.15);
      transform: rotate(45deg);
      transition: all 0.6s cubic-bezier(0.19, 1, 0.22, 1);
      opacity: 0;
    }

    .alert-btn:hover::after {
      opacity: 1;
      transform: rotate(45deg) translate(0, 50%);
    }

    @keyframes alertPulse {
      0% {
        box-shadow: 0 5px 15px rgba(255, 105, 180, 0.15);
      }

      100% {
        box-shadow: 0 5px 20px rgba(255, 105, 180, 0.35);
      }
    }

    @keyframes shakeIcon {

      0%,
      100% {
        transform: rotate(0deg);
      }

      20% {
        transform: rotate(15deg);
      }

      40% {
        transform: rotate(-10deg);
      }

      60% {
        transform: rotate(5deg);
      }

      80% {
        transform: rotate(-5deg);
      }
    }

    /* Style pour le titre de section */
    .section-title {
      font-size: 24px;
      color: #333;
      margin: 10px 0 20px;
      text-align: center;
      font-weight: 600;
      background: linear-gradient(to right, #FF69B4, #B19CD9);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-fill-color: transparent;
      animation: fadeInUp 0.8s ease-out;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .activity-log {
      background: white;
      border-radius: 15px;
      padding: 20px;
      margin: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .activity-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .activity-item {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 10px;
      transition: transform 0.2s ease;
    }

    .activity-item:hover {
      transform: translateX(5px);
    }

    .activity-icon {
      font-size: 1.5em;
      min-width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #FF69B4, #9370DB);
      color: white;
      border-radius: 50%;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .activity-content {
      flex: 1;
    }

    .activity-details {
      color: #333;
      line-height: 1.5;
    }

    .activity-details strong {
      color: #FF69B4;
    }

    .activity-error,
    .activity-empty {
      color: #666;
      text-align: center;
      width: 100%;
    }

    .categories-overview {
      margin-bottom: 20px;
      margin-top: 100px;
    }

    .categories {
      display: flex;
      gap: 25px;
      overflow-x: auto;
      white-space: nowrap;
      padding: 15px 0;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .categories::-webkit-scrollbar {
      display: none;
    }

    .category {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      flex: 0 0 auto;
      text-align: center;
      min-width: 280px;
      box-shadow: 0 3px 8px rgba(138, 43, 226, 0.15);
    }

    .category h4 {
      font-size: 20px;
      margin-bottom: 15px;
      color: #333;
    }

    .category p {
      font-size: 16px;
      color: #666;
      margin: 8px 0;
    }

    .category p:first-of-type {
      font-size: 18px;
      font-weight: 600;
      color: #8A2BE2;
    }

    .categories-stats {
      background: white;
      border-radius: 10px;
      padding: 20px;
      margin: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .categories-stats h3 {
      color: #333;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #FF69B4;
      font-size: 1.2em;
      text-align: center;
    }

    .category-chart-container {
      height: 500px;
      position: relative;
      margin: 20px auto;
      max-width: 800px;
      padding: 20px;
    }

    .trends-dashboard {
      padding: 20px;
      margin: 20px;
    }

    .trends-dashboard h3 {
      color: #333;
      margin-bottom: 25px;
      text-align: center;
      font-size: 1.5em;
      font-weight: 600;
    }

    .insights-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 25px;
      margin-top: 20px;
    }

    .insight-card {
      background: white;
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .insight-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }

    .insight-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .insight-header h4 {
      font-size: 1.1em;
      font-weight: 600;
      color: #333;
      margin: 0;
    }

    .insight-actions {
      display: flex;
      gap: 8px;
    }

    .filter-btn {
      padding: 6px 12px;
      border: 1px solid #e0e0e0;
      border-radius: 20px;
      background: white;
      color: #666;
      font-size: 0.8em;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .filter-btn.active {
      background: #FF69B4;
      color: white;
      border-color: #FF69B4;
    }

    .products-showcase {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .showcase-item {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 10px;
      transition: transform 0.2s ease;
    }

    .showcase-item:hover {
      transform: scale(1.02);
    }

    .product-rank {
      font-size: 1.2em;
      font-weight: 700;
      color: #FF69B4;
      min-width: 30px;
    }

    .product-details {
      flex: 1;
    }

    .product-details h5 {
      margin: 0 0 8px 0;
      font-size: 1em;
      color: #333;
    }

    .product-meta {
      display: flex;
      gap: 10px;
      margin-bottom: 8px;
    }

    .category-tag {
      background: #FFE2EC;
      color: #FF69B4;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.8em;
    }

    .price-tag {
      color: #666;
      font-size: 0.9em;
    }

    .stock-progress {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .progress-bar {
      flex: 1;
      height: 6px;
      background: #e0e0e0;
      border-radius: 3px;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      background: #FF69B4;
      border-radius: 3px;
      transition: width 0.3s ease;
    }

    .stock-count {
      font-size: 0.8em;
      color: #666;
      min-width: 80px;
      text-align: right;
    }

    .alerts-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 20px;
    }

    .restock-button-container {
      display: flex;
      justify-content: center;
      margin-top: 35px;
      margin-bottom: 10px;
    }

    .restock-main-button {
      background: linear-gradient(135deg, #FF69B4, #B19CD9);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 25px;
      cursor: pointer;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(177, 156, 217, 0.3);
    }

    .restock-main-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(177, 156, 217, 0.5);
      background: linear-gradient(135deg, #FF5CAD, #A896D3);
    }

    .restock-main-button:active {
      transform: translateY(0);
      box-shadow: 0 3px 8px rgba(177, 156, 217, 0.3);
    }

    .restock-main-button::after {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: rgba(255, 255, 255, 0.15);
      transform: rotate(45deg);
      transition: all 0.6s cubic-bezier(0.19, 1, 0.22, 1);
      opacity: 0;
    }

    .restock-main-button:hover::after {
      opacity: 1;
      transform: rotate(45deg) translate(0, 50%);
    }

    .alert-item {
      background: #FFF5F5;
      border-radius: 10px;
      padding: 12px;
    }

    .alert-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }

    .alert-info h5 {
      margin: 0 0 4px 0;
      font-size: 0.9em;
      color: #333;
    }

    .stock-level {
      font-size: 0.8em;
      color: #FF4444;
    }

    .restock-action {
      padding: 6px 12px;
      background: #FF4444;
      color: white;
      border: none;
      border-radius: 15px;
      font-size: 0.8em;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .restock-action:hover {
      background: #FF0000;
    }

    .progress-bar.danger .progress-fill {
      background: #FF4444;
    }

    .empty-state {
      text-align: center;
      padding: 30px;
      color: #666;
    }

    .empty-icon {
      font-size: 2em;
      margin-bottom: 10px;
      display: block;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
    }

    .stat-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 10px;
    }

    .stat-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2em;
    }

    .stat-info h5 {
      margin: 0;
      font-size: 0.8em;
      color: #666;
    }

    .stat-value {
      font-size: 1.1em;
      font-weight: 600;
      color: #333;
    }

    .alert-count {
      background: #FF4444;
      color: white;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.8em;
    }

    .sales-tag {
      background: #E1BEE7;
      color: #7B1FA2;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.8em;
      margin-left: 8px;
    }

    .performance-metrics {
      display: flex;
      gap: 20px;
      padding: 20px;
      overflow-x: auto;
      scroll-behavior: smooth;
    }

    .metric-card {
      background: white;
      border-radius: 15px;
      padding: 20px;
      height: 350px;
      box-shadow: 0 4px 6px rgba(138, 43, 226, 0.1);
      transition: transform 0.3s ease;
      overflow-y: auto;
    }

    .metric-card:hover {
      transform: translateY(-5px);
    }

    .metric-icon {
      font-size: 2em;
      margin-bottom: 15px;
      color: #8A2BE2;
    }

    .metric-content h4 {
      font-size: 1.2em;
      margin-bottom: 15px;
      color: #333;
      padding-top: 5px;
    }

    .performance-details {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .performance-item {
      border-left: 3px solid #8A2BE2;
      padding-left: 10px;
    }

    .label {
      color: #666;
      font-size: 0.9em;
      display: block;
    }

    .value {
      color: #333;
      font-weight: 600;
      font-size: 1.1em;
      display: block;
      margin: 5px 0;
    }

    .trend {
      font-size: 0.9em;
      padding: 3px 8px;
      border-radius: 12px;
      display: inline-block;
    }

    .trend.positive {
      background: #E8F5E9;
      color: #2E7D32;
    }

    .satisfaction-score {
      text-align: center;
    }

    .score {
      font-size: 2.5em;
      font-weight: 700;
      color: #8A2BE2;
    }

    .score span {
      font-size: 0.5em;
      color: #666;
    }

    .reviews-count {
      color: #666;
      font-size: 0.9em;
      margin-top: 5px;
    }

    .trend-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .trend-item {
      margin-bottom: 10px;
    }

    .trend-label {
      display: block;
      margin-bottom: 5px;
      color: #333;
      font-size: 0.9em;
    }

    .trend-bar {
      height: 8px;
      background: #F0E6FF;
      border-radius: 4px;
      overflow: hidden;
    }

    .trend-progress {
      height: 100%;
      background: linear-gradient(90deg, #8A2BE2, #9370DB);
      border-radius: 4px;
      transition: width 1s ease;
    }

    .satisfaction-overview {
      padding: 20px;
      margin-bottom: 20px;
    }

    .satisfaction-overview .metric-card {
      max-width: 400px;
      margin: 0 auto;
    }

    .goals-metrics {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      padding: 20px;
    }

    .metric-card.monthly-goal {
      display: flex;
      flex-direction: column;
    }

    .progress-circle {
      position: relative;
      width: 150px;
      height: 150px;
    }

    .circular-chart {
      width: 100%;
      height: 100%;
    }

    .circle-bg {
      fill: none;
      stroke: #f0e6ff;
      stroke-width: 3;
    }

    .circle {
      fill: none;
      stroke: url(#gradient);
      stroke-width: 3;
      stroke-linecap: round;
      animation: progress 1s ease-out forwards;
    }

    @keyframes progress {
      0% {
        stroke-dasharray: 0 100;
      }
    }

    .percentage {
      font-size: 1.8em;
      font-weight: 600;
      color: #8A2BE2;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    .goal-progress {
      display: flex;
      align-items: center;
      gap: 30px;
      margin-top: 30px;
      flex: 1;
    }

    .goal-details p {
      margin: 12px 0;
      font-size: 1.2em;
      color: #333;
    }

    .achievements-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 15px;
      padding-right: 10px;
    }

    .achievement-item {
      padding: 15px;
      background: #f8f9fa;
      border-radius: 8px;
      margin-bottom: 10px;
    }

    .achievement-info {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .achievement-title {
      font-weight: 600;
      color: #333;
    }

    .achievement-progress {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .progress-bar {
      flex: 1;
      height: 8px;
      background: #e0e0e0;
      border-radius: 4px;
      overflow: hidden;
    }

    .progress {
      height: 100%;
      background: linear-gradient(90deg, #FF69B4, #9370DB);
      border-radius: 4px;
      transition: width 0.3s ease;
    }

    .achievement-progress span {
      font-size: 0.9em;
      color: #666;
      white-space: nowrap;
    }

    .completed .achievement-progress span {
      color: #8A2BE2;
      font-weight: 600;
    }

    .percentage {
      font-size: 1.5em;
    }

    /* Styles pour le modal des promotions */
    .modal-content {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      max-width: 500px;
      width: 90%;
      margin: 20px auto;
    }

    .modal-header {
      padding: 20px;
      border-bottom: 1px solid #eee;
    }

    .modal-header h3 {
      margin: 0;
      color: #333;
    }

    .modal-body {
      padding: 20px;
    }

    .modal-footer {
      padding: 20px;
      border-top: 1px solid #eee;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .close-btn,
    .save-btn {
      padding: 10px 20px;
      border-radius: 5px;
      border: none;
      cursor: pointer;
      font-weight: 500;
    }

    .close-btn {
      background-color: #f44336;
      color: white;
    }

    .save-btn {
      background-color: #4CAF50;
      color: white;
    }

    .close-btn:hover {
      background-color: #d32f2f;
    }

    .save-btn:hover {
      background-color: #388E3C;
    }

    .photo-preview {
      margin-top: 10px;
      text-align: center;
    }

    .photo-preview img {
      max-width: 200px;
      border-radius: 4px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

    .reviews-stats {
      margin: 20px 0;
      display: flex;
      gap: 20px;
    }

    .stat-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 14px;
    }

    .stat-badge.approved {
      background-color: #E8F5E9;
      color: #2E7D32;
    }

    .stat-badge.rejected {
      background-color: #FFEBEE;
      color: #C62828;
    }

    .search-filter-container {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .filter-select {
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      background-color: white;
      font-size: 14px;
      color: #333;
      cursor: pointer;
      min-width: 150px;
    }

    .filter-select:focus {
      outline: none;
      border-color: #FF69B4;
    }

    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      margin-top: 20px;
      padding: 10px;
      scroll-behavior: smooth;
    }

    .pagination-btn {
      padding: 8px 16px;
      background-color: #FF69B4;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s ease;
      font-size: 18px;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .pagination-btn:hover {
      background-color: #FF1493;
    }

    .page-info {
      color: #666;
      font-size: 16px;
      font-weight: 500;
      padding: 8px 16px;
      background-color: #f8f9fa;
      border-radius: 4px;
      min-width: 120px;
      text-align: center;
    }

    .pagination-btn:disabled {
      background-color: #ccc;
      cursor: not-allowed;
    }

    .pending-orders-notification {
      position: fixed;
      right: 20px;
      top: 80%;
      /* Changé de 70% à 80% pour descendre la notification plus bas */
      transform: translateY(-50%);
      background: linear-gradient(135deg, #FF69B4, #B19CD9);
      /* Changed gradient endpoint to pastel purple */
      border-radius: 12px;
      padding: 15px;
      /* Réduit de 20px à 15px */
      display: flex;
      align-items: center;
      gap: 12px;
      /* Réduit de 15px à 12px */
      box-shadow: 0 4px 15px rgba(177, 156, 217, 0.3);
      /* Pastel purple shadow */
      z-index: 1000;
      animation: slideIn 0.5s ease-in-out;
      max-width: 280px;
      /* Réduit de 300px à 280px */
      border: none;
    }

    .notification-icon {
      font-size: 24px;
      /* Réduit de 28px à 24px */
      flex-shrink: 0;
      color: white;
      animation: ring 2s infinite;
    }

    .notification-text {
      color: white;
      font-weight: 600;
      font-size: 14px;
      /* Réduit de 16px à 14px */
      font-family: 'Inter', sans-serif;
    }

    .notification-close {
      position: absolute;
      top: 8px;
      /* Réduit de 10px à 8px */
      right: 8px;
      /* Réduit de 10px à 8px */
      background: rgba(255, 255, 255, 0.2);
      border: none;
      width: 20px;
      /* Réduit de 24px à 20px */
      height: 20px;
      /* Réduit de 24px à 20px */
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: white;
      font-size: 14px;
      /* Réduit de 16px à 14px */
      padding: 0;
      transition: background-color 0.3s ease;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translate(100%, -50%);
      }

      to {
        opacity: 1;
        transform: translate(0, -50%);
      }
    }

    @keyframes ring {
      0% {
        transform: rotate(0deg);
      }

      5% {
        transform: rotate(15deg);
      }

      10% {
        transform: rotate(-15deg);
      }

      15% {
        transform: rotate(15deg);
      }

      20% {
        transform: rotate(-15deg);
      }

      25% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(0deg);
      }
    }

    .alert-btn:hover {
      background: #9F84CF;
      /* Slightly darker pastel purple on hover */
    }

    .export-container {
      display: flex;
      justify-content: flex-end;
      margin: 0 20px 10px;
      width: auto;
    }

    .export-excel-btn {
      background: linear-gradient(135deg, #FF69B4, #B19CD9) !important;
      color: white;
      border: none;
      padding: 5px 10px;
      border-radius: 25px;
      font-size: 12px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      box-shadow: 0 4px 10px rgba(177, 156, 217, 0.3);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .export-excel-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(177, 156, 217, 0.5);
      background: linear-gradient(135deg, #FF5CAD, #A896D3);
    }

    .export-excel-btn:active {
      transform: translateY(0);
      box-shadow: 0 3px 8px rgba(177, 156, 217, 0.3);
    }

    .export-excel-btn::after {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: rgba(255, 255, 255, 0.15);
      transform: rotate(45deg);
      transition: all 0.6s cubic-bezier(0.19, 1, 0.22, 1);
      opacity: 0;
    }

    .export-excel-btn:hover::after {
      opacity: 1;
      transform: rotate(45deg) translate(0, 50%);
    }

    .export-container-centered {
      display: flex;
      justify-content: center;
      margin: 0 auto 15px;
      width: 100%;
    }

    .title-export-container {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .title-export-container h2 {
      margin: 0;
    }

    .title-export-container .export-excel-btn {
      font-size: 12px;
      padding: 5px 10px;
    }

    .title-container {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
    }

    .export-left-container {
      display: flex;
      margin-top: 5px;
    }

    .activity-log {
      background: white;
      border-radius: 15px;
      padding: 20px;
      margin: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

        .profile-container {
          display: flex;
          align-items: center;
          gap: 20px;
          padding-left: 25px;
          border-left: 1px solid rgba(151, 104, 209, 0.2);
        }

    .export-container {
      display: flex;
      margin-right: 10px;
    }

    /* Cette définition est utilisée dans le profil et doit être ajustée */
    .export-excel-btn {
      background: linear-gradient(135deg, #FF69B4, #B19CD9) !important;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 25px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      box-shadow: 0 4px 10px rgba(177, 156, 217, 0.3);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    /* Style pour la navigation des modules */
    .modules-nav {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin: 15px auto 25px;
      max-width: 90%;
      padding: 5px;
      background: #f3eeff;
      overflow-x: auto;
      white-space: nowrap;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .module-btn {
      padding: 10px 28px;
      background: #fff;
      color: #4a4a4a;
      border-radius: 30px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
      border: 1px solid #e0e0e0;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
      font-size: 15px;
    }

    .module-btn.active {
      background: #e859c0;
      color: white;
      border: 1px solid #e859c0;
      box-shadow: none;
    }

    .module-btn:hover:not(.active) {
      background: #f9f9f9;
      border-color: #d0d0d0;
    }

    @media (max-width: 768px) {
      .modules-nav {
        gap: 8px;
        max-width: 100%;
        padding: 5px 10px;
      }

      .module-btn {
        padding: 8px 15px;
        font-size: 13px;
      }
    }
  </style>
  <!-- Ajout d'un style supplémentaire avec !important pour forcer les changements -->
  <style>
    /* Styles forcés pour les boutons */
    .btn.edit-button,
    button.edit-button,
    .btn.edit-button:focus,
    .edit-button {
      background-color: #BCA9E0 !important;
      color: white !important;
    }

    .btn.edit-button:hover,
    button.edit-button:hover,
    .edit-button:hover {
      background-color: #A896D3 !important;
    }

    .btn.delete-button,
    button.delete-button,
    .btn.delete-button:focus,
    .delete-button {
      background-color: #FF8C94 !important;
      color: white !important;
    }

    .btn.delete-button:hover,
    button.delete-button:hover,
    .delete-button:hover {
      background-color: #FF7680 !important;
    }

    .dashboard button:not(.delete-button):not(.close-btn),
    .dashboard .btn:not(.delete-button):not(.close-btn) {
      background-color: #BCA9E0 !important;
    }

    .dashboard button:not(.delete-button):not(.close-btn):hover,
    .dashboard .btn:not(.delete-button):not(.close-btn):hover {
      background-color: #A896D3 !important;
    }
  </style>
  <style>
    /* Style pour les modules dans l'en-tête */
    .header {
          background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.98));
          padding: 5px 30px;
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

    .header-modules {
      display: flex;
      gap: 25px;
    }

    .header-module {
      font-size: 16px;
      color: #666;
      cursor: pointer;
      padding: 5px 0;
      position: relative;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    .header-module.active {
      color: #e859c0;
    }

    .header-module:hover:not(.active) {
      color: #333;
    }

    .header-module.active::after {
      content: "";
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 2px;
      background-color: #e859c0;
      border-radius: 2px;
    }
  </style>
  <style>
    /* Autres styles qui ne sont pas liés à modules-text */
    .modules-nav {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin: 15px auto 25px;
      max-width: 90%;
      padding: 5px;
      background: #f3eeff;
      overflow-x: auto;
      white-space: nowrap;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .module-btn {
      padding: 10px 28px;
      background: #fff;
      color: #4a4a4a;
      border-radius: 30px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
      border: 1px solid #e0e0e0;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
      font-size: 15px;
    }

    .module-btn.active {
      background: #e859c0;
      color: white;
      border: 1px solid #e859c0;
      box-shadow: none;
    }

    .module-btn:hover:not(.active) {
      background: #f9f9f9;
      border-color: #d0d0d0;
    }

    @media (max-width: 768px) {
      .modules-nav {
        gap: 8px;
        max-width: 100%;
        padding: 5px 10px;
      }

      .module-btn {
        padding: 8px 15px;
        font-size: 13px;
      }
    }
  </style>
  <style>
    /* Suppression des styles inutilisés */

    /* Styles pour la nouvelle navbar */
    .navbar-backoffice-wrapper {
      display: flex;
      justify-content: center;
      width: 100%;
    }

    .navbar-backoffice {
      padding: 10px 0;
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
    }

    .navbar-backoffice a {
      transition: color 0.3s ease;
      position: relative;
    }

    .navbar-backoffice a:hover {
      color: #e859c0 !important;
    }

    .navbar-backoffice a::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 0;
      height: 2px;
      background-color: #e859c0;
      transition: width 0.3s ease;
    }

    .navbar-backoffice a:hover::after {
      width: 100%;
    }
  </style>
  <style>
    /* Styles pour le profil dans la navbar */
    .profile-container-navbar {
      position: relative;
      cursor: pointer;
      margin-right: 20px;
    }

    .profile-container-navbar img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid white;
      transition: all 0.3s ease;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .profile-dropdown {
      position: absolute;
      top: 50px;
      right: 0;
      width: 220px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      padding: 15px;
      display: none;
      z-index: 1000;
      animation: fadeIn 0.3s ease;
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
      background: linear-gradient(135deg, #FF69B4, #B19CD9);
      color: white;
      border: none;
      border-radius: 12px;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
      box-shadow: 0 4px 15px rgba(177, 156, 217, 0.15);
    }

    .logout-btn:hover {
      background: linear-gradient(135deg, #FF5CAD, #A896D3);
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(255, 105, 180, 0.25);
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
  </style>
  <style>
    /* Style pour le header de section avec titre et barre de recherche côte à côte */
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin: 20px 15px;
      padding: 10px 0;
    }

    .section-header h2 {
      margin: 0;
      color: #333;
    }

    /* Style pour le bouton Exporter Excel */
    .export-button-container {
      margin-top: 10px;
    }

    .export-excel-btn {
      background: linear-gradient(135deg, #B19CD9, #9370DB);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 500;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      text-decoration: none;
      font-size: 14px;
      display: inline-block;
    }

    .export-excel-btn:hover {
      background: linear-gradient(135deg, #a08bc8, #8a63d7);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Style pour la combinaison barre de recherche + bouton */
    .search-and-add {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* Style pour l'affichage du titre et bouton export */
    .title-with-export {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .section-header .title-container {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .section-header .title-actions {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .section-header .export-left-container {
      margin-left: 15px;
    }

    .search-container {
      min-width: 250px;
    }

    .search-container .search {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 20px;
      font-size: 14px;
      outline: none;
      transition: border-color 0.3s;
    }

    .search-container .search:focus {
      border-color: #e859c0;
      box-shadow: 0 0 0 2px rgba(232, 89, 192, 0.2);
    }

    /* Amélioration du style pour le bouton Ajouter */
    .add-product-btn {
      background: linear-gradient(135deg, #B19CD9, #9370DB);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 500;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      white-space: nowrap;
      font-size: 14px;
      min-width: 100px;
      text-align: center;
    }

    .add-product-btn:hover {
      background: linear-gradient(135deg, #a08bc8, #8a63d7);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
  </style>
  <style>
    /* Styles pour les titres spécifiques - Couleur exacte #663399 (violet plus foncé) */
    /* Ciblage précis des titres demandés */
    .section-header h2,
    /* "Gestion des Produits", "Gestion des Commandes", "Gestion des Avis", "Statistiques" */
    .categories-overview h3,
    /* "Objectifs et Réalisations" */
    .trends-dashboard h3,
    /* "Aperçu des Produits" */
    #statistics .section-header h2,
    /* "Statistiques" */
    #products .section-header h2,
    /* "Gestion des Produits" */
    #orders .section-header h2,
    /* "Gestion des Commandes" */
    #reviews .section-header h2,
    /* "Gestion des Avis" */
    .product-cards h2,
    /* Titre dans la section produits */
    .activity-log h3

    /* Titre "Activités Récentes" */
      {
      color: #663399 !important;
      /* Couleur exacte #663399 comme demandé */
    }

    /* Exceptions pour les titres qui doivent rester avec leur couleur d'origine */
    .card-details h3,
    .orders-section h2,
    .modal-header h3,
    .product-details h3,
    .form-group label,
    .activity-item h3,
    .reviews-stats h3,
    .stats-grid h5,
    .stat-card h3,
    .achievement-item h5,
    .review-item h3,
    .order-card h3,
    .achievement-info h3,
    .stat-info h5,
    #productForm label,
    .modal h3 {
      color: inherit !important;
    }
  </style>
  <style>
    .reviews-stats {
      margin: 20px 0;
      display: flex;
      gap: 20px;
    }

    .stat-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 14px;
    }

    .stat-badge.approved {
      background-color: #E8F5E9;
      color: #2E7D32;
    }

    .stat-badge.rejected {
      background-color: #FFEBEE;
      color: #C62828;
    }

    /* Style pour le tableau des avis */
    .reviews-table {
      margin: 25px 20px;
      background: white;
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      overflow: hidden;
    }

    .reviews-table h3 {
      color: #663399;
      margin-bottom: 15px;
      font-size: 18px;
      font-weight: 600;
    }

    .reviews-stats {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 25px;
    }

    .reviews-table thead {
      background: linear-gradient(135deg, #FF69B4, #B19CD9);
      color: white;
    }

    .reviews-table th {
      padding: 15px;
      text-align: left;
      font-weight: 600;
      font-size: 15px;
    }

    .reviews-table td {
      padding: 12px 15px;
      border-bottom: 1px solid #f0f0f0;
    }

    .reviews-table tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .reviews-table tbody tr:hover {
      background-color: #f2f2f2;
    }

    .reviews-table .action-cell {
      text-align: center;
    }
  </style>
  <style>
    /* Style pour le message "Aucun avis disponible" */
    .reviews-table tbody tr td[colspan="5"] {
      text-align: center;
      padding: 30px;
      color: #888;
      font-style: italic;
    }

    /* Styles pour la barre de recherche et le filtre */
    .search-filter-container {
      display: flex;
      gap: 15px;
      margin-top: 10px;
    }

    .search {
      flex: 1;
      border: 1px solid #e0e0e0;
      border-radius: 25px;
      padding: 10px 15px;
      font-size: 14px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      outline: none;
    }

    .search:focus {
      border-color: #BFA2F7;
      box-shadow: 0 2px 5px rgba(191, 162, 247, 0.2);
    }

    .filter-select {
      min-width: 180px;
      border: 1px solid #e0e0e0;
      border-radius: 25px;
      padding: 10px 15px;
      font-size: 14px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      outline: none;
      cursor: pointer;
      background-color: white;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 16px;
      padding-right: 35px;
    }

    .filter-select:focus {
      border-color: #BFA2F7;
      box-shadow: 0 2px 5px rgba(191, 162, 247, 0.2);
    }

    /* Style pour le header de la section */
    .section-header {
      margin-top: 100px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }

    .section-header h2 {
      color: #663399;
      font-size: 22px;
      font-weight: 700;
      margin: 0;
    }
  </style>
  <style>
    /* Style pour le logo dans la sidebar - plus grand */
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
  </style>
  <style>
    /* Style pour remonter les items du menu */
    .sidebar>div:first-child {
      position: relative;
      top: 0px;
      padding-top: 10px;
    }
  </style>
  <style>
    /* Style pour espacer les boutons du bas */
    .sidebar>div:last-child {
      margin-top: 0px;
    }

    /* Style pour rapprocher les 2 derniers items */
    .sidebar>div:nth-last-child(2) {
      margin-bottom: -10px;
    }
  </style>
  <style>
    .menu-item.logout {
      margin-top: 15px !important;
    }
  </style>
  <style>
    /* ... autres styles ... */
    .menu-item.settings {
      margin-top: 5px !important;
      margin-bottom: 5px !important;
    }

    .menu-item.logout {
      margin-top: 5px !important;
    }
  </style>
</head>

<body>
  <?php if (isset($_SESSION['promo_message'])): ?>
    <script>
      alert('<?= $_SESSION['promo_message'] ?>');
    </script>
    <?php unset($_SESSION['promo_message']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['promo_error'])): ?>
    <script>
      alert('<?= $_SESSION['promo_error'] ?>');
    </script>
    <?php unset($_SESSION['promo_error']); ?>
  <?php endif; ?>

  <div class="sidebar">
    <div>
      <img src="logo.png" alt="Logo" class="logo">

      <div class="menu-item <?= $active_section === 'overview' ? 'active' : '' ?>" data-section="overview" onclick="window.location.href='?section=overview'"><span class="icon">🏠</span> Vue Générale</div>
      <div class="menu-item <?= $active_section === 'statistics' ? 'active' : '' ?>" data-section="statistics" onclick="window.location.href='?section=statistics'"><span class="icon">📊</span> Statistiques</div>
      <div class="menu-item <?= $active_section === 'products' ? 'active' : '' ?>" data-section="products" onclick="window.location.href='?section=products'"><span class="icon">📦</span> Produits</div>
      <div class="menu-item <?= $active_section === 'orders' ? 'active' : '' ?>" data-section="orders" onclick="window.location.href='?section=orders'"><span class="icon">📋</span> Commandes</div>
      <div class="menu-item <?= $active_section === 'reviews' ? 'active' : '' ?>" data-section="reviews" onclick="window.location.href='?section=reviews'">
        <span class="icon">⭐</span> Avis
        <?php
        $pendingReviews = $avisController->getPendingReviewsCount();
        $pendingCount = $pendingReviews['success'] ? $pendingReviews['pending_count'] : 0;
        if ($pendingCount > 0):
        ?>
          <span class="notification-badge"><?php echo $pendingCount; ?></span>
        <?php endif; ?>
      </div>
    </div>
    <div>
      <div class="menu-item logout"><span class="icon">🚪</span> Déconnexion</div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Récupérer la section active depuis l'URL
      const urlParams = new URLSearchParams(window.location.search);
      let section = urlParams.get('section') || 'overview';

      // Si nous sommes dans la section orders, conserver les paramètres de pagination
      if (section === 'orders') {
        const purchasePage = urlParams.get('purchase_page') || '1';
        const rentalPage = urlParams.get('rental_page') || '1';

        // Mettre à jour l'URL avec les paramètres de pagination
        const url = new URL(window.location);
        url.searchParams.set('purchase_page', purchasePage);
        url.searchParams.set('rental_page', rentalPage);
        window.history.replaceState({}, '', url);
      }

      // Désactiver toutes les sections
      document.querySelectorAll('.dashboard-section').forEach(section => {
        section.classList.remove('active');
      });

      // Désactiver tous les éléments du menu
      document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
      });

      // Activer la section correspondante
      document.getElementById(section).classList.add('active');

      // Activer l'élément du menu correspondant
      document.querySelector(`[data-section="${section}"]`).classList.add('active');

      if (section === 'orders') {
        // Afficher la notification si elle existe
        const notification = document.getElementById('ordersNotification');
        if (notification) {
          setTimeout(() => {
            notification.style.opacity = '1';
          }, 100);
        }
      }

      // Gestion des boutons de module
      const moduleButtons = document.querySelectorAll('.module-btn');
      moduleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();

          // Désactiver tous les boutons
          moduleButtons.forEach(btn => btn.classList.remove('active'));

          // Activer le bouton cliqué
          this.classList.add('active');

          // Afficher une notification de fonctionnalité 
          const moduleName = this.textContent;
          alert(`Module "${moduleName}" sélectionné. Cette fonctionnalité sera disponible prochainement.`);
        });
      });
    });
  </script>

  <div class="dashboard">
    <!-- Overview Section (Tableau de Bord) -->
    <div class="dashboard-section <?= $active_section === 'overview' ? 'active' : '' ?>" id="overview">
      <div class="header">
        <div class="navbar-backoffice-wrapper">
          <nav class="navbar-backoffice">
            <ul style="display:flex;gap:40px;list-style:none;margin:0;padding:0;">
            <li><a href="/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link">Utilisateurs</a></li>
            <li><a href="/mvcact/view/back%20office/dashboard.php" class="nav-link">Activités</a></li>
            <li><a href="/mvcEvent/View/BackOffice/dashboard.php" class="nav-link">Événements</a></li>
            <li><a href="/mvcProduit/view/back%20office/indeex.php" class="nav-link active">Produits</a></li>
            <li><a href="/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link">Transports</a></li>
            <li><a href="/mvcSponsor/crud/view/back/back.php" class="nav-link">Sponsors</a></li>
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
                    <img src="/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
                <?php else: ?>
                    <div class="profile-circle" style="background-color: <?= stringToColor($fullName) ?>;" onclick="toggleDropdown()">
                        <?= strtoupper(substr($fullName, 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="/mvcUtilisateur/View/FrontOffice/profile.php">👤 Mon Profil</a>
                    <a href="/mvcUtilisateur/View/BackOffice/login/logout.php">🚪 Déconnexion</a>
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
          
                .user-profile:hover .dropdown-menu {
                  display: block;
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
            display: none;
            position: absolute;
            top: 55px;
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-width: 160px;
            overflow: hidden;
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

          /* Style pour le menu déroulant */
          .dropdown-menu {
            display: none;
            position: absolute;
            top: 60px;
            right: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 10px;
            z-index: 1000;
          }

          .dropdown-menu a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
          }

          .dropdown-menu a:hover {
            background-color: #f9f9f9;
          }
        </style>

            </ul>
          </nav>
        </div>

      </div>

      <div class="categories-overview">
        <h2 class="section-title">Suivez vos produits, boostez vos ventes ! ✨</h2>
        <h3>Objectifs et Réalisations</h3>
        <div class="goals-metrics">
          <div class="metric-card monthly-goal">
            <div class="metric-icon">🎯</div>
            <div class="metric-content">
              <h4>Objectif Mensuel</h4>
              <div class="goal-progress">
                <div class="progress-circle">
                  <svg viewBox="0 0 36 36" class="circular-chart">
                    <defs>
                      <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#FF69B4" />
                        <stop offset="100%" style="stop-color:#9370DB" />
                      </linearGradient>
                    </defs>
                    <path class="circle-bg" d="M18 2.0845
                      a 15.9155 15.9155 0 0 1 0 31.831
                      a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                    <path class="circle" stroke-dasharray="<?= ($globalStats['total_value'] / 200000) * 100 ?>, 100" d="M18 2.0845
                      a 15.9155 15.9155 0 0 1 0 31.831
                      a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                  </svg>
                  <div class="percentage"><?= round(($globalStats['total_value'] / 200000) * 100) ?>%</div>
                </div>
                <div class="goal-details">
                  <p>Objectif: 200,000 TND</p>
                  <p>Réalisé: <?= number_format($globalStats['total_value'], 2) ?> TND</p>
                </div>
              </div>
            </div>
          </div>

          <div class="metric-card achievements">
            <div class="metric-icon">🌟</div>
            <div class="metric-content">
              <h4>Réalisations par Catégorie</h4>
              <div class="achievements-list">
                <?php foreach ($stats as $stat):
                  $targetProducts = [
                    'Équipements Sportifs' => 15,
                    'Vêtements et Accessoires' => 12,
                    'Gadgets & Technologies' => 10,
                    'Articles de Bien-être' => 8,
                    'Nutrition & Hydratation' => 8,
                    'Accessoires de Voyage' => 6,
                    'Supports d\'atelier' => 5,
                    'Univers du cerveau' => 5
                  ];
                  $target = $targetProducts[$stat['category']] ?? 5;
                  $progress = ($stat['product_count'] / $target) * 100;
                  $isCompleted = $stat['product_count'] >= $target;
                ?>
                  <div class="achievement-item <?= $isCompleted ? 'completed' : '' ?>">
                    <div class="achievement-info">
                      <span class="achievement-title"><?= htmlspecialchars($stat['category']) ?></span>
                      <div class="achievement-progress">
                        <div class="progress-bar">
                          <div class="progress" style="width: <?= min(100, $progress) ?>%"></div>
                        </div>
                        <?php if ($isCompleted): ?>
                          <span><?= $stat['product_count'] ?>/<?= $target ?> - Objectif atteint!</span>
                        <?php else: ?>
                          <span><?= $stat['product_count'] ?>/<?= $target ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="stock-alert">
        <p><span class="alert-icon">⚠️</span> <strong>Attention :</strong>
          <?= $outOfStockData['success'] ? $outOfStockData['count'] : '0' ?> produit<?= $outOfStockData['count'] !== 1 ? 's' : '' ?> en rupture de stock !</p>
        <button class="alert-btn" onclick="document.querySelector('[data-section=\'products\']').click()">Agir Maintenant</button>
      </div>

      <div class="trends-dashboard">
        <h3>Aperçu des Produits</h3>
        <div class="insights-grid">
          <!-- Top Produits -->
          <div class="insight-card featured">
            <div class="insight-header">
              <h4>⭐ Derniers avis clients</h4>
            </div>
            <div class="reviews-list">
              <?php if (empty($avis)): ?>
                <div class="empty-state">
                  <p>Aucun avis disponible</p>
                </div>
                <?php else:
                $recentAvis = array_slice($avis, 0, 5); // Prendre les 5 derniers avis
                foreach ($recentAvis as $review):
                ?>
                  <div class="review-item">
                    <div class="review-header">
                      <div class="stars"><?= str_repeat('⭐', $review['stars']) ?></div>
                      <div class="status <?= $review['status'] ?>">
                        <?= $review['status'] === 'approved' ? '✅' : ($review['status'] === 'pending' ? '⏳' : '❌') ?>
                      </div>
                    </div>
                    <div class="review-content">
                      <strong><?= htmlspecialchars($review['product_name']) ?></strong>
                      <p><?= htmlspecialchars($review['comment'] ?? 'Aucun commentaire') ?></p>
                    </div>
                  </div>
              <?php endforeach;
              endif; ?>
            </div>
          </div>

          <style>
            .reviews-list {
              display: flex;
              flex-direction: column;
              gap: 10px;
              padding: 10px;
            }

            .review-item {
              background: #f8f9fa;
              border-radius: 8px;
              padding: 12px;
              border-left: 3px solid #FF69B4;
            }

            .review-header {
              display: flex;
              justify-content: space-between;
              align-items: center;
              margin-bottom: 8px;
            }

            .review-content {
              font-size: 0.9em;
            }

            .review-content strong {
              color: #333;
              display: block;
              margin-bottom: 4px;
            }

            .review-content p {
              color: #666;
              margin: 0;
            }

            .status {
              font-size: 1.2em;
            }

            .stars {
              letter-spacing: 2px;
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
          color: #F687B3;
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

          </style>

          <!-- Alertes Stock -->
          <div class="insight-card warning">
            <div class="insight-header">
              <h4>⚠️ Alertes Stock</h4>
              <span class="alert-count"><?php
                                        $lowStockCount = count(array_filter($products, function ($p) {
                                          return $p['stock'] < 10;
                                        }));
                                        echo $lowStockCount;
                                        ?> produits</span>
            </div>
            <div class="alerts-list">
              <?php
              $lowStockProducts = array_filter($products, function ($p) {
                return $p['stock'] < 10;
              });
              $lowStockProducts = array_slice($lowStockProducts, 0, 4);

              if (empty($lowStockProducts)): ?>
                <div class="empty-state">
                  <span class="empty-icon">📦</span>
                  <p>Aucune alerte de stock</p>
                </div>
                <?php else:
                foreach ($lowStockProducts as $product): ?>
                  <div class="alert-item">
                    <div class="alert-content">
                      <div class="alert-info">
                        <h5><?= htmlspecialchars($product['name']) ?></h5>
                        <span class="stock-level critical"><?= $product['stock'] ?> restants</span>
                      </div>
                    </div>
                    <div class="alert-progress">
                      <div class="progress-bar danger">
                        <div class="progress-fill" style="width: <?= ($product['stock'] / 10) * 100 ?>%"></div>
                      </div>
                    </div>
                  </div>
              <?php endforeach;
              endif; ?>
              <div class="restock-button-container">
                <button class="restock-main-button" onclick="document.querySelector('[data-section=\'products\']').click()">Restocker</button>
              </div>
            </div>
          </div>

          <!-- Statistiques Rapides -->
          <div class="insight-card stats">
            <div class="insight-header">
              <h4>📊 Statistiques Rapides</h4>
            </div>
            <div class="stats-grid">
              <div class="stat-item">
                <div class="stat-icon" style="background-color: #FFE2EC">📦</div>
                <div class="stat-info">
                  <h5>Total Produits</h5>
                  <span class="stat-value"><?= count($products) ?></span>
                </div>
              </div>
              <div class="stat-item">
                <div class="stat-icon" style="background-color: #E2F5FF">💰</div>
                <div class="stat-info">
                  <h5>Valeur Stock</h5>
                  <span class="stat-value"><?= number_format(array_sum(array_map(function ($p) {
                                              return $p['price'] * $p['stock'];
                                            }, $products)), 2) ?> TND</span>
                </div>
              </div>
              <div class="stat-item">
                <div class="stat-icon" style="background-color: #FFE2E2">⚡</div>
                <div class="stat-info">
                  <h5>Stock Moyen</h5>
                  <span class="stat-value"><?= round(array_sum(array_column($products, 'stock')) / count($products)) ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="activity-log">
        <h3>Activités Récentes</h3>
        <div class="activity-list">
          <?php if (!$recentActivities['success']): ?>
            <div class="activity-item">
              <span class="activity-error">Erreur lors du chargement des activités récentes</span>
            </div>
          <?php elseif (empty($recentActivities['activities'])): ?>
            <div class="activity-item">
              <span class="activity-empty">Aucune activité récente</span>
            </div>
            <?php else:
            foreach ($recentActivities['activities'] as $activity):
              $date = new DateTime($activity['date']);
            ?>
              <div class="activity-item">
                <span class="activity-icon">📦</span>
                <div class="activity-content">
                  <span class="activity-details">
                    Nouveau produit ajouté : <strong><?= htmlspecialchars($activity['product_name']) ?></strong>
                    <br>
                    Stock initial : <strong><?= $activity['quantity'] ?> unités</strong>
                  </span>
                </div>
              </div>
          <?php endforeach;
          endif; ?>
        </div>
      </div>
    </div>

    <!-- Products Section -->
    <div class="dashboard-section <?= $active_section === 'products' ? 'active' : '' ?>" id="products">
      <div class="header">
        <div class="navbar-backoffice-wrapper">
          <nav class="navbar-backoffice">
            <ul style="display:flex;gap:40px;list-style:none;margin:0;padding:0;">
            <li><a href="/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link">Utilisateurs</a></li>
            <li><a href="/mvcact/view/back%20office/dashboard.php" class="nav-link">Activités</a></li>
            <li><a href="/mvcEvent/View/BackOffice/dashboard.php" class="nav-link">Événements</a></li>
            <li><a href="/mvcProduit/view/back%20office/indeex.php" class="nav-link active">Produits</a></li>
            <li><a href="/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link">Transports</a></li>
            <li><a href="/mvcSponsor/crud/view/back/back.php" class="nav-link">Sponsors</a></li>
            </ul>
          </nav>
        </div>

        <div class="profile-container">
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
                    <img src="/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
                <?php else: ?>
                    <div class="profile-circle" style="background-color: <?= stringToColor($fullName) ?>;" onclick="toggleDropdown()">
                        <?= strtoupper(substr($fullName, 0, 1)) ?>
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

      <div class="section-header">
        <h2>Gestion des Produits 📦</h2>
        <div class="search-and-add">
          <div class="search-container">
            <input class="search" type="text" placeholder="Rechercher un produit" id="productSearch">
          </div>
          <button class="add-product-btn" onclick="openProductModal('add')">+ Ajouter</button>
        </div>
      </div>

      <div class="product-cards">
        <?php if (!$response['success']): ?>
          <p class="error">Erreur lors du chargement des produits: <?= htmlspecialchars($response['error']) ?></p>
        <?php elseif (empty($products)): ?>
          <p class="no-products">Aucun produit disponible</p>
        <?php else: ?>
          <?php foreach ($products as $product):
            $stock = (int)$product['stock'];
            $stockPercentage = min(100, max(0, ($stock / 100) * 100));
            $purchaseStatus = $product['purchase_available'] ? 'Oui' : 'Non';
            $rentalStatus = $product['rental_available'] ? 'Oui' : 'Non';
            $imagePath = !empty($product['photo']) ? $product['photo'] : 'images/products/logo.png';
          ?>
            <div class="card">
              <div class="product-image">
                <img src="../../<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
              </div>
              <div class="product-details">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p>Prix: <?= number_format($product['price'], 2) ?> TND</p>
                <p>Stock: <?= $stock ?> unités</p>
                <p>Catégorie: <?= htmlspecialchars($product['category']) ?></p>
                <p>Achat: <?= $purchaseStatus ?> | Location: <?= $rentalStatus ?></p>
                <div class="progress-bar">
                  <div class="progress-bar-fill" style="width: <?= $stockPercentage ?>%;"></div>
                </div>
                <div class="card-buttons">
                  <button class="btn edit-button" style="background-color: #B19CD9 !important;" onclick="openProductModal('edit', <?= $product['id'] ?>)">Modifier</button>
                  <button class="btn delete-button" style="background-color: #F7B2D9 !important;" onclick="confirmDelete(<?= $product['id'] ?>)">Supprimer</button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="categories">
        <?php foreach ($stats as $stat): ?>
          <div class="category">
            <h4><?= htmlspecialchars($stat['category']) ?></h4>
            <p><?= $stat['product_count'] ?> produits</p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Orders Section -->
    <div class="dashboard-section <?= $active_section === 'orders' ? 'active' : '' ?>" id="orders">
      <div class="header">
        <div class="navbar-backoffice-wrapper">
          <nav class="navbar-backoffice">
            <ul style="display:flex;gap:40px;list-style:none;margin:0;padding:0;">
            <li><a href="/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link">Utilisateurs</a></li>
            <li><a href="/mvcact/view/back%20office/dashboard.php" class="nav-link">Activités</a></li>
            <li><a href="/mvcEvent/View/BackOffice/dashboard.php" class="nav-link">Événements</a></li>
            <li><a href="/mvcProduit/view/back%20office/indeex.php" class="nav-link active">Produits</a></li>
            <li><a href="/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link">Transports</a></li>
            <li><a href="/mvcSponsor/crud/view/back/back.php" class="nav-link">Sponsors</a></li>
            </ul>
          </nav>
        </div>

        <div class="profile-container">
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
                    <img src="/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
                <?php else: ?>
                    <div class="profile-circle" style="background-color: <?= stringToColor($fullName) ?>;" onclick="toggleDropdown()">
                        <?= strtoupper(substr($fullName, 0, 1)) ?>
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

      <div class="section-header">
        <div>
          <h2>Gestion des Commandes 📋</h2>
          <div class="export-button-container">
            <a href="export_orders.php" class="export-excel-btn">Exporter Excel</a>
          </div>
        </div>
        <div class="search-container">
          <input class="search" type="text" placeholder="Rechercher par ID" id="orderSearch">
        </div>
      </div>

      <div class="orders-container">
        <div class="orders-section purchase-orders">
          <h2>Commandes d'achat</h2>
          <?php
          // Compter les commandes en attente
          $pending_purchases = $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut_commande = 'en_attente'")->fetchColumn();
          $pending_rentals = $pdo->query("SELECT COUNT(*) FROM louer WHERE statut_location = 'en_attente'")->fetchColumn();
          $total_pending = $pending_purchases + $pending_rentals;

          if ($total_pending > 0): ?>
            <div class="pending-orders-notification" id="ordersNotification">
              <span class="notification-icon">🔔</span>
              <span class="notification-text">
                <?= $total_pending ?> commande<?= $total_pending > 1 ? 's' : '' ?> en attente
                <br>
                <small style="opacity: 0.8;">(<?= $pending_purchases ?> achat<?= $pending_purchases > 1 ? 's' : '' ?>, <?= $pending_rentals ?> location<?= $pending_rentals > 1 ? 's' : '' ?>)</small>
              </span>
              <button class="notification-close" onclick="document.getElementById('ordersNotification').style.display='none'">×</button>
            </div>
          <?php endif; ?>
          <div class="order-cards" id="purchaseOrders">
            <?php
            $page = isset($_GET['purchase_page']) ? (int)$_GET['purchase_page'] : 1;
            $per_page = 4;
            $offset = ($page - 1) * $per_page;

            $total_cmds = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
            $total_pages = ceil($total_cmds / $per_page);

            $cmds = $pdo->query("SELECT * FROM commandes ORDER BY date_commande ASC LIMIT $offset, $per_page")->fetchAll();

            if (empty($cmds)): ?>
              <p class="no-orders">Aucune commande d'achat disponible</p>
            <?php else: ?>
              <?php foreach ($cmds as $cmd): ?>
                <div class="order-card">
                  <div class="order-details">
                    <h3>ID Commande: <?= $cmd['id_commande'] ?></h3>
                    <p><strong>ID Utilisateur :</strong> <?= $cmd['id_user'] ?></p>
                    <p><strong>ID Produit :</strong> <?= $cmd['id_produit'] ?></p>
                    <p><strong>Quantité :</strong> <?= $cmd['quantite'] ?></p>
                    <p><strong>Date :</strong> <?= $cmd['date_commande'] ?></p>
                    <p><strong>Statut :</strong> <?= $cmd['statut_commande'] ?></p>
                  </div>
                  <div class="order-actions">
                    <form method="post" action="changer_statut.php">
                      <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                      <select name="nouveau_statut">
                        <option value="en_attente" <?= $cmd['statut_commande'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="confirmee" <?= $cmd['statut_commande'] == 'confirmee' ? 'selected' : '' ?>>Confirmée</option>
                        <option value="livree" <?= $cmd['statut_commande'] == 'livree' ? 'selected' : '' ?>>Livrée</option>
                        <option value="annulee" <?= $cmd['statut_commande'] == 'annulee' ? 'selected' : '' ?>>Annulée</option>
                      </select>
                      <button type="submit" class="btn update-button">Mettre à jour</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <?php if ($total_pages > 1): ?>
            <div class="pagination">
              <a href="?purchase_page=<?= $page - 1 ?>&rental_page=<?= isset($_GET['rental_page']) ? $_GET['rental_page'] : 1 ?>&section=orders#orders" class="pagination-btn" <?= $page <= 1 ? 'style="visibility: hidden;"' : '' ?>>←</a>
              <span class="page-info">Page <?= $page ?> sur <?= $total_pages ?></span>
              <a href="?purchase_page=<?= $page + 1 ?>&rental_page=<?= isset($_GET['rental_page']) ? $_GET['rental_page'] : 1 ?>&section=orders#orders" class="pagination-btn" <?= $page >= $total_pages ? 'style="visibility: hidden;"' : '' ?>>→</a>
            </div>
          <?php endif; ?>
        </div>

        <div class="orders-section rental-orders">
          <h2>Locations</h2>
          <div class="order-cards" id="rentalOrders">
            <?php
            $page = isset($_GET['rental_page']) ? (int)$_GET['rental_page'] : 1;
            $per_page = 4;
            $offset = ($page - 1) * $per_page;

            $total_locations = $pdo->query("SELECT COUNT(*) FROM louer")->fetchColumn();
            $total_pages = ceil($total_locations / $per_page);

            $locations = $pdo->query("SELECT * FROM louer ORDER BY date_location ASC LIMIT $offset, $per_page")->fetchAll();

            if (empty($locations)): ?>
              <p class="no-orders">Aucune location disponible</p>
            <?php else: ?>
              <?php foreach ($locations as $loc): ?>
                <div class="order-card">
                  <div class="order-details">
                    <h3>ID Location: <?= $loc['id'] ?></h3>
                    <p><strong>ID Utilisateur :</strong> <?= $loc['id_user'] ?></p>
                    <p><strong>Produit :</strong> <?= htmlspecialchars($loc['produit']) ?></p>
                    <p><strong>Date :</strong> <?= $loc['date_location'] ?></p>
                    <p><strong>Heure :</strong> <?= $loc['heure_debut'] ?> - <?= $loc['heure_fin'] ?></p>
                    <p><strong>Statut :</strong> <?= $loc['statut_location'] ?></p>
                  </div>
                  <div class="order-actions">
                    <form method="post" action="changer_statut_location.php">
                      <input type="hidden" name="id_location" value="<?= $loc['id'] ?>">
                      <select name="nouveau_statut">
                        <option value="en_attente" <?= $loc['statut_location'] == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="confirmee" <?= $loc['statut_location'] == 'confirmee' ? 'selected' : '' ?>>Confirmée</option>
                        <option value="livree" <?= $loc['statut_location'] == 'livree' ? 'selected' : '' ?>>Livrée</option>
                        <option value="annulee" <?= $loc['statut_location'] == 'annulee' ? 'selected' : '' ?>>Annulée</option>
                      </select>
                      <button type="submit" class="btn update-button">Mettre à jour</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <?php if ($total_pages > 1): ?>
            <div class="pagination">
              <a href="?rental_page=<?= $page - 1 ?>&purchase_page=<?= isset($_GET['purchase_page']) ? $_GET['purchase_page'] : 1 ?>&section=orders#orders" class="pagination-btn" <?= $page <= 1 ? 'style="visibility: hidden;"' : '' ?>>←</a>
              <span class="page-info">Page <?= $page ?> sur <?= $total_pages ?></span>
              <a href="?rental_page=<?= $page + 1 ?>&purchase_page=<?= isset($_GET['purchase_page']) ? $_GET['purchase_page'] : 1 ?>&section=orders#orders" class="pagination-btn" <?= $page >= $total_pages ? 'style="visibility: hidden;"' : '' ?>>→</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>


    <!-- Reviews Section -->
    <div class="dashboard-section <?= $active_section === 'reviews' ? 'active' : '' ?>" id="reviews">
      <div class="header">
        <div class="navbar-backoffice-wrapper">
          <nav class="navbar-backoffice">
            <ul style="display:flex;gap:40px;list-style:none;margin:0;padding:0;">
            <li><a href="/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link">Utilisateurs</a></li>
            <li><a href="/mvcact/view/back%20office/dashboard.php" class="nav-link">Activités</a></li>
            <li><a href="/mvcEvent/View/BackOffice/dashboard.php" class="nav-link">Événements</a></li>
            <li><a href="/mvcProduit/view/back%20office/indeex.php" class="nav-link active">Produits</a></li>
            <li><a href="/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link">Transports</a></li>
            <li><a href="/mvcSponsor/crud/view/back/back.php" class="nav-link">Sponsors</a></li>
            </ul>
          </nav>
        </div>

        <div class="profile-container">
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
                    <img src="/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
                <?php else: ?>
                    <div class="profile-circle" style="background-color: <?= stringToColor($fullName) ?>;" onclick="toggleDropdown()">
                        <?= strtoupper(substr($fullName, 0, 1)) ?>
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

      <div class="section-header">
        <h2>Gestion des Avis ⭐</h2>
        <div class="search-filter-container">
          <input class="search" type="text" placeholder="Rechercher un avis..." id="reviewSearch">
          <select class="filter-select" id="ratingFilter">
            <option value="">Toutes les notes</option>
            <option value="5">⭐⭐⭐⭐⭐ (5 étoiles)</option>
            <option value="4">⭐⭐⭐⭐ (4 étoiles)</option>
            <option value="3">⭐⭐⭐ (3 étoiles)</option>
            <option value="2">⭐⭐ (2 étoiles)</option>
            <option value="1">⭐ (1 étoile)</option>
          </select>
        </div>
      </div>

      <div class="reviews-table" style="margin: 25px 20px; background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
        <h3 style="color: #8624C1; margin-bottom: 15px; font-size: 20px; font-weight: 600;">Liste des Avis</h3>

        <?php
        // Compter les avis approuvés et rejetés
        $approvedCount = 0;
        $rejectedCount = 0;
        foreach ($avis as $review) {
          if ($review['status'] === 'approved') {
            $approvedCount++;
          } elseif ($review['status'] === 'rejected') {
            $rejectedCount++;
          }
        }
        ?>

        <div class="reviews-stats" style="margin: 20px 0; display: flex; gap: 15px;">
          <span class="stat-badge approved" style="padding: 8px 16px; border-radius: 30px; font-weight: 500; font-size: 14px; background-color: #E8F5E9; color: #1B5E20;"><?= $approvedCount ?> approuvés</span>
          <span class="stat-badge rejected" style="padding: 8px 16px; border-radius: 30px; font-weight: 500; font-size: 14px; background-color: #FFEBEE; color: #B71C1C;"><?= $rejectedCount ?> rejetés</span>
        </div>

        <table style="width: 100%; border-collapse: collapse; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0, 0, 0, 0.03);">
          <thead>
            <tr style="background: #8624C1;">
              <th style="padding: 15px; text-align: left; color: white; font-weight: 600; font-size: 15px;">Produit</th>
              <th style="padding: 15px; text-align: left; color: white; font-weight: 600; font-size: 15px;">Client</th>
              <th style="padding: 15px; text-align: left; color: white; font-weight: 600; font-size: 15px;">Note</th>
              <th style="padding: 15px; text-align: left; color: white; font-weight: 600; font-size: 15px;">Commentaire</th>
              <th style="padding: 15px; text-align: center; color: white; font-weight: 600; font-size: 15px;">Actions</th>
            </tr>
          </thead>
          <tbody id="reviewsTableBody">
            <?php if (empty($avis)): ?>
              <tr>
                <td colspan="5" style="text-align: center; padding: 30px; color: #888; font-style: italic;">Aucun avis disponible</td>
              </tr>
            <?php else: ?>
              <?php
              $i = 0;
              foreach ($avis as $review):
                $rowStyle = $i % 2 === 0 ? "background-color: white;" : "background-color: #f5f0fa;";
                $i++;
              ?>
                <tr style="border-bottom: 1px solid #f0f0f0; <?= $rowStyle ?>">
                  <td style="padding: 15px; font-size: 14px;"><?= htmlspecialchars($review['product_name']) ?></td>
                  <td style="padding: 15px; font-size: 14px;"><?= htmlspecialchars($review['email']) ?></td>
                  <td style="padding: 15px; font-size: 16px; color: #FFB400;"><?= str_repeat('★', $review['stars']) . str_repeat('☆', 5 - $review['stars']) ?></td>
                  <td style="padding: 15px; font-size: 14px;"><?= htmlspecialchars($review['comment'] ?? 'Aucun commentaire') ?></td>
                  <td style="padding: 15px; text-align: center;">
                    <?php if ($review['status'] === 'pending'): ?>
                      <div style="display: flex; gap: 10px; justify-content: center;">
                        <button onclick="approveReview(<?= $review['id'] ?>)" style="background: #BBA5D9; color: white; border: none; padding: 8px 16px; border-radius: 30px; font-size: 13px; cursor: pointer;">Approuver</button>
                        <button onclick="openRejectModal(<?= $review['id'] ?>)" style="background: #E0B0D7; color: white; border: none; padding: 8px 16px; border-radius: 30px; font-size: 13px; cursor: pointer;">Rejeter</button>
                      </div>
                    <?php elseif ($review['status'] === 'approved'): ?>
                      <span style="display: inline-block; padding: 6px 12px; background-color: #E8F5E9; color: #2E7D32; border-radius: 20px; font-size: 13px;">Approuvé</span>
                    <?php elseif ($review['status'] === 'rejected'): ?>
                      <span style="display: inline-block; padding: 6px 12px; background-color: #FFEBEE; color: #C62828; border-radius: 20px; font-size: 13px;">Rejeté</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <style>
        .reviews-stats {
          margin: 20px 0;
          display: flex;
          gap: 20px;
        }

        .stat-badge {
          padding: 8px 16px;
          border-radius: 20px;
          font-weight: 600;
          font-size: 14px;
        }

        .stat-badge.approved {
          background-color: #E8F5E9;
          color: #2E7D32;
        }

        .stat-badge.rejected {
          background-color: #FFEBEE;
          color: #C62828;
        }

        /* Nouveau style pour le tableau des avis */
        .reviews-table {
          margin: 25px 20px;
          background: white;
          border-radius: 15px;
          padding: 20px;
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .reviews-table h3 {
          color: #8624C1;
          margin-bottom: 15px;
          font-size: 18px;
          font-weight: 600;
        }

        .reviews-table table {
          width: 100%;
          border-collapse: separate;
          border-spacing: 0;
          margin-top: 15px;
          border-radius: 10px;
          overflow: hidden;
          box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .reviews-table thead {
          background: #8624C1;
          color: white;
        }

        .reviews-table th {
          padding: 16px 20px;
          text-align: left;
          font-weight: 600;
          font-size: 15px;
          letter-spacing: 0.5px;
          border: none;
        }

        .reviews-table td {
          padding: 15px 20px;
          border-bottom: 1px solid #f0f0f0;
          font-size: 14px;
          color: #333;
        }

        .reviews-table tbody tr:last-child td {
          border-bottom: none;
        }

        .reviews-table tbody tr {
          transition: all 0.2s ease;
        }

        .reviews-table tbody tr:nth-child(even) {
          background-color: #f5f0fa;
        }

        .reviews-table tbody tr:nth-child(odd) {
          background-color: white;
        }

        .reviews-table tbody tr:hover {
          background-color: rgba(191, 162, 247, 0.08);
          transform: translateY(-2px);
        }

        .reviews-table .action-cell {
          text-align: center;
        }

        .reviews-table .approve-btn {
          background: #BFA2F7;
          color: white;
          border: none;
          padding: 8px 15px;
          border-radius: 25px;
          font-size: 13px;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.3s ease;
          margin-right: 5px;
        }

        .reviews-table .reject-btn {
          background: #F7B2D9;
          color: white;
          border: none;
          padding: 8px 15px;
          border-radius: 25px;
          font-size: 13px;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.3s ease;
        }

        .reviews-table .approve-btn:hover {
          background: #AD8DF3;
          transform: translateY(-2px);
          box-shadow: 0 4px 8px rgba(173, 141, 243, 0.3);
        }

        .reviews-table .reject-btn:hover {
          background: #F094C3;
          transform: translateY(-2px);
          box-shadow: 0 4px 8px rgba(240, 148, 195, 0.3);
        }

        .reviews-table .status-badge {
          display: inline-block;
          padding: 6px 12px;
          border-radius: 20px;
          font-size: 13px;
          font-weight: 500;
        }

        .reviews-table .status-badge.approved {
          background-color: rgba(46, 125, 50, 0.1);
          color: #2E7D32;
          border: 1px solid rgba(46, 125, 50, 0.2);
        }

        .reviews-table .status-badge.rejected {
          background-color: rgba(198, 40, 40, 0.1);
          color: #C62828;
          border: 1px solid rgba(198, 40, 40, 0.2);
        }

        .reviews-table span {
          display: inline-block;
        }

        .reviews-table tr[data-review-id] td:nth-child(3) {
          color: #FFB400;
          font-size: 16px;
          letter-spacing: 2px;
        }

        .reviews-table tbody tr td:nth-child(1) {
          font-weight: 600;
          color: #555;
        }

        /* Style pour le message "Aucun avis disponible" */
        .reviews-table tbody tr td[colspan="5"] {
          text-align: center;
          padding: 30px;
          color: #888;
          font-style: italic;
        }
      </style>
    </div>

    <!-- Reject Reason Modal -->
    <div class="modal" id="rejectReviewModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Raison du Rejet</h3>
        </div>
        <div class="modal-body">
          <form id="rejectReviewForm">
            <input type="hidden" id="rejectReviewId" name="review_id">
            <div class="form-group">
              <label for="rejectionReason">Raison du rejet (facultatif)</label>
              <textarea id="rejectionReason" name="rejection_reason" rows="4" style="width: 100%; padding: 8px;"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="close-btn" onclick="closeRejectModal()">Annuler</button>
          <button type="button" class="save-btn" onclick="submitRejectReview()">Confirmer</button>
        </div>
      </div>
    </div>
    <!-- Settings Section -->
    <div class="dashboard-section <?= $active_section === 'settings' ? 'active' : '' ?>" id="settings">
      <div class="header">
        <div class="navbar-backoffice-wrapper">
          <nav class="navbar-backoffice">
            <ul style="display:flex;gap:40px;list-style:none;margin:0;padding:0;">
            <li><a href="/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link">Utilisateurs</a></li>
            <li><a href="/mvcact/view/back%20office/dashboard.php" class="nav-link">Activités</a></li>
            <li><a href="/mvcEvent/View/BackOffice/dashboard.php" class="nav-link">Événements</a></li>
            <li><a href="/mvcProduit/view/back%20office/indeex.php" class="nav-link active">Produits</a></li>
            <li><a href="/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link">Transports</a></li>
            <li><a href="/mvcSponsor/crud/view/back/back.php" class="nav-link">Sponsors</a></li>
            </ul>
          </nav>
        </div>

        <div class="profile-container">
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
                    <img src="/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
                <?php else: ?>
                    <div class="profile-circle" style="background-color: <?= stringToColor($fullName) ?>;" onclick="toggleDropdown()">
                        <?= strtoupper(substr($fullName, 0, 1)) ?>
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

      <h2 style="margin-top: 100px;">Réglages ⚙️</h2>
      <div class="card">
        <h3>Paramètres du Site</h3>
        <p>Frais de livraison : 10 TND<br>Durée max. location : 7 jours</p>
        <div class="card-buttons">
          <button class="btn">Modifier Paramètres</button>
        </div>
      </div>
    </div>

    <!-- Statistics Section -->
    <div class="dashboard-section <?= $active_section === 'statistics' ? 'active' : '' ?>" id="statistics">
      <div class="header">
        <div class="navbar-backoffice-wrapper">
          <nav class="navbar-backoffice">
            <ul style="display:flex;gap:40px;list-style:none;margin:0;padding:0;">
            <li><a href="/mvcUtilisateur/View/BackOffice/indeex.php" class="nav-link">Utilisateurs</a></li>
            <li><a href="/mvcact/view/back%20office/dashboard.php" class="nav-link">Activités</a></li>
            <li><a href="/mvcEvent/View/BackOffice/dashboard.php" class="nav-link">Événements</a></li>
            <li><a href="/mvcProduit/view/back%20office/indeex.php" class="nav-link active">Produits</a></li>
            <li><a href="/mvcCovoiturage/view/backoffice/dashboard.php" class="nav-link">Transports</a></li>
            <li><a href="/mvcSponsor/crud/view/back/back.php" class="nav-link">Sponsors</a></li>
            </ul>
          </nav>
        </div>

        <div class="profile-container">
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
                    <img src="/mvcUtilisateur/View/FrontOffice/<?= htmlspecialchars($photoPath) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
                <?php else: ?>
                    <div class="profile-circle" style="background-color: <?= stringToColor($fullName) ?>;" onclick="toggleDropdown()">
                        <?= strtoupper(substr($fullName, 0, 1)) ?>
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

      <div class="section-header">
        <h2>Statistiques 📊</h2>
      </div>

      <div class="stats-container">
        <div class="stat-card">
          <div class="stat-icon">📦</div>
          <h3>Stock Total</h3>
          <p class="stat-value"><?= $globalStats['success'] ? $globalStats['total_stock'] : '0' ?></p>
        </div>
        <div class="stat-card">
          <div class="stat-icon">💸</div>
          <h3>Revenus</h3>
          <p class="stat-value"><?= $globalStats['success'] ? number_format($globalStats['total_value'], 2) : '0.00' ?> TND</p>
        </div>
        <div class="stat-card">
          <div class="stat-icon">📋</div>
          <h3>Commandes</h3>
          <p class="stat-value"><?= $orderStats['success'] ? $orderStats['total_orders'] : '0' ?></p>
          <p class="stat-details">
            Achats: <?= $orderStats['success'] ? $orderStats['purchase_count'] : '0' ?> |
            Locations: <?= $orderStats['success'] ? $orderStats['rental_count'] : '0' ?>
          </p>
        </div>
        <div class="stat-card">
          <div class="stat-icon">⭐</div>
          <h3>Avis</h3>
          <p class="stat-value"><?= $reviewCount ?></p>
        </div>
      </div>

      <div class="categories-stats">
        <h3>Statistiques par Catégorie</h3>
        <div class="category-chart-container">
          <canvas id="categoryStats"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Product Modal -->
  <div class="modal" id="productModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalTitle">Ajouter un Produit</h3>
      </div>
      <div class="modal-body">
        <form id="productForm" action="../../Controller/produitcontroller.php" method="POST" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="action" value="add">
          <div class="form-group">
            <label for="productName">Nom</label>
            <input type="text" id="productName" name="name" required>
            <span class="error-message" id="nameError"></span>
          </div>

          <div class="form-group">
            <label for="productPrice">Prix (TND)</label>
            <input type="number" id="productPrice" name="price" min="0" step="0.01" required>
            <span class="error-message" id="priceError"></span>
          </div>

          <div class="form-group">
            <label for="productStock">Stock</label>
            <input type="number" id="productStock" name="stock" min="0" required>
            <span class="error-message" id="stockError"></span>
          </div>

          <div class="form-group">
            <label for="productCategory">Catégorie</label>
            <select id="productCategory" name="category" required>
              <option value="">Choisissez une catégorie</option>
              <option value="Équipements Sportifs">Équipements Sportifs</option>
              <option value="Vêtements et Accessoires">Vêtements et Accessoires</option>
              <option value="Gadgets & Technologies">Gadgets & Technologies</option>
              <option value="Articles de Bien-être & Récupération">Articles de Bien-être & Récupération</option>
              <option value="Nutrition & Hydratation">Nutrition & Hydratation</option>
              <option value="Accessoires de Voyage & Mobilité">Accessoires de Voyage & Mobilité</option>
              <option value="Supports et accessoires d'atelier">Supports et accessoires d'atelier</option>
              <option value="Univers du cerveau">Univers du cerveau</option>
            </select>
            <span class="error-message" id="categoryError"></span>
          </div>

          <div class="form-group">
            <label for="productPurchase">Disponible à l'achat</label>
            <select id="productPurchase" name="purchase_available" required>
              <option value="yes">Oui</option>
              <option value="no">Non</option>
            </select>
          </div>

          <div class="form-group">
            <label for="productRental">Disponible à la location</label>
            <select id="productRental" name="rental_available" required>
              <option value="yes">Oui</option>
              <option value="no">Non</option>
            </select>
          </div>

          <div class="form-group">
            <label for="productPhoto">Photo</label>
            <input type="file" id="productPhoto" name="photo" accept="image/*">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="close-btn" onclick="closeModal()">Annuler</button>
        <button type="submit" form="productForm" class="save-btn">Enregistrer</button>
      </div>
    </div>
  </div>

  <!-- Promo Modal -->
  <div class="modal" id="promoModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Ajouter une Promotion</h3>
      </div>
      <div class="modal-body">
        <form id="promoForm" action="../../Controller/promotioncontroller.php" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="promoProduct">Nom du produit</label>
            <input type="text" id="promoProduct" name="nom_produit" required>
          </div>
          <div class="form-group">
            <label for="promoOriginalPrice">Prix avant réduction</label>
            <input type="number" id="promoOriginalPrice" name="prix_original" step="0.01" required>
          </div>
          <div class="form-group">
            <label for="promoDiscount">Prix après réduction</label>
            <input type="number" id="promoDiscount" name="prix_promotion" step="0.01" required>
          </div>
          <div class="form-group">
            <label for="promoStartDate">Date de début</label>
            <input type="date" id="promoStartDate" name="date_debut" required>
          </div>
          <div class="form-group">
            <label for="promoEndDate">Date de fin</label>
            <input type="date" id="promoEndDate" name="date_fin" required>
          </div>
          <div class="form-group">
            <label for="promoPhoto">Photo</label>
            <input type="file" id="promoPhoto" name="photo" accept="image/*" required>
            <div class="photo-preview"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="close-btn" onclick="closePromoModal()">Annuler</button>
        <button type="submit" form="promoForm" class="save-btn">Enregistrer</button>
      </div>
    </div>
  </div>

  <script src="scripts.js"></script>
  <script>
    // Fonction pour approuver un avis
    function approveReview(reviewId) {
      if (confirm('Voulez-vous vraiment approuver cet avis ?')) {
        fetch('../../Controller/AvisController.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=approve&id=${reviewId}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert(data.message);
              refreshReviews();
            } else {
              alert('Erreur : ' + data.error);
            }
          })
          .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de l\'approbation.');
          });
      }
    }

    // Fonction pour ouvrir le modal de rejet
    function openRejectModal(reviewId) {
      document.getElementById('rejectReviewId').value = reviewId;
      document.getElementById('rejectionReason').value = '';
      document.getElementById('rejectReviewModal').style.display = 'block';
    }

    // Fonction pour fermer le modal de rejet
    function closeRejectModal() {
      document.getElementById('rejectReviewModal').style.display = 'none';
    }

    // Fonction pour soumettre le rejet
    function submitRejectReview() {
      const reviewId = document.getElementById('rejectReviewId').value;
      const rejectionReason = document.getElementById('rejectionReason').value;

      fetch('../../Controller/AvisController.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: `action=reject&id=${reviewId}&rejection_reason=${encodeURIComponent(rejectionReason)}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            closeRejectModal();
            refreshReviews();
          } else {
            alert('Erreur : ' + data.error);
          }
        })
        .catch(error => {
          console.error('Erreur:', error);
          alert('Une erreur est survenue lors du rejet.');
        });
    }

    // Fonction pour rafraîchir la liste des avis
    // Fonction pour rafraîchir la liste des avis
    // Fonction pour rafraîchir la liste des avis
    function refreshReviews() {
      fetch('../../Controller/AvisController.php?action=get_all', {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.json())
        .then(data => {
          const tbody = document.getElementById('reviewsTableBody');
          tbody.innerHTML = '';

          if (data.success && data.avis.length > 0) {
            let i = 0;
            data.avis.forEach(review => {
              const rowStyle = i % 2 === 0 ? "background-color: white;" : "background-color: #f5f0fa;";
              i++;

              let actionCellContent = '';
              if (review.status === 'pending') {
                actionCellContent = `
            <div style="display: flex; gap: 10px; justify-content: center;">
              <button class="btn approve-btn" data-review-id="${review.id}" onclick="approveReview(${review.id})" style="background: #BBA5D9; color: white; border: none; padding: 8px 16px; border-radius: 30px; font-size: 13px; cursor: pointer;">Approuver</button>
              <button class="btn reject-btn" data-review-id="${review.id}" onclick="openRejectModal(${review.id})" style="background: #E0B0D7; color: white; border: none; padding: 8px 16px; border-radius: 30px; font-size: 13px; cursor: pointer;">Rejeter</button>
            </div>
          `;
              } else if (review.status === 'approved') {
                actionCellContent = `<span style="display: inline-block; padding: 6px 12px; background-color: #E8F5E9; color: #2E7D32; border-radius: 20px; font-size: 13px;">Approuvé</span>`;
              } else if (review.status === 'rejected') {
                actionCellContent = `<span style="display: inline-block; padding: 6px 12px; background-color: #FFEBEE; color: #C62828; border-radius: 20px; font-size: 13px;">Rejeté</span>`;
              }

              const row = `
          <tr data-review-id="${review.id}" style="${rowStyle}">
            <td style="padding: 15px; font-size: 14px;">${review.product_name}</td>
            <td style="padding: 15px; font-size: 14px;">${review.email}</td>
            <td style="padding: 15px; font-size: 16px; color: #FFB400;">${'★'.repeat(review.stars) + '☆'.repeat(5 - review.stars)}</td>
            <td style="padding: 15px; font-size: 14px;">${review.comment || 'Aucun commentaire'}</td>
            <td style="padding: 15px; text-align: center;">${actionCellContent}</td>
          </tr>`;
              tbody.insertAdjacentHTML('beforeend', row);
            });
          } else {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 30px; color: #888; font-style: italic;">Aucun avis disponible</td></tr>';
          }
        })
        .catch(error => {
          console.error('Erreur:', error);
          alert('Erreur lors du chargement des avis.');
        });
    }

    // Recherche dans les avis
    document.getElementById('reviewSearch').addEventListener('input', function(e) {
      const searchValue = e.target.value.toLowerCase();
      const selectedRating = document.getElementById('ratingFilter').value;
      const rows = document.querySelectorAll('#reviewsTableBody tr');

      rows.forEach(row => {
        const productName = row.cells[0].textContent.toLowerCase();
        const email = row.cells[1].textContent.toLowerCase();
        const comment = row.cells[3].textContent.toLowerCase();
        const stars = row.cells[2].textContent;
        const starCount = (stars.match(/★/g) || []).length;

        const matchesSearch = productName.includes(searchValue) ||
          email.includes(searchValue) ||
          comment.includes(searchValue);

        const matchesRating = selectedRating === '' || starCount === parseInt(selectedRating);

        if (matchesSearch && matchesRating) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    // Charger les avis en attente au démarrage
    document.addEventListener('DOMContentLoaded', refreshReviews);
    document.addEventListener('DOMContentLoaded', function() {
      // Activer la section promotions si #promos est dans l'URL
      if (window.location.hash === '#promos') {
        document.querySelectorAll('.dashboard-section').forEach(section => section.classList.remove('active'));
        document.getElementById('promos').classList.add('active');
        document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
        document.querySelector('[data-section="promos"]').classList.add('active');
      }

      const nameRegex = /^[A-Za-zÀ-ÖØ-öø-ÿ\s'-]+$/;
      const form = document.getElementById('productForm');
      const nameInput = document.getElementById('productName');
      const priceInput = document.getElementById('productPrice');
      const stockInput = document.getElementById('productStock');
      const categorySelect = document.getElementById('productCategory');

      nameInput.addEventListener('input', () => validateField(nameInput, 'nameError', validateName));
      priceInput.addEventListener('input', () => validateField(priceInput, 'priceError', validatePrice));
      stockInput.addEventListener('input', () => validateField(stockInput, 'stockError', validateStock));
      categorySelect.addEventListener('change', () => validateField(categorySelect, 'categoryError', validateCategory));

      form.addEventListener('submit', function(event) {
        const isNameValid = validateField(nameInput, 'nameError', validateName);
        const isPriceValid = validateField(priceInput, 'priceError', validatePrice);
        const isStockValid = validateField(stockInput, 'stockError', validateStock);
        const isCategoryValid = validateField(categorySelect, 'categoryError', validateCategory);

        if (!isNameValid || !isPriceValid || !isStockValid || !isCategoryValid) {
          event.preventDefault();
          const firstInvalid = document.querySelector('.invalid');
          if (firstInvalid) {
            firstInvalid.scrollIntoView({
              behavior: 'smooth',
              block: 'center'
            });
            firstInvalid.focus();
          }
        }
      });

      function validateField(input, errorId, validationFn) {
        const errorElement = document.getElementById(errorId);
        const isValid = validationFn(input.value.trim(), input);

        if (!isValid.valid) {
          errorElement.textContent = isValid.message;
          input.classList.add('invalid');
          input.classList.remove('valid');
          return false;
        } else {
          errorElement.textContent = '';
          input.classList.add('valid');
          input.classList.remove('invalid');
          return true;
        }
      }

      function validateName(value) {
        if (value === "") {
          return {
            valid: false,
            message: "Le nom du produit est requis."
          };
        }
        if (!nameRegex.test(value)) {
          return {
            valid: false,
            message: "Le nom ne doit contenir que des lettres et des espaces."
          };
        }
        return {
          valid: true
        };
      }

      function validatePrice(value) {
        if (value === "") {
          return {
            valid: false,
            message: "Le prix est requis."
          };
        }
        if (isNaN(value) || parseFloat(value) < 0) {
          return {
            valid: false,
            message: "Le prix doit être un nombre positif."
          };
        }
        return {
          valid: true
        };
      }

      function validateStock(value) {
        if (value === "") {
          return {
            valid: false,
            message: "Le stock est requis."
          };
        }
        if (isNaN(value) || !Number.isInteger(Number(value)) || parseInt(value) < 0) {
          return {
            valid: false,
            message: "Le stock doit être un entier positif."
          };
        }
        return {
          valid: true
        };
      }

      function validateCategory(value) {
        if (value === "") {
          return {
            valid: false,
            message: "La catégorie est requise."
          };
        }
        return {
          valid: true
        };
      }

      nameInput.addEventListener('blur', () => validateField(nameInput, 'nameError', validateName));
      priceInput.addEventListener('blur', () => validateField(priceInput, 'priceError', validatePrice));
      stockInput.addEventListener('blur', () => validateField(stockInput, 'stockError', validateStock));
    });

    function closeModal() {
      document.getElementById('productModal').style.display = "none";
    }

    function openModal() {
      document.getElementById('productModal').style.display = "block";
    }

    window.onload = function() {
      const urlParams = new URLSearchParams(window.location.search);
      const message = urlParams.get('message');
      if (message) {
        alert(message);
      }
    };

    document.addEventListener('DOMContentLoaded', function() {
      const ctx = document.getElementById('categoryStats').getContext('2d');
      const categoryStats = <?php echo json_encode($stats); ?>;

      // Préparer les données pour le graphique
      const labels = categoryStats.map(stat => stat.category);
      const productCounts = categoryStats.map(stat => stat.product_count);

      // Calculer un pas approprié pour l'échelle
      const maxCount = Math.max(...productCounts);
      const step = Math.ceil(maxCount / 5); // Diviser l'échelle en 5 intervalles

      new Chart(ctx, {
        type: 'radar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Nombre de produits',
            data: productCounts,
            backgroundColor: 'rgba(255, 105, 180, 0.4)',
            borderColor: '#FF1493',
            borderWidth: 2,
            pointBackgroundColor: '#9370DB',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: '#FF69B4',
            pointRadius: 5,
            pointHoverRadius: 7,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            r: {
              angleLines: {
                color: 'rgba(255, 105, 180, 0.2)'
              },
              grid: {
                color: 'rgba(147, 112, 219, 0.2)'
              },
              pointLabels: {
                font: {
                  size: 12,
                  family: 'Inter'
                },
                color: '#333'
              },
              ticks: {
                backdropColor: 'transparent',
                color: '#666',
                stepSize: step,
                font: {
                  size: 10
                },
                callback: function(value) {
                  return value + ' produits';
                }
              },
              min: 0,
              max: (Math.ceil(maxCount / step) * step), // Arrondir au multiple de step supérieur
              beginAtZero: true
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(255, 255, 255, 0.9)',
              titleColor: '#333',
              titleFont: {
                size: 14,
                weight: 'bold',
                family: 'Inter'
              },
              bodyColor: '#666',
              bodyFont: {
                size: 13,
                family: 'Inter'
              },
              borderColor: '#FF69B4',
              borderWidth: 1,
              padding: 10,
              callbacks: {
                label: function(context) {
                  const categoryIndex = context.dataIndex;
                  const category = categoryStats[categoryIndex];
                  return [
                    `Produits: ${category.product_count}`,
                    `Stock total: ${category.total_stock}`,
                    `Valeur: ${category.total_value.toFixed(2)} TND`
                  ];
                }
              }
            }
          },
          animation: {
            duration: 2000,
            easing: 'easeOutQuart'
          }
        }
      });
    });

    document.addEventListener('DOMContentLoaded', function() {
      const filterButtons = document.querySelectorAll('.filter-btn');

      filterButtons.forEach(button => {
        button.addEventListener('click', function() {
          const period = this.dataset.period;
          window.location.href = 'indeex.php?period=' + period;
        });
      });
    });

    // Fonction de recherche pour les commandes
    document.getElementById('orderSearch').addEventListener('input', function(e) {
      const searchValue = e.target.value.toLowerCase();
      const orderCards = document.querySelectorAll('.order-card');

      orderCards.forEach(card => {
        const orderId = card.querySelector('h3').textContent.toLowerCase();
        if (orderId.includes(searchValue)) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });

    // Fonction de recherche pour les produits
    document.getElementById('productSearch').addEventListener('input', function(e) {
      const searchValue = e.target.value.toLowerCase();
      const productCards = document.querySelectorAll('.card');

      productCards.forEach(card => {
        const productName = card.querySelector('h3').textContent.toLowerCase();
        if (productName.startsWith(searchValue)) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });

    function closePromoModal() {
      document.getElementById('promoModal').style.display = "none";
    }

    function openPromoModal() {
      document.getElementById('promoModal').style.display = "block";
    }

    // Fonction pour prévisualiser l'image lors de l'ajout d'une promotion
    document.getElementById('promoPhoto').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const preview = document.createElement('img');
          preview.src = e.target.result;
          preview.style.maxWidth = '200px';
          preview.style.marginTop = '10px';

          // Supprimer l'ancienne prévisualisation s'il y en a une
          const oldPreview = document.querySelector('.photo-preview');
          if (oldPreview) {
            oldPreview.remove();
          }

          // Ajouter la nouvelle prévisualisation
          const previewContainer = document.createElement('div');
          previewContainer.className = 'photo-preview';
          previewContainer.appendChild(preview);
          document.getElementById('promoPhoto').parentNode.appendChild(previewContainer);
        }
        reader.readAsDataURL(file);
      }
    });

    document.addEventListener('DOMContentLoaded', function() {
      // Navigation rapide pour la barre de recherche
      const searchInput = document.querySelector('#overview .search');

      searchInput.addEventListener('input', function(e) {
        const searchValue = e.target.value.toLowerCase();

        if (searchValue === 's') {
          document.querySelector('.categories-stats').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        } else if (searchValue === 'a') {
          document.querySelector('.trends-dashboard').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        } else if (searchValue === 'o') {
          document.querySelector('.categories-overview').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });

    // Reste du code existant
    document.addEventListener('DOMContentLoaded', function() {
      // Initialiser les deux graphiques radar de statistiques par catégorie
      const ctxOverview = document.getElementById('categoryStats');
      const ctxStats = document.getElementById('categoryStatsChart');
      const categoryStats = <?php echo json_encode($stats); ?>;

      // Préparer les données pour le graphique
      const labels = categoryStats.map(stat => stat.category);
      const productCounts = categoryStats.map(stat => stat.product_count);

      // Calculer un pas approprié pour l'échelle
      const maxCount = Math.max(...productCounts);
      const step = Math.ceil(maxCount / 5); // Diviser l'échelle en 5 intervalles

      // Configuration commune pour les deux graphiques
      const chartConfig = {
        type: 'radar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Nombre de produits',
            data: productCounts,
            backgroundColor: 'rgba(255, 105, 180, 0.4)',
            borderColor: '#FF1493',
            borderWidth: 2,
            pointBackgroundColor: '#9370DB',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: '#FF69B4',
            pointRadius: 5,
            pointHoverRadius: 7,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            r: {
              angleLines: {
                color: 'rgba(255, 105, 180, 0.2)'
              },
              grid: {
                color: 'rgba(147, 112, 219, 0.2)'
              },
              pointLabels: {
                font: {
                  size: 12,
                  family: 'Inter'
                },
                color: '#333'
              },
              ticks: {
                backdropColor: 'transparent',
                color: '#666',
                stepSize: step,
                font: {
                  size: 10
                },
                callback: function(value) {
                  return value + ' produits';
                }
              },
              min: 0,
              max: (Math.ceil(maxCount / step) * step), // Arrondir au multiple de step supérieur
              beginAtZero: true
            }
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(255, 255, 255, 0.9)',
              titleColor: '#333',
              titleFont: {
                size: 14,
                weight: 'bold',
                family: 'Inter'
              },
              bodyColor: '#666',
              bodyFont: {
                size: 13,
                family: 'Inter'
              },
              borderColor: '#FF69B4',
              borderWidth: 1,
              padding: 10,
              callbacks: {
                label: function(context) {
                  const categoryIndex = context.dataIndex;
                  const category = categoryStats[categoryIndex];
                  return [
                    `Produits: ${category.product_count}`,
                    `Stock total: ${category.total_stock}`,
                    `Valeur: ${category.total_value.toFixed(2)} TND`
                  ];
                }
              }
            }
          },
          animation: {
            duration: 2000,
            easing: 'easeOutQuart'
          }
        }
      };

      // Créer le graphique dans la section Vue Générale si l'élément existe
      if (ctxOverview) {
        new Chart(ctxOverview.getContext('2d'), JSON.parse(JSON.stringify(chartConfig)));
      }

      // Créer le graphique dans la section Statistiques si l'élément existe
      if (ctxStats) {
        new Chart(ctxStats.getContext('2d'), JSON.parse(JSON.stringify(chartConfig)));
      }
    });

    document.addEventListener('DOMContentLoaded', function() {
      // Données PHP vers JS
      const achatStats = <?php echo json_encode(array_reverse($achatStats)); ?>;
      const locationStats = <?php echo json_encode(array_reverse($locationStats)); ?>;

      // Achat
      const achatLabels = achatStats.map(item => item.date);
      const achatData = achatStats.map(item => item.total);

      new Chart(document.getElementById('achatChart').getContext('2d'), {
        type: 'bar',
        data: {
          labels: achatLabels,
          datasets: [{
            label: 'Achats',
            data: achatData,
            backgroundColor: '#FF69B4'
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });

      // Location
      const locationLabels = locationStats.map(item => item.date);
      const locationData = locationStats.map(item => item.total);

      new Chart(document.getElementById('locationChart').getContext('2d'), {
        type: 'bar',
        data: {
          labels: locationLabels,
          datasets: [{
            label: 'Locations',
            data: locationData,
            backgroundColor: '#9370DB'
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    });

    function updatePendingReviewsCount() {
      fetch('../../Controller/AvisController.php?action=get_pending_count', {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(response => response.json())
        .then(data => {
          const badge = document.querySelector('.notification-badge');
          if (data.success && data.pending_count > 0) {
            if (!badge) {
              const reviewsMenu = document.querySelector('.menu-item[data-section="reviews"]');
              reviewsMenu.insertAdjacentHTML('beforeend', `<span class="notification-badge">${data.pending_count}</span>`);
            } else {
              badge.textContent = data.pending_count;
            }
          } else if (badge) {
            badge.remove();
          }
        })
        .catch(error => console.error('Erreur lors de la mise à jour du compte des avis:', error));
    }

    // Appeler au chargement de la page
    document.addEventListener('DOMContentLoaded', updatePendingReviewsCount);

    // Rafraîchir après chaque action (exemple)
    document.addEventListener('click', (e) => {
      if (e.target.closest('.approve-btn') || e.target.closest('.reject-btn')) {
        setTimeout(updatePendingReviewsCount, 500);
      }
    });

    // Ajouter le filtre de note
    document.getElementById('ratingFilter').addEventListener('change', function(e) {
      const selectedRating = e.target.value;
      const rows = document.querySelectorAll('#reviewsTableBody tr');

      rows.forEach(row => {
        const stars = row.cells[2].textContent;
        const starCount = (stars.match(/★/g) || []).length;

        if (selectedRating === '' || starCount === parseInt(selectedRating)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    document.addEventListener('DOMContentLoaded', function() {
      // Vérifier si nous sommes dans la section orders
      const urlParams = new URLSearchParams(window.location.search);
      const section = urlParams.get('section');

      if (section === 'orders') {
        // Désactiver toutes les sections
        document.querySelectorAll('.dashboard-section').forEach(section => {
          section.classList.remove('active');
        });

        // Désactiver tous les éléments du menu
        document.querySelectorAll('.menu-item').forEach(item => {
          item.classList.remove('active');
        });

        // Activer la section orders
        document.getElementById('orders').classList.add('active');

        // Activer l'élément du menu orders
        document.querySelector('[data-section="orders"]').classList.add('active');
      }
    });

    // Script pour gérer le menu déroulant du profil
    document.addEventListener('DOMContentLoaded', function() {
      // Sélectionner tous les profils navbars (un par section)
      const profileNavbars = document.querySelectorAll('.profile-container-navbar');

      profileNavbars.forEach(profileNavbar => {
        const profileDropdown = profileNavbar.querySelector('.profile-dropdown');

        // Ouvrir/fermer au clic sur l'avatar
        profileNavbar.addEventListener('click', function(e) {
          e.stopPropagation();
          profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Empêcher la fermeture quand on clique dans le dropdown
        profileDropdown.addEventListener('click', function(e) {
          e.stopPropagation();
        });
      });

      // Fermer tous les dropdowns si on clique ailleurs sur la page
      document.addEventListener('click', function() {
        document.querySelectorAll('.profile-dropdown').forEach(dropdown => {
          dropdown.style.display = 'none';
        });
      });
    });
  </script>

  <script>
    // Script pour améliorer l'interaction avec le tableau des avis
    document.addEventListener('DOMContentLoaded', function() {
      // Effet de survol amélioré pour les lignes du tableau
      const tableRows = document.querySelectorAll('#reviewsTableBody tr');

      tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
          this.style.backgroundColor = 'rgba(191, 162, 247, 0.08)';
          this.style.transform = 'translateY(-2px)';
          this.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.05)';
        });

        row.addEventListener('mouseleave', function() {
          this.style.backgroundColor = '';
          this.style.transform = '';
          this.style.boxShadow = '';
        });
      });

      // Effet pour les boutons
      const approveButtons = document.querySelectorAll('.approve-btn');
      const rejectButtons = document.querySelectorAll('.reject-btn');

      approveButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
          this.style.backgroundColor = '#AD8DF3';
          this.style.transform = 'translateY(-2px)';
          this.style.boxShadow = '0 4px 8px rgba(173, 141, 243, 0.3)';
        });

        button.addEventListener('mouseleave', function() {
          this.style.backgroundColor = '#BFA2F7';
          this.style.transform = '';
          this.style.boxShadow = '';
        });
      });

      rejectButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
          this.style.backgroundColor = '#F094C3';
          this.style.transform = 'translateY(-2px)';
          this.style.boxShadow = '0 4px 8px rgba(240, 148, 195, 0.3)';
        });

        button.addEventListener('mouseleave', function() {
          this.style.backgroundColor = '#F7B2D9';
          this.style.transform = '';
          this.style.boxShadow = '';
        });
      });
    });
  </script>
</body>

</html>