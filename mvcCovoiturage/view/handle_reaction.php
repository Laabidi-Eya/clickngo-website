<?php
header('Content-Type: application/json');
session_start();

// Include config file
require_once dirname(__DIR__) . '/config.php';

// Get PDO connection
$pdo = config::getConnexion();

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['driver_id']) || !isset($input['rating'])) {
        throw new Exception('Invalid input');
    }

    $driverId = $input['driver_id'];
    $rating = (int)$input['rating'];
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Invalid rating value');
    }

    $parts = explode('_', $driverId);
    if (count($parts) < 2) {
        throw new Exception('Invalid driver ID format');
    }
    $prenom = $parts[0];
    $nom = $parts[1];

    $stmt = $pdo->prepare("SELECT likes AS average_rating, dislikes AS vote_count FROM annonce_covoiturage WHERE prenom_conducteur = ? AND nom_conducteur = ?");
    $stmt->execute([$prenom, $nom]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$driver) {
        throw new Exception('Conducteur non trouvé.');
    }

    $currentAverage = (float)$driver['average_rating'];
    $voteCount = (int)$driver['vote_count'];

    // Calculate new average
    $totalRatingPoints = $currentAverage * $voteCount;
    $totalRatingPoints += $rating;
    $newVoteCount = $voteCount + 1;
    $newAverage = $totalRatingPoints / $newVoteCount;

    // Update database
    $updateStmt = $pdo->prepare("UPDATE annonce_covoiturage SET likes = ?, dislikes = ? WHERE prenom_conducteur = ? AND nom_conducteur = ?");
    $updateStmt->execute([$newAverage, $newVoteCount, $prenom, $nom]);

    // Store user rating in session
    $_SESSION['user_ratings'][$driverId] = $rating;

    echo json_encode([
        'success' => true,
        'average_rating' => $newAverage,
        'vote_count' => $newVoteCount
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>