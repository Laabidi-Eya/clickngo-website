<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devenir Partenaire - Click'N'Go</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .partner-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .partner-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .partner-header h1 {
            color: #9768D1;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .partner-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .partner-section {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(151, 104, 209, 0.1);
            padding: 40px;
            margin-bottom: 40px;
        }

        .partner-section h2 {
            color: #9768D1;
            font-size: 1.8rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .partner-section h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
        }

        .partner-section p {
            color: #555;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .benefit-card {
            background: linear-gradient(135deg, rgba(245, 240, 255, 0.8), rgba(255, 250, 255, 0.9));
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(151, 104, 209, 0.2);
        }

        .benefit-card i {
            font-size: 40px;
            color: #9768D1;
            margin-bottom: 15px;
        }

        .benefit-card h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 10px;
        }

        .benefit-card p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0;
        }

        .steps-container {
            margin-top: 40px;
        }

        .step {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
            align-items: center;
        }

        .step:last-child {
            margin-bottom: 0;
        }

        .step-number {
            width: 70px;
            height: 70px;
            min-width: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(151, 104, 209, 0.3);
        }

        .step-content h3 {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 10px;
        }

        .step-content p {
            color: #555;
            margin-bottom: 0;
        }

        .contact-form {
            max-width: 600px;
            margin: 40px auto 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input, 
        .form-group textarea, 
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            color: #333;
            transition: border-color 0.3s;
        }

        .form-group input:focus, 
        .form-group textarea:focus, 
        .form-group select:focus {
            border-color: #9768D1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(151, 104, 209, 0.2);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .submit-btn {
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: block;
            width: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(151, 104, 209, 0.3);
        }

        .testimonials {
            margin-top: 50px;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .testimonial-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(151, 104, 209, 0.1);
            position: relative;
        }

        .testimonial-card:before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 60px;
            color: rgba(151, 104, 209, 0.1);
            font-family: serif;
            line-height: 1;
        }

        .testimonial-content {
            position: relative;
            z-index: 1;
            color: #555;
            font-style: italic;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .testimonial-author img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .testimonial-author-info h4 {
            margin: 0 0 5px;
            color: #333;
            font-size: 1rem;
        }

        .testimonial-author-info p {
            margin: 0;
            color: #9768D1;
            font-size: 0.9rem;
        }

        .cta-section {
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-top: 50px;
            box-shadow: 0 10px 30px rgba(151, 104, 209, 0.3);
        }

        .cta-section h2 {
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .cta-section p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            color: rgba(255, 255, 255, 0.9);
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-btn {
            background: white;
            color: #9768D1;
            border: none;
            border-radius: 30px;
            padding: 14px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-block;
            text-decoration: none;
        }

        .cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .step {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .step-number {
                margin: 0 auto;
            }
            
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            
            .partner-section {
                padding: 30px 20px;
            }
        }

        .header-partner {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/banner.jpg');
            background-size: cover;
            background-position: center;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .header-partner h1 {
            font-size: 3rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/logopar.jpg'); background-size: cover; background-position: center; padding: 20px 0;">
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
        <h1 style="text-align: center; color: white; margin-top: 40px;">Devenir Partenaire</h1>
    </header>

    <!-- Main Content -->
    <div class="partner-container">
        <div class="partner-header">
            <h1>Développez votre activité avec Click'N'Go</h1>
            <p>Rejoignez le premier réseau d'activités de loisirs en Tunisie et faites découvrir vos expériences à des milliers de nouveaux clients.</p>
        </div>

        <div class="partner-section">
            <h2>Pourquoi devenir partenaire ?</h2>
            <p>En rejoignant Click'N'Go, vous bénéficiez d'une visibilité accrue et d'un système de réservation simplifié qui vous permet de vous concentrer sur ce que vous faites de mieux : offrir des expériences exceptionnelles à vos clients.</p>
            
            <div class="benefits-grid">
                <div class="benefit-card">
                    <i class="fas fa-users"></i>
                    <h3>Plus de clients</h3>
                    <p>Accédez à notre communauté de plus de 50 000 utilisateurs actifs à la recherche d'expériences uniques.</p>
                </div>
                <div class="benefit-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Augmentez votre chiffre d'affaires</h3>
                    <p>Nos partenaires constatent en moyenne une augmentation de 30% de leur CA dès la première année.</p>
                </div>
                <div class="benefit-card">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Gestion simplifiée</h3>
                    <p>Notre plateforme s'occupe des réservations, paiements et rappels, vous permettant de vous concentrer sur votre activité.</p>
                </div>
                <div class="benefit-card">
                    <i class="fas fa-star"></i>
                    <h3>Valorisez votre offre</h3>
                    <p>Les avis clients et notre système de notation vous aident à gagner en crédibilité et à améliorer votre service.</p>
                </div>
            </div>
        </div>

        <div class="partner-section">
            <h2>Comment ça marche ?</h2>
            <p>Rejoindre notre réseau de partenaires est simple et rapide. Voici les étapes pour commencer :</p>
            
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Inscrivez-vous gratuitement</h3>
                        <p>Remplissez le formulaire de demande de partenariat ci-dessous avec les informations sur votre activité et votre entreprise.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Rendez-vous avec notre équipe</h3>
                        <p>Un chargé de partenariat vous contactera pour discuter de votre activité et vous expliquer notre fonctionnement.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Créez votre page</h3>
                        <p>Après validation, créez votre page d'activité avec photos, descriptions, horaires et tarifs depuis votre espace partenaire.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Commencez à recevoir des réservations</h3>
                        <p>Votre activité est maintenant visible sur notre plateforme ! Gérez vos réservations et développez votre activité.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="partner-section">
            <h2>Témoignages de nos partenaires</h2>
            <p>Découvrez comment Click'N'Go a transformé l'activité de nos partenaires :</p>
            
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "Depuis que nous avons rejoint Click'N'Go il y a 2 ans, nous avons vu notre nombre de clients augmenter de 45%. La plateforme est très intuitive et le support est toujours disponible pour nous aider."
                    </div>
                    <div class="testimonial-author">
                        <img src="images/leila.jpg" alt="Ahmed Ben Ali" onerror="this.src='images/default-avatar.jpg'">
                        <div class="testimonial-author-info">
                            <h4>Leila Ben Ali</h4>
                            <p>Directeur, Aventures Médina</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "La facilité de gestion des réservations m'a permis de me concentrer sur l'amélioration de mes services. Click'N'Go nous a également donné accès à une clientèle internationale que nous n'aurions pas pu atteindre autrement."
                    </div>
                    <div class="testimonial-author">
                        <img src="images/sarah.jpg" alt="Sophia Mrad" onerror="this.src='images/default-avatar.jpg'">
                        <div class="testimonial-author-info">
                            <h4>Sophia Mrad</h4>
                            <p>Fondatrice, Ateliers de Sidi Bou</p>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        "Les outils d'analyse fournis par Click'N'Go nous ont permis d'optimiser nos offres et d'augmenter notre taux de satisfaction client. Notre activité est maintenant complète presque tous les week-ends !"
                    </div>
                    <div class="testimonial-author">
                        <img src="images/mohamed.jpg" alt="Karim Jendoubi" onerror="this.src='images/default-avatar.jpg'">
                        <div class="testimonial-author-info">
                            <h4>Karim Jendoubi</h4>
                            <p>Gérant, Parachute Tunisie</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="partner-section">
            <h2>Demande de partenariat</h2>
            <p>Prêt à rejoindre notre réseau ? Remplissez le formulaire ci-dessous et notre équipe vous contactera dans les 48 heures.</p>
            
            <form class="contact-form">
                <div class="form-group">
                    <label for="nom">Nom complet *</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email professionnel *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone *</label>
                    <input type="tel" id="telephone" name="telephone" required>
                </div>
                
                <div class="form-group">
                    <label for="nom_entreprise">Nom de l'entreprise *</label>
                    <input type="text" id="nom_entreprise" name="nom_entreprise" required>
                </div>
                
                <div class="form-group">
                    <label for="type_activite">Type d'activité *</label>
                    <select id="type_activite" name="type_activite" required>
                        <option value="">Sélectionnez...</option>
                        <option value="Ateliers">Ateliers</option>
                        <option value="bien-etre">Bien-être</option>
                        <option value="Aérien">Aérien</option>
                        <option value="Aquatique">Aquatique</option>
                        <option value="Terestre">Terrestre</option>
                        <option value="Insolite">Insolite</option>
                        <option value="culture">Culture</option>
                        <option value="Détente">Détente</option>
                        <option value="sport">Sport</option>
                        <option value="nature">Nature</option>
                        <option value="aventure">Aventure</option>
                        <option value="Famille">Famille</option>
                        <option value="Extreme">Extrême</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ville">Ville *</label>
                    <input type="text" id="ville" name="ville" required>
                </div>
                
                <div class="form-group">
                    <label for="site_web">Site web (optionnel)</label>
                    <input type="url" id="site_web" name="site_web">
                </div>
                
                <div class="form-group">
                    <label for="message">Description de votre activité *</label>
                    <textarea id="message" name="message" required placeholder="Décrivez votre activité, vos services, vos capacités d'accueil..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="comment">Comment avez-vous connu Click'N'Go ?</label>
                    <select id="comment" name="comment">
                        <option value="">Sélectionnez...</option>
                        <option value="Recherche internet">Recherche internet</option>
                        <option value="Réseaux sociaux">Réseaux sociaux</option>
                        <option value="Bouche à oreille">Bouche à oreille</option>
                        <option value="Presse">Presse</option>
                        <option value="Un partenaire">Un partenaire</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                
                <button type="submit" class="submit-btn">Envoyer ma demande</button>
            </form>
        </div>

        <div class="cta-section">
            <h2>Prêt à faire passer votre activité à la vitesse supérieure ?</h2>
            <p>Rejoignez plus de 200 partenaires qui font confiance à Click'N'Go pour développer leur activité et toucher de nouveaux clients.</p>
            <a href="#" class="cta-btn">Discuter avec un expert</a>
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
</body>
</html> 