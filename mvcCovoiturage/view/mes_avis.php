<?php
require_once '../config.php';
require_once '../Controller/AvisController.php';

try {
    $controller = new AvisController();
    $avis = $controller->getAllAvis();
} catch (Exception $e) {
    $avis = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Avis - Click'N'go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
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

        .author-input {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
            animation: slideUp 1s ease-out;
        }

        .author-input input {
            width: 300px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            font-size: 1rem;
            color: #333;
            outline: none;
            transition: all 0.3s ease;
        }

        .author-input input:focus {
            border-color: #ff8fa3;
            box-shadow: 0 0 10px rgba(255, 143, 163, 0.2);
        }

        .author-input button {
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .author-input button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 143, 163, 0.4);
        }

        .review-list {
            display: grid;
            grid-template-columns: 1fr;
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

        .review-item .actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .review-item .actions button {
            padding: 8px 20px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .review-item .actions .edit-btn {
            background: #ff8fa3;
            color: #fff;
        }

        .review-item .actions .edit-btn:hover {
            background: #e07b8a;
            transform: translateY(-2px);
        }

        .review-item .actions .delete-btn {
            background: #c084fc;
            color: #fff;
        }

        .review-item .actions .delete-btn:hover {
            background: #a56ee0;
            transform: translateY(-2px);
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

        .no-reviews {
            text-align: center;
            color: #666;
            font-size: 1.1rem;
            font-weight: 400;
            margin: 20px 0;
            animation: fadeIn 1s ease-out;
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
        .elsa-gradient-primary {
            background: linear-gradient(to right, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
        }

        .elsa-gradient-primary-hover:hover {
            background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
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
            <h1>Mes Avis</h1>
        </div>

        <!-- Author Input -->
        <div class="author-input">
            <input type="text" id="user-author" placeholder="Entrez votre nom">
            <button onclick="filterReviews()">Afficher mes avis</button>
        </div>

        <!-- Review List -->
        <div class="review-list" id="review-list">
            <p class="no-reviews">Veuillez entrer votre nom pour voir vos avis.</p>
        </div>

        <!-- Form to Edit a Review -->
        <div class="form-section" id="review-form">
            <h2>Modifier votre avis</h2>
            <div id="form-message" class="message" style="display: none;"></div>
            <form id="review-form-data" enctype="multipart/form-data">
                <input type="hidden" id="review-id" name="id_avis">
                <label for="titre">Titre</label>
                <input type="text" id="titre" name="titre" placeholder="Titre de votre avis" required>

                <label for="commentaire">Commentaire</label>
                <textarea id="commentaire" name="commentaire" placeholder="Votre commentaire" required></textarea>

                <label for="note">Note (1 à 5)</label>
                <input type="number" id="note" name="note" min="1" max="5" step="0.1" placeholder="Note sur 5" required>

                <label for="auteur">Votre nom</label>
                <input type="text" id="auteur" name="auteur" placeholder="Votre nom" required readonly>

                <label for="image">Nouvelle image (JPEG, PNG, GIF, max 5MB)</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif">

                <button type="submit">Modifier mon avis</button>
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
        let currentAuthor = '';

        function filterReviews() {
            currentAuthor = document.getElementById('user-author').value.trim();
            const reviewList = document.getElementById('review-list');

            if (!currentAuthor) {
                reviewList.innerHTML = '<p class="no-reviews">Veuillez entrer votre nom pour voir vos avis.</p>';
                return;
            }

            const matchingReviews = reviews.filter(review => review.auteur === currentAuthor);

            if (matchingReviews.length === 0) {
                reviewList.innerHTML = '<p class="no-reviews">Aucun avis trouvé pour cet auteur.</p>';
                return;
            }

            reviewList.innerHTML = '';
            matchingReviews.forEach(review => {
                const reviewItem = document.createElement('div');
                reviewItem.classList.add('review-item');
                reviewItem.setAttribute('data-id', review.id_avis);
                reviewItem.setAttribute('data-author', review.auteur);
                reviewItem.innerHTML = `
                    <div class="stars">★ ${review.note}/5</div>
                    <h3>${review.titre}</h3>
                    <p>${review.commentaire}</p>
                    <div class="author">${review.auteur}, ${new Date(review.date_creation).toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' })}</div>
                    <img src="${review.image_url}" alt="Review Image" onerror="this.src='https://via.placeholder.com/120?text=Image+Indisponible';">
                    <div class="actions">
                        <button class="edit-btn" onclick="editReview(${review.id_avis})">Modifier</button>
                        <button class="delete-btn" onclick="deleteReview(${review.id_avis})">Supprimer</button>
                    </div>
                `;
                reviewList.appendChild(reviewItem);
            });
        }

        function toggleForm(reviewId = null) {
            const form = document.getElementById('review-form');
            form.classList.toggle('active');

            if (reviewId) {
                const review = reviews.find(r => r.id_avis == reviewId);
                document.getElementById('review-id').value = review.id_avis;
                document.getElementById('titre').value = review.titre;
                document.getElementById('commentaire').value = review.commentaire;
                document.getElementById('note').value = review.note;
                document.getElementById('auteur').value = review.auteur;
                document.getElementById('image').value = ''; // File inputs can't be pre-filled
            }
        }

        function addReviewToList(review) {
            const reviewList = document.getElementById('review-list');
            const reviewItem = document.createElement('div');
            reviewItem.classList.add('review-item');
            reviewItem.setAttribute('data-id', review.id_avis);
            reviewItem.setAttribute('data-author', review.auteur);
            reviewItem.innerHTML = `
                <div class="stars">★ ${review.note}/5</div>
                <h3>${review.titre}</h3>
                <p>${review.commentaire}</p>
                <div class="author">${review.auteur}, ${new Date(review.date_creation).toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' })}</div>
                <img src="${review.image_url}" alt="Review Image" onerror="this.src='https://via.placeholder.com/120?text=Image+Indisponible';">
                <div class="actions">
                    <button class="edit-btn" onclick="editReview(${review.id_avis})">Modifier</button>
                    <button class="delete-btn" onclick="deleteReview(${review.id_avis})">Supprimer</button>
                </div>
            `;
            reviewList.insertBefore(reviewItem, reviewList.firstChild);
        }

        async function editReview(id) {
            const review = reviews.find(r => r.id_avis == id);
            if (currentAuthor !== review.auteur) {
                alert("Vous ne pouvez modifier que vos propres avis.");
                return;
            }
            toggleForm(id);
        }

        async function deleteReview(id) {
            const review = reviews.find(r => r.id_avis == id);
            if (currentAuthor !== review.auteur) {
                alert("Vous ne pouvez supprimer que vos propres avis.");
                return;
            }

            if (!confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')) return;

            const messageDiv = document.getElementById('form-message');
            try {
                const response = await fetch('delete_review.php', {
                    method: 'POST',
                    body: new URLSearchParams({ id_avis: id })
                });
                const result = await response.json();

                if (result.success) {
                    reviews = reviews.filter(r => r.id_avis != id);
                    document.querySelector(`.review-item[data-id="${id}"]`).remove();
                    if (reviews.filter(r => r.auteur === currentAuthor).length === 0) {
                        const reviewList = document.getElementById('review-list');
                        reviewList.innerHTML = '<p class="no-reviews">Aucun avis trouvé pour cet auteur.</p>';
                    }

                    messageDiv.className = 'message success-message';
                    messageDiv.textContent = result.message;
                    messageDiv.style.display = 'block';
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
        }

        // Handle form submission for editing
        document.getElementById('review-form-data').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const messageDiv = document.getElementById('form-message');

            try {
                const response = await fetch('update_review.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    messageDiv.className = 'message success-message';
                    messageDiv.textContent = result.message;
                    messageDiv.style.display = 'block';

                    // Update the existing review in the reviews array
                    const index = reviews.findIndex(r => r.id_avis == result.review.id_avis);
                    reviews[index] = result.review;
                    document.querySelector(`.review-item[data-id="${result.review.id_avis}"]`).remove();
                    addReviewToList(result.review);

                    // Hide the form
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
    </script>
</body>
</html>