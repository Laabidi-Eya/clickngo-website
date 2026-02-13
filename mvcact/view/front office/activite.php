<?php
session_start();
$loggedIn = isset($_SESSION['user']); // ou 'customer', selon ta structure
$customer_name = $loggedIn ? htmlspecialchars($_SESSION['user']['full_name']) : '';
$customer_email = $loggedIn ? htmlspecialchars($_SESSION['user']['email']) : '';

require_once __DIR__ . '/../../model/ActivityModel.php';
require_once __DIR__ . '/../../controller/ReviewController.php';

// Initialiser le modèle d'activité
$activityModel = new ActivityModel();

// Initialiser le contrôleur de critiques
$reviewController = new ReviewController();
$approvedReviews = $reviewController->index();
$averageRating = $reviewController->getAverageRating();

// Récupérer toutes les activités
$allActivities = $activityModel->getAllActivities();

// Organiser les activités par catégorie
$categorizedActivities = [];
foreach ($allActivities as $activity) {
  $category = $activity['category'];
  if (!isset($categorizedActivities[$category])) {
    $categorizedActivities[$category] = [];
  }
  $categorizedActivities[$category][] = $activity;
}

// Function to fetch activities by categories
function fetchActivitiesByCategories($categories, $age = '')
{
  require_once '../../model/ActivityModel.php';

  $activityModel = new ActivityModel();
  $activities = $activityModel->getAllActivities();

  $filteredActivities = [];

  // First filter by categories
  if (!empty($categories)) {
    foreach ($activities as $activity) {
      if (in_array($activity['category'], $categories)) {
        $filteredActivities[] = $activity;
      }
    }
  } else {
    // If no categories specified, use all activities
    $filteredActivities = $activities;
  }

  // Then apply age filtering if specified
  $ageFilteredActivities = [];
  if (!empty($age)) {
    foreach ($filteredActivities as $activity) {
      // Apply age-specific filtering - this is a simplified example
      // In a real implementation, you would have an age_range field in your database

      switch ($age) {
        case 'under18':
          // Activities suitable for under 18
          if (
            $activity['category'] == 'Famille' ||
            $activity['category'] == 'sport' ||
            $activity['category'] == 'Ateliers' ||
            $activity['price'] <= 50 // Cheaper activities for younger audience
          ) {
            $ageFilteredActivities[] = $activity;
          }
          break;

        case '18-25':
          // Activities suitable for 18-25
          if (
            $activity['category'] == 'aventure' ||
            $activity['category'] == 'sport' ||
            $activity['category'] == 'Extreme'
          ) {
            $ageFilteredActivities[] = $activity;
          }
          break;

        case '26-35':
          // No specific filtering, most activities are suitable
          $ageFilteredActivities[] = $activity;
          break;

        case '36-50':
          // Activities suitable for 36-50
          if (
            $activity['category'] == 'bien-etre' ||
            $activity['category'] == 'culture' ||
            $activity['category'] == 'Détente' ||
            $activity['category'] == 'sport'
          ) {
            $ageFilteredActivities[] = $activity;
          }
          break;

        case 'over50':
          // Activities suitable for over 50
          if (
            $activity['category'] == 'bien-etre' ||
            $activity['category'] == 'culture' ||
            $activity['category'] == 'Détente'
          ) {
            $ageFilteredActivities[] = $activity;
          }
          break;

        default:
          // No age filtering
          $ageFilteredActivities[] = $activity;
      }
    }
  } else {
    // If no age specified, use all category-filtered activities
    $ageFilteredActivities = $filteredActivities;
  }

  // Limit to maximum 10 suggestions to avoid overwhelming the user
  return array_slice($ageFilteredActivities, 0, 10);
}

// AJAX endpoint to fetch personalized suggestions
if (isset($_POST['action']) && $_POST['action'] === 'get_suggestions') {
  // Set content type to JSON for response
  header('Content-Type: application/json');

  try {
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $age = isset($_POST['age']) ? $_POST['age'] : '';

    // Validate input data
    if (!is_array($categories)) {
      $categories = []; // Reset if not an array
    }

    // Get activities based on filters
    $activities = fetchActivitiesByCategories($categories, $age);

    // Build response
    $response = [];
    foreach ($activities as $activity) {
      $image = $activity['image'] ? $activity['image'] : 'images/default.jpg';

      // Ensure image path is valid
      if (!filter_var($image, FILTER_VALIDATE_URL) && !file_exists($image)) {
        $image = 'images/default.jpg';
      }

      $response[] = [
        'id' => $activity['id'],
        'name' => $activity['name'],
        'description' => $activity['description'],
        'image' => $image,
        'category' => $activity['category'],
        'price' => $activity['price']
      ];
    }

    // If no suggestions found but filters were set, return random activities
    if (empty($response) && (!empty($categories) || !empty($age))) {
      // Get random activities as fallback
      $activityModel = new ActivityModel();
      $randomActivities = $activityModel->getAllActivities();

      // Limit to 5 random activities
      shuffle($randomActivities);
      $randomActivities = array_slice($randomActivities, 0, 5);

      foreach ($randomActivities as $activity) {
        $image = $activity['image'] ? $activity['image'] : 'images/default.jpg';

        // Ensure image path is valid
        if (!filter_var($image, FILTER_VALIDATE_URL) && !file_exists($image)) {
          $image = 'images/default.jpg';
        }

        $response[] = [
          'id' => $activity['id'],
          'name' => $activity['name'],
          'description' => $activity['description'] . ' (Recommandation générale)',
          'image' => $image,
          'category' => $activity['category'],
          'price' => $activity['price']
        ];
      }
    }

    echo json_encode($response);
  } catch (Exception $e) {
    // Log the error
    error_log('Error in personalization panel: ' . $e->getMessage());

    // Return error response
    echo json_encode([
      'error' => true,
      'message' => 'Une erreur est survenue lors de la récupération des suggestions.'
    ]);
  }

  exit;
}


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

?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Activités - Click'N'Go</title>
  <link rel="stylesheet" href="style.css" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Script pour masquer la notification et corriger les boutons -->
  <script>
    $(document).ready(function() {
      // Masquer la notification
      $(".new-feature-notification").hide();

      // Corriger le bouton flottant quand la page est chargée
      $(".floating-customize-btn, #toggle-personalization").off('click').on('click', function() {
        $(".personalization-panel").toggleClass("open");

        // Animation du bouton flottant
        $(".floating-customize-btn").toggleClass("active");

        // Repositionnement du bouton lorsque le panneau est ouvert
        if ($(".personalization-panel").hasClass("open")) {
          $(".floating-customize-btn").css({
            "right": "350px",
            "transition": "right 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)"
          });
        } else {
          $(".floating-customize-btn").css("right", "30px");
        }

        return false; // Empêcher la propagation de l'événement
      });
    });
  </script>
  <style>
    /* Style pour les boutons Réserver dans les catégories */
    .category-content .register-btn {
      font-size: 14px;
      padding: 7px 15px;
      border-radius: 20px;
      display: inline-block;
      margin-top: 8px;
      background-color: #FF385C;
      color: white;
      text-decoration: none;
      transition: background-color 0.3s, transform 0.2s;
      text-align: center;
    }

    .category-content .register-btn:hover {
      background-color: #E4002B;
      transform: translateY(-2px);
    }

    /* Style pour les cartes d'activité */
    .category-content .exclusives div {
      overflow: hidden;
      border-radius: 12px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s;
      margin-bottom: 20px;
      display: flex;
      flex-direction: column;
    }

    .category-content .exclusives div:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .category-content .exclusives img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 12px 12px 0 0;
    }

    .category-content .exclusives .description-container {
      padding: 12px 15px;
      background: #f8f9fa;
      border-bottom: 1px solid #e9ecef;
    }

    .category-content .exclusives p.description {
      margin: 0;
      color: #444;
      font-size: 14px;
      line-height: 1.5;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
      font-weight: 400;
      letter-spacing: 0.2px;
    }

    .category-content .exclusives span {
      padding: 12px 15px;
      background: white;
    }

    .category-content .exclusives span h3 {
      margin-bottom: 6px;
      color: #333;
      font-size: 16px;
    }

    .category-content .exclusives span p.price {
      margin-bottom: 8px;
      color: #FF385C;
      font-weight: bold;
      font-size: 15px;
    }

    /* Style pour améliorer l'affichage des sections de catégories */
    .category-content h3 {
      margin-bottom: 25px;
      font-size: 24px;
      color: #333;
      position: relative;
      padding-bottom: 10px;
    }

    .category-content h3:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 3px;
      background-color: #FF385C;
    }

    /* Styles pour les messages d'avis */
    .avis-message {
      padding: 15px;
      border-radius: 5px;
      margin-top: 15px;
      font-weight: 500;
      text-align: center;
    }

    .avis-message.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .avis-message.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    /* Styles pour le formulaire d'avis */
    #avis-form {
      display: flex;
      flex-direction: column;
      gap: 15px;
      max-width: 600px;
      margin: 0 auto;
    }

    #avis-form input,
    #avis-form textarea {
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
    }

    #avis-form input:focus,
    #avis-form textarea:focus {
      border-color: #FF385C;
      outline: none;
      box-shadow: 0 0 0 2px rgba(255, 56, 92, 0.2);
    }

    #avis-form input[type="email"] {
      background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="%23999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>');
      background-repeat: no-repeat;
      background-position: 12px center;
      background-size: 20px;
      padding-left: 42px;
    }

    #avis-form textarea {
      min-height: 120px;
      resize: vertical;
    }

    #avis-form button {
      background-color: #FF385C;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 5px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    #avis-form button:hover {
      background-color: #E4002B;
    }

    .avis-stars {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .star {
      font-size: 30px;
      color: #ddd;
      cursor: pointer;
      transition: color 0.2s;
    }

    .star.selected {
      color: #FFD700;
    }

    .avis-form-section {
      background-color: #f9f9f9;
      padding: 30px;
      border-radius: 10px;
      margin-top: 50px;
    }

    .avis-form-section h3 {
      text-align: center;
      margin-bottom: 25px;
      color: #333;
    }

    .avis-auteur {
      font-weight: 600;
      color: #333;
      margin-top: 15px;
      text-align: right;
    }

    .avis-date {
      font-size: 12px;
      color: #777;
      margin-top: 5px;
      text-align: right;
    }

    /* Style pour les images par défaut */
    .avis-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }

    /* Meilleur style pour le slider */
    .avis-slider-wrapper {
      position: relative;
      overflow: hidden;
      padding: 20px 0;
      margin-bottom: 40px;
    }

    .avis-slider {
      display: flex;
      transition: transform 0.5s ease-in-out;
    }

    .avis-slide {
      min-width: 100%;
      padding: 0 20px;
    }

    .avis-content {
      background-color: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    @media (min-width: 768px) {
      .avis-content {
        flex-direction: row;
      }

      .avis-image {
        width: 40%;
      }

      .avis-card {
        width: 60%;
      }
    }

    .avis-nav {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-bottom: 20px;
    }

    .btn-nav {
      background-color: #FF385C;
      color: white;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background-color 0.3s, transform 0.2s;
    }

    .btn-nav:hover {
      background-color: #E4002B;
      transform: scale(1.1);
    }

    .btn-nav.disabled {
      background-color: #ccc;
      cursor: not-allowed;
      opacity: 0.6;
      transform: none;
    }

    .btn-nav.disabled:hover {
      background-color: #ccc;
      transform: none;
    }

    .image-upload {
      margin-top: 10px;
    }

    .image-upload label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
    }

    .image-preview {
      margin-top: 10px;
      max-width: 100%;
      max-height: 200px;
      overflow: hidden;
      border-radius: 5px;
      display: none;
    }

    .image-preview img {
      max-width: 100%;
      max-height: 200px;
      object-fit: contain;
    }

    /* Styles pour la section descriptive */
    .description-section {
      padding: 20px 0;
      background: linear-gradient(135deg, #f8e1f4, #e6d5f5);
      /* Dégradé rose bébé vers lilas */
      margin-bottom: 25px;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(208, 163, 246, 0.2);
      width: 65%;
      max-width: 850px;
      margin-left: auto;
      margin-right: auto;
    }

    .description-container {
      max-width: 750px;
      /* Rétrécir encore plus la largeur */
      margin: 0 auto;
      padding: 0 8px;
    }

    .description-container h2 {
      text-align: center;
      font-size: 1.5rem;
      color: #9768D1;
      /* Violet */
      margin-bottom: 20px;
      position: relative;
    }

    .description-container h2:after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 50%;
      transform: translateX(-50%);
      width: 300px;
      height: 2px;
      background-color: #D48DD8;
      /* Rose lilas */
    }

    .description-content {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      justify-content: space-between;
      align-items: flex-start;
    }

    .description-text {
      flex: 1;
      min-width: 300px;
      font-size: 1.0rem;
      line-height: 1.8;
      color: #5F4B8B;
      /* Violet foncé */
    }

    .description-intro {
      font-size: 0.95rem;
      margin-bottom: 10px;
      font-weight: 500;
      color: #7B506F;
      /* Violet rosé */
    }

    .description-highlight {
      font-weight: 600;
      color: #B565A7;
      /* Rose violacé */
      margin-top: 12px;
      font-size: 0.9rem;
      line-height: 1.4;
      padding: 6px;
      background-color: rgba(255, 255, 255, 0.5);
      border-radius: 5px;
    }

    .benefits-list {
      margin: 12px 0;
      padding-left: 3px;
    }

    .benefits-list li {
      list-style-type: none;
      margin-bottom: 6px;
      padding-left: 20px;
      position: relative;
      font-size: 0.82rem;
    }

    .benefits-list li i {
      position: absolute;
      left: 0;
      top: 3px;
      color: #9768D1;
      /* Violet */
      font-size: 0.8rem;
    }

    .description-stats {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: center;
      width: 100%;
      max-width: 240px;
      /* Rétrécir encore plus les stats */
    }

    .stat-item {
      background: linear-gradient(135deg, #D48DD8, #9768D1);
      /* Dégradé rose lilas vers violet */
      color: white;
      padding: 10px 5px;
      border-radius: 7px;
      box-shadow: 0 2px 8px rgba(151, 104, 209, 0.2);
      text-align: center;
      width: calc(50% - 8px);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-number {
      display: block;
      font-size: 1.4rem;
      font-weight: 700;
      margin-bottom: 0;
      line-height: 1.1;
    }

    .stat-label {
      display: block;
      font-size: 0.7rem;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    /* Réduire la section descriptive sur mobile */
    @media (max-width: 768px) {
      .description-section {
        width: 85%;
        padding: 18px 0;
      }

      .description-container {
        padding: 0 6px;
      }

      .description-content {
        gap: 12px;
      }

      .description-text {
        min-width: 100%;
      }

      .description-stats {
        max-width: 100%;
      }

      .stat-item {
        width: calc(50% - 4px);
        padding: 8px 4px;
      }

      .stat-number {
        font-size: 1.2rem;
      }

      .stat-label {
        font-size: 0.65rem;
      }
    }

    /* Styles pour le menu déroulant */
    .dropdown {
      position: relative;
      display: inline-block;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background: transparent !important;
      min-width: 250px;
      box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
      z-index: 1000;
      border-radius: 8px;
      overflow: hidden;
      backdrop-filter: none !important;
    }

    .dropdown-content a {
      color: #9768D1 !important;
      /* ou #fff si tu veux blanc */
      font-weight: 700;
      text-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
      /* Pour la lisibilité sur image */
      padding: 12px 16px;
      text-decoration: none;
      display: block;
      transition: background 0.3s, color 0.3s;
      font-size: 14px;
    }

    .dropdown-content a:hover {
      background-color: #9768D1;
      color: white !important;
    }

    .dropdown:hover .dropdown-content {
      display: block;
      animation: fadeIn 0.3s;
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

    /* Style pour la section active */
    .dropdown-content a.active {
      background-color: #9768D1;
      color: white;
      font-weight: bold;
    }

    /* Styles pour la section des catégories d'entreprises */
    .categories-section {
      padding: 40px 30px;
      background: linear-gradient(135deg, #e6d5f5, #f8e1f4);
      /* Dégradé lilas vers rose bébé (inversé) */
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(208, 163, 246, 0.2);
      text-align: center;
      max-width: 1000px;
      margin: 60px auto;
    }

    .section-title {
      font-size: 1.9rem;
      color: #9768D1;
      /* Violet */
      margin-bottom: 30px;
      position: relative;
      display: inline-block;
      padding-bottom: 10px;
    }

    .section-title:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 3px;
      background-color: #D48DD8;
      /* Rose lilas */
    }

    .categories-buttons {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 15px;
      margin-top: 25px;
    }

    .cat-btn {
      background: linear-gradient(135deg, #D48DD8, #9768D1);
      /* Dégradé rose lilas vers violet */
      color: white;
      padding: 12px 25px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 500;
      font-size: 0.95rem;
      letter-spacing: 0.5px;
      border: none;
      transition: transform 0.3s, box-shadow 0.3s, opacity 0.2s;
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(151, 104, 209, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .cat-btn:before {
      content: "";
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.7s;
    }

    .cat-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(151, 104, 209, 0.3);
    }

    .cat-btn:hover:before {
      left: 100%;
    }

    .cat-btn:active {
      transform: translateY(0);
      opacity: 0.9;
    }

    /* Ajout d'icônes pour les boutons */
    .cat-btn i {
      margin-right: 8px;
      font-size: 14px;
    }

    @media (max-width: 768px) {
      .categories-section {
        padding: 30px 15px;
      }

      .cat-btn {
        font-size: 0.85rem;
        padding: 10px 20px;
      }
    }

    /* Styles globaux pour tous les boutons et éléments interactifs */
    .register-btn,
    button[type="submit"],
    .voir-tout,
    .activity-card,
    .btn-nav {
      background: linear-gradient(135deg, #D48DD8, #9768D1) !important;
      color: white !important;
      border: none !important;
      box-shadow: 0 0 15px rgba(212, 141, 216, 0.5) !important;
      transition: all 0.3s ease !important;
      position: relative !important;
      overflow: hidden !important;
      z-index: 1 !important;
    }

    /* Effet néon sur hover */
    .register-btn:hover,
    button[type="submit"]:hover,
    .voir-tout:hover,
    .activity-card:hover,
    .btn-nav:hover {
      box-shadow: 0 0 20px rgba(151, 104, 209, 0.8), 0 0 30px rgba(151, 104, 209, 0.6), 0 0 40px rgba(151, 104, 209, 0.4) !important;
      transform: translateY(-3px) !important;
    }

    /* Effet fluo/glow sur les éléments cliquables */
    .register-btn:before,
    button[type="submit"]:before,
    .voir-tout:before,
    .btn-nav:before {
      content: "" !important;
      position: absolute !important;
      top: -5px !important;
      left: -5px !important;
      right: -5px !important;
      bottom: -5px !important;
      z-index: -1 !important;
      background: linear-gradient(45deg, #ff00e1, #8A2BE2, #00bfff, #D48DD8, #9768D1) !important;
      background-size: 400% !important;
      border-radius: 35px !important;
      opacity: 0 !important;
      transition: all 0.6s ease !important;
    }

    .register-btn:hover:before,
    button[type="submit"]:hover:before,
    .voir-tout:hover:before,
    .btn-nav:hover:before {
      opacity: 0.4 !important;
      filter: blur(20px) !important;
      animation: glowing 8s linear infinite !important;
    }

    @keyframes glowing {
      0% {
        background-position: 0 0;
      }

      50% {
        background-position: 400% 0;
      }

      100% {
        background-position: 0 0;
      }
    }

    /* Personnalisation de la navbar */
    .nav-links li a {
      color: #fff !important;
      font-weight: 600 !important;
      transition: all 0.3s ease !important;
    }

    .nav-links li a:hover {
      color: #D48DD8 !important;
      text-shadow: 0 0 10px rgba(212, 141, 216, 0.7) !important;
    }

    /* Menu déroulant amélioré */
    .dropdown-content {
      background: rgba(255, 255, 255, 0.35) !important;
      backdrop-filter: blur(2.5px) !important;
      border: none !important;
    }

    /* Effet spécial pour les catégories */
    .trending .activity-card {
      position: relative !important;
      overflow: hidden !important;
      transition: all 0.5s ease !important;
      border: 3px solid transparent !important;
      background: white !important;
    }

    .trending .activity-card:hover {
      transform: translateY(-10px) scale(1.02) !important;
      border: 3px solid #D48DD8 !important;
      box-shadow:
        0 10px 20px rgba(151, 104, 209, 0.3),
        0 0 30px rgba(151, 104, 209, 0.5) !important;
    }

    .trending .activity-card:hover h3 {
      color: #9768D1 !important;
      text-shadow: 0 0 10px rgba(151, 104, 209, 0.3) !important;
    }

    .trending .activity-card:hover::after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg,
          rgba(212, 141, 216, 0.2) 0%,
          rgba(151, 104, 209, 0.2) 100%);
      pointer-events: none;
    }

    /* Stylisation spéciale pour les étoiles de notation */
    .star,
    .etoile {
      color: #D48DD8 !important;
      text-shadow: 0 0 10px rgba(151, 104, 209, 0.5) !important;
      transition: all 0.3s ease !important;
    }

    .star.selected,
    .star:hover {
      color: #9768D1 !important;
      text-shadow:
        0 0 10px rgba(151, 104, 209, 0.7),
        0 0 20px rgba(151, 104, 209, 0.5),
        0 0 30px rgba(151, 104, 209, 0.3) !important;
      transform: scale(1.2) !important;
    }

    /* Stylisation du formulaire d'avis */
    #avis-form input,
    #avis-form textarea {
      border: 2px solid rgba(212, 141, 216, 0.3) !important;
      transition: all 0.3s ease !important;
    }

    #avis-form input:focus,
    #avis-form textarea:focus {
      border-color: #9768D1 !important;
      box-shadow: 0 0 15px rgba(151, 104, 209, 0.3) !important;
    }

    /* Boutons de navigation améliorés */
    .btn-nav {
      position: relative !important;
      overflow: hidden !important;
      z-index: 1 !important;
      border-radius: 50% !important;
    }

    .btn-nav::before {
      content: '' !important;
      position: absolute !important;
      top: 0 !important;
      left: -100% !important;
      width: 100% !important;
      height: 100% !important;
      background: linear-gradient(90deg,
          transparent 0%,
          rgba(255, 255, 255, 0.3) 50%,
          transparent 100%) !important;
      transition: left 0.7s ease !important;
    }

    .btn-nav:hover::before {
      left: 100% !important;
    }

    /* Animation fluide pour les cartes d'activités */
    .activite-card {
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
      border: 2px solid transparent !important;
    }

    .activite-card:hover {
      transform: translateY(-10px) !important;
      border: 2px solid #D48DD8 !important;
      box-shadow:
        0 15px 25px rgba(151, 104, 209, 0.2),
        0 0 30px rgba(151, 104, 209, 0.3) !important;
    }

    .activite-card:hover .prix {
      color: #9768D1 !important;
      text-shadow: 0 0 10px rgba(151, 104, 209, 0.3) !important;
    }

    /* Style pour le prix */
    .prix {
      color: #D48DD8 !important;
      transition: all 0.3s ease !important;
    }

    /* Styles pour le panneau de personnalisation */
    .personalization-panel {
      position: fixed;
      top: 180px;
      /* Position légèrement plus haute */
      right: -320px;
      width: 320px;
      background: rgba(245, 240, 255, 0.97);
      /* Fond plus clair avec teinte lilas très légère */
      border-radius: 12px 0 0 12px;
      border-left: 3px solid #D48DD8;
      /* Bordure lilas sur le côté gauche */
      box-shadow: -5px 0 20px rgba(151, 104, 209, 0.2);
      z-index: 9998;
      /* Réduire le z-index pour être sous le bouton */
      transition: right 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      overflow: hidden;
    }

    .personalization-panel.open {
      right: 0 !important;
      /* Forcer la position à droite avec !important */
      display: block !important;
      /* S'assurer que le panneau est visible */
    }

    /* Notification pour indiquer la nouvelle fonctionnalité - SUPPRIMÉE */
    .new-feature-notification {
      display: none;
      /* Masquer complètement la notification */
    }

    @keyframes bounce {

      0%,
      20%,
      50%,
      80%,
      100% {
        transform: translateY(0);
      }

      40% {
        transform: translateY(-10px);
      }

      60% {
        transform: translateY(-5px);
      }
    }

    @keyframes fadeOut {
      from {
        opacity: 1;
      }

      to {
        opacity: 0;
        visibility: hidden;
      }
    }

    .personalization-toggle {
      position: absolute;
      top: 50%;
      left: -90px;
      transform: translateY(-50%);
      z-index: 9999;
    }

    #toggle-personalization {
      width: 90px;
      height: 90px;
      background: linear-gradient(135deg, #D48DD8, #9768D1);
      /* Lilas pastel au lieu de rose vif */
      border: 3px solid white;
      border-radius: 12px 0 0 12px;
      color: white;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 10px 0;
      box-shadow: -3px 0 20px rgba(151, 104, 209, 0.5);
      transition: all 0.3s ease;
      animation: pulse 2s infinite;
      z-index: 9999;
    }

    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(151, 104, 209, 0.7);
      }

      70% {
        box-shadow: 0 0 0 15px rgba(151, 104, 209, 0);
      }

      100% {
        box-shadow: 0 0 0 0 rgba(151, 104, 209, 0);
      }
    }

    #toggle-personalization:hover {
      background: linear-gradient(135deg, #C57EC5, #8656BD);
      transform: scale(1.05);
      box-shadow: -3px 0 25px rgba(151, 104, 209, 0.7);
    }

    #toggle-personalization i {
      font-size: 30px;
      margin-bottom: 5px;
    }

    #toggle-personalization span {
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 600;
    }

    .personalization-content {
      padding: 20px;
      max-height: 70vh;
      /* Réduire légèrement pour s'assurer que tout le contenu est visible */
      overflow-y: auto;
      background: linear-gradient(to bottom, rgba(245, 240, 255, 0.5), rgba(255, 250, 255, 0.8));
      /* Dégradé très léger */
      scrollbar-width: thin;
      /* Pour Firefox */
      scrollbar-color: #9768D1 rgba(255, 255, 255, 0.3);
      /* Pour Firefox */
    }

    /* Style pour les barres de défilement */
    .personalization-content::-webkit-scrollbar {
      width: 8px;
    }

    .personalization-content::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.3);
      border-radius: 10px;
    }

    .personalization-content::-webkit-scrollbar-thumb {
      background: #9768D1;
      border-radius: 10px;
    }

    .personalization-content::-webkit-scrollbar-thumb:hover {
      background: #8056BD;
    }

    .personalization-section {
      margin-bottom: 25px;
      border-bottom: 1px solid rgba(151, 104, 209, 0.2);
      padding-bottom: 20px;
      background-color: rgba(255, 255, 255, 0.7);
      /* Fond blanc semi-transparent */
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(151, 104, 209, 0.1);
    }

    .personalization-section:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .personalization-section h3 {
      color: #9768D1;
      font-size: 18px;
      margin-bottom: 15px;
      position: relative;
      padding-bottom: 8px;
    }

    .personalization-section h3:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 40px;
      height: 2px;
      background-color: #D48DD8;
    }

    /* Options de thème */
    .theme-options {
      display: flex;
      gap: 10px;
    }

    .theme-btn {
      flex: 1;
      background: #f8f8f8;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      padding: 12px;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .theme-btn i {
      font-size: 24px;
      margin-bottom: 8px;
      color: #666;
    }

    .theme-btn span {
      font-size: 14px;
      color: #444;
      font-weight: 500;
    }

    .theme-btn.active {
      border-color: #9768D1;
      background: rgba(151, 104, 209, 0.1);
    }

    .theme-btn.active i,
    .theme-btn.active span {
      color: #9768D1;
    }

    #theme-light i {
      color: #ffa41b;
    }

    #theme-dark i {
      color: #334180;
    }

    /* Contrôles de musique */
    .music-controls {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
    }

    .music-btn {
      flex: 1;
      min-width: 70px;
      background: #f8f8f8;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      padding: 10px;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .music-btn.active {
      border-color: #9768D1;
      background: rgba(151, 104, 209, 0.1);
    }

    .music-btn.active i,
    .music-btn.active span {
      color: #9768D1;
    }

    .music-btn i {
      font-size: 18px;
      margin-bottom: 5px;
      color: #666;
    }

    .music-btn span {
      font-size: 12px;
      color: #444;
    }

    .music-volume {
      display: flex;
      align-items: center;
      width: 100%;
      gap: 10px;
      margin-top: 10px;
    }

    .music-volume i {
      color: #9768D1;
      font-size: 16px;
    }

    #volume-control {
      flex: 1;
      height: 4px;
      -webkit-appearance: none;
      appearance: none;
      background: linear-gradient(to right, #9768D1, #D48DD8);
      border-radius: 10px;
      cursor: pointer;
    }

    #volume-control::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 15px;
      height: 15px;
      border-radius: 50%;
      background: #9768D1;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
    }

    .music-select {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: 2px solid #e0e0e0;
      background: #f8f8f8;
      color: #444;
      font-size: 14px;
      margin-top: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .music-select:focus {
      border-color: #9768D1;
      outline: none;
    }

    /* Inputs de profil */
    .profile-inputs {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .profile-field {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .profile-field label {
      font-size: 14px;
      color: #666;
      font-weight: 500;
    }

    .profile-field select {
      padding: 10px;
      border-radius: 8px;
      border: 2px solid #e0e0e0;
      background: #f8f8f8;
      color: #444;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .profile-field select:focus {
      border-color: #9768D1;
      outline: none;
    }

    .profile-field select[multiple] {
      height: 120px;
    }

    .update-btn {
      background: linear-gradient(135deg, #D48DD8, #9768D1);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 12px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.3s ease;
      margin-top: 5px;
    }

    .update-btn:hover {
      background: linear-gradient(135deg, #C57EC5, #8656BD);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.3);
    }

    .personalized-suggestions {
      margin-top: 15px;
      border-radius: 8px;
      background: rgba(151, 104, 209, 0.05);
      padding: 15px;
      max-height: 300px;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: #9768D1 rgba(255, 255, 255, 0.3);
    }

    .personalized-suggestions::-webkit-scrollbar {
      width: 6px;
    }

    .personalized-suggestions::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.3);
      border-radius: 10px;
    }

    .personalized-suggestions::-webkit-scrollbar-thumb {
      background: #9768D1;
      border-radius: 10px;
    }

    .personalized-suggestions::-webkit-scrollbar-thumb:hover {
      background: #8056BD;
    }

    .suggestion-placeholder {
      color: #888;
      text-align: center;
      font-style: italic;
      font-size: 14px;
    }

    .suggestion-item {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
      padding-bottom: 10px;
      border-bottom: 1px dashed rgba(151, 104, 209, 0.2);
    }

    .suggestion-item:last-child {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
    }

    .suggestion-item img {
      width: 40px;
      height: 40px;
      border-radius: 6px;
      object-fit: cover;
    }

    .favorite-link {
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .favorite-link:hover {
      background-color: rgba(151, 104, 209, 0.1);
      border-radius: 5px;
    }

    .favorite-link:hover h4 {
      color: #D48DD8;
    }

    .suggestion-item .suggestion-content {
      flex: 1;
    }

    .suggestion-item h4 {
      font-size: 14px;
      color: #9768D1;
      margin-bottom: 2px;
    }

    .suggestion-item p {
      font-size: 12px;
      color: #666;
      margin: 0;
    }

    .suggestion-item .suggestion-btn {
      color: #9768D1;
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .suggestion-item .suggestion-btn:hover {
      color: #D48DD8;
      transform: scale(1.1);
    }

    /* Style pour les boutons de favoris actifs */
    .suggestion-btn.favorite-active {
      color: #FF385C !important;
      animation: pulse-heart 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes pulse-heart {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.3);
      }

      100% {
        transform: scale(1);
      }
    }

    /* Style pour les boutons de suppression de favoris */
    .remove-favorite-btn {
      color: #FF385C;
      background: none;
      border: none;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .remove-favorite-btn:hover {
      color: #E4002B;
      transform: scale(1.1);
    }

    /* Styles pour le thème sombre */
    body.dark-theme {
      background-color: #2D2340;
      /* Fond violet foncé */
      color: #f0f0f0;
    }

    body.dark-theme .header {
      background-color: #2A1F3D;
      /* Violet très foncé */
      box-shadow: 0 5px 20px rgba(151, 104, 209, 0.3);
    }

    body.dark-theme .container {
      background-color: #2D2340;
    }

    body.dark-theme h1,
    body.dark-theme h2,
    body.dark-theme h3,
    body.dark-theme .subtitle {
      color: #D48DD8;
      /* Lilas */
    }

    body.dark-theme .activity-card,
    body.dark-theme .category-content .exclusives div,
    body.dark-theme .avis-content {
      background-color: #3A2E52 !important;
      /* Fond violet moyen */
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.2) !important;
      border: 1px solid rgba(151, 104, 209, 0.3) !important;
    }

    body.dark-theme .activity-card h3,
    body.dark-theme .category-content .exclusives span h3 {
      color: #D48DD8 !important;
    }

    body.dark-theme .nav-links li a {
      color: #f0f0f0 !important;
    }

    body.dark-theme .dropdown-content {
      background-color: #3A2E52 !important;
      box-shadow: 0 10px 20px rgba(151, 104, 209, 0.3) !important;
    }

    body.dark-theme .dropdown-content a {
      color: #f0f0f0 !important;
    }

    body.dark-theme .dropdown-content a:hover {
      background-color: #D48DD8 !important;
    }

    body.dark-theme .category-content .exclusives .description-container {
      background-color: #483D61 !important;
      /* Violet doux */
      border-color: #544870 !important;
    }

    body.dark-theme .category-content .exclusives p.description {
      color: #e0d1f0 !important;
      /* Texte lavande clair */
    }

    body.dark-theme .category-content .exclusives span {
      background-color: #3A2E52 !important;
    }

    body.dark-theme .personalization-panel {
      background: rgba(45, 35, 64, 0.97);
      /* Fond violet foncé avec transparence */
      border-left: 3px solid #D48DD8;
      /* Bordure lilas */
      box-shadow: -5px 0 20px rgba(151, 104, 209, 0.3);
    }

    body.dark-theme .personalization-content {
      background: linear-gradient(to bottom, rgba(58, 46, 82, 0.8), rgba(45, 35, 64, 0.9));
      /* Dégradé violet */
    }

    body.dark-theme .personalization-section {
      background-color: rgba(58, 46, 82, 0.8);
      /* Fond violet moyen */
      box-shadow: 0 3px 10px rgba(151, 104, 209, 0.2);
      border: 1px solid rgba(151, 104, 209, 0.2);
    }

    body.dark-theme .theme-btn {
      background: #3A2E52;
      border-color: #544870;
    }

    body.dark-theme .theme-btn span {
      color: #e0d1f0;
    }

    body.dark-theme .theme-btn.active {
      background: rgba(212, 141, 216, 0.2);
      border-color: #D48DD8;
    }

    body.dark-theme .music-btn,
    body.dark-theme .music-select,
    body.dark-theme .profile-field select {
      background: #3A2E52;
      border-color: #544870;
      color: #e0d1f0;
    }

    body.dark-theme .music-btn span {
      color: #e0d1f0;
    }

    body.dark-theme .personalized-suggestions {
      background: rgba(151, 104, 209, 0.1);
    }

    body.dark-theme .suggestion-placeholder {
      color: #BCA8E0;
      /* Lavande clair */
    }

    body.dark-theme .footer-wrapper {
      background-color: #2A1F3D;
      /* Violet très foncé */
    }

    body.dark-theme .footer-content p,
    body.dark-theme .footer-bottom p {
      color: #e0d1f0;
    }

    body.dark-theme .footer-links-bottom a {
      color: #D48DD8;
    }

    body.dark-theme .description-section,
    body.dark-theme .categories-section {
      background: linear-gradient(135deg, #463366, #2a1f3d);
    }

    body.dark-theme .description-text {
      color: #e0d1f0;
    }

    body.dark-theme .description-intro {
      color: #d0b5e3;
    }

    /* Bouton flottant indépendant pour assurer la visibilité */
    .floating-customize-btn {
      position: fixed;
      bottom: 10px;
      right: 30px;
      width: 90px;
      height: 90px;
      background: linear-gradient(135deg, #D48DD8, #9768D1);
      border: 4px solid white;
      border-radius: 50%;
      color: white;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-shadow: 0 5px 25px rgba(151, 104, 209, 0.7);
      z-index: 10001;
      animation: pulse-float 2s infinite;
      text-align: center;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 11px;
      /* Réduit encore la taille de police */
      padding: 5px;
      /* Réduit le padding */
      word-break: break-word;
      /* Permet de briser le mot si nécessaire */
      line-height: 1.1;
      /* Réduit l'interligne */
    }

        .floating-customize-btn1 {
      position: fixed;
      bottom: 110px;
      right: 50px;
      width: 50px;
      height:50px;
      background: linear-gradient(135deg, #D48DD8, #9768D1);
      border: 4px solid white;
      border-radius: 50%;
      color: white;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-shadow: 0 5px 25px rgba(151, 104, 209, 0.7);
      z-index: 10001;
      animation: pulse-float 2s infinite;
      text-align: center;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 11px;
      /* Réduit encore la taille de police */
      padding: 5px;
      /* Réduit le padding */
      word-break: break-word;
      /* Permet de briser le mot si nécessaire */
      line-height: 1.1;
      /* Réduit l'interligne */
    }

    .floating-customize-btn i {
      font-size: 26px;
      /* Réduit encore l'icône */
      margin-bottom: 3px;
      /* Réduit la marge */
    }

        .floating-customize-btn1 i {
      font-size: 26px;
      /* Réduit encore l'icône */
      margin-bottom: 3px;
      /* Réduit la marge */
    }

    @keyframes pulse-float {
      0% {
        box-shadow: 0 0 0 0 rgba(212, 141, 216, 0.7);
        /* Lilas pastel */
        transform: scale(1);
      }

      50% {
        box-shadow: 0 0 25px 10px rgba(212, 141, 216, 0.5);
        /* Lilas pastel */
        transform: scale(1.05);
      }

      100% {
        box-shadow: 0 0 0 0 rgba(212, 141, 216, 0);
        /* Lilas pastel */
        transform: scale(1);
      }
    }

    .social-icons {
      display: flex;
      gap: 15px;
      margin: 15px 0;
    }

    .social-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .social-icon img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .social-icon:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.3);
    }

    .social-icon:hover img {
      transform: scale(1.1);
    }

    .payment-methods {
      display: flex;
      align-items: center;
      gap: 25px;
      margin-top: 15px;
      padding: 10px 0;
    }

    .payment-icon {
      height: 30px;
      width: auto;
      object-fit: contain;
    }

    .newsletter {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
      padding: 30px;
      border-radius: 15px;
      max-width: 1000px;
      margin: 0 auto 30px auto;
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.2);
    }

    .newsletter-left {
      padding-right: 20px;
    }

    .newsletter-left h2 {
      color: #fff;
      font-size: 1.3rem;
      margin-bottom: 5px;
    }

    .newsletter-left h1 {
      color: #fff;
      font-size: 1.8rem;
      margin-top: 0;
      font-weight: bold;
    }

    .newsletter-right {
      flex: 1;
      max-width: 500px;
      margin-left: auto;
    }

    .newsletter-input {
      display: flex;
      align-items: center;
      background: white;
      border-radius: 10px;
      padding: 5px;
      max-width: 100%;
    }

    .newsletter-input input {
      flex: 1;
      padding: 12px 20px;
      border: none;
      outline: none;
      font-size: 1rem;
      background: transparent;
      color: #333;
      min-width: 250px;
    }

    .fotter-btn {
      background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 12px 25px;
      font-size: 1rem;
      cursor: pointer;
      transition: transform 0.3s, box-shadow 0.3s;
      white-space: nowrap;
    }

    .fotter-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.3);
    }

    @media (max-width: 768px) {
      .newsletter {
        flex-direction: column;
        padding: 20px;
        text-align: center;
        margin: 0 20px 30px 20px;
      }

      .newsletter-left {
        padding-right: 0;
        margin-bottom: 15px;
      }

      .newsletter-right {
        width: 100%;
        margin: 0;
      }

      .newsletter-input {
        flex-direction: column;
        gap: 10px;
        padding: 10px;
      }

      .newsletter-input input {
        width: 100%;
        min-width: auto;
        text-align: center;
      }

      .fotter-btn {
        width: 100%;
      }
    }

    .footer-wrapper {
      width: 100vw;
      margin-left: calc(-50vw + 50%);
      background-color: white;
      background-attachment: fixed;
      background-position: center;
      background-size: cover;
      color: #333;
    }

    .footer-content {
      background-color: #f4f4f4;
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
      background-color: #f4f4f4;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 0;
      font-size: 0.9rem;
      color: #333;
    }

    .footer-links-bottom a {
      color: #333;
      margin-left: 20px;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .footer-links-bottom a:hover {
      color: #9768D1;
    }

    .newsletter {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
      padding: 20px 40px;
      border-radius: 50px;
      margin: 0 20px 30px 20px;
    }

    .newsletter-left h2 {
      color: #fff;
      font-size: 1.3rem;
      margin-bottom: 0;
    }

    .newsletter-left h1 {
      color: #fff;
      font-size: 1.5rem;
      margin-top: 0;
    }

    .newsletter-input {
      display: flex;
      align-items: center;
      gap: 10px;
      background: white;
      border-radius: 30px;
      padding: 5px;
    }

    .newsletter-input input {
      padding: 10px 18px;
      border-radius: 30px;
      border: none;
      outline: none;
      font-size: 1rem;
      background: transparent;
      color: #333;
      min-width: 300px;
    }

    .fotter-btn {
      background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
      color: #fff;
      border: none;
      border-radius: 30px;
      padding: 12px 25px;
      font-size: 1rem;
      cursor: pointer;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .fotter-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.3);
    }

    .footer-logo {
      max-width: 120px;
      margin-bottom: 15px;
    }

    .social-icons {
      display: flex;
      gap: 15px;
      margin: 15px 0;
    }

    .social-icons .icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      background: white;
      box-shadow: 0 2px 8px rgba(151, 104, 209, 0.08);
      transition: transform 0.3s, box-shadow 0.3s;
      text-decoration: none;
    }

    .social-icons .icon:hover {
      transform: scale(1.15) translateY(-3px);
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.18);
    }

    .links {
      margin-bottom: 30px;
      min-width: 200px;
    }

    .links p {
      font-weight: bold;
      margin-bottom: 15px;
      color: #9768D1;
    }

    .links a {
      display: block;
      color: #333;
      text-decoration: none;
      margin-bottom: 8px;
      transition: color 0.3s;
    }

    .links a:hover {
      color: #9768D1;
    }

    .payment-methods {
      display: flex;
      align-items: center;
      gap: 25px;
      margin-top: 15px;
      padding: 10px 0;
    }

    .payment-icon {
      height: 30px;
      width: auto;
      object-fit: contain;
    }

    .footer-section {
      width: 100%;
      background: none;
      color: #333;
      margin-top: 20px;
    }

    .footer-separator {
      height: 1px;
      background-color: #e0e0e0;
      margin: 20px 0;
    }

    @media (max-width: 900px) {
      .footer-content {
        flex-direction: column;
        align-items: center;
        padding: 60px 10px 30px;
      }

      .links {
        min-width: 150px;
        margin-bottom: 20px;
      }
    }

    @media (max-width: 600px) {
      .newsletter {
        flex-direction: column;
        padding: 25px 10px;
        text-align: center;
      }

      .footer-content {
        padding: 40px 5px 20px;
      }
    }
  </style>
</head>

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



  <!-- Header -->
  <header class="header header-activite" style="background-image: url('images/banner act.jpg'); background-size: cover; background-position: center; padding-top: 10px;">
    <nav style="margin-top: -90px;">
      <img src="images/logo.png" class="logo" alt="Logo ClickNGo" style="filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.9));">
      <ul class="nav-links">
        <li><a href="/mvcUtilisateur/View/FrontOffice/index.php">Accueil</a></li>
        <li class="dropdown">
          <a href="/mvcact/view/front office/activite.php" class="dropbtn">Activités</a>
          <div class="dropdown-content">
            <a href="#categories-section">Catégories</a>
            <a href="#activites-pres-de-vous">Activités près de vous</a>
            <a href="#categories-entreprises">Catégories d'entreprises</a>
            <a href="#nos-atouts">Nos atouts</a>
            <a href="#description-activites">Nos activités exceptionnelles</a>
            <a href="#avis-clients">Avis clients</a>
          </div>
        </li>
        <li><a href="/mvcEvent/View/FrontOffice/evenemant.php">Événements</a></li>
        <li><a href="/mvcProduit/view/front office/produit.php">Produits</a></li>
        <li><a href="/mvcCovoiturage/view/index.php">Transports</a></li>
        <li><a href="/mvcSponsor/crud/view/front/index.php">Sponsors</a></li>
      </ul>

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

  </style>

    </nav>
    <h1>Choisissez votre style d'activité</h1>
  </header>

  <!-- Section de personnalisation en temps réel -->
  <div class="personalization-panel">
    <div class="personalization-toggle">
      <button id="toggle-personalization">
        <i class="fas fa-cog"></i>
        <span>Options</span>
      </button>
    </div>
    <div class="personalization-content">
      <div class="personalization-section">
        <h3>Thème</h3>
        <div class="theme-options">
          <button id="theme-light" class="theme-btn active">
            <i class="fas fa-sun"></i>
            <span>Clair</span>
          </button>
          <button id="theme-dark" class="theme-btn">
            <i class="fas fa-moon"></i>
            <span>Sombre</span>
          </button>
        </div>
      </div>
      <div class="personalization-section">
        <h3>Musique d'ambiance</h3>
        <div class="music-controls">
          <button id="music-play" class="music-btn">
            <i class="fas fa-play"></i>
            <span>Lecture</span>
          </button>
          <button id="music-pause" class="music-btn">
            <i class="fas fa-pause"></i>
            <span>Pause</span>
          </button>
          <div class="music-volume">
            <i class="fas fa-volume-down"></i>
            <input type="range" id="volume-control" min="0" max="100" value="50">
            <i class="fas fa-volume-up"></i>
          </div>
          <select id="music-select" class="music-select">
            <option value="ambient">Ambiance relaxante</option>
            <option value="upbeat">Énergique</option>
            <option value="jazz">Jazz lounge</option>
            <option value="nature">Sons de la nature</option>
          </select>
        </div>
        <audio id="background-music" loop preload="none">
          <source src="sounds/ambient.mp3" type="audio/mpeg">
          Votre navigateur ne prend pas en charge l'élément audio.
        </audio>
      </div>
      <div class="personalization-section">
        <h3>Suggestions personnalisées</h3>
        <div class="profile-inputs">
          <div class="profile-field">
            <label for="user-age">Âge</label>
            <select id="user-age">
              <option value="">Sélectionner...</option>
              <option value="under18">Moins de 18 ans</option>
              <option value="18-25">18-25 ans</option>
              <option value="26-35">26-35 ans</option>
              <option value="36-50">36-50 ans</option>
              <option value="over50">Plus de 50 ans</option>
            </select>
          </div>
          <div class="profile-field">
            <label for="user-interests">Intérêts</label>
            <select id="user-interests" multiple>
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
          <button id="update-suggestions" class="update-btn">
            <i class="fas fa-sync-alt"></i>
            <span>Mettre à jour</span>
          </button>
        </div>
        <div id="personalized-suggestions" class="personalized-suggestions">
          <p class="suggestion-placeholder">Renseignez votre profil pour obtenir des suggestions personnalisées</p>
        </div>
      </div>
      <!-- Nouvelle section Favoris -->
      <div class="personalization-section">
        <h3>Mes Favoris</h3>
        <div id="favorites-container" class="personalized-suggestions">
          <p class="suggestion-placeholder">Aucun favori pour le moment. Utilisez le cœur pour ajouter des activités à vos favoris.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Notification pour la nouvelle fonctionnalité -->
  <div class="new-feature-notification">Nouveau ! Personnalisez votre expérience</div>

  <!-- Catégories -->
  <div class="container">
    <h2 id="categories-section" class="subtitle" style="margin-top: 40px; margin-bottom: 30px; scroll-margin-top: 80px;">Catégories</h2>
    <div class="trending">
      <!-- 13 catégories -->
      <div class="activity-card category-toggle" data-target="#Ateliers"><img src="images/atelier.jpg" alt="">
        <h3>Ateliers</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#bien-etre"><img src="images/bien.jpg" alt="">
        <h3>Bien-être</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#Aérien"><img src="images/air.jpg" alt="">
        <h3>Aérien</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#Aquatique"><img src="images/image-3.png" alt="">
        <h3>Aquatique</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#Terestre"><img src="images/image-2.png" alt="">
        <h3>Terrestre</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#Insolite"><img src="images/insolite.jpg" alt="">
        <h3>Insolite</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#culture"><img src="images/culture.jpg" alt="">
        <h3>Culture</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#Détente"><img src="images/detente.jpg" alt="">
        <h3>Détente</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#sport"><img src="images/sport.jpg" alt="">
        <h3>Sport</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#nature"><img src="images/nature.jpg" alt="">
        <h3>Nature</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#aventure"><img src="images/aventure.jpg" alt="">
        <h3>Aventure</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#Famille"><img src="images/1.jpg" alt="">
        <h3>Famille</h3>
      </div>
      <div class="activity-card category-toggle" data-target="#Extreme"><img src="images/extreme.jpg" alt="">
        <h3>Extrême</h3>
      </div>
    </div>

    <!-- Sections dynamiques -->
    <?php
    // Liste des catégories et leurs sections
    $categories = [
      'Ateliers' => 'Activités Ateliers',
      'bien-etre' => 'Activités Bien-être',
      'Aérien' => 'Activités Aériennes',
      'Aquatique' => 'Activités Aquatiques',
      'Terestre' => 'Activités Terrestres',
      'Insolite' => 'Activités Insolites',
      'culture' => 'Activités Culturelles',
      'Détente' => 'Activités de Détente',
      'sport' => 'Activités Sportives',
      'nature' => 'Activités Nature',
      'aventure' => 'Activités d\'Aventure',
      'Famille' => 'Activités en Famille',
      'Extreme' => 'Activités Extrêmes'
    ];

    // Générer les sections pour chaque catégorie
    foreach ($categories as $categoryId => $categoryTitle):
    ?>
      <div id="<?php echo $categoryId; ?>" class="category-content" style="display:none;">
        <h3><?php echo $categoryTitle; ?></h3>
        <div class="exclusives">
          <?php
          // Si la catégorie existe dans nos données
          if (isset($categorizedActivities[$categoryId]) && !empty($categorizedActivities[$categoryId])):
            foreach ($categorizedActivities[$categoryId] as $activity):
          ?>
              <div>
                <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
                <div class="description-container">
                  <p class="description"><?php echo substr(htmlspecialchars($activity['description']), 0, 100); ?>...</p>
                </div>
                <span>
                  <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                  <p class="price"><?php echo htmlspecialchars($activity['price']); ?> DT</p>
                  <?php if (isset($_SESSION['user'])): ?>
                    <a href="reservation.php?id=<?= urlencode($activity['id']) ?>" class="register-btn">Réserver</a>
                  <?php else: ?>
                    <a href="/mvcUtilisateur/View/BackOffice/login/login.php" class="register-btn">Réserver</a>
                  <?php endif; ?>

                </span>
              </div>
            <?php
            endforeach;
          else:
            // Afficher des activités statiques par défaut si aucune activité n'est trouvée dans la base de données
            ?>
            <div>
              <img src="images/default-activity.jpg" alt="Aucune activité disponible">
              <div class="description-container">
                <p class="description">Revenez plus tard pour voir nos nouvelles activités! Nous ajoutons régulièrement de nouvelles expériences passionnantes.</p>
              </div>
              <span>
                <h3>Aucune activité disponible</h3>
                <p class="price">- DT</p>
              </span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Section Nos activités près de vous -->
    <section id="activites-pres-de-vous" class="activites-container" style="margin-top: 80px; margin-bottom: 80px; scroll-margin-top: 80px;">
      <div class="section-header">
        <h2>Nos activités près de vous</h2>
        <a href="#" class="voir-tout" id="voir-tout-activites">Tout afficher &gt;</a>
      </div>
      <!-- Boutons régions -->
      <div class="categories-buttons" id="regions-buttons" style="margin-bottom: 30px;">
        <?php
        $regions = [
          'Tunis',
          'Ariana',
          'Ben Arous',
          'Manouba',
          'Nabeul',
          'Zaghouan',
          'Bizerte',
          'Béja',
          'Jendouba',
          'Kef',
          'Siliana',
          'Sousse',
          'Monastir',
          'Mahdia',
          'Sfax',
          'Kairouan',
          'Kasserine',
          'Sidi Bouzid',
          'Gabès',
          'Medenine',
          'Tataouine',
          'Gafsa',
          'Tozeur',
          'Kebili'
        ];
        foreach ($regions as $region) {
          echo '<button class="cat-btn region-btn" data-region="' . htmlspecialchars($region) . '">' . htmlspecialchars($region) . '</button>';
        }
        ?>
      </div>
      <div class="activites-grid" id="activites-par-region">
        <!-- Les activités filtrées s'afficheront ici -->
      </div>
    </section>
    <script>
      // Générer le tableau des activités côté JS
      const allActivities = <?php echo json_encode($allActivities); ?>;
      // Fonction pour afficher les activités d'une région
      function renderActivities(region) {
        const grid = document.getElementById('activites-par-region');
        grid.innerHTML = '';
        let filtered = region ? allActivities.filter(a => a.location === region) : allActivities;
        if (filtered.length === 0) {
          grid.innerHTML = '<p style="text-align:center;color:#888;width:100%;">Aucune activité trouvée pour cette région.</p>';
          return;
        }
        filtered.forEach(activity => {
          grid.innerHTML += `
            <div class="activite-card">
              <img src="${activity.image}" alt="${activity.name}">
              <div class="activite-content">
                <p class="categorie">${activity.category}</p>
                <h3>${activity.name}</h3>
                <p class="prix">${activity.price} DT <span>/ personne</span></p>
                <p class="note"><span class="etoile">★</span> ${activity.rating ? activity.rating : ''}</p>
<?php if (isset($_SESSION['user'])): ?>
  <a href="reservation.php?id=<?= urlencode($activity['id']) ?>" class="register-btn">Réserver</a>
<?php else: ?>
  <a href="/mvcUtilisateur/View/BackOffice/login/login.php" class="register-btn">Réserver</a>
<?php endif; ?>

              </div>
            </div>
          `;
        });
      }
      // Gestion des clics sur les boutons région
      document.addEventListener('DOMContentLoaded', function() {
        const regionBtns = document.querySelectorAll('.region-btn');
        regionBtns.forEach(btn => {
          btn.addEventListener('click', function() {
            // Si déjà actif, on désactive et on masque les activités
            if (this.classList.contains('active')) {
              this.classList.remove('active');
              document.getElementById('activites-par-region').innerHTML = '<p style="text-align:center;color:#888;width:100%;">Sélectionnez une région pour voir les activités.</p>';
              return;
            }
            regionBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            renderActivities(this.dataset.region);
          });
        });
        // Bouton "Tout afficher"
        document.getElementById('voir-tout-activites').addEventListener('click', function(e) {
          e.preventDefault();
          regionBtns.forEach(b => b.classList.remove('active'));
          renderActivities(null);
        });
        // Affichage initial : aucune activité
        document.getElementById('activites-par-region').innerHTML = '<p style="text-align:center;color:#888;width:100%;">Sélectionnez une région pour voir les activités.</p>';
      });
    </script>

    <!-- Section Découvrez nos catégories d'entreprises - Déplacée avant les atouts -->
    <section id="categories-entreprises" class="categories-section" style="scroll-margin-top: 80px;">
      <h2 class="section-title">Découvrez nos catégories d'entreprises</h2>
      <div class="categories-buttons">
        <a href="categorie.php" class="cat-btn"><i class="fa-solid fa-users-line"></i>Team building</a>
        <a href="categorie.php" class="cat-btn"><i class="fa-solid fa-wand-magic-sparkles"></i>Animation</a>
        <a href="categorie.php" class="cat-btn"><i class="fa-solid fa-handshake"></i>Réunions</a>
        <a href="categorie.php" class="cat-btn"><i class="fa-solid fa-champagne-glasses"></i>Soirée</a>
        <a href="categorie.php" class="cat-btn"><i class="fa-solid fa-utensils"></i>Repas</a>
        <a href="categorie.php" class="cat-btn"><i class="fa-solid fa-calendar-day"></i>Fundays</a>
      </div>
    </section>

    <!-- Section atouts - Déplacée après la section des entreprises -->
    <div id="nos-atouts" class="see-all-events" style="margin-top: 60px; margin-bottom: 60px; scroll-margin-top: 80px;">
      <section class="atouts">
        <div class="atout">
          <img src="images/l1.webp" alt="Activités" />
          <div>
            <h3>Des offres adaptées à votre événement</h3>
          </div>
        </div>
        <div class="atout">
          <img src="images/l2.webp" alt="Prix" />
          <div>
            <h3>Même prix qu'en direct</h3>
          </div>
        </div>
        <div class="atout">
          <img src="images/l3.webp" alt="Contact" />
          <div>
            <h3>Des devis faciles à comparer</h3>
          </div>
        </div>
        <div class="atout">
          <img src="images/l4.webp" alt="Contact" />
          <div>
            <h3>Un contact dédié à votre projet</h3>
          </div>
        </div>
      </section>
    </div>

    <!-- Nouvelle section descriptive -->
    <section id="description-activites" class="description-section" style="margin-top: 60px; margin-bottom: 60px; scroll-margin-top: 80px;">
      <div class="description-container">
        <h2>Des activités exceptionnelles pour tous les goûts</h2>
        <div class="description-content">
          <div class="description-text">
            <p class="description-intro">Découvrez chez <strong>Click'N'Go</strong> la plus grande sélection d'activités en Tunisie, sélectionnées avec soin pour des expériences inoubliables.</p>

            <p>Que vous soyez amateur de sensations fortes, passionné de culture, ou à la recherche d'une parenthèse de détente, notre plateforme vous propose des activités uniques dans tout le pays.</p>

            <p>Des ateliers créatifs aux activités sportives, chaque activité est testée pour vous assurer:</p>

            <ul class="benefits-list">
              <li><i class="fa-solid fa-check"></i> Une qualité irréprochable</li>
              <li><i class="fa-solid fa-check"></i> Des prestataires professionnels</li>
              <li><i class="fa-solid fa-check"></i> Les meilleurs prix garantis</li>
              <li><i class="fa-solid fa-check"></i> Un service client 7j/7</li>
            </ul>

            <p class="description-highlight">Avec plus de 95% de clients satisfaits, Click'N'Go s'impose comme la référence des activités de loisirs en Tunisie.</p>
          </div>
          <div class="description-stats">
            <div class="stat-item">
              <span class="stat-number">500+</span>
              <span class="stat-label">Activités</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">50+</span>
              <span class="stat-label">Villes</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">10k+</span>
              <span class="stat-label">Réservations</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">95%</span>
              <span class="stat-label">Satisfaction</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section Avis clients -->
    <section id="avis-clients" class="avis-section" style="margin-top: 80px; margin-bottom: 80px; scroll-margin-top: 80px;">
      <div class="avis-header">
        <h2><span class="ecriture-plume">Nos clients adorent !</span> <span class="etoile">★</span> <?php echo number_format($averageRating, 1); ?>/5</h2>
        <p class="ecriture-plume"><?php echo count($approvedReviews); ?> avis pour vous aider à choisir</p>
      </div>

      <div class="avis-nav">
        <button class="btn-nav" onclick="changeAvis(-1)">←</button>
        <button class="btn-nav" onclick="changeAvis(1)">→</button>
      </div>

      <!-- WRAPPER pour scroll horizontal -->
      <div class="avis-slider-wrapper">
        <div class="avis-slider" id="avis-slider">
          <?php if (!empty($approvedReviews)): ?>
            <?php foreach ($approvedReviews as $index => $review): ?>
              <div class="avis-slide">
                <div class="avis-content">
                  <div class="avis-image">
                    <?php
                    // Utiliser l'image uploaddée si disponible, sinon utiliser l'image de l'activité ou une image par défaut
                    $imagePath = 'images/default-review.jpg'; // Image par défaut

                    if (!empty($review['image_path'])) {
                      // Utiliser l'image téléchargée avec l'avis
                      $imagePath = $review['image_path'];
                      // Debug - à retirer après vérification
                      echo "<!-- Image path from DB: " . htmlspecialchars($review['image_path']) . " -->";
                    } else {
                      // Sinon, essayer de trouver une image correspondante dans les activités
                      foreach ($allActivities as $activity) {
                        if (strtolower($activity['name']) === strtolower($review['activity_name'])) {
                          $imagePath = $activity['image'];
                          break;
                        }
                      }
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($review['activity_name']); ?>" onerror="this.src='images/default-review.jpg'; console.log('Image not found: ' + this.src);">
                  </div>
                  <div class="avis-card">
                    <h3><?php echo htmlspecialchars($review['activity_name']); ?></h3>
                    <div class="avis-rating">
                      <span class="etoile">★</span> <?php echo $review['rating']; ?>/5
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    <p class="avis-auteur"><?php echo htmlspecialchars($review['customer_name']); ?></p>
                    <p class="avis-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></p>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <!-- Avis par défaut si aucun avis approuvé -->
            <div class="avis-slide">
              <div class="avis-content">
                <div class="avis-image">
                  <img src="images/para2.jpg" alt="Saut en parachute">
                </div>
                <div class="avis-card">
                  <h3>Saut en parachute</h3>
                  <div class="avis-rating"><span class="etoile">★</span> 5/5</div>
                  <p>Équipe hyper sympa: au top! Sensations garanties, mais on se sent en toute confiance ! Moment inoubliable .. je n'ai qu'une envie: Recommencer !! Le kiff total</p>
                  <p class="avis-auteur">Pauline</p>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <section class="avis-form-section">
        <h3>Laissez votre avis</h3>
        <?php
        $loggedIn = isset($_SESSION['user']); // ou 'customer', selon ta structure
        $customer_name = $loggedIn ? htmlspecialchars($_SESSION['user']['full_name']) : '';
        $customer_email = $loggedIn ? htmlspecialchars($_SESSION['user']['email']) : '';
        ?>
        <form id="avis-form" method="post" enctype="multipart/form-data" action="<?= $loggedIn ? 'submit_avis.php' : 'login.php' ?>">
          <div class="avis-stars" id="avis-stars">
            <span class="star" data-value="1">★</span>
            <span class="star" data-value="2">★</span>
            <span class="star" data-value="3">★</span>
            <span class="star" data-value="4">★</span>
            <span class="star" data-value="5">★</span>
          </div>

          <input type="hidden" id="rating" name="rating" value="0">
          <input type="hidden" id="activity_id" name="activity_id" value="<?php if (isset($_GET['id'])) echo intval($_GET['id']); ?>">

          <input type="text" id="customer_name" name="customer_name" placeholder="Votre prénom"
            value="<?= $customer_name ?>" <?= $loggedIn ? 'readonly' : 'required' ?>>

          <input type="email" id="customer_email" name="customer_email" placeholder="Votre email"
            value="<?= $customer_email ?>" <?= $loggedIn ? 'readonly' : 'required' ?>>

          <input type="text" id="activity_name" name="activity_name" placeholder="Nom de l'activité" required>

          <textarea id="comment" name="comment" placeholder="Votre avis..." required></textarea>

          <div class="image-upload">
            <label for="review_image">Ajouter une image (optionnel):</label>
            <input type="file" id="review_image" name="review_image" accept="image/*">
            <div id="image_preview" class="image-preview"></div>
          </div>

          <button type="submit"><?= $loggedIn ? 'Envoyer' : 'Connectez-vous pour commenter' ?></button>
        </form>

        <div id="avis-message" class="avis-message" style="display: none;"></div>
      </section>
    </section>
  </div>

  <!-- Script -->
  <script>
    $(document).ready(function() {
      $(".category-toggle").click(function() {
        const target = $(this).data("target");
        $(".category-content").not(target).slideUp();
        $(target).slideToggle();
      });

      // Correction du bouton de personnalisation
      console.log("Boutons trouvés:", $(".floating-customize-btn").length, $("#toggle-personalization").length);

      $(".floating-customize-btn, #toggle-personalization").off('click').on('click', function(e) {
        console.log("Bouton cliqué !");
        console.log("Cible:", this);

        $(".personalization-panel").toggleClass("open");
        console.log("Panel ouvert:", $(".personalization-panel").hasClass("open"));

        // Animation du bouton flottant
        $(".floating-customize-btn").toggleClass("active");

        // Repositionnement du bouton lorsque le panneau est ouvert
        if ($(".personalization-panel").hasClass("open")) {
          $(".floating-customize-btn").css({
            "right": "350px",
            "transition": "right 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)"
          });
        } else {
          $(".floating-customize-btn").css("right", "30px");
        }

        e.preventDefault(); // Empêcher la propagation de l'événement
        return false;
      });
    });
  </script>

  <script>
    // Script pour les étoiles de notation
    let selectedNote = 0;
    const stars = document.querySelectorAll('.star');

    function updateStars(note) {
      stars.forEach((star, index) => {
        if (index < note) {
          star.classList.add('selected');
        } else {
          star.classList.remove('selected');
        }
      });
      // Mettre à jour la valeur du champ caché
      document.getElementById('rating').value = note;
    }

    stars.forEach(star => {
      star.addEventListener('click', function() {
        selectedNote = parseInt(this.getAttribute('data-value'));
        updateStars(selectedNote);
      });

      star.addEventListener('mouseover', function() {
        const note = parseInt(this.getAttribute('data-value'));
        updateStars(note);
      });

      star.addEventListener('mouseout', function() {
        updateStars(selectedNote);
      });
    });

    document.getElementById('avis-form').addEventListener('submit', function(e) {
      e.preventDefault();

      // Vérifier que tous les champs sont remplis
      const customerName = document.getElementById('customer_name').value;
      const customerEmail = document.getElementById('customer_email').value;
      const activityName = document.getElementById('activity_name').value;
      const comment = document.getElementById('comment').value;
      const rating = document.getElementById('rating').value;
      const reviewImage = document.getElementById('review_image').files[0];

      if (!customerName || !customerEmail || !activityName || !comment || rating === "0") {
        showMessage("Veuillez remplir tous les champs et attribuer une note.", "error");
        return;
      }

      // Préparer les données
      const formData = new FormData();
      formData.append('customer_name', customerName);
      formData.append('customer_email', customerEmail);
      formData.append('activity_name', activityName);
      formData.append('comment', comment);
      formData.append('rating', rating);

      // Ajouter l'image si elle existe
      if (reviewImage) {
        formData.append('review_image', reviewImage);
      }

      // Envoyer la requête (chemin absolu pour éviter les problèmes)
      fetch('../../process_review_frontend.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showMessage(data.message, "success");
            this.reset();
            updateStars(0);
            selectedNote = 0;
            // Réinitialiser l'aperçu de l'image
            document.getElementById('image_preview').style.display = 'none';
            document.getElementById('image_preview').innerHTML = '';
          } else {
            showMessage(data.message, "error");
          }
        })
        .catch(error => {
          console.error('Erreur:', error);
          showMessage("Une erreur est survenue lors de l'envoi de votre avis.", "error");
        });
    });

    // Prévisualisation de l'image
    document.getElementById('review_image').addEventListener('change', function() {
      const file = this.files[0];
      const preview = document.getElementById('image_preview');

      if (file) {
        const reader = new FileReader();

        reader.onload = function(e) {
          preview.innerHTML = `<img src="${e.target.result}" alt="Aperçu de l'image">`;
          preview.style.display = 'block';
        };

        reader.readAsDataURL(file);
      } else {
        preview.innerHTML = '';
        preview.style.display = 'none';
      }
    });

    function showMessage(message, type) {
      const messageElement = document.getElementById('avis-message');
      messageElement.textContent = message;
      messageElement.className = 'avis-message ' + type;
      messageElement.style.display = 'block';

      // Masquer le message après 5 secondes
      setTimeout(() => {
        messageElement.style.display = 'none';
      }, 5000);
    }
  </script>

  <script>
    let currentSlide = 0;
    const slider = document.getElementById("avis-slider");
    const slides = document.querySelectorAll(".avis-slide");
    const totalSlides = slides.length;

    function changeAvis(direction) {
      if (totalSlides <= 1) return; // Ne rien faire s'il n'y a qu'un seul avis

      currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
      const offset = -currentSlide * 100;
      slider.style.transform = `translateX(${offset}%)`;

      // Mettre à jour l'état des boutons de navigation
      updateNavigationButtons();
    }

    function updateNavigationButtons() {
      const prevButton = document.querySelector(".btn-nav:first-child");
      const nextButton = document.querySelector(".btn-nav:last-child");

      // Désactiver le bouton précédent au premier slide
      if (prevButton && nextButton) {
        if (currentSlide === 0) {
          prevButton.classList.add("disabled");
        } else {
          prevButton.classList.remove("disabled");
        }

        // Désactiver le bouton suivant au dernier slide
        if (currentSlide === totalSlides - 1) {
          nextButton.classList.add("disabled");
        } else {
          nextButton.classList.remove("disabled");
        }
      }
    }

    // Initialiser l'état des boutons au chargement
    document.addEventListener("DOMContentLoaded", function() {
      updateNavigationButtons();
    });
  </script>

  <script>
    // JavaScript pour le défilement fluide vers les sections
    document.addEventListener('DOMContentLoaded', function() {
      // Récupérer tous les liens de la dropdown
      const dropdownLinks = document.querySelectorAll('.dropdown-content a');

      // Ajouter un gestionnaire d'événements pour chaque lien
      dropdownLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();

          // Récupérer l'ID de la section cible
          const targetId = this.getAttribute('href');
          const targetSection = document.querySelector(targetId);

          // Faire défiler la page vers la section
          if (targetSection) {
            window.scrollTo({
              top: targetSection.offsetTop - 50,
              behavior: 'smooth'
            });

            // Mettre à jour l'URL avec l'ancre
            history.pushState(null, null, targetId);

            // Marquer le lien actif
            dropdownLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
          }
        });
      });

      // Vérifier si l'URL contient une ancre au chargement
      if (window.location.hash) {
        const targetSection = document.querySelector(window.location.hash);
        if (targetSection) {
          setTimeout(() => {
            window.scrollTo({
              top: targetSection.offsetTop - 50,
              behavior: 'smooth'
            });

            // Marquer le lien actif
            const activeLink = document.querySelector(`.dropdown-content a[href="${window.location.hash}"]`);
            if (activeLink) {
              activeLink.classList.add('active');
            }
          }, 300);
        }
      }
    });
  </script>

  <!-- Bouton flottant de personnalisation -->
  <div class="floating-customize-btn">
    <i class="fas fa-magic"></i>
    <span>Personnaliser</span>
  </div>

<!-- Bouton flottant de personnalisation -->
<a href="tinder.php" class="floating-customize-btn1" title="Voir les activités en mode swipe">
  <i class="fa fa-fire"></i>
</a>


  <!-- Nouveau Footer -->
  <div class="footer-wrapper">
    <div class="newsletter">
      <div class="newsletter-left">
        <h2>Abonnez-vous à notre</h2>
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
          <a href="#" class="icon" style="color: #0072b1;"><i class="fa-brands fa-linkedin"></i></a>
          <a href="#" class="icon" style="color: #E1306C;"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" class="icon" style="color: #FF0050;"><i class="fa-brands fa-tiktok"></i></a>
          <a href="#" class="icon" style="color: #4267B2;"><i class="fa-brands fa-facebook"></i></a>
        </div>
      </div>
      <div class="links">
        <p>Moyens de paiement</p>
        <div class="payment-methods">
          <img src="images/visa.webp" alt="Visa" class="payment-icon">
          <img src="images/mastercard-v2.webp" alt="Mastercard" class="payment-icon">
          <img src="images/logo-cb.webp" alt="CB" class="payment-icon">
          <img src="images/paypal.webp" alt="PayPal" class="payment-icon">
        </div>
      </div>
      <div class="links">
        <p>À propos</p>
        <a href="about.php">À propos </a>
        <a href="presse.php">Presse</a>
        <a href="nous-rejoindre.php">Nous rejoindre</a>
      </div>
      <div class="links">
        <p>Liens utiles</p>
        <a href="devenir-partenaire.php">Devenir partenaire</a>
        <a href="faq.php">FAQ - Besoin d'aide ?</a>
        <a href="avis.php">Tous les avis click'N'go</a>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© click'N'go 2025 - tous droits réservés</p>
      <div class="footer-links-bottom">
        <a href="#">Conditions générales</a>
        <a href="#">Mentions légales</a>
      </div>
    </div>
  </div>
  </div>
  <style>
    .footer-wrapper {
      width: 100vw;
      margin-left: calc(-50vw + 50%);
      background-color: white;
      background-attachment: fixed;
      background-position: center;
      background-size: cover;
      color: #333;
    }

    .footer-content {
      background-color: #f4f4f4;
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
      background-color: #f4f4f4;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 0;
      font-size: 0.9rem;
      color: #333;
    }

    .footer-links-bottom a {
      color: #333;
      margin-left: 20px;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .footer-links-bottom a:hover {
      color: #9768D1;
    }

    .newsletter {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
      padding: 30px;
      border-radius: 15px;
      max-width: 1000px;
      margin: 0 auto 30px auto;
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.2);
    }

    .newsletter-left {
      padding-right: 20px;
    }

    .newsletter-left h2 {
      color: #fff;
      font-size: 1.3rem;
      margin-bottom: 5px;
    }

    .newsletter-left h1 {
      color: #fff;
      font-size: 1.8rem;
      margin-top: 0;
      font-weight: bold;
    }

    .newsletter-right {
      flex: 1;
      max-width: 500px;
      margin-left: auto;
    }

    .newsletter-input {
      display: flex;
      align-items: center;
      background: white;
      border-radius: 10px;
      padding: 5px;
      max-width: 100%;
    }

    .newsletter-input input {
      flex: 1;
      padding: 12px 20px;
      border: none;
      outline: none;
      font-size: 1rem;
      background: transparent;
      color: #333;
      min-width: 250px;
    }

    .fotter-btn {
      background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 12px 25px;
      font-size: 1rem;
      cursor: pointer;
      transition: transform 0.3s, box-shadow 0.3s;
      white-space: nowrap;
    }

    .fotter-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.3);
    }

    .footer-logo {
      max-width: 120px;
      margin-bottom: 15px;
    }

    .social-icons {
      display: flex;
      gap: 15px;
      margin: 15px 0;
    }

    .social-icons .icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      background: white;
      box-shadow: 0 2px 8px rgba(151, 104, 209, 0.08);
      transition: transform 0.3s, box-shadow 0.3s;
      text-decoration: none;
    }

    .social-icons .icon:hover {
      transform: scale(1.15) translateY(-3px);
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.18);
    }

    .links {
      margin-bottom: 30px;
      min-width: 200px;
    }

    .links p {
      font-weight: bold;
      margin-bottom: 15px;
      color: #9768D1;
    }

    .links a {
      display: block;
      color: #333;
      text-decoration: none;
      margin-bottom: 8px;
      transition: color 0.3s;
    }

    .links a:hover {
      color: #9768D1;
    }

    .payment-methods {
      display: flex;
      align-items: center;
      gap: 25px;
      margin-top: 15px;
      padding: 10px 0;
    }

    .payment-icon {
      height: 30px;
      width: auto;
      object-fit: contain;
    }

    .footer-section {
      width: 100%;
      background: none;
      color: #333;
      margin-top: 20px;
    }

    .footer-separator {
      height: 1px;
      background-color: #e0e0e0;
      margin: 20px 0;
    }

    @media (max-width: 900px) {
      .footer-content {
        flex-direction: column;
        align-items: center;
        padding: 60px 10px 30px;
      }

      .links {
        min-width: 150px;
        margin-bottom: 20px;
      }
    }

    @media (max-width: 600px) {
      .newsletter {
        flex-direction: column;
        padding: 25px 10px;
        text-align: center;
      }

      .footer-content {
        padding: 40px 5px 20px;
      }
    }
  </style>

  <!-- Script pour le panneau de personnalisation -->
  <script>
    $(document).ready(function() {
      // Gestion du thème
      $("#theme-light").click(function() {
        $("body").removeClass("dark-theme");
        $(".theme-btn").removeClass("active");
        $(this).addClass("active");
      });

      $("#theme-dark").click(function() {
        $("body").addClass("dark-theme");
        $(".theme-btn").removeClass("active");
        $(this).addClass("active");
      });

      // Gestion de la musique
      const backgroundMusic = document.getElementById("background-music");

      $("#music-play").click(function() {
        backgroundMusic.play();
        $(".music-btn").removeClass("active");
        $(this).addClass("active");
      });

      $("#music-pause").click(function() {
        backgroundMusic.pause();
        $(".music-btn").removeClass("active");
        $(this).addClass("active");
      });

      // Contrôle du volume
      $("#volume-control").on("input", function() {
        backgroundMusic.volume = $(this).val() / 100;
      });

      // Changement de piste audio
      $("#music-select").change(function() {
        const selectedMusic = $(this).val();
        backgroundMusic.querySelector("source").src = `sounds/${selectedMusic}.mp3`;
        backgroundMusic.load();
        if ($("#music-play").hasClass("active")) {
          backgroundMusic.play();
        }
      });

      // Mise à jour des suggestions
      $("#update-suggestions").click(function() {
        const age = $("#user-age").val();
        const interests = Array.from($("#user-interests option:selected")).map(option => option.value);

        console.log("Âge sélectionné:", age);
        console.log("Intérêts sélectionnés:", interests);

        if (!age && (!interests || interests.length === 0)) {
          $("#personalized-suggestions").html('<p class="suggestion-placeholder">Veuillez renseigner votre profil pour obtenir des suggestions</p>');
          return;
        }

        // Afficher l'animation de chargement
        $("#personalized-suggestions").html(`
        <div class="loading-container" style="text-align: center; padding: 20px;">
          <div class="spinner" style="border: 4px solid rgba(151, 104, 209, 0.1); border-radius: 50%; border-top: 4px solid #9768D1; width: 40px; height: 40px; margin: 0 auto; animation: spin 1s linear infinite;"></div>
          <p style="margin-top: 10px; color: #9768D1;">Chargement des suggestions personnalisées...</p>
        </div>
        <style>
          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        </style>
      `);

        // Convertir les intérêts sélectionnés pour qu'ils correspondent aux catégories en base de données
        const categoryMapping = {
          "Ateliers": "Ateliers",
          "bien-etre": "bien-etre",
          "Aérien": "Aérien",
          "Aquatique": "Aquatique",
          "Terestre": "Terestre",
          "Insolite": "Insolite",
          "culture": "culture",
          "Détente": "Détente",
          "sport": "sport",
          "nature": "nature",
          "aventure": "aventure",
          "Famille": "Famille",
          "Extreme": "Extreme"
        };

        // Mappez les intérêts sélectionnés aux catégories de la base de données
        const mappedCategories = interests.map(interest => categoryMapping[interest] || interest);

        // Faire une requête AJAX vers le serveur pour obtenir des suggestions réelles
        $.ajax({
          url: window.location.href, // Same PHP script acting as an AJAX endpoint
          method: 'POST',
          data: {
            action: 'get_suggestions',
            categories: mappedCategories,
            age: age
          },
          dataType: 'json',
          success: function(data) {
            // Check if we received an error
            if (data.error) {
              $("#personalized-suggestions").html(`<p class="suggestion-placeholder">${data.message}</p>`);
              return;
            }

            let suggestions = '';

            if (data.length === 0) {
              suggestions = '<p class="suggestion-placeholder">Aucune suggestion disponible pour votre profil. Essayez de sélectionner d\'autres intérêts.</p>';
            } else {
              // Create HTML from received activities
              data.forEach(function(activity) {
                const truncatedDescription = activity.description ?
                  (activity.description.length > 50 ? activity.description.substring(0, 50) + '...' : activity.description) :
                  'Aucune description disponible';

                const price = activity.price ? `${activity.price} TND` : 'Prix non disponible';

                suggestions += `
                <div class="suggestion-item">
                  <img src="${activity.image}" alt="${activity.name}" onerror="this.src='images/default.jpg'">
                  <div class="suggestion-content">
                    <h4>${activity.name}</h4>
                    <p>${truncatedDescription}</p>
                    <div class="activity-details">
                      <span class="activity-category" style="font-size: 12px; color: #9768D1;">${activity.category}</span>
                      <span class="activity-price" style="font-size: 12px; color: #333; font-weight: bold; margin-left: 10px;">${price}</span>
                    </div>
                  </div>
                  <button class="suggestion-btn" data-id="${activity.id}" data-name="${activity.name}" data-image="${activity.image}"><i class="fas fa-heart"></i></button>
                </div>
              `;
              });
            }

            $("#personalized-suggestions").html(suggestions);

            // Ajout des gestionnaires d'événements pour les boutons de favoris
            setupFavoriteButtons();
          },
          error: function(xhr, status, error) {
            console.error("Erreur AJAX:", error);
            // Fallback to static suggestions if AJAX fails
            let suggestions = '<p class="suggestion-placeholder">Erreur lors du chargement des suggestions. Veuillez réessayer plus tard.</p>';
            $("#personalized-suggestions").html(suggestions);
          }
        });
      });

      // Système de favoris
      let favorites = JSON.parse(localStorage.getItem('clickngo_favorites')) || [];

      // Fonction pour mettre à jour l'affichage des favoris
      function updateFavoritesDisplay() {
        if (favorites.length === 0) {
          $("#favorites-container").html('<p class="suggestion-placeholder">Aucun favori pour le moment. Utilisez le cœur pour ajouter des activités à vos favoris.</p>');
        } else {
          let favoritesHtml = '';
          favorites.forEach(function(fav) {
            favoritesHtml += `
            <div class="suggestion-item">
              <a href="reservation.php?id=${fav.id}" class="favorite-link">
                <img src="${fav.image}" alt="${fav.name}">
                <div class="suggestion-content">
                  <h4>${fav.name}</h4>
                </div>
              </a>
              <button class="remove-favorite-btn" data-id="${fav.id}"><i class="fas fa-trash"></i></button>
            </div>
          `;
          });
          $("#favorites-container").html(favoritesHtml);

          // Ajouter les gestionnaires d'événements pour les boutons de suppression
          $(".remove-favorite-btn").click(function() {
            const id = $(this).data('id');
            favorites = favorites.filter(f => f.id !== id);
            localStorage.setItem('clickngo_favorites', JSON.stringify(favorites));
            updateFavoritesDisplay();

            // Mettre à jour l'état des boutons de favoris dans les suggestions
            updateFavoriteButtonStates();
          });
        }
      }

      // Fonction pour mettre à jour l'état des boutons de favoris
      function updateFavoriteButtonStates() {
        $(".suggestion-btn").each(function() {
          const id = $(this).data('id');
          if (favorites.some(f => f.id === id)) {
            $(this).addClass('favorite-active');
          } else {
            $(this).removeClass('favorite-active');
          }
        });
      }

      // Configuration des boutons de favoris
      function setupFavoriteButtons() {
        $(".suggestion-btn").click(function() {
          const id = $(this).data('id');
          const name = $(this).data('name');
          const image = $(this).data('image');

          const existingIndex = favorites.findIndex(f => f.id === id);

          if (existingIndex === -1) {
            // Ajouter aux favoris
            favorites.push({
              id,
              name,
              image
            });
            $(this).addClass('favorite-active');
          } else {
            // Retirer des favoris
            favorites.splice(existingIndex, 1);
            $(this).removeClass('favorite-active');
          }

          localStorage.setItem('clickngo_favorites', JSON.stringify(favorites));
          updateFavoritesDisplay();
        });

        updateFavoriteButtonStates();
      }

      // Initialiser l'affichage des favoris au chargement
      updateFavoritesDisplay();
    });
  </script>

  <script>
    const isLoggedIn = <?= json_encode($loggedIn) ?>;
    const form = document.getElementById('avis-form');

    form.addEventListener('submit', function(e) {
      if (!isLoggedIn) {
        e.preventDefault();
        window.location.href = '/mvcUtilisateur/View/BackOffice/login/login.php';
      }
    });
  </script>

</body>

</html>