<?php
require_once __DIR__ . '/../../controller/ReviewController.php';

// Initialiser le contrôleur de critiques
$reviewController = new ReviewController();
$approvedReviews = $reviewController->index();
$averageRating = $reviewController->getAverageRating();

// Analyser les données pour les statistiques
$totalReviews = count($approvedReviews);
$ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

foreach ($approvedReviews as $review) {
    $rating = $review['rating'];
    if (isset($ratingCounts[$rating])) {
        $ratingCounts[$rating]++;
    }
}

// Calculer les pourcentages pour chaque note
$ratingPercentages = [];
foreach ($ratingCounts as $rating => $count) {
    $ratingPercentages[$rating] = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avis Clients - Click'N'Go</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .reviews-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .reviews-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .reviews-header h1 {
            color: #9768D1;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .reviews-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .reviews-section {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(151, 104, 209, 0.1);
            padding: 40px;
            margin-bottom: 40px;
        }

        .reviews-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            margin-bottom: 40px;
        }

        .average-rating {
            flex: 1;
            min-width: 250px;
            text-align: center;
        }

        .average-rating .rating-value {
            font-size: 6rem;
            font-weight: 700;
            color: #333;
            line-height: 1;
            margin-bottom: 10px;
        }

        .average-rating .stars {
            color: #FFD700;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .average-rating .review-count {
            color: #666;
            font-size: 1.1rem;
        }

        .rating-bars {
            flex: 2;
            min-width: 300px;
        }

        .rating-bar {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .rating-label {
            width: 100px;
            display: flex;
            align-items: center;
        }

        .rating-label .star {
            color: #FFD700;
            margin-right: 5px;
        }

        .rating-label .count {
            color: #666;
            font-size: 0.9rem;
        }

        .progress-container {
            flex: 1;
            height: 15px;
            background-color: #F0F0F0;
            border-radius: 10px;
            margin-right: 15px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(to right, #9768D1, #D48DD8);
            border-radius: 10px;
            transition: width 1s;
        }

        .percentage {
            color: #666;
            font-size: 0.9rem;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .review-card {
            background: linear-gradient(135deg, rgba(245, 240, 255, 0.8), rgba(255, 250, 255, 0.9));
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(151, 104, 209, 0.2);
        }

        .review-header {
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            padding: 20px;
            color: white;
        }

        .review-header h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .review-header .review-stars {
            color: #FFD700;
        }

        .review-content {
            padding: 20px;
        }

        .review-text {
            color: #555;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .review-author {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .review-author-name {
            font-weight: 600;
            color: #333;
        }

        .review-date {
            color: #777;
            font-size: 0.9rem;
        }

        .filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }

        .filter-btn {
            background: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 8px 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-btn:hover, .filter-btn.active {
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            color: white;
            border-color: transparent;
        }

        .filter-btn i {
            margin-right: 5px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }

        .page-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: white;
            border: 1px solid #ddd;
            color: #333;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .page-btn:hover, .page-btn.active {
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            color: white;
            border-color: transparent;
        }

        .page-btn.prev, .page-btn.next {
            width: auto;
            padding: 0 15px;
            border-radius: 20px;
        }

        .review-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .review-activity {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            font-size: 0.9rem;
            color: #666;
        }

        .review-activity i {
            color: #9768D1;
        }

        .write-review-section {
            background: linear-gradient(135deg, rgba(245, 240, 255, 0.8), rgba(255, 250, 255, 0.9));
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-top: 60px;
        }

        .write-review-section h3 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .write-review-section p {
            color: #666;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .write-review-btn {
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .write-review-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(151, 104, 209, 0.3);
        }

        .header-reviews {
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/banner.jpg');
            background-size: cover;
            background-position: center;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .header-reviews h1 {
            font-size: 3rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .reviews-summary {
                flex-direction: column;
                gap: 20px;
            }
            
            .reviews-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-options {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/rating.jpg'); background-size: cover; background-position: center; padding: 20px 0;">
        <div style="display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div class="logo-container">
                <img src="images/logo.png" class="logo" alt="Logo ClickNGo" style="height: 180px; filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.9));">
            </div>
            <nav style="display: flex; align-items: center;">
                <ul class="nav-links" style="display: flex; list-style: none; margin: 0; padding: 0;">
                    <li style="margin: 0 15px;"><a href="index.html" style="color: white; text-decoration: none;">Accueil</a></li>
                    <li class="dropdown" style="margin: 0 15px; position: relative;">
                        <a href="activite.php" class="dropbtn" style="color: white; text-decoration: none;">Activités</a>
                        <div class="dropdown-content" style="position: absolute; background-color: white; min-width: 200px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); z-index: 1; display: none;">
                            <a href="activite.php#categories-section" style="color: #333; padding: 12px 16px; text-decoration: none; display: block;">Catégories</a>
                            <a href="activite.php#activites-pres-de-vous" style="color: #333; padding: 12px 16px; text-decoration: none; display: block;">Activités près de vous</a>
                            <a href="activite.php#categories-entreprises" style="color: #333; padding: 12px 16px; text-decoration: none; display: block;">Catégories d'entreprises</a>
                            <a href="activite.php#nos-atouts" style="color: #333; padding: 12px 16px; text-decoration: none; display: block;">Nos atouts</a>
                            <a href="activite.php#description-activites" style="color: #333; padding: 12px 16px; text-decoration: none; display: block;">Nos activités exceptionnelles</a>
                            <a href="activite.php#avis-clients" style="color: #333; padding: 12px 16px; text-decoration: none; display: block;">Avis clients</a>
                        </div>
                    </li>
                    <li style="margin: 0 15px;"><a href="events.html" style="color: white; text-decoration: none;">Événements</a></li>
                    <li style="margin: 0 15px;"><a href="Produits.html" style="color: white; text-decoration: none;">Produits</a></li>
                    <li style="margin: 0 15px;"><a href="transports.html" style="color: white; text-decoration: none;">Transports</a></li>
                    <li style="margin: 0 15px;"><a href="sponsors.html" style="color: white; text-decoration: none;">Sponsors</a></li>
                </ul>
            </nav>
            <a href="#" class="register-btn" style="background-color: #E435E9; color: white; padding: 10px 20px; border-radius: 30px; text-decoration: none;">Register</a>
        </div>
        <h1 style="text-align: center; color: white; margin-top: 40px;">Avis de nos clients</h1>
    </header>

    <!-- Main Content -->
    <div class="reviews-container">
        <div class="reviews-header">
            <h1>Ce que nos clients disent de nous</h1>
            <p>Découvrez les témoignages authentiques de personnes qui ont vécu nos expériences. Votre satisfaction est notre priorité !</p>
        </div>

        <div class="reviews-section">
            <div class="reviews-summary">
                <div class="average-rating">
                    <div class="rating-value"><?php echo number_format($averageRating, 1); ?></div>
                    <div class="stars">
                        <?php
                        $fullStars = floor($averageRating);
                        $halfStar = $averageRating - $fullStars >= 0.5;
                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                        
                        for ($i = 0; $i < $fullStars; $i++) {
                            echo '<i class="fas fa-star"></i>';
                        }
                        
                        if ($halfStar) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        }
                        
                        for ($i = 0; $i < $emptyStars; $i++) {
                            echo '<i class="far fa-star"></i>';
                        }
                        ?>
                    </div>
                    <div class="review-count"><?php echo $totalReviews; ?> avis</div>
                </div>
                
                <div class="rating-bars">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                    <div class="rating-bar">
                        <div class="rating-label">
                            <span class="star"><?php echo $i; ?> <i class="fas fa-star"></i></span>
                            <span class="count">(<?php echo $ratingCounts[$i]; ?>)</span>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo $ratingPercentages[$i]; ?>%;"></div>
                        </div>
                        <div class="percentage"><?php echo round($ratingPercentages[$i]); ?>%</div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="filter-options">
                <button class="filter-btn active" data-filter="all">Tous les avis</button>
                <button class="filter-btn" data-filter="5"><i class="fas fa-star"></i> 5 étoiles</button>
                <button class="filter-btn" data-filter="4"><i class="fas fa-star"></i> 4 étoiles</button>
                <button class="filter-btn" data-filter="3"><i class="fas fa-star"></i> 3 étoiles</button>
                <button class="filter-btn" data-filter="2"><i class="fas fa-star"></i> 2 étoiles</button>
                <button class="filter-btn" data-filter="1"><i class="fas fa-star"></i> 1 étoile</button>
                <button class="filter-btn" data-filter="image"><i class="fas fa-image"></i> Avec photos</button>
            </div>
            
            <div class="reviews-grid">
                <?php if (!empty($approvedReviews)): ?>
                    <?php foreach ($approvedReviews as $review): ?>
                        <div class="review-card" data-rating="<?php echo $review['rating']; ?>" data-has-image="<?php echo !empty($review['image_path']) ? 'true' : 'false'; ?>">
                            <div class="review-header">
                                <h3><?php echo htmlspecialchars($review['activity_name']); ?></h3>
                                <div class="review-stars">
                                    <?php
                                    for ($i = 0; $i < $review['rating']; $i++) {
                                        echo '<i class="fas fa-star"></i>';
                                    }
                                    for ($i = $review['rating']; $i < 5; $i++) {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php if (!empty($review['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($review['image_path']); ?>" alt="Image review" class="review-image" onerror="this.src='images/default-review.jpg'">
                            <?php endif; ?>
                            <div class="review-content">
                                <div class="review-text"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></div>
                                <div class="review-author">
                                    <div>
                                        <div class="review-author-name"><?php echo htmlspecialchars($review['customer_name']); ?></div>
                                        <div class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1 / -1; text-align: center; color: #666;">Aucun avis pour le moment. Soyez le premier à partager votre expérience !</p>
                <?php endif; ?>
            </div>
            
            <?php if (count($approvedReviews) > 0): ?>
                <div class="pagination">
                    <button class="page-btn prev" id="prev-page"><i class="fas fa-chevron-left"></i> Précédent</button>
                    <div id="pagination-numbers">
                        <!-- Buttons will be added dynamically -->
                    </div>
                    <button class="page-btn next" id="next-page">Suivant <i class="fas fa-chevron-right"></i></button>
                </div>
            <?php endif; ?>
            
            <div class="write-review-section">
                <h3>Partagez votre expérience</h3>
                <p>Avez-vous récemment participé à l'une de nos activités ? Nous serions ravis de connaître votre avis. Votre témoignage aide d'autres voyageurs à choisir la meilleure expérience pour eux.</p>
                <a href="activite.php#avis-clients" class="write-review-btn">Laisser un avis</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
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
                <a href="about.php">À propos</a>
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
                <a href="conditions-generales.php">Conditions générales</a>
                <a href="mentions-legales.php">Mentions légales</a>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Filtrage des avis
            $('.filter-btn').click(function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                
                var filter = $(this).data('filter');
                
                $('.review-card').hide();
                
                if (filter === 'all') {
                    $('.review-card').show();
                } else if (filter === 'image') {
                    $('.review-card[data-has-image="true"]').show();
                } else {
                    $('.review-card[data-rating="' + filter + '"]').show();
                }
                
                // Réinitialiser la pagination
                currentPage = 1;
                setupPagination();
            });
            
            // Pagination
            var currentPage = 1;
            var itemsPerPage = 6;
            var totalPages = Math.ceil($('.review-card').length / itemsPerPage);
            
            function setupPagination() {
                var visibleItems = $('.review-card:visible');
                totalPages = Math.ceil(visibleItems.length / itemsPerPage);
                
                $('#pagination-numbers').empty();
                
                for (var i = 1; i <= totalPages; i++) {
                    $('#pagination-numbers').append('<button class="page-btn" data-page="' + i + '">' + i + '</button>');
                }
                
                $('.page-btn[data-page="' + currentPage + '"]').addClass('active');
                
                // Pagination des éléments
                visibleItems.hide();
                visibleItems.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage).show();
                
                // Afficher/cacher les boutons de navigation
                if (currentPage === 1) {
                    $('#prev-page').prop('disabled', true).css('opacity', 0.5);
                } else {
                    $('#prev-page').prop('disabled', false).css('opacity', 1);
                }
                
                if (currentPage === totalPages || totalPages === 0) {
                    $('#next-page').prop('disabled', true).css('opacity', 0.5);
                } else {
                    $('#next-page').prop('disabled', false).css('opacity', 1);
                }
            }
            
            // Événements de pagination
            $(document).on('click', '.page-btn[data-page]', function() {
                currentPage = parseInt($(this).data('page'));
                setupPagination();
            });
            
            $('#prev-page').click(function() {
                if (currentPage > 1) {
                    currentPage--;
                    setupPagination();
                }
            });
            
            $('#next-page').click(function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    setupPagination();
                }
            });
            
            // Initialiser la pagination
            setupPagination();
        });
    </script>
</body>
</html> 