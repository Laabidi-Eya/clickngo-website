<?php
// Inclure les contrôleurs nécessaires
require_once __DIR__ . '/../../controller/ReviewController.php';
require_once __DIR__ . '/../../controller/ActivityController.php';

// Initialiser les contrôleurs
$reviewController = new ReviewController();
$activityController = new ActivityController();

// Initialiser les variables pour éviter les erreurs
$reviews = [];
$averageRating = 0;
$ratingDistribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$totalRatings = 0;

try {
    // Récupérer les données
    $reviews = $reviewController->getApprovedReviews();
    $averageRating = $reviewController->getAverageRating();
    $ratingDistribution = $reviewController->getRatingsDistribution();
    $activities = $activityController->getAllActivities(); // Pour obtenir les images des activités

    // Calculer le nombre total de notes
    $totalRatings = array_sum($ratingDistribution);
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des avis: " . $e->getMessage());
}

// Message de succès (si présent)
$successMessage = isset($_GET['success']) && $_GET['success'] == 1 ? "Votre avis a été soumis avec succès et sera visible après modération." : "";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avis Clients - ClickN'Go</title>
    <link rel="stylesheet" href="../front office/style.css">
    <style>
        /* Styles spécifiques pour la page des avis */
        .reviews-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .reviews-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .average-rating {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ff6b6b;
        }
        
        .rating-stars {
            font-size: 2rem;
            color: #ffd700;
            margin: 10px 0;
        }
        
        .rating-distribution {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        
        .rating-bar {
            margin: 0 10px;
            text-align: center;
        }
        
        .bar-label {
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .bar-outer {
            width: 150px;
            height: 10px;
            background-color: #eee;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .bar-inner {
            height: 100%;
            background-color: #ff6b6b;
        }
        
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        
        .review-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }
        
        .review-card:hover {
            transform: translateY(-5px);
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .review-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .review-rating {
            color: #ffd700;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
        
        .review-author {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .review-date {
            font-size: 0.8rem;
            color: #888;
        }
        
        .review-content {
            line-height: 1.6;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="reviews-container">
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <div class="reviews-header">
            <h1>Ce que nos clients disent de nous</h1>
            <div class="average-rating"><?php echo number_format($averageRating, 1); ?> / 5</div>
            <div class="rating-stars">
                <?php 
                $fullStars = floor($averageRating);
                $halfStar = $averageRating - $fullStars >= 0.5;
                
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $fullStars) {
                        echo '★'; // Full star
                    } elseif ($halfStar && $i == $fullStars + 1) {
                        echo '⯪'; // Half star
                    } else {
                        echo '☆'; // Empty star
                    }
                }
                ?>
            </div>
            <p>Basé sur <?php echo $totalRatings; ?> avis</p>
            
            <div class="rating-distribution">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <div class="rating-bar">
                        <div class="bar-label"><?php echo $i; ?> étoiles</div>
                        <div class="bar-outer">
                            <div class="bar-inner" style="width: <?php echo $totalRatings > 0 ? ($ratingDistribution[$i] / $totalRatings * 100) : 0; ?>%;"></div>
                        </div>
                        <div class="bar-count"><?php echo $ratingDistribution[$i]; ?></div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="reviews-grid">
            <?php if (is_array($reviews) && count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <?php
                            // Déterminer l'image à afficher
                            $imagePath = 'images/default-review.jpg'; // Image par défaut
                            
                            if (!empty($review['image_path'])) {
                                // Utiliser l'image téléchargée avec l'avis
                                if (filter_var($review['image_path'], FILTER_VALIDATE_URL)) {
                                    // Si c'est une URL (Cloudinary), on l'utilise directement
                                    $imagePath = $review['image_path'];
                                } else {
                                    // Sinon c'est un chemin local
                                    $imagePath = $review['image_path'];
                                    // Vérifier si le fichier existe
                                    if (!file_exists($imagePath)) {
                                        $imagePath = 'images/default-review.jpg';
                                    }
                                }
                            } else {
                                // Sinon, essayer de trouver une image correspondante dans les activités
                                if (isset($activities) && is_array($activities)) {
                                    foreach ($activities as $activity) {
                                        if (isset($activity['name']) && isset($review['activity_name']) && 
                                            strtolower($activity['name']) === strtolower($review['activity_name'])) {
                                            if (!empty($activity['image'])) {
                                                if (filter_var($activity['image'], FILTER_VALIDATE_URL)) {
                                                    // URL Cloudinary
                                                    $imagePath = $activity['image'];
                                                } else {
                                                    // Chemin local
                                                    $imagePath = $activity['image'];
                                                    // Vérifier si le fichier existe
                                                    if (!file_exists($imagePath)) {
                                                        $imagePath = 'images/default-review.jpg';
                                                    }
                                                }
                                            }
                                            break;
                                        }
                                    }
                                }
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($review['activity_name'] ?? 'Activité'); ?>" class="review-image" onerror="this.src='images/default-review.jpg';">
                            <div>
                                <div class="review-rating">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= ($review['rating'] ?? 0) ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                                <div class="review-author"><?php echo htmlspecialchars($review['customer_name'] ?? 'Client'); ?></div>
                                <div class="review-date"><?php echo isset($review['created_at']) ? date('d/m/Y', strtotime($review['created_at'])) : ''; ?></div>
                            </div>
                        </div>
                        <div class="review-content">
                            <h3><?php echo htmlspecialchars($review['activity_name'] ?? 'Activité'); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($review['comment'] ?? '')); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="review-card">
                    <p>Aucun avis pour le moment. Soyez le premier à partager votre expérience !</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 