<?php
/**
 * Exemple d'intégration de Cloudinary dans la page d'activité
 * 
 * Ce fichier montre comment remplacer les images locales par des images Cloudinary
 * dans le contexte réel de l'application Click'N'Go
 */

// Inclure les fichiers requis
require_once __DIR__ . '/../utils/cloudinary_helper.php';

// Simulons des données d'activité (normalement récupérées depuis la base de données)
$activity = [
    'id' => 1,
    'name' => 'Saut en parachute',
    'description' => 'Découvrez des sensations fortes avec un saut en parachute au-dessus des paysages tunisiens. Une expérience inoubliable encadrée par des professionnels.',
    'price' => 250,
    'category' => 'Aérien',
    'location' => 'Tunis',
    'image' => 'parc.jpg', // Nom du fichier image
    'rating' => 4.8
];

// Simulons des activités supplémentaires pour la section "Vous pourriez aussi aimer"
$relatedActivities = [
    [
        'id' => 2,
        'name' => 'Dégustation culinaire à La Marsa',
        'description' => 'Découvrez les saveurs tunisiennes lors d\'une dégustation guidée par un chef local.',
        'price' => 85,
        'category' => 'Gastronomie',
        'image' => 'Dégustation culinaire à La Marsa.jpg'
    ],
    [
        'id' => 3,
        'name' => 'Karting à La Soukra',
        'description' => 'Adrénaline et vitesse sur une piste professionnelle de karting.',
        'price' => 60,
        'category' => 'Sport',
        'image' => 'Karting à La Soukra.jpg'
    ],
    [
        'id' => 4,
        'name' => 'Visite culturelle à Sidi Bou Said',
        'description' => 'Découvrez l\'histoire et l\'architecture unique de ce village pittoresque.',
        'price' => 40,
        'category' => 'Culture',
        'image' => 'Visite culturelle à Sidi Bou Said.jpg'
    ]
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($activity['name']); ?> - Click'N'Go</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }
        
        .header {
            background-image: linear-gradient(135deg, #D48DD8, #9768D1);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .activity-image-container {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .activity-image {
            width: 100%;
            height: 450px;
            object-fit: cover;
        }
        
        .activity-details {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .activity-description {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #555;
            margin-bottom: 25px;
        }
        
        .activity-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .meta-item {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .meta-item i {
            color: #9768D1;
            font-size: 1.3rem;
        }
        
        .price-card {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.05);
            position: sticky;
            top: 30px;
        }
        
        .price {
            font-size: 2.5rem;
            font-weight: 700;
            color: #9768D1;
            margin-bottom: 20px;
        }
        
        .book-btn {
            background-image: linear-gradient(135deg, #D48DD8, #9768D1);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 15px 25px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .book-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(151, 104, 209, 0.4);
        }
        
        .rating {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .stars {
            color: #FFD700;
            margin-right: 10px;
        }
        
        .related-section {
            margin: 60px 0;
        }
        
        .related-section h2 {
            margin-bottom: 25px;
            color: #9768D1;
            font-weight: 600;
        }
        
        .related-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .related-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .related-content {
            padding: 20px;
        }
        
        .related-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .related-price {
            color: #9768D1;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }
        
        .related-btn {
            background-color: transparent;
            color: #9768D1;
            border: 2px solid #9768D1;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .related-btn:hover {
            background-color: #9768D1;
            color: white;
        }
        
        .example-note {
            background-color: #fef8e3;
            border-left: 4px solid #ffd659;
            padding: 15px;
            margin: 30px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="example-note container">
        <h3><i class="fas fa-info-circle"></i> Note d'exemple</h3>
        <p>Ceci est une page d'exemple montrant comment intégrer les images Cloudinary dans une page d'activité de Click'N'Go. Les éléments en violet montrent les différentes façons d'utiliser Cloudinary.</p>
    </div>

    <header class="header">
        <div class="container">
            <h1><?php echo htmlspecialchars($activity['name']); ?></h1>
            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($activity['location']); ?></p>
        </div>
    </header>

    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Image principale de l'activité avec Cloudinary -->
                <div class="activity-image-container">
                    <!-- Méthode 1: Utilisation de la fonction d'aide cloudinaryImage() -->
                    <?php echo cloudinaryImage($activity['image'], $activity['name'], ['w_1200', 'q_auto'], ['class' => 'activity-image']); ?>
                </div>

                <div class="activity-details">
                    <h2>Description</h2>
                    <p class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></p>
                    
                    <div class="activity-meta">
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span><?php echo htmlspecialchars($activity['category']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-users"></i>
                            <span>2-6 personnes</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>3 heures</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Disponible toute l'année</span>
                        </div>
                    </div>
                    
                    <h3>Ce qui est inclus</h3>
                    <ul>
                        <li>Équipement de sécurité</li>
                        <li>Instructeur professionnel</li>
                        <li>Vidéo souvenir</li>
                        <li>Transport depuis l'hôtel (en option)</li>
                    </ul>
                    
                    <h3>À savoir</h3>
                    <ul>
                        <li>Âge minimum : 18 ans</li>
                        <li>Condition physique requise</li>
                        <li>Réservation 48h à l'avance</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="price-card">
                    <div class="price"><?php echo htmlspecialchars($activity['price']); ?> DT</div>
                    
                    <div class="rating">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= floor($activity['rating'])): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i - 0.5 <= $activity['rating']): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span><?php echo $activity['rating']; ?> (42 avis)</span>
                    </div>
                    
                    <button class="book-btn">Réserver maintenant</button>
                    
                    <hr>
                    
                    <h4>Partagez cette activité</h4>
                    <div class="social-share">
                        <a href="#" class="btn btn-outline-primary"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-info"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-danger"><i class="fab fa-pinterest"></i></a>
                        <a href="#" class="btn btn-outline-secondary"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="related-section">
            <h2>Vous pourriez aussi aimer</h2>
            
            <div class="row">
                <?php foreach ($relatedActivities as $relatedActivity): ?>
                    <div class="col-md-4 mb-4">
                        <div class="related-card">
                            <!-- Méthode 2: Utilisation de getCloudinaryUrl() pour obtenir juste l'URL -->
                            <img src="<?php echo getCloudinaryUrl($relatedActivity['image'], ['w_600', 'h_400', 'c_fill', 'q_auto']); ?>" 
                                 alt="<?php echo htmlspecialchars($relatedActivity['name']); ?>" 
                                 class="related-image">
                            
                            <div class="related-content">
                                <h3 class="related-title"><?php echo htmlspecialchars($relatedActivity['name']); ?></h3>
                                <p><?php echo htmlspecialchars($relatedActivity['description']); ?></p>
                                <div class="related-price"><?php echo htmlspecialchars($relatedActivity['price']); ?> DT</div>
                                <a href="#" class="btn related-btn">Voir les détails</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <!-- Méthode 3: URL Cloudinary directe avec transformation -->
                    <img src="https://res.cloudinary.com/dbm44rmok/image/upload/w_200,q_auto/v1683407006/logo_abcdef" alt="Click'N'Go" class="mb-3">
                    <p>Découvrez les meilleures activités en Tunisie avec Click'N'Go.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liens rapides</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Accueil</a></li>
                        <li><a href="#" class="text-white">Activités</a></li>
                        <li><a href="#" class="text-white">Événements</a></li>
                        <li><a href="#" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <address>
                        <p><i class="fas fa-map-marker-alt"></i> Tunis, Tunisie</p>
                        <p><i class="fas fa-phone"></i> +216 71 123 456</p>
                        <p><i class="fas fa-envelope"></i> info@clickngo.tn</p>
                    </address>
                </div>
            </div>
            <hr>
            <p class="text-center mb-0">© <?php echo date('Y'); ?> Click'N'Go - Tous droits réservés</p>
        </div>
    </footer>
    
    <div class="container mt-5 mb-5">
        <div class="text-center">
            <a href="../exemples/exemple_cloudinary.php" class="btn btn-primary mx-2">Voir d'autres exemples</a>
            <a href="../cloudinary_gallery.php" class="btn btn-secondary mx-2">Retour à la galerie Cloudinary</a>
            <a href="../index.php" class="btn btn-outline-primary mx-2">Page d'accueil</a>
        </div>
    </div>
</body>
</html> 