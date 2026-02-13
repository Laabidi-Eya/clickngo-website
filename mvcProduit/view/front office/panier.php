<?php
session_start();


if (isset($_GET['success'])): ?>
    <p style="color: green;">Commande passée avec succès !</p>
<?php elseif (isset($_GET['error'])): ?>
    <p style="color: red;">Erreur : <?= htmlspecialchars($_GET['error']) ?></p>
<?php endif; ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>click'N'go - Validation d'achat</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;500&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .panier-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .panier-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #333;
        }

        .cart-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table th {
            text-align: left;
            padding: 15px;
            background: #ff8fa3;
            color: white;
            font-weight: 500;
        }

        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .cart-table tr:last-child td {
            border-bottom: none;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }

        .cart-total {
            text-align: right;
            margin-top: 20px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
        }

        .checkout-btn {
            display: block;
            width: 100%;
            max-width: 250px;
            padding: 12px 25px;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            margin: 20px auto;
            transition: all 0.3s ease;
        }

        .checkout-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 143, 163, 0.3);
        }

        .checkout-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #ccc;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            background: #fff;
            border: 1px solid #ddd;
            width: 30px;
            height: 30px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: #ff8fa3;
            color: white;
            border-color: #ff8fa3;
        }

        .remove-btn {
            color: #ff6b6b;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            padding: 5px;
            margin-left: 10px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .cart-table {
                display: block;
                overflow-x: auto;
            }
        }

        .promo-section {
            position: relative;
            margin-top: 20px;
        }

        .promo-tag {
            background: #ff8fa3;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .promo-tag i {
            font-size: 18px;
        }

        .promo-slide {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: none;
        }

        .promo-slide input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .promo-slide button {
            background: #ff8fa3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .promo-slide button:hover {
            background: #c084fc;
        }

        .promo-slide.active {
            display: block;
        }

        .best-sellers-section,
        .gestion-features,
        .reviews-section {
            display: none;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .cart-item-details {
            flex-grow: 1;
            padding: 0 20px;
        }

        .cart-item-price {
            font-weight: bold;
            color: #ff4d4d;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            background: #f0f0f0;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .quantity-btn:hover {
            background: #e0e0e0;
        }

        .remove-btn {
            color: #ff4d4d;
            background: none;
            border: none;
            cursor: pointer;
        }

        .remove-btn:hover {
            text-decoration: underline;
        }

        .cart-total {
            text-align: right;
            margin-top: 20px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
        }

        .gift-button-container {
            text-align: center;
            margin: 20px 0;
        }

        .gift-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            background: linear-gradient(135deg, #ff6b6b, #ff4d4d);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 77, 77, 0.2);
        }

        .gift-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 77, 77, 0.3);
        }

        .gift-btn i {
            font-size: 1.2rem;
        }

        .promo-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            height: 0;
            overflow: hidden;
            transition: all 0.5s ease;
            visibility: hidden;
        }

        .promo-container.active {
            height: auto;
            visibility: visible;
        }

        .promo-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            cursor: pointer;
        }

        .promo-title i {
            font-size: 32px;
            color: #FF69B4;
        }

        .promo-content {
            padding: 20px;
            text-align: center;
        }

        .promo-input {
            width: 100%;
            max-width: 300px;
            padding: 12px;
            border: 2px solid #FF69B4;
            border-radius: 8px;
            margin: 20px auto;
            font-size: 16px;
            text-align: center;
        }

        .promo-btn {
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .promo-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(216, 106, 216, 0.3);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="panier-header">
            <h1>Finalisez votre achat</h1>
            <span id="itemCount">0 articles</span>
        </div>

        <div class="cart-container">
            <table class="cart-table" id="panierTable">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Quantité</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="cart-total">
                Total à payer: <span id="totalPrix">0</span> TND
                <div id="economie" style="font-size: 0.9em; color: #333; margin-top: 5px;"></div>
            </div>

            <div class="promo-title" onclick="togglePromo()">
                <i class="fas fa-tag"></i>
                <h2>Avez-vous un code promo ?</h2>
                <i class="fas fa-chevron-down" style="font-size: 24px; color: #FF69B4; margin-left: 10px; transition: transform 0.3s ease;"></i>
            </div>

            <div class="promo-container" id="promoContainer">
                <div class="promo-content">
                    <input type="text" class="promo-input" placeholder="Entrez votre code promo" id="promoCode">
                    <button class="promo-btn" onclick="applyPromo()">Appliquer</button>
                </div>
            </div>

            <button class="checkout-btn" onclick="redirigerVersAcheter()">Valider l'achat</button>

        </div>
    </div>

    <script>
        const validerBtn = document.querySelector('.checkout-btn');
        let panier = JSON.parse(localStorage.getItem('panier')) || [];
        const tbody = document.querySelector('#panierTable tbody');
        const totalElement = document.getElementById('totalPrix');
        const itemCount = document.getElementById('itemCount');
        let reductionAppliquee = false;

        function calculerPrixAvecReduction(prix) {
            return reductionAppliquee ? prix * 0.8 : prix;
        }

        afficherPanier();

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

        async function modifierQuantite(index, changement) {
            const newQuantity = panier[index].quantite + changement;

            if (newQuantity < 1) {
                panier[index].quantite = 1;
                return;
            }

            const isAvailable = await checkStock(panier[index].id, newQuantity);

            if (!isAvailable) {
                alert('Stock insuffisant pour cette quantité !');
                return;
            }

            panier[index].quantite = newQuantity;
            localStorage.setItem('panier', JSON.stringify(panier));
            afficherPanier();
        }

        function afficherPanier() {
            tbody.innerHTML = '';
            let totalGlobalSansRemise = 0;
            let nombreTotalArticles = 0;

            if (panier.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center;">Votre panier est vide.</td></tr>`;
                validerBtn.disabled = true;
                validerBtn.style.opacity = 0.5;
                validerBtn.style.cursor = 'not-allowed';
                itemCount.textContent = '0 articles';
                totalElement.textContent = '0.00';
                return;
            }

            panier.forEach((p, index) => {
                const prixUnitaire = p.prix;
                const total = p.quantite * prixUnitaire;
                totalGlobalSansRemise += total;
                nombreTotalArticles += p.quantite;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><img src="${p.image}" alt="${p.nom}" class="product-image">${p.nom}</td>
                    <td>${prixUnitaire.toFixed(2)} TND</td>
                    <td>
                        <div class="quantity-control">
                            <button class="quantity-btn" onclick="modifierQuantite(${index}, -1)">-</button>
                            <span>${p.quantite}</span>
                            <button class="quantity-btn" onclick="modifierQuantite(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td>${total.toFixed(2)} TND</td>
                    <td><button class="remove-btn" onclick="supprimerProduit(${index})">×</button></td>
                `;
                tbody.appendChild(row);
            });

            let totalGlobal = totalGlobalSansRemise;
            if (reductionAppliquee) {
                totalGlobal = totalGlobalSansRemise * 0.8;
                totalElement.innerHTML = `
                    <span style="text-decoration: line-through; color: #999; margin-right: 10px;">${totalGlobalSansRemise.toFixed(2)}</span>
                    <span style="color: #ff4d4d;">${totalGlobal.toFixed(2)}</span>`;
                document.getElementById('economie').textContent = `Économie : ${(totalGlobalSansRemise - totalGlobal).toFixed(2)} TND`;
            } else {
                totalElement.textContent = totalGlobalSansRemise.toFixed(2);
                document.getElementById('economie').textContent = '';
            }

            itemCount.textContent = `${nombreTotalArticles} article${nombreTotalArticles > 1 ? 's' : ''}`;
            validerBtn.disabled = false;
            validerBtn.style.opacity = 1;
            validerBtn.style.cursor = 'pointer';
        }

        function supprimerProduit(index) {
            if (confirm("Voulez-vous vraiment supprimer ce produit de votre panier ?")) {
                panier.splice(index, 1);
                localStorage.setItem('panier', JSON.stringify(panier));
                afficherPanier();
            }
        }

        const isLoggedIn = <?php echo isset($_SESSION['user']['id_user']) ? 'true' : 'false'; ?>;

        function redirigerVersAcheter() {

            if (!isLoggedIn) {
                window.location.href = '/mvcUtilisateur/View/BackOffice/login/login.php'; // redirection vers login
            } else {

                if (panier.length === 0) {
                    alert("Votre panier est vide !");
                    return;
                }

                let totalQuantite = panier.reduce((sum, item) => sum + item.quantite, 0);
                const totalFinal = reductionAppliquee ?
                    parseFloat(totalElement.querySelector('span:last-child').textContent) :
                    parseFloat(totalElement.textContent);

                window.location.href = `acheter.php?quantite=${totalQuantite}&prix_total=${totalFinal.toFixed(2)}`;
            }
        }




        function togglePromo() {
            const container = document.getElementById('promoContainer');
            const arrow = document.querySelector('.promo-title .fa-chevron-down');
            container.classList.toggle('active');
            arrow.style.transform = container.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0)';

            if (container.classList.contains('active')) {
                const height = container.scrollHeight;
                container.style.height = height + 'px';
            } else {
                container.style.height = '0';
            }
        }

        function applyPromo() {
            const promoCode = document.getElementById('promoCode').value;

            if (promoCode.toLowerCase() === 'eya120') {
                reductionAppliquee = true;
                afficherPanier();

                document.getElementById('promoCode').disabled = true;
                document.querySelector('.promo-btn').disabled = true;
                document.querySelector('.promo-btn').style.opacity = '0.5';

                alert('Code promo appliqué avec succès ! Une réduction de 20% a été appliquée à votre commande.');
            } else {
                alert('Code promo invalide');
            }
        }
    </script>

</body>

</html>