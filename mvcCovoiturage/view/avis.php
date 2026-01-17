<?php
require_once '../config.php';
require_once '../Controller/avisController.php';

try {
    $controller = new AvisController();
    $avis = $controller->getAllAvis();
    $stats = $controller->getAvisStats();
    $averageRating = $stats['average_rating'];
    $totalReviews = $stats['total_reviews'];
} catch (Exception $e) {
    $avis = [];
    $averageRating = 0;
    $totalReviews = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avis - Click'N'go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
           
        }

        body {
            background: linear-gradient(135deg, #f5e7f3, #e9e6ff);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 1000px;
            width: 100%;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            padding: 30px;
            position: relative;
            overflow: hidden;
            margin-top: 80px; /* Ajout d'espace entre la navbar et la carte */
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d2d2d;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeIn 1s ease-out;
        }

        .header h1::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            margin: 15px auto;
            border-radius: 2px;
        }

        .rating-summary {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            font-size: 1.2rem;
            color: #333;
            animation: slideUp 1s ease-out;
        }

        .rating-summary .stars {
            color: #ff8fa3;
            margin-right: 10px;
            font-size: 1.3rem;
        }

        .rating-summary .count {
            color: #666;
            font-weight: 400;
        }

        .review-carousel {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            animation: slideUp 0.5s ease-out;
        }

        .review-image {
            width: 300px;
            height: 200px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .review-image:hover {
            transform: scale(1.05);
        }

        .review-content {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            max-width: 400px;
            position: relative;
            transition: all 0.3s ease;
        }

        .review-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .review-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d2d2d;
            margin-bottom: 10px;
        }

        .review-content .stars {
            color: #ff8fa3;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .review-content p {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .review-content .author {
            font-weight: 500;
            color: #333;
            text-align: right;
            font-size: 0.9rem;
        }

        .arrow {
            font-size: 1.5rem;
            color: #666;
            cursor: pointer;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: #fff;
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .arrow:hover {
            color: #ff8fa3;
            transform: translateY(-50%) scale(1.1);
        }

        .arrow-left {
            left: 20px;
        }

        .arrow-right {
            right: 20px;
        }

        .verified {
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.9rem;
            color: #ff8fa3;
            font-weight: 500;
            background: #fff;
            padding: 5px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .all-reviews {
            margin-top: 40px;
        }

        .all-reviews h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d2d2d;
            margin-bottom: 20px;
            text-align: center;
            animation: fadeIn 1s ease-out;
        }

        .review-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }

        .review-item {
            background: #f9f9f9;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            animation: slideUp 0.5s ease-out;
        }

        .review-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .review-item .stars {
            color: #ff8fa3;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .review-item h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d2d2d;
            margin-bottom: 8px;
        }

        .review-item p {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .review-item .author {
            font-weight: 500;
            color: #333;
            font-size: 0.9rem;
        }

        .review-item .date {
            font-size: 0.85rem;
            color: #999;
            margin-left: 5px;
        }

        .review-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            margin-top: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .leave-review-btn {
            display: block;
            margin: 30px auto;
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: #fff;
            transition: all 0.3s ease;
            animation: slideUp 1s ease-out;
        }

        .leave-review-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 143, 163, 0.4);
        }

        .form-section {
            display: none;
            margin-top: 30px;
            padding: 25px;
            background: #f9f9f9;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .form-section.active {
            display: block;
            animation: slideUp 0.5s ease-out;
        }

        .form-section h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d2d2d;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-section label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 500;
            color: #333;
            font-size: 0.95rem;
        }

        .form-section input,
        .form-section textarea {
            width: 100%;
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-size: 0.95rem;
            background: #fff;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-section input:focus,
        .form-section textarea:focus {
            border-color: #ff8fa3;
            box-shadow: 0 0 10px rgba(255, 143, 163, 0.2);
        }

        .form-section textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-section button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: #fff;
            transition: all 0.3s ease;
        }

        .form-section button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 143, 163, 0.4);
        }

        .message {
            margin: 20px 0;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 0.95rem;
        }

        .success-message {
            background: #e0f7fa;
            color: #00695c;
            border: 1px solid #00695c;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #c62828;
        }

        /* Rating Stars */
        .rating-stars {
            display: flex;
            gap: 5px;
            margin-top: 5px;
        }

        .rating-stars .star {
            font-size: 1.5rem;
            color: #e0e0e0;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .rating-stars .star:hover,
        .rating-stars .star.selected {
            color: #ff8fa3;
        }

        /* Animations */
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes slideUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* Styles pour la navbar */
        nav {
            background: transparent; /* Fond transparent comme dans l'image */
        }

        nav .text-white {
            color: black !important;
        }

        nav .hover\:text-pink-300:hover {
            color: rgb(203, 55, 191) !important;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="absolute top-0 left-0 w-full z-50 p-4">
        <div class="flex items-center justify-center max-w-7xl mx-auto">
            <div class="flex space-x-8 text-lg font-bold text-white relative">
                <a href="#home" class="hover:text-pink-300">Accueil</a>
                <a href="#about" class="hover:text-pink-300">À propos</a>
                <div class="group relative">
                    <button class="hover:text-pink-300 font-bold text-lg">
                        Nos Détails ▾
                    </button>
                    <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                        <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Nos trajets</a>
                        <a href="#top-conducteurs" class="block px-4 py-2 hover:bg-gray-100">Top Conducteurs</a>
                        <a href="ListConducteurs.php" class="block px-4 py-2 hover:bg-gray-100">Mes annonces</a>
                        <a href="ListPassager.php" class="block px-4 py-2 hover:bg-gray-100">Mes Demandes</a>
                        <a href="mes_avis.php" class="block px-4 py-2 hover:bg-gray-100">Mes avis</a>
                    </div>
                </div>
                
                <!-- LISTE DÉROULANTE SERVICES -->
                <div class="relative group">
                    <button class="hover:text-pink-300">Services ▾</button>
                    <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[220px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                        <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Trouver un covoiturage</a>
                        <a href="AjouterConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Proposer un covoiturage</a>
                    </div>
                </div>
                
                <!-- LISTE DÉROULANTE CONTACT -->
                <div class="relative group">
                    <button class="hover:text-pink-300">Contact ▾</button>
                    <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[180px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                        <a href="/clickngo/view/reclamation.php" class="block px-4 py-2 hover:bg-gray-100">Réclamation</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h1>Les Avis</h1>
        </div>

        <!-- Rating Summary -->
        <div class="rating-summary">
            <span class="stars">★ <span id="avg-rating"><?php echo htmlspecialchars($averageRating); ?></span>/5</span>
            <span class="count">(<span id="total-reviews"><?php echo htmlspecialchars($totalReviews); ?></span> avis)</span>
        </div>

        <!-- Review Carousel -->
        <div class="review-carousel">
            <i class="fas fa-chevron-left arrow arrow-left" onclick="prevReview()"></i>
            <img id="review-image" class="review-image" src="<?php echo htmlspecialchars($avis[0]->getImageUrl() ?? ''); ?>" alt="Review Image" onerror="this.src='https://via.placeholder.com/300x200?text=Image+Indisponible';">
            <div class="review-content">
                <h3 id="review-title"><?php echo htmlspecialchars($avis[0]->getTitre() ?? ''); ?></h3>
                <div class="stars">★ <span id="review-rating"><?php echo htmlspecialchars($avis[0]->getNote() ?? ''); ?>/5</span></div>
                <p id="review-comment"><?php echo htmlspecialchars($avis[0]->getCommentaire() ?? ''); ?></p>
                <div class="author" id="review-author"><?php echo htmlspecialchars($avis[0]->getAuteur() ?? ''); ?></div>
            </div>
            <i class="fas fa-chevron-right arrow arrow-right" onclick="nextReview()"></i>
            <div class="verified">Les derniers avis 100% VÉRIFIÉS</div>
        </div>

        <!-- All Reviews Section -->
        <div class="all-reviews">
            <h2>Nos avis proviennent uniquement des clients ayant réalisé cette activité</h2>
            <div class="review-list" id="review-list">
                <?php foreach ($avis as $review): ?>
                    <div class="review-item" data-id="<?php echo $review->getIdAvis(); ?>">
                        <div class="stars">★ <?php echo htmlspecialchars($review->getNote()); ?>/5</div>
                        <h3><?php echo htmlspecialchars($review->getTitre()); ?></h3>
                        <p><?php echo htmlspecialchars($review->getCommentaire()); ?></p>
                        <div class="author"><?php echo htmlspecialchars($review->getAuteur()); ?>, <?php echo $review->getDateCreation()->format('M. Y'); ?></div>
                        <img src="<?php echo htmlspecialchars($review->getImageUrl()); ?>" alt="Review Image" onerror="this.src='https://via.placeholder.com/120?text=Image+Indisponible';">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Leave Review Button -->
        <button class="leave-review-btn" onclick="toggleForm()">Laissez votre avis</button>

        <!-- Form to Add a Review -->
        <div class="form-section" id="review-form">
            <h2>Laissez votre avis</h2>
            <div id="form-message" class="message" style="display: none;"></div>
            <form id="review-form-data" enctype="multipart/form-data">
                <label for="titre">Titre</label>
                <input type="text" id="titre" name="titre" placeholder="Titre de votre avis" required>

                <label for="commentaire">Commentaire</label>
                <textarea id="commentaire" name="commentaire" placeholder="Votre commentaire" required></textarea>

                <label>Note (1 à 5)</label>
                <div class="rating-stars" id="rating-stars">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <input type="hidden" id="note" name="note" value="0" required>

                <label for="auteur">Votre nom</label>
                <input type="text" id="auteur" name="auteur" placeholder="Votre nom" required>

                <label for="image">Image (JPEG, PNG, GIF, max 5MB)</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif" required>

                <button type="submit">Ajouter mon avis</button>
            </form>
        </div>
    </div>

    <script>
        let reviews = <?php echo json_encode(array_map(function($review) {
            return [
                'id_avis' => $review->getIdAvis(),
                'titre' => $review->getTitre(),
                'commentaire' => $review->getCommentaire(),
                'note' => $review->getNote(),
                'auteur' => $review->getAuteur(),
                'image_url' => $review->getImageUrl(),
                'date_creation' => $review->getDateCreation()->format('Y-m-d H:i:s')
            ];
        }, $avis)); ?>;
        let currentIndex = 0;

        function updateReview() {
            if (reviews.length === 0) return;
            const review = reviews[currentIndex];
            document.getElementById('review-image').src = review.image_url;
            document.getElementById('review-title').textContent = review.titre;
            document.getElementById('review-rating').textContent = review.note + '/5';
            document.getElementById('review-comment').textContent = review.commentaire;
            document.getElementById('review-author').textContent = review.auteur;
        }

        function nextReview() {
            currentIndex = (currentIndex + 1) % reviews.length;
            updateReview();
        }

        function prevReview() {
            currentIndex = (currentIndex - 1 + reviews.length) % reviews.length;
            updateReview();
        }

        function toggleForm() {
            const form = document.getElementById('review-form');
            form.classList.toggle('active');
            document.getElementById('review-form-data').reset();
            resetStars();
        }

        function addReviewToList(review) {
            const reviewList = document.getElementById('review-list');
            const reviewItem = document.createElement('div');
            reviewItem.classList.add('review-item');
            reviewItem.setAttribute('data-id', review.id_avis);
            reviewItem.innerHTML = `
                <div class="stars">★ ${review.note}/5</div>
                <h3>${review.titre}</h3>
                <p>${review.commentaire}</p>
                <div class="author">${review.auteur}, ${new Date(review.date_creation).toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' })}</div>
                <img src="${review.image_url}" alt="Review Image" onerror="this.src='https://via.placeholder.com/120?text=Image+Indisponible';">
            `;
            reviewList.insertBefore(reviewItem, reviewList.firstChild);
        }

        // Rating Stars Logic
        const stars = document.querySelectorAll('.rating-stars .star');
        const noteInput = document.getElementById('note');

        stars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const value = this.getAttribute('data-value');
                stars.forEach(s => {
                    s.classList.remove('selected');
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.add('selected');
                    }
                });
            });

            star.addEventListener('mouseout', function() {
                const selectedValue = noteInput.value;
                stars.forEach(s => {
                    s.classList.remove('selected');
                    if (s.getAttribute('data-value') <= selectedValue) {
                        s.classList.add('selected');
                    }
                });
            });

            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                noteInput.value = value;
                stars.forEach(s => {
                    s.classList.remove('selected');
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.add('selected');
                    }
                });
            });
        });

        function resetStars() {
            noteInput.value = 0;
            stars.forEach(s => s.classList.remove('selected'));
        }

        document.getElementById('review-form-data').addEventListener('submit', async (e) => {
            e.preventDefault();

            if (noteInput.value == 0) {
                const messageDiv = document.getElementById('form-message');
                messageDiv.className = 'message error-message';
                messageDiv.textContent = 'Veuillez sélectionner une note.';
                messageDiv.style.display = 'block';
                return;
            }

            const formData = new FormData(e.target);
            const messageDiv = document.getElementById('form-message');

            try {
                const response = await fetch('add_review.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    messageDiv.className = 'message success-message';
                    messageDiv.textContent = result.message;
                    messageDiv.style.display = 'block';

                    reviews.unshift(result.review);
                    addReviewToList(result.review);
                    currentIndex = 0;
                    updateReview();

                    document.getElementById('avg-rating').textContent = result.avg_rating;
                    document.getElementById('total-reviews').textContent = result.total_reviews;

                    e.target.reset();
                    resetStars();
                    document.getElementById('review-form').classList.remove('active');
                } else {
                    messageDiv.className = 'message error-message';
                    messageDiv.textContent = result.message;
                    messageDiv.style.display = 'block';
                }
            } catch (error) {
                messageDiv.className = 'message error-message';
                messageDiv.textContent = 'Une erreur est survenue. Veuillez réessayer.';
                messageDiv.style.display = 'block';
            }
        });

        if (reviews.length > 0) {
            updateReview();
        }
    </script>
</body>
</html>