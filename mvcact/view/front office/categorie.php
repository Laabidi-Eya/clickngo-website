<?php
// Inclure les fichiers nécessaires
require_once __DIR__ . '/../../model/EnterpriseModel.php';

// Créer une instance du modèle
$enterpriseModel = new EnterpriseModel();

// Récupérer le terme de recherche s'il existe
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchCategory = isset($_GET['category']) ? $_GET['category'] : '';

// Récupérer toutes les activités par catégorie
$activities = [];

// Si une recherche est effectuée
if (!empty($searchTerm)) {
    // Utiliser la méthode searchEnterpriseActivities pour rechercher les activités
    $searchResults = $enterpriseModel->searchEnterpriseActivities($searchTerm, $searchCategory);
    
    // Organiser les résultats par catégorie
    $filteredActivities = [];
    foreach ($searchResults as $activity) {
        $filteredActivities[$activity['category']][] = $activity;
    }
    
    $activities = $filteredActivities;
} else {
    // Sans recherche, récupérer les activités normalement
    $activities = [
        'team-building' => $enterpriseModel->getActivitiesByCategory('team-building'),
        'animation' => $enterpriseModel->getActivitiesByCategory('animation'),
        'seminaire' => $enterpriseModel->getActivitiesByCategory('seminaire'),
        'reunion' => $enterpriseModel->getActivitiesByCategory('reunion'),
        'soiree' => $enterpriseModel->getActivitiesByCategory('soiree'),
        'repas' => $enterpriseModel->getActivitiesByCategory('repas'),
        'fundays' => $enterpriseModel->getActivitiesByCategory('fundays'),
        'projets-sur-mesure' => $enterpriseModel->getActivitiesByCategory('projets-sur-mesure')
    ];
}

// Fonction pour compter le nombre total d'activités
function countTotalActivities($activitiesArray) {
    $count = 0;
    foreach ($activitiesArray as $categoryActivities) {
        $count += count($categoryActivities);
    }
    return $count;
}

$totalActivities = countTotalActivities($activities);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Nos catégories entreprise</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .categorie-page-header {
      text-align: center;
      padding: 50px 0;
      background: linear-gradient(135deg, #ff7676, #D48DD8, #9768D1);
      margin-bottom: 30px;
      border-radius: 0 0 30px 30px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(151, 104, 209, 0.3);
    }
    
    .categorie-page-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 60%);
      opacity: 0.7;
      pointer-events: none;
      animation: shimmer 8s infinite linear;
    }
    
    @keyframes shimmer {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .categorie-page-header h1 {
      font-size: 36px;
      color: white;
      margin-bottom: 15px;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      position: relative;
      z-index: 1;
    }
    
    .categorie-page-header p {
      font-size: 18px;
      color: rgba(255, 255, 255, 0.9);
      position: relative;
      z-index: 1;
      max-width: 700px;
      margin: 0 auto;
    }
    
    .search-bar {
      max-width: 1000px;
      margin: -25px auto 40px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 25px rgba(151, 104, 209, 0.3);
      overflow: hidden;
      position: relative;
      z-index: 10;
      border: 1px solid rgba(212, 141, 216, 0.2);
    }
    
    .search-tabs {
      display: flex;
      overflow-x: auto;
      border-bottom: 1px solid rgba(212, 141, 216, 0.2);
      background: linear-gradient(to right, rgba(248, 225, 244, 0.5), rgba(230, 213, 245, 0.5));
    }
    
    .tab {
      padding: 15px 22px;
      background: none;
      border: none;
      cursor: pointer;
      font-size: 15px;
      white-space: nowrap;
      transition: all 0.3s;
      color: #5F4B8B;
      font-weight: 500;
      position: relative;
      overflow: hidden;
    }
    
    .tab:hover {
      color: #9768D1;
      background-color: rgba(248, 225, 244, 0.7);
      box-shadow: 0 -2px 10px rgba(151, 104, 209, 0.1);
    }
    
    .tab:hover::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 2px;
      background: linear-gradient(to right, #D48DD8, #9768D1);
      opacity: 0.7;
    }
    
    .tab.active {
      color: #9768D1;
      font-weight: 600;
      background-color: rgba(248, 225, 244, 0.7);
      box-shadow: 0 -4px 15px rgba(151, 104, 209, 0.15);
    }
    
    .tab.active::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(to right, #D48DD8, #9768D1);
      box-shadow: 0 0 10px rgba(151, 104, 209, 0.5);
    }
    
    .search-fields {
      display: flex;
      padding: 15px;
    }
    
    .location-field {
      display: flex;
      align-items: center;
      padding: 0 15px;
      border-right: 1px solid rgba(212, 141, 216, 0.3);
      min-width: 250px;
    }
    
    .location-field img {
      opacity: 0.7;
      transition: all 0.3s;
    }
    
    .location-field:hover img {
      opacity: 1;
      transform: scale(1.1);
    }
    
    .input-field {
      flex: 1;
      display: flex;
      margin-left: 15px;
    }
    
    .input-field input {
      flex: 1;
      border: none;
      padding: 10px 12px;
      font-size: 16px;
      color: #5F4B8B;
      background: transparent;
    }
    
    .input-field input:focus {
      outline: none;
    }
    
    .input-field input::placeholder {
      color: #B565A7;
      opacity: 0.6;
    }
    
    .search-btn {
      background: linear-gradient(135deg, #D48DD8, #9768D1);
      color: white;
      border: none;
      border-radius: 50%;
      width: 44px;
      height: 44px;
      cursor: pointer;
      box-shadow: 0 3px 10px rgba(151, 104, 209, 0.3);
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
    }
    
    .search-btn::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 70%);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.6s;
    }
    
    .search-btn:hover {
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.5), 0 0 20px rgba(151, 104, 209, 0.3);
    }
    
    .search-btn:hover::before {
      opacity: 1;
    }
    
    .category-select {
      border: none;
      background: transparent;
      font-size: 16px;
      color: #5F4B8B;
      margin-left: 10px;
      width: 100%;
      cursor: pointer;
      padding: 5px;
    }
    
    .category-select:focus {
      outline: none;
      color: #9768D1;
    }
    
    .category-select option {
      background-color: white;
      color: #5F4B8B;
    }
    
    .search-results-info {
      background: linear-gradient(to right, rgba(248, 225, 244, 0.5), rgba(230, 213, 245, 0.5));
      padding: 10px 15px;
      font-size: 14px;
      color: #5F4B8B;
      border-top: 1px solid rgba(212, 141, 216, 0.2);
    }
    
    .search-results-info p {
      margin: 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .reset-search {
      color: #9768D1;
      text-decoration: none;
      font-weight: 600;
      margin-left: 10px;
      transition: all 0.3s;
      position: relative;
    }
    
    .reset-search:hover {
      color: #D48DD8;
      text-shadow: 0 0 5px rgba(212, 141, 216, 0.5);
    }
    
    .reset-search::after {
      content: '';
      position: absolute;
      width: 0;
      height: 1px;
      bottom: -2px;
      left: 0;
      background: linear-gradient(to right, #D48DD8, #9768D1);
      transition: width 0.3s;
    }
    
    .reset-search:hover::after {
      width: 100%;
    }
    
    .no-results {
      text-align: center;
      padding: 40px 20px;
      font-size: 18px;
      color: #666;
      background: #f9f9f9;
      border-radius: 10px;
      margin: 20px auto;
      max-width: 800px;
    }
    
    .categorie-block {
      max-width: 1200px;
      margin: 0 auto 50px;
      padding: 30px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .categorie-block h2 {
      font-size: 28px;
      color: #333;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #f5f5f5;
    }
    
    .categorie-block p {
      color: #666;
      font-size: 16px;
      margin-bottom: 25px;
    }
    
    .activity-cards {
      display: flex;
      overflow-x: auto;
      gap: 25px;
      padding-bottom: 15px;
      scroll-behavior: smooth;
      scrollbar-width: thin;
      scrollbar-color: #a604ab #f0f0f0;
    }
    
    .activity-cards::-webkit-scrollbar {
      height: 8px;
    }
    
    .activity-cards::-webkit-scrollbar-track {
      background: #f0f0f0;
      border-radius: 10px;
    }
    
    .activity-cards::-webkit-scrollbar-thumb {
      background-color: #a604ab;
      border-radius: 10px;
    }
    
    .activity-card {
      flex: 0 0 280px;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .activity-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    
    .activity-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }
    
    .activity-card-content {
      padding: 15px;
    }
    
    .activity-card h3 {
      font-size: 18px;
      margin-bottom: 8px;
      color: #333;
    }
    
    .activity-card .description {
      font-size: 14px;
      color: #666;
      margin-bottom: 12px;
      line-height: 1.4;
    }
    
    .activity-card .price {
      font-weight: bold;
      color: #a604ab;
      font-size: 16px;
      margin-bottom: 12px;
    }
    
    .activity-card .cta-button {
      display: inline-block;
      background: #a604ab;
      color: white;
      padding: 8px 15px;
      border-radius: 20px;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.3s;
    }
    
    .activity-card .cta-button:hover {
      background: #8a038f;
    }
    
    .contact-for-custom {
      margin-top: 30px;
      background-color: #f8f9fb;
      padding: 25px;
      border-radius: 10px;
      text-align: center;
    }
    
    .contact-for-custom h3 {
      font-size: 22px;
      color: #333;
      margin-bottom: 15px;
    }
    
    .contact-for-custom p {
      font-size: 16px;
      color: #555;
      margin-bottom: 20px;
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
    }
    
    .custom-btn {
      display: inline-block;
      background: #a604ab;
      color: white;
      padding: 12px 25px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s, transform 0.2s;
    }
    
    .custom-btn:hover {
      background: #8a038f;
      transform: translateY(-2px);
    }
    
    .cards-container {
      position: relative;
      padding: 0 40px;
    }
    
    .nav-arrow {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: white;
      border: 1px solid #ddd;
      font-size: 18px;
      cursor: pointer;
      z-index: 5;
    }
    
    .prev-arrow {
      left: 0;
    }
    
    .next-arrow {
      right: 0;
    }
    
    .seminaire-options {
      margin-top: 40px;
    }
    
    .options-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin: 30px 0;
    }
    
    .option-item {
      display: flex;
      align-items: flex-start;
      padding: 15px;
      background: #f5f7fa;
      border-radius: 10px;
    }
    
    .option-icon {
      font-size: 24px;
      margin-right: 15px;
    }
    
    .option-content h4 {
      font-size: 18px;
      margin-bottom: 8px;
      color: #333;
    }
    
    .option-content p {
      font-size: 14px;
      color: #666;
      margin: 0;
    }
    
    .seminaire-cta {
      background: #f0f2f7;
      padding: 25px;
      text-align: center;
      border-radius: 10px;
    }
    
    .seminaire-cta h3 {
      font-size: 22px;
      margin-bottom: 10px;
    }
    
    .seminaire-cta p {
      margin-bottom: 20px;
    }
    
    .seminaire-btn {
      display: inline-block;
      background: #a604ab;
      color: white;
      padding: 12px 24px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 500;
      transition: background 0.3s;
    }
    
    .seminaire-btn:hover {
      background: #8a038f;
    }

    .activity-section {
      margin-bottom: 50px;
      overflow: hidden;
      position: relative;
    }

    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      position: relative;
    }

    .section-header h2 {
      font-size: 28px;
      color: #5F4B8B;
      position: relative;
      padding-left: 15px;
      margin: 0;
    }

    .section-header h2::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 4px;
      height: 24px;
      background: linear-gradient(to bottom, #D48DD8, #9768D1);
      border-radius: 2px;
    }

    .section-header .nav-buttons {
      display: flex;
      gap: 8px;
    }

    .nav-btn {
      background: white;
      border: none;
      width: 38px;
      height: 38px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 2px 10px rgba(151, 104, 209, 0.15);
      transition: all 0.3s ease;
    }

    .nav-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.3);
      background: linear-gradient(135deg, #f7f3ff, #ffe6f5);
    }

    .nav-btn:active {
      transform: translateY(0);
      box-shadow: 0 2px 5px rgba(151, 104, 209, 0.2);
    }

    .nav-btn img {
      width: 20px;
      height: 20px;
      opacity: 0.7;
      transition: opacity 0.3s;
    }

    .nav-btn:hover img {
      opacity: 1;
    }

    .activities-row {
      display: flex;
      overflow-x: auto;
      padding: 10px 0;
      scroll-behavior: smooth;
      gap: 20px;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .activities-row::-webkit-scrollbar {
      display: none;
    }

    .activity-card {
      min-width: 300px;
      flex: 0 0 300px;
      border-radius: 15px;
      overflow: hidden;
      background: white;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      position: relative;
      border: 1px solid rgba(212, 141, 216, 0.1);
    }

    .activity-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(151, 104, 209, 0.2);
      border-color: rgba(212, 141, 216, 0.3);
    }

    .activity-card .image {
      height: 180px;
      position: relative;
      overflow: hidden;
    }

    .activity-card .image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .activity-card:hover .image img {
      transform: scale(1.08);
    }

    .activity-card .image::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 50px;
      background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);
      opacity: 0.7;
    }

    .activity-card .badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: linear-gradient(135deg, #D48DD8, #9768D1);
      color: white;
      font-size: 12px;
      font-weight: 600;
      padding: 6px 12px;
      border-radius: 20px;
      z-index: 2;
      box-shadow: 0 2px 10px rgba(151, 104, 209, 0.3);
    }

    .activity-card .content {
      padding: 20px;
      position: relative;
    }

    .activity-card .title {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 8px;
      color: #5F4B8B;
      transition: color 0.3s;
    }

    .activity-card:hover .title {
      color: #9768D1;
    }

    .activity-card .description {
      font-size: 14px;
      color: #777;
      margin-bottom: 15px;
      line-height: 1.5;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .activity-card .meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid rgba(212, 141, 216, 0.2);
    }

    .activity-card .price {
      font-size: 18px;
      font-weight: 700;
      color: #9768D1;
    }

    .activity-card .btn {
      background: linear-gradient(135deg, #D48DD8, #9768D1);
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 30px;
      font-weight: 600;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.3s;
      box-shadow: 0 3px 10px rgba(151, 104, 209, 0.2);
      position: relative;
      overflow: hidden;
    }

    .activity-card .btn::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
      opacity: 0;
      transform: scale(1);
      pointer-events: none;
      transition: transform 0.6s, opacity 0.6s;
    }

    .activity-card .btn:hover {
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.4);
      transform: translateY(-2px);
    }

    .activity-card .btn:hover::before {
      opacity: 1;
      transform: scale(1.5);
    }

    .activity-card .location {
      font-size: 14px;
      color: #888;
      display: flex;
      align-items: center;
      margin-bottom: 10px;
    }

    .activity-card .location img {
      width: 16px;
      height: 16px;
      margin-right: 5px;
      opacity: 0.7;
    }

    .no-results-message {
      text-align: center;
      padding: 40px 20px;
      background: rgba(248, 225, 244, 0.3);
      border-radius: 10px;
      box-shadow: 0 2px 15px rgba(151, 104, 209, 0.1);
      margin: 20px 0;
    }

    .no-results-message h3 {
      font-size: 22px;
      color: #5F4B8B;
      margin-bottom: 10px;
    }

    .no-results-message p {
      font-size: 16px;
      color: #777;
      margin-bottom: 20px;
    }

    .no-results-message .btn-reset {
      background: linear-gradient(135deg, #D48DD8, #9768D1);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 30px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 3px 10px rgba(151, 104, 209, 0.2);
    }

    .no-results-message .btn-reset:hover {
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.4);
      transform: translateY(-2px);
    }

    .description-section {
      padding: 40px 0;
      background: linear-gradient(to right, rgba(248, 225, 244, 0.3), rgba(230, 213, 245, 0.3));
      position: relative;
      overflow: hidden;
    }
    
    .description-section::before {
      content: '';
      position: absolute;
      top: -100px;
      left: -100px;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(212, 141, 216, 0.2) 0%, transparent 70%);
      z-index: 0;
    }
    
    .description-section::after {
      content: '';
      position: absolute;
      bottom: -100px;
      right: -100px;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(151, 104, 209, 0.2) 0%, transparent 70%);
      z-index: 0;
    }
    
    .description-container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 0 20px;
      position: relative;
      z-index: 1;
    }
    
    .description-content {
      background-color: white;
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 10px 30px rgba(151, 104, 209, 0.15);
      border-top: 4px solid transparent;
      border-image: linear-gradient(to right, #D48DD8, #9768D1);
      border-image-slice: 1;
      text-align: center;
    } 
    
    .description-content h2 {
      font-size: 30px;
      color: #5F4B8B;
      margin-bottom: 20px;
      position: relative;
      display: inline-block;
    }
    
    .description-content h2::after {
      content: '';
      display: block;
      width: 100px;
      height: 3px;
      background: linear-gradient(to right, #D48DD8, #9768D1);
      margin: 10px auto 0;
      border-radius: 3px;
    }
    
    .description-content p {
      color: #666;
      font-size: 16px;
      line-height: 1.7;
      margin-bottom: 20px;
    }
    
    .description-badges {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 15px;
      margin-top: 30px;
    }
    
    .description-badges .badge {
      background: linear-gradient(135deg, rgba(212, 141, 216, 0.2), rgba(151, 104, 209, 0.2));
      color: #5F4B8B;
      font-weight: 600;
      padding: 10px 20px;
      border-radius: 30px;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      box-shadow: 0 2px 10px rgba(151, 104, 209, 0.1);
      transition: all 0.3s ease;
    }
    
    .description-badges .badge:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(151, 104, 209, 0.2);
      background: linear-gradient(135deg, rgba(212, 141, 216, 0.3), rgba(151, 104, 209, 0.3));
    }
    
    @media (max-width: 768px) {
      .description-content {
        padding: 30px 20px;
      }
      
      .description-content h2 {
        font-size: 24px;
      }
      
      .description-badges {
        gap: 10px;
      }
      
      .description-badges .badge {
        padding: 8px 15px;
        font-size: 12px;
      }
    }

    /* Styles pour la section témoignages améliorée */
    .testimonials-section {
      padding: 40px 0;
      background: linear-gradient(to bottom, #fcfcfc, #f5f5f5);
      position: relative;
      overflow: hidden;
    }
    
    .testimonials-section:before,
    .testimonials-section:after {
      display: none;
    }
    
    .testimonials-container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .testimonials-header {
      text-align: center;
      margin-bottom: 25px;
    }
    
    .testimonials-title-icon {
      display: none;
    }
    
    .testimonials-section h2 {
      font-size: 26px;
      color: #333;
      margin-bottom: 0;
    }
    
    .testimonials-section h2:after {
      content: '';
      display: block;
      width: 60px;
      height: 2px;
      background: linear-gradient(to right, transparent, #a604ab, transparent);
      margin: 10px auto 0;
    }
    
    .testimonials-slider {
      position: relative;
      padding: 10px 0;
    }
    
    .testimonials-track {
      display: flex;
      gap: 15px;
      transition: transform 0.5s ease;
      max-width: 100%;
      overflow: hidden;
    }
    
    .testimonial-card {
      flex: 0 0 100%;
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
      padding: 20px;
      transition: all 0.3s ease;
      position: relative;
      border-top: 3px solid transparent;
      border-image: linear-gradient(to right, #a604ab, #d38bda);
      border-image-slice: 1;
    }
    
    .testimonial-card:hover {
      transform: translateY(-5px);
    }
    
    .testimonial-content {
      margin-bottom: 15px;
    }
    
    .testimonial-content p {
      font-size: 15px;
      line-height: 1.5;
      color: #555;
      font-style: italic;
      margin: 0;
      position: relative;
    }
    
    .testimonial-content p:before,
    .testimonial-content p:after {
      font-size: 24px;
    }
    
    .testimonial-author {
      display: flex;
      align-items: center;
      padding-top: 15px;
      border-top: 1px solid #f0f0f0;
    }
    
    .author-image {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      overflow: hidden;
      margin-right: 12px;
    }
    
    .author-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    
    .author-info h4 {
      font-size: 16px;
      margin: 0 0 3px;
    }
    
    .author-info p {
      font-size: 13px;
      color: #777;
      margin: 0;
    }
    
    .testimonials-navigation {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 20px;
    }
    
    .testimonial-dot {
      width: 8px;
      height: 8px;
    }
    
    @media (min-width: 992px) {
      .testimonials-track {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
      }
    }
    
    @media (max-width: 991px) {
      .testimonial-card {
        flex: 0 0 90%;
        margin-right: 10px;
      }
    }
    
    @media (max-width: 768px) {
      .testimonials-section {
        padding: 30px 0;
      }
      
      .testimonial-card {
        padding: 15px;
      }
      
      .testimonial-content p {
        font-size: 14px;
      }
      
      .author-image {
        width: 35px;
        height: 35px;
      }
    }

    /* Styles pour la section CTA révisée */
    .cta-section {
      padding: 50px 0;
      background: linear-gradient(135deg, #f8f8f8, #f0f0f0);
      position: relative;
      overflow: hidden;
    }
    
    .cta-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .cta-content {
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      padding: 30px 0;
    }
    
    .cta-decoration {
      position: absolute;
      width: 150px;
      height: 150px;
      border-radius: 50%;
      opacity: 0.15;
      z-index: 0;
    }
    
    .cta-decoration.left {
      background: linear-gradient(45deg, #a604ab, #6b0b6e);
      left: -35px;
      top: -35px;
    }
    
    .cta-decoration.right {
      background: linear-gradient(45deg, #a604ab, #6b0b6e);
      right: -35px;
      bottom: -35px;
    }
    
    .cta-text {
      background-color: white;
      padding: 40px;
      border-radius: 10px;
      text-align: center;
      position: relative;
      z-index: 1;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
      max-width: 600px;
      border-left: 3px solid #a604ab;
      border-right: 3px solid #a604ab;
    }
    
    .cta-text:before, .cta-text:after {
      content: '';
      position: absolute;
      width: 50px;
      height: 3px;
      background-color: #a604ab;
    }
    
    .cta-text:before {
      top: 0;
      left: 0;
    }
    
    .cta-text:after {
      bottom: 0;
      right: 0;
    }
    
    .cta-section h2 {
      font-size: 28px;
      color: #333;
      margin-bottom: 15px;
      position: relative;
      display: inline-block;
    }
    
    .cta-section h2:after {
      content: '';
      display: block;
      width: 50px;
      height: 2px;
      background-color: #a604ab;
      margin: 10px auto 0;
    }
    
    .cta-section p {
      font-size: 16px;
      color: #666;
      margin-bottom: 25px;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
    }
    
    .cta-button-fancy {
      display: inline-block;
      background: linear-gradient(135deg, #a604ab, #8a038f);
      color: white;
      padding: 12px 35px;
      border-radius: 30px;
      font-size: 16px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(166, 4, 171, 0.3);
    }
    
    .cta-button-fancy:before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: all 0.6s;
    }
    
    .cta-button-fancy:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(166, 4, 171, 0.4);
    }
    
    .cta-button-fancy:hover:before {
      left: 100%;
    }
    
    @media (max-width: 768px) {
      .cta-text {
        padding: 30px 20px;
      }
      
      .cta-decoration {
        width: 100px;
        height: 100px;
      }
    }
    
    /* Styles pour le footer */
    .footer-main {
      text-align: center;
      margin-bottom: 30px;
      background-color: #2C3E50;
      color: #ECF0F1;
      padding: 60px 0 20px;
    }
    
    .footer-logo {
      max-width: 150px;
      margin-bottom: 15px;
    }
    
    .social-icons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin: 15px 0;
    }
    
    .social-icons .icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background-color: var(--color, rgba(255,255,255,0.1));
      border-radius: 50%;
      color: white;
      font-size: 18px;
      text-decoration: none;
      transition: all 0.3s;
    }
    
    .social-icons .icon:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .links {
      margin-bottom: 30px;
      min-width: 200px;
      background-color: #2C3E50;
      color: #ECF0F1;
    }
    
    .links p {
      font-weight: bold;
      margin-bottom: 15px;
      color: white;
    }
    
    .links a {
      display: block;
      color: #BDC3C7;
      text-decoration: none;
      margin-bottom: 8px;
      transition: color 0.3s;
    }
    
    .links a:hover {
      color: #a604ab;
    }
    
    .payment-methods {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }
    
    .payment-methods img {
      height: 30px;
      filter: brightness(0) invert(1);
      opacity: 0.8;
    }
    
    .footer-section {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      background-color: #2C3E50;
      color: #ECF0F1;
    }
    
    .footer-separator {
      height: 1px;
      background-color: rgba(255,255,255,0.1);
      margin: 20px 0;
    }
    
    .footer-bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      padding: 20px 0;
    }
    
    .footer-bottom p {
      color: #BDC3C7;
      font-size: 14px;
      margin: 0;
    }
    
    .footer-links-bottom {
      display: flex;
      gap: 20px;
    }
    
    .footer-links-bottom a {
      color: #BDC3C7;
      text-decoration: none;
      font-size: 14px;
      transition: color 0.3s;
    }
    
    .footer-links-bottom a:hover {
      color: #a604ab;
    }
    
    @media (max-width: 768px) {
      .footer-bottom {
        flex-direction: column;
        text-align: center;
        gap: 15px;
      }
      
      .footer-links-bottom {
        justify-content: center;
      }
    }

    .custom-solution-section {
      background: linear-gradient(135deg, #f8e1f4 0%, #D48DD8 60%, #9768D1 100%);
      padding: 60px 0;
      position: relative;
      overflow: hidden;
      margin-top: 40px;
    }
    .custom-solution-section .bubble {
      position: absolute;
      border-radius: 50%;
      opacity: 0.18;
      z-index: 0;
    }
    .custom-solution-section .bubble.left {
      top: -60px;
      left: -60px;
      width: 180px;
      height: 180px;
      background: radial-gradient(circle, #fff3fa 0%, #D48DD8 80%, transparent 100%);
    }
    .custom-solution-section .bubble.right {
      bottom: -70px;
      right: -70px;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, #fff3fa 0%, #9768D1 80%, transparent 100%);
    }
    .custom-solution-section .content {
      max-width: 600px;
      margin: 0 auto;
      background: rgba(255,255,255,0.10);
      border-radius: 28px;
      box-shadow: 0 8px 32px rgba(151,104,209,0.13);
      text-align: center;
      padding: 48px 30px 44px 30px;
      position: relative;
      z-index: 1;
    }
    .custom-solution-section h3 {
      font-size: 2.1rem;
      font-weight: 800;
      margin-bottom: 18px;
      color: #fff;
      letter-spacing: 1px;
    }
    .custom-solution-section p {
      font-size: 1.18rem;
      color: #fff;
      margin-bottom: 32px;
      max-width: 520px;
      margin-left: auto;
      margin-right: auto;
      line-height: 1.7;
    }
    .custom-solution-section a {
      background: linear-gradient(135deg, #ff6ec4 0%, #7873f5 100%);
      color: #fff;
      font-size: 1.15rem;
      font-weight: 700;
      padding: 17px 44px;
      border-radius: 30px;
      box-shadow: 0 4px 18px rgba(212,141,216,0.18);
      border: none;
      transition: background 0.3s, transform 0.2s;
      text-decoration: none;
      display: inline-block;
      margin-top: 10px;
      letter-spacing: 0.5px;
    }
  </style>
</head>
<body>
    <section class="categorie-page-header">
        <h1>Nos offres pour les entreprises</h1>
        <p>Toutes nos catégories pour répondre à vos besoins professionnels.</p>
      </section>
      
      <div class="description-section">
        <div class="description-container">
          <div class="description-content">
            <h2>Découvrez nos expériences inoubliables</h2>
            <p>Chez Click'N'Go, nous créons des événements d'entreprise mémorables qui renforcent la cohésion d'équipe et stimulent la motivation. Notre catalogue complet d'activités répond à tous vos besoins professionnels - team building, réunions, soirées, repas - avec une approche personnalisée qui s'adapte parfaitement à votre culture d'entreprise, vos objectifs et votre budget.</p>
            <div class="description-badges">
              <span class="badge">✓ Expériences de qualité</span>
              <span class="badge">✓ Équipe professionnelle</span>
              <span class="badge">✓ Solutions sur mesure</span>
              <span class="badge">✓ Satisfaction garantie</span>
            </div>
          </div>
        </div>
      </div>
      <br>
      <br>
      <br>

    <div class="search-bar">
        <div class="search-tabs">
          <button class="tab active">Team building</button>
          <button class="tab">Animation</button>
          <button class="tab">Réunions</button>
          <button class="tab">Soirée</button>
          <button class="tab">Repas</button>
          <button class="tab">Fundays</button>
          <button class="tab">Projets sur mesure</button>
        </div>
        <form method="GET" action="" class="search-fields">
          <div class="location-field">
            <span class="icon">📩</span>
            <select name="category" class="category-select">
              <option value="">Toutes les catégories</option>
              <option value="team-building" <?php echo $searchCategory == 'team-building' ? 'selected' : ''; ?>>Team Building</option>
              <option value="animation" <?php echo $searchCategory == 'animation' ? 'selected' : ''; ?>>Animation</option>
              <option value="reunion" <?php echo $searchCategory == 'reunion' ? 'selected' : ''; ?>>Réunions</option>
              <option value="soiree" <?php echo $searchCategory == 'soiree' ? 'selected' : ''; ?>>Soirées</option>
              <option value="repas" <?php echo $searchCategory == 'repas' ? 'selected' : ''; ?>>Repas</option>
              <option value="fundays" <?php echo $searchCategory == 'fundays' ? 'selected' : ''; ?>>Fundays</option>
              <option value="projets-sur-mesure" <?php echo $searchCategory == 'projets-sur-mesure' ? 'selected' : ''; ?>>Projets sur mesure</option>
            </select>
          </div>
          <div class="input-field">
            <input type="text" name="search" placeholder="Rechercher par nom ou prix" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit" class="search-btn">🔍</button>
          </div>
        </form>
        
        <?php if (!empty($searchTerm)): ?>
        <div class="search-results-info">
          <p>Résultats pour "<?php echo htmlspecialchars($searchTerm); ?>" : <?php echo $totalActivities; ?> activité(s) trouvée(s)
            <a href="categorie.php" class="reset-search">Effacer la recherche</a>
          </p>
        </div>
        <?php endif; ?>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          // Gestion des onglets
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
          tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Afficher la section correspondante
            const sections = document.querySelectorAll('.categorie-block');
            sections.forEach(section => section.style.display = 'none');
            
            const tabText = tab.textContent.toLowerCase().trim();
            let target = tabText.replace(' ', '-');
            
            // Cas spécifiques pour certains onglets
            if (tabText === 'soirée') {
              target = 'soiree';
            } else if (tabText === 'réunions') {
              target = 'reunion';
            } else if (tabText === 'projets sur mesure') {
              target = 'projets-sur-mesure';
            }
            
            document.getElementById(target).style.display = 'block';
              
              // Mettre à jour le sélecteur de catégorie dans la barre de recherche
              const categorySelect = document.querySelector('.category-select');
              if (categorySelect) {
                categorySelect.value = target;
              }
          });
        });
        
          // Afficher uniquement la section Team Building par défaut
          // ou la première section qui contient des résultats en cas de recherche
          const sections = document.querySelectorAll('.categorie-block');
          sections.forEach(section => section.style.display = 'none');
          
          <?php if (!empty($searchTerm) && $totalActivities > 0): ?>
            // En cas de recherche, afficher la première section qui contient des résultats
            let firstSectionWithResults = null;
            <?php foreach ($activities as $categoryKey => $categoryActivities): ?>
              <?php if (!empty($categoryActivities)): ?>
                if (!firstSectionWithResults) {
                  firstSectionWithResults = '<?php echo $categoryKey; ?>';
                  
                  // Activer l'onglet correspondant
                  tabs.forEach(t => {
                    t.classList.remove('active');
                    const tabText = t.textContent.toLowerCase().trim();
                    let tabTarget = tabText.replace(' ', '-');
                    
                    // Cas spécifiques pour certains onglets
                    if (tabText === 'soirée') {
                      tabTarget = 'soiree';
                    } else if (tabText === 'réunions') {
                      tabTarget = 'reunion';
                    } else if (tabText === 'projets sur mesure') {
                      tabTarget = 'projets-sur-mesure';
                    }
                    
                    if (tabTarget === firstSectionWithResults) {
                      t.classList.add('active');
                    }
                  });
                }
              <?php endif; ?>
            <?php endforeach; ?>
            
            if (firstSectionWithResults) {
              document.getElementById(firstSectionWithResults).style.display = 'block';
            } else {
              // Aucun résultat, afficher team-building par défaut
          document.getElementById('team-building').style.display = 'block';
            }
          <?php else: ?>
            // Sans recherche, afficher team-building par défaut
            document.getElementById('team-building').style.display = 'block';
          <?php endif; ?>
          
          // Gestion des carousels d'activités
          const prevButtons = document.querySelectorAll('.prev-arrow');
          const nextButtons = document.querySelectorAll('.next-arrow');
          
          prevButtons.forEach(button => {
            button.addEventListener('click', function() {
              const container = this.nextElementSibling;
              container.scrollBy({ left: -300, behavior: 'smooth' });
            });
          });
          
          nextButtons.forEach(button => {
            button.addEventListener('click', function() {
              const container = this.previousElementSibling;
              container.scrollBy({ left: 300, behavior: 'smooth' });
            });
          });
        });
      </script>

    <!-- Section pour afficher un message si aucun résultat n'est trouvé -->
    <?php if (!empty($searchTerm) && $totalActivities === 0): ?>
    <div class="no-results">
      <h3>Aucun résultat trouvé</h3>
      <p>Aucune activité ne correspond à votre recherche "<?php echo htmlspecialchars($searchTerm); ?>". Essayez avec d'autres termes ou <a href="categorie.php">réinitialisez la recherche</a>.</p>
    </div>
    <?php endif; ?>

  <section class="categorie-block" id="team-building">
    <h2>Team Building 🏢</h2>
    <p>Renforcez les liens de votre équipe avec des expériences uniques.</p>
    
    <div class="cards-container">
      <button class="nav-arrow prev-arrow">❮</button>
      <div class="activity-cards">
        <?php if (isset($activities['team-building']) && !empty($activities['team-building'])): ?>
          <?php foreach ($activities['team-building'] as $activity): ?>
        <div class="activity-card">
              <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
          <div class="activity-card-content">
                <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                <p class="description"><?php echo htmlspecialchars($activity['description']); ?></p>
                <p class="price">À partir de <?php echo htmlspecialchars($activity['price']); ?> <?php echo htmlspecialchars($activity['price_type']); ?></p>
            <a href="reservation.php?id=<?php echo urlencode($activity['id']); ?>" class="cta-button">Réserver</a>
          </div>
        </div>
          <?php endforeach; ?>
        <?php else: ?>
        <div class="activity-card">
          <div class="activity-card-content">
              <h3>Aucune activité disponible</h3>
              <p class="description">
                <?php if (!empty($searchTerm)): ?>
                  Aucun résultat ne correspond à votre recherche dans cette catégorie.
                <?php else: ?>
                  Aucune activité de team building n'est disponible pour le moment.
                <?php endif; ?>
              </p>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <button class="nav-arrow next-arrow">❯</button>
    </div>
  </section>

  <section class="categorie-block" id="animation">
    <h2>Animation 🎉</h2>
    <p>Des animations interactives pour dynamiser vos événements.</p>
    
    <div class="cards-container">
      <button class="nav-arrow prev-arrow">❮</button>
      <div class="activity-cards">
        <?php if (isset($activities['animation']) && !empty($activities['animation'])): ?>
          <?php foreach ($activities['animation'] as $activity): ?>
        <div class="activity-card">
              <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
          <div class="activity-card-content">
                <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                <p class="description"><?php echo htmlspecialchars($activity['description']); ?></p>
                <p class="price">À partir de <?php echo htmlspecialchars($activity['price']); ?> <?php echo htmlspecialchars($activity['price_type']); ?></p>
            <a href="reservation.php?id=<?php echo urlencode($activity['id']); ?>" class="cta-button">Réserver</a>
          </div>
        </div>
          <?php endforeach; ?>
        <?php else: ?>
        <div class="activity-card">
          <div class="activity-card-content">
              <h3>Aucune activité disponible</h3>
              <p class="description">
                <?php if (!empty($searchTerm)): ?>
                  Aucun résultat ne correspond à votre recherche dans cette catégorie.
                <?php else: ?>
                  Aucune animation n'est disponible pour le moment.
                <?php endif; ?>
              </p>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <button class="nav-arrow next-arrow">❯</button>
    </div>
  </section>

  <section class="categorie-block" id="reunion">
    <h2>Réunions 📋</h2>
    <p>Des espaces adaptés et équipés pour vos réunions professionnelles, qu'elles soient en petit ou grand comité.</p>
    
    <div class="cards-container">
      <button class="nav-arrow prev-arrow">❮</button>
      <div class="activity-cards">
        <?php if (isset($activities['reunion']) && !empty($activities['reunion'])): ?>
          <?php foreach ($activities['reunion'] as $activity): ?>
        <div class="activity-card">
              <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
          <div class="activity-card-content">
                <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                <p class="description"><?php echo htmlspecialchars($activity['description']); ?></p>
                <p class="price">À partir de <?php echo htmlspecialchars($activity['price']); ?> <?php echo htmlspecialchars($activity['price_type']); ?></p>
            <a href="reservation.php?id=<?php echo urlencode($activity['id']); ?>" class="cta-button">Réserver</a>
          </div>
        </div>
          <?php endforeach; ?>
        <?php else: ?>
        <div class="activity-card">
          <div class="activity-card-content">
              <h3>Aucune activité disponible</h3>
              <p class="description">
                <?php if (!empty($searchTerm)): ?>
                  Aucun résultat ne correspond à votre recherche dans cette catégorie.
                <?php else: ?>
                  Aucune salle de réunion n'est disponible pour le moment.
                <?php endif; ?>
              </p>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <button class="nav-arrow next-arrow">❯</button>
    </div>
  </section>

  <section class="categorie-block" id="soiree">
    <h2>Soirées d'Entreprise 🌟</h2>
    <p>Des soirées mémorables pour célébrer vos succès, renforcer l'esprit d'équipe ou marquer des occasions spéciales.</p>
    
    <div class="cards-container">
      <button class="nav-arrow prev-arrow">❮</button>
      <div class="activity-cards">
        <?php if (isset($activities['soiree']) && !empty($activities['soiree'])): ?>
          <?php foreach ($activities['soiree'] as $activity): ?>
        <div class="activity-card">
              <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
          <div class="activity-card-content">
                <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                <p class="description"><?php echo htmlspecialchars($activity['description']); ?></p>
                <p class="price">À partir de <?php echo htmlspecialchars($activity['price']); ?> <?php echo htmlspecialchars($activity['price_type']); ?></p>
            <a href="reservation.php?id=<?php echo urlencode($activity['id']); ?>" class="cta-button">Réserver</a>
          </div>
        </div>
          <?php endforeach; ?>
        <?php else: ?>
        <div class="activity-card">
          <div class="activity-card-content">
              <h3>Aucune activité disponible</h3>
              <p class="description">
                <?php if (!empty($searchTerm)): ?>
                  Aucun résultat ne correspond à votre recherche dans cette catégorie.
                <?php else: ?>
                  Aucune soirée d'entreprise n'est disponible pour le moment.
                <?php endif; ?>
              </p>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <button class="nav-arrow next-arrow">❯</button>
    </div>
  </section>

  <section class="categorie-block" id="repas">
    <h2>Repas d'Affaires 🍽️</h2>
    <p>Des formules de restauration adaptées à vos événements professionnels, alliant qualité gastronomique et service impeccable.</p>
    
    <div class="cards-container">
      <button class="nav-arrow prev-arrow">❮</button>
      <div class="activity-cards">
        <?php if (isset($activities['repas']) && !empty($activities['repas'])): ?>
          <?php foreach ($activities['repas'] as $activity): ?>
        <div class="activity-card">
              <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
          <div class="activity-card-content">
                <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                <p class="description"><?php echo htmlspecialchars($activity['description']); ?></p>
                <p class="price">À partir de <?php echo htmlspecialchars($activity['price']); ?> <?php echo htmlspecialchars($activity['price_type']); ?></p>
            <a href="reservation.php?id=<?php echo urlencode($activity['id']); ?>" class="cta-button">Réserver</a>
          </div>
        </div>
          <?php endforeach; ?>
        <?php else: ?>
        <div class="activity-card">
          <div class="activity-card-content">
              <h3>Aucune activité disponible</h3>
              <p class="description">
                <?php if (!empty($searchTerm)): ?>
                  Aucun résultat ne correspond à votre recherche dans cette catégorie.
                <?php else: ?>
                  Aucun repas d'affaires n'est disponible pour le moment.
                <?php endif; ?>
              </p>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <button class="nav-arrow next-arrow">❯</button>
    </div>
  </section>

  <section class="categorie-block" id="fundays">
    <h2>Fundays 🎡</h2>
    <p>Des journées récréatives pour vos équipes, alliant détente, plaisir et convivialité dans des cadres exceptionnels.</p>
    
    <div class="cards-container">
      <button class="nav-arrow prev-arrow">❮</button>
      <div class="activity-cards">
        <?php if (isset($activities['fundays']) && !empty($activities['fundays'])): ?>
          <?php foreach ($activities['fundays'] as $activity): ?>
        <div class="activity-card">
              <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
          <div class="activity-card-content">
                <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                <p class="description"><?php echo htmlspecialchars($activity['description']); ?></p>
                <p class="price">À partir de <?php echo htmlspecialchars($activity['price']); ?> <?php echo htmlspecialchars($activity['price_type']); ?></p>
            <a href="reservation.php?id=<?php echo urlencode($activity['id']); ?>" class="cta-button">Réserver</a>
          </div>
        </div>
          <?php endforeach; ?>
        <?php else: ?>
        <div class="activity-card">
          <div class="activity-card-content">
              <h3>Aucune activité disponible</h3>
              <p class="description">
                <?php if (!empty($searchTerm)): ?>
                  Aucun résultat ne correspond à votre recherche dans cette catégorie.
                <?php else: ?>
                  Aucun funday n'est disponible pour le moment.
                <?php endif; ?>
              </p>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <button class="nav-arrow next-arrow">❯</button>
    </div>
  </section>

  <section class="categorie-block" id="projets-sur-mesure">
    <h2>Projets sur Mesure 🔧</h2>
    <p>Des solutions personnalisées adaptées à vos besoins spécifiques et à votre vision d'entreprise.</p>
    
    <div class="cards-container">
      <button class="nav-arrow prev-arrow">❮</button>
      <div class="activity-cards">
        <?php if (isset($activities['projets-sur-mesure']) && !empty($activities['projets-sur-mesure'])): ?>
          <?php foreach ($activities['projets-sur-mesure'] as $activity): ?>
        <div class="activity-card">
              <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
          <div class="activity-card-content">
                <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                <p class="description"><?php echo htmlspecialchars($activity['description']); ?></p>
                <p class="price">À partir de <?php echo htmlspecialchars($activity['price']); ?> <?php echo htmlspecialchars($activity['price_type']); ?></p>
                <a href="reservation.php?id=<?php echo urlencode($activity['id']); ?>" class="cta-button">Réserver</a>
          </div>
        </div>
          <?php endforeach; ?>
        <?php else: ?>
        <div class="activity-card">
          <div class="activity-card-content">
              <h3>Aucune activité disponible</h3>
              <p class="description">
                <?php if (!empty($searchTerm)): ?>
                  Aucun résultat ne correspond à votre recherche dans cette catégorie.
                <?php else: ?>
                  Aucun projet sur mesure n'est disponible pour le moment. Contactez-nous pour discuter de vos besoins spécifiques.
                <?php endif; ?>
              </p>
          </div>
        </div>
        <?php endif; ?>
          </div>
      <button class="nav-arrow next-arrow">❯</button>
        </div>
        

 
 



<style>
  /* Styles pour la navigation des cartes */
  .cards-container {
    position: relative;
    display: flex;
    align-items: center;
    padding: 0 40px;
  }
  
  .nav-arrow {
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: white;
    border: none;
    border-radius: 50%;
    font-size: 18px;
    color: #a604ab;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 10;
    transition: all 0.3s;
  }
  
  .nav-arrow:hover {
    background-color: #a604ab;
    color: white;
  }
  
  .prev-arrow {
    left: 0;
  }
  
  .next-arrow {
    right: 0;
  }
  
  .activity-cards {
    flex: 1;
  }
</style>

<script>
  // Script pour la navigation des cartes
  document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('.cards-container');
    
    containers.forEach(container => {
      const cards = container.querySelector('.activity-cards');
      const prevBtn = container.querySelector('.prev-arrow');
      const nextBtn = container.querySelector('.next-arrow');
      const cardWidth = 305; // 280px largeur + 25px gap
      
      prevBtn.addEventListener('click', function() {
        cards.scrollBy({
          left: -cardWidth,
          behavior: 'smooth'
        });
      });
      
      nextBtn.addEventListener('click', function() {
        cards.scrollBy({
          left: cardWidth,
          behavior: 'smooth'
        });
      });
    });
  });
</script>

<style>
  /* Styles pour la section témoignages améliorée */
  .testimonials-section {
    padding: 40px 0;
    background: linear-gradient(to bottom, #fcfcfc, #f5f5f5);
    position: relative;
    overflow: hidden;
  }
  
  .testimonials-section:before,
  .testimonials-section:after {
    display: none;
  }
  
  .testimonials-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 20px;
  }
  
  .testimonials-header {
    text-align: center;
    margin-bottom: 25px;
  }
  
  .testimonials-title-icon {
    display: none;
  }
  
  .testimonials-section h2 {
    font-size: 26px;
    color: #333;
    margin-bottom: 0;
  }
  
  .testimonials-section h2:after {
    content: '';
    display: block;
    width: 60px;
    height: 2px;
    background: linear-gradient(to right, transparent, #a604ab, transparent);
    margin: 10px auto 0;
  }
  
  .testimonials-slider {
    position: relative;
    padding: 10px 0;
  }
  
  .testimonials-track {
    display: flex;
    gap: 15px;
    transition: transform 0.5s ease;
    max-width: 100%;
    overflow: hidden;
  }
  
  .testimonial-card {
    flex: 0 0 100%;
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
    padding: 20px;
    transition: all 0.3s ease;
    position: relative;
    border-top: 3px solid transparent;
    border-image: linear-gradient(to right, #a604ab, #d38bda);
    border-image-slice: 1;
  }
  
  .testimonial-card:hover {
    transform: translateY(-5px);
  }
  
  .testimonial-content {
    margin-bottom: 15px;
  }
  
  .testimonial-content p {
    font-size: 15px;
    line-height: 1.5;
    color: #555;
    font-style: italic;
    margin: 0;
    position: relative;
  }
  
  .testimonial-content p:before,
  .testimonial-content p:after {
    font-size: 24px;
  }
  
  .testimonial-author {
    display: flex;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
  }
  
  .author-image {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 12px;
  }
  
  .author-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  
  .author-info h4 {
    font-size: 16px;
    margin: 0 0 3px;
  }
  
  .author-info p {
    font-size: 13px;
    color: #777;
    margin: 0;
  }
  
  .testimonials-navigation {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 20px;
  }
  
  .testimonial-dot {
    width: 8px;
    height: 8px;
  }
  
  @media (min-width: 992px) {
    .testimonials-track {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 15px;
    }
  }
  
  @media (max-width: 991px) {
    .testimonial-card {
      flex: 0 0 90%;
      margin-right: 10px;
    }
  }
  
  @media (max-width: 768px) {
    .testimonials-section {
      padding: 30px 0;
    }
    
    .testimonial-card {
      padding: 15px;
    }
    
    .testimonial-content p {
      font-size: 14px;
    }
    
    .author-image {
      width: 35px;
      height: 35px;
    }
  }

  /* Styles pour la section CTA révisée */
  .cta-section {
    padding: 50px 0;
    background: linear-gradient(135deg, #f8f8f8, #f0f0f0);
    position: relative;
    overflow: hidden;
  }
  
  .cta-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 20px;
  }
  
  .cta-content {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    padding: 30px 0;
  }
  
  .cta-decoration {
    position: absolute;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    opacity: 0.15;
    z-index: 0;
  }
  
  .cta-decoration.left {
    background: linear-gradient(45deg, #a604ab, #6b0b6e);
    left: -35px;
    top: -35px;
  }
  
  .cta-decoration.right {
    background: linear-gradient(45deg, #a604ab, #6b0b6e);
    right: -35px;
    bottom: -35px;
  }
  
  .cta-text {
    background-color: white;
    padding: 40px;
    border-radius: 10px;
    text-align: center;
    position: relative;
    z-index: 1;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    max-width: 600px;
    border-left: 3px solid #a604ab;
    border-right: 3px solid #a604ab;
  }
  
  .cta-text:before, .cta-text:after {
    content: '';
    position: absolute;
    width: 50px;
    height: 3px;
    background-color: #a604ab;
  }
  
  .cta-text:before {
    top: 0;
    left: 0;
  }
  
  .cta-text:after {
    bottom: 0;
    right: 0;
  }
  
  .cta-section h2 {
    font-size: 28px;
    color: #333;
    margin-bottom: 15px;
    position: relative;
    display: inline-block;
  }
  
  .cta-section h2:after {
    content: '';
    display: block;
    width: 50px;
    height: 2px;
    background-color: #a604ab;
    margin: 10px auto 0;
  }
  
  .cta-section p {
    font-size: 16px;
    color: #666;
    margin-bottom: 25px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
  }
  
  .cta-button-fancy {
    display: inline-block;
    background: linear-gradient(135deg, #a604ab, #8a038f);
    color: white;
    padding: 12px 35px;
    border-radius: 30px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(166, 4, 171, 0.3);
  }
  
  .cta-button-fancy:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: all 0.6s;
  }
  
  .cta-button-fancy:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(166, 4, 171, 0.4);
  }
  
  .cta-button-fancy:hover:before {
    left: 100%;
  }
  
  @media (max-width: 768px) {
    .cta-text {
      padding: 30px 20px;
    }
    
    .cta-decoration {
      width: 100px;
      height: 100px;
    }
  }
  
  /* Styles pour le footer */
  .footer-main {
    text-align: center;
    margin-bottom: 30px;
    background-color: #2C3E50;
    color: #ECF0F1;
    padding: 60px 0 20px;
  }
  
  .footer-logo {
    max-width: 150px;
    margin-bottom: 15px;
  }
  
  .social-icons {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin: 15px 0;
  }
  
  .social-icons .icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: var(--color, rgba(255,255,255,0.1));
    border-radius: 50%;
    color: white;
    font-size: 18px;
    text-decoration: none;
    transition: all 0.3s;
  }
  
  .social-icons .icon:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  }
  
  .links {
    margin-bottom: 30px;
    min-width: 200px;
    background-color: #2C3E50;
    color: #ECF0F1;
  }
  
  .links p {
    font-weight: bold;
    margin-bottom: 15px;
    color: white;
  }
  
  .links a {
    display: block;
    color: #BDC3C7;
    text-decoration: none;
    margin-bottom: 8px;
    transition: color 0.3s;
  }
  
  .links a:hover {
    color: #a604ab;
  }
  
  .payment-methods {
    display: flex;
    gap: 10px;
    margin-top: 10px;
  }
  
  .payment-methods img {
    height: 30px;
    filter: brightness(0) invert(1);
    opacity: 0.8;
  }
  
  .footer-section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    background-color: #2C3E50;
    color: #ECF0F1;
  }
  
  .footer-separator {
    height: 1px;
    background-color: rgba(255,255,255,0.1);
    margin: 20px 0;
  }
  
  .footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    padding: 20px 0;
  }
  
  .footer-bottom p {
    color: #BDC3C7;
    font-size: 14px;
    margin: 0;
  }
  
  .footer-links-bottom {
    display: flex;
    gap: 20px;
  }
  
  .footer-links-bottom a {
    color: #BDC3C7;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s;
  }
  
  .footer-links-bottom a:hover {
    color: #a604ab;
  }
  
  @media (max-width: 768px) {
    .footer-bottom {
      flex-direction: column;
      text-align: center;
      gap: 15px;
    }
    
    .footer-links-bottom {
      justify-content: center;
    }
  }
</style>

</body>
</html>
