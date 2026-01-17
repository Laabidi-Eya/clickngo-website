<?php
require_once '../../Controller/produitcontroller.php';
$controller = new ProductController();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>click'N'go - Wishlist</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        .page-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .page-title {
            font-size: 32px;
            color: #333;
            margin: 0;
            display: inline-block;
            position: relative;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
        }

        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        .favorite-item {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .favorite-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .favorite-image {
            width: 100%;
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .favorite-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .favorite-item:hover .favorite-image img {
            transform: scale(1.1);
        }

        .favorite-name {
            padding: 20px;
            text-align: center;
            font-size: 18px;
            color: #333;
            font-weight: 500;
            background: white;
            position: relative;
            z-index: 1;
        }

        .favorite-details {
            padding: 15px 20px 20px;
            text-align: center;
            background: white;
        }

        .favorite-price {
            font-size: 20px;
            font-weight: 600;
            color: #FF6F91;
            margin-bottom: 15px;
        }

        .add-to-cart-btn {
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(216, 106, 216, 0.3);
            display: inline-block;
            width: auto;
            text-decoration: none;
        }

        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(216, 106, 216, 0.4);
        }

        .no-favorites {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .no-favorites i {
            font-size: 64px;
            color: #FF6F91;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .no-favorites p {
            font-size: 20px;
            margin: 0;
            color: #666;
        }

        .back-button {
            position: fixed;
            top: 30px;
            left: 30px;
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            padding: 12px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1000;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(216, 106, 216, 0.4);
        }

        .favorite-count {
            position: fixed;
            top: 30px;
            right: 30px;
            background: white;
            color: #333;
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1000;
        }

        .favorite-count i {
            color: #FF6F91;
        }

        @media (max-width: 768px) {
            .favorites-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
                padding: 10px;
            }

            .page-title {
                font-size: 28px;
            }

            .back-button, .favorite-count {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <a href="produit.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Retour
    </a>

    <div class="favorite-count">
        <i class="fas fa-heart"></i>
        <span id="favorites-count">0</span> wishlist
    </div>

    <div class="page-container">
        <div class="page-header">
            <h1 class="page-title">Wishlist</h1>
        </div>

        <div id="favorites-grid" class="favorites-grid">
            <!-- Les produits favoris seront affichés ici dynamiquement -->
        </div>

        <div id="no-favorites" class="no-favorites" style="display: none;">
            <i class="fas fa-heart-broken"></i>
            <p>Vous n'avez pas encore de produits dans votre wishlist</p>
        </div>
    </div>

    <script>
        function updateCartCount() {
            let panier = JSON.parse(localStorage.getItem('panier')) || [];
            let totalItems = panier.reduce((sum, item) => sum + item.quantite, 0);
            // Si un élément avec l'ID cartCount existe, mettre à jour son contenu
            const cartCountElement = document.getElementById('cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = totalItems;
            }
        }

        async function checkStock(productId, quantity) {
            try {
                const response = await fetch(`../../Controller/produitcontroller.php?action=get_one&id=${productId}`);
                const data = await response.json();
                if (data.success && data.product.stock >= quantity) {
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Erreur lors de la vérification du stock:', error);
                return false;
            }
        }

        async function addToCart(productId, productName, productPrice, productImage) {
            const isAvailable = await checkStock(productId, 1);
            if (!isAvailable) {
                alert('Produit en rupture de stock !');
                return;
            }

            let panier = JSON.parse(localStorage.getItem('panier')) || [];
            const produitExistantIndex = panier.findIndex(p => p.id === productId);
            
            if (produitExistantIndex !== -1) {
                const newQuantity = panier[produitExistantIndex].quantite + 1;
                const canIncrement = await checkStock(productId, newQuantity);
                if (!canIncrement) {
                    alert('Stock insuffisant pour ajouter une unité supplémentaire !');
                    return;
                }
                panier[produitExistantIndex].quantite = newQuantity;
            } else {
                const produit = {
                    id: productId,
                    nom: productName,
                    prix: parseFloat(productPrice),
                    image: productImage,
                    quantite: 1
                };
                panier.push(produit);
            }
            
            localStorage.setItem('panier', JSON.stringify(panier));
            updateCartCount();
            alert(`${productName} a été ajouté au panier !`);
        }

        function loadFavorites() {
            const favorites = JSON.parse(localStorage.getItem('favorites')) || [];
            const favoritesGrid = document.getElementById('favorites-grid');
            const noFavorites = document.getElementById('no-favorites');
            const favoritesCount = document.getElementById('favorites-count');

            // Mettre à jour le compteur
            favoritesCount.textContent = favorites.length;

            if (favorites.length === 0) {
                favoritesGrid.style.display = 'none';
                noFavorites.style.display = 'block';
                return;
            }

            favoritesGrid.style.display = 'grid';
            noFavorites.style.display = 'none';
            favoritesGrid.innerHTML = '';

            // Charger les détails de chaque produit favori
            favorites.forEach(productId => {
                fetch(`../../Controller/produitcontroller.php?action=get_one&id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const product = data.product;
                            const imagePath = product.photo && product.photo !== 'images/products/logo.png' 
                                ? `../../${product.photo}` 
                                : 'images/products/logo.png';
                            
                            const productElement = document.createElement('div');
                            productElement.className = 'favorite-item';
                            productElement.innerHTML = `
                                <div class="favorite-image">
                                    <img src="${imagePath}" alt="${product.name}" onerror="this.src='images/products/logo.png'">
                                </div>
                                <div class="favorite-name">
                                    ${product.name}
                                </div>
                                <div class="favorite-details">
                                    <div class="favorite-price">
                                        ${product.price} TND
                                    </div>
                                    <button class="add-to-cart-btn" onclick="addToCart('${product.id}', '${product.name.replace(/'/g, "\\'")}', '${product.price}', '${imagePath}')">
                                        <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                    </button>
                                </div>
                            `;
                            favoritesGrid.appendChild(productElement);
                        }
                    })
                    .catch(error => console.error('Erreur:', error));
            });
        }

        // Charger les favoris au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadFavorites();
            updateCartCount(); // Mettre à jour le nombre d'articles dans le panier
        });
    </script>
</body>
</html> 