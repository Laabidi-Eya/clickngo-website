<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permet à BotPenguin d'accéder à l'API

require_once '../config.php'; // Connexion à la base de données

$pdo = config::getConnexion();

// Récupérer les paramètres de la requête
$action = $_GET['action'] ?? '';
$depart = $_GET['depart'] ?? '';
$arrivee = $_GET['arrivee'] ?? '';
$date = $_GET['date'] ?? '';

$response = [];

if ($action === 'get_annonces') {
    // Rechercher des annonces
    $query = "SELECT * FROM annonce_covoiturage WHERE lieu_depart = :depart AND lieu_arrivee = :arrivee AND DATE(date_depart) = :date AND (statut = 'active' OR statut IS NULL)";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['depart' => $depart, 'arrivee' => $arrivee, 'date' => $date]);
    $annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = $annonces;
} elseif ($action === 'get_places') {
    // Vérifier les places disponibles
    $query = "SELECT nombre_places FROM annonce_covoiturage WHERE lieu_depart = :depart AND lieu_arrivee = :arrivee AND DATE(date_depart) = :date AND (statut = 'active' OR statut IS NULL) LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['depart' => $depart, 'arrivee' => $arrivee, 'date' => $date]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response = $result ? ['places' => $result['nombre_places']] : ['places' => 0];
} elseif ($action === 'get_conducteur') {
    // Infos sur le conducteur
    $query = "SELECT prenom_conducteur, nom_conducteur, tel_conducteur FROM annonce_covoiturage WHERE lieu_depart = :depart AND lieu_arrivee = :arrivee AND DATE(date_depart) = :date AND (statut = 'active' OR statut IS NULL) LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['depart' => $depart, 'arrivee' => $arrivee, 'date' => $date]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response = $result ?: ['error' => 'Aucun conducteur trouvé'];
} elseif ($action === 'get_popular_routes') {
    // Trajets populaires
    $query = "SELECT lieu_depart, lieu_arrivee, COUNT(*) as count FROM annonce_covoiturage GROUP BY lieu_depart, lieu_arrivee ORDER BY count DESC LIMIT 3";
    $stmt = $pdo->query($query);
    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = $routes;
}

echo json_encode($response);
?>