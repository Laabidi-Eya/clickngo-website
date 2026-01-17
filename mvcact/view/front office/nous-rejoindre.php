<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nous rejoindre - Click'N'Go</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Style spécifique à la page "Nous rejoindre" */
        .join-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .join-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .join-header h1 {
            color: #9768D1;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .join-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .join-section {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(151, 104, 209, 0.1);
            padding: 40px;
            margin-bottom: 40px;
        }

        .join-section h2 {
            color: #9768D1;
            font-size: 1.8rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .join-section h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
        }

        .join-section p {
            color: #555;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .job-card {
            background: linear-gradient(135deg, rgba(245, 240, 255, 0.8), rgba(255, 250, 255, 0.9));
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(151, 104, 209, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(151, 104, 209, 0.2);
        }

        .job-header {
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            padding: 20px;
            color: white;
        }

        .job-header h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }

        .job-header p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
        }

        .job-body {
            padding: 20px;
        }

        .job-body ul {
            margin-left: 20px;
            margin-bottom: 20px;
            color: #555;
        }

        .job-body li {
            margin-bottom: 8px;
        }

        .apply-btn {
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(151, 104, 209, 0.3);
        }

        .job-details {
            margin-bottom: 15px;
        }

        .job-details div {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
            color: #666;
            font-size: 0.9rem;
        }

        .job-details i {
            color: #9768D1;
            font-size: 1rem;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .value-card {
            text-align: center;
            padding: 25px 15px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(151, 104, 209, 0.1);
            transition: transform 0.3s;
        }

        .value-card:hover {
            transform: translateY(-5px);
        }

        .value-card i {
            font-size: 40px;
            color: #9768D1;
            margin-bottom: 15px;
        }

        .value-card h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 10px;
        }

        .value-card p {
            font-size: 0.9rem;
            color: #666;
        }

        .benefits-section {
            background: linear-gradient(135deg, rgba(245, 240, 255, 0.5), rgba(255, 250, 255, 0.6));
            border-radius: 15px;
            padding: 40px;
            margin-top: 30px;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .benefit-icon {
            width: 50px;
            height: 50px;
            min-width: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        .benefit-text h4 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 5px;
        }

        .benefit-text p {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
        }

        .application-steps {
            margin-top: 30px;
        }

        .step {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            position: relative;
        }

        .step:last-child {
            margin-bottom: 0;
        }

        .step:not(:last-child):after {
            content: '';
            position: absolute;
            top: 60px;
            left: 30px;
            width: 2px;
            height: calc(100% - 30px);
            background: linear-gradient(to bottom, #9768D1, #D48DD8);
        }

        .step-number {
            width: 60px;
            height: 60px;
            min-width: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .step-content h4 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 10px;
        }

        .step-content p {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 0;
        }

        .contact-us {
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-top: 50px;
        }

        .contact-us h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .contact-us p {
            font-size: 1.1rem;
            margin-bottom: 25px;
            color: rgba(255, 255, 255, 0.9);
        }

        .contact-us .btn {
            background: white;
            color: #9768D1;
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .contact-us .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .jobs-grid, .values-grid, .benefits-grid {
                grid-template-columns: 1fr;
            }

            .step {
                flex-direction: column;
            }

            .step:not(:last-child):after {
                display: none;
            }

            .step-number {
                margin: 0 auto 15px;
            }

            .step-content {
                text-align: center;
            }
        }

        /* Header style for this page */
        .header-join {
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

        .header-join h1 {
            font-size: 3rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/logojoin.jpg'); background-size: cover; background-position: center; padding: 20px 0;">
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
        <h1 style="text-align: center; color: white; margin-top: 40px;">Rejoignez notre équipe</h1>
    </header>

    <!-- Main Content -->
    <div class="join-container">
        <div class="join-header">
            <h1>Rejoignez l'aventure Click'N'Go</h1>
            <p>Envie de participer à la révolution du tourisme et des loisirs en Tunisie ? Nous recherchons des talents passionnés pour grandir ensemble !</p>
        </div>

        <div class="join-section">
            <h2>Pourquoi nous rejoindre ?</h2>
            <p>Chez Click'N'Go, nous croyons que le travail devrait être épanouissant et enrichissant. Nous cultivons un environnement où l'innovation, la créativité et l'esprit d'équipe sont au cœur de notre quotidien.</p>
            
            <div class="values-grid">
                <div class="value-card">
                    <i class="fas fa-rocket"></i>
                    <h3>Innovation</h3>
                    <p>Nous encourageons les idées nouvelles et les approches créatives pour résoudre les défis.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-users"></i>
                    <h3>Esprit d'équipe</h3>
                    <p>Nous croyons en la puissance de la collaboration et du travail d'équipe.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Croissance</h3>
                    <p>Nous offrons des opportunités de développement professionnel et personnel.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-balance-scale"></i>
                    <h3>Équilibre</h3>
                    <p>Nous valorisons l'équilibre entre vie professionnelle et vie personnelle.</p>
                </div>
            </div>
            
            <div class="benefits-section">
                <h3>Nos avantages</h3>
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <div class="benefit-text">
                            <h4>Travail flexible</h4>
                            <p>Possibilité de télétravail et horaires flexibles.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="benefit-text">
                            <h4>Formation continue</h4>
                            <p>Budget dédié pour votre développement professionnel.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="benefit-text">
                            <h4>Assurance santé</h4>
                            <p>Couverture médicale complète pour vous et votre famille.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-cocktail"></i>
                        </div>
                        <div class="benefit-text">
                            <h4>Événements d'équipe</h4>
                            <p>Activités régulières pour renforcer la cohésion d'équipe.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-plane"></i>
                        </div>
                        <div class="benefit-text">
                            <h4>Réductions sur les activités</h4>
                            <p>Accès privilégié à toutes nos offres d'activités.</p>
                        </div>
                    </div>
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <div class="benefit-text">
                            <h4>Impact social</h4>
                            <p>Contribuez au développement durable du tourisme local.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="join-section">
            <h2>Nos offres d'emploi</h2>
            <p>Découvrez nos opportunités actuelles et rejoignez une équipe dynamique et passionnée.</p>
            
            <div class="jobs-grid">
                <div class="job-card">
                    <div class="job-header">
                        <h3>Développeur Full Stack</h3>
                        <p>Temps plein - Tunis</p>
                    </div>
                    <div class="job-body">
                        <div class="job-details">
                            <div><i class="fas fa-clock"></i>Immédiat</div>
                            <div><i class="fas fa-map-marker-alt"></i>Tunis, avec possibilité de télétravail</div>
                            <div><i class="fas fa-briefcase"></i>3+ ans d'expérience</div>
                        </div>
                        <h4>Missions principales :</h4>
                        <ul>
                            <li>Développer et maintenir notre plateforme web et mobile</li>
                            <li>Concevoir des solutions techniques innovantes</li>
                            <li>Collaborer avec l'équipe produit et design</li>
                        </ul>
                        <a href="#" class="apply-btn">Postuler maintenant</a>
                    </div>
                </div>
                
                <div class="job-card">
                    <div class="job-header">
                        <h3>Responsable Marketing Digital</h3>
                        <p>Temps plein - Tunis</p>
                    </div>
                    <div class="job-body">
                        <div class="job-details">
                            <div><i class="fas fa-clock"></i>Immédiat</div>
                            <div><i class="fas fa-map-marker-alt"></i>Tunis</div>
                            <div><i class="fas fa-briefcase"></i>5+ ans d'expérience</div>
                        </div>
                        <h4>Missions principales :</h4>
                        <ul>
                            <li>Élaborer et mettre en œuvre la stratégie marketing digital</li>
                            <li>Gérer les campagnes publicitaires en ligne</li>
                            <li>Analyser les performances et optimiser les actions</li>
                        </ul>
                        <a href="#" class="apply-btn">Postuler maintenant</a>
                    </div>
                </div>
                
                <div class="job-card">
                    <div class="job-header">
                        <h3>Chargé de Relations Partenaires</h3>
                        <p>Temps plein - Tunis</p>
                    </div>
                    <div class="job-body">
                        <div class="job-details">
                            <div><i class="fas fa-clock"></i>Immédiat</div>
                            <div><i class="fas fa-map-marker-alt"></i>Tunis, avec déplacements</div>
                            <div><i class="fas fa-briefcase"></i>2+ ans d'expérience</div>
                        </div>
                        <h4>Missions principales :</h4>
                        <ul>
                            <li>Identifier et recruter de nouveaux prestataires d'activités</li>
                            <li>Entretenir les relations avec les partenaires existants</li>
                            <li>Former les partenaires à l'utilisation de notre plateforme</li>
                        </ul>
                        <a href="#" class="apply-btn">Postuler maintenant</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="join-section">
            <h2>Processus de recrutement</h2>
            <p>Notre processus de recrutement est conçu pour être transparent, équitable et vous permettre de découvrir Click'N'Go autant que nous découvrons votre potentiel.</p>
            
            <div class="application-steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Candidature en ligne</h4>
                        <p>Envoyez votre CV et une lettre de motivation expliquant pourquoi vous souhaitez rejoindre Click'N'Go et ce que vous pouvez apporter à notre équipe.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Premier entretien téléphonique</h4>
                        <p>Un appel de 30 minutes avec notre équipe RH pour discuter de votre parcours, vos motivations et répondre à vos premières questions.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Entretien technique ou test pratique</h4>
                        <p>Selon le poste, vous pourriez être amené à réaliser un test technique ou un cas pratique pour évaluer vos compétences dans des situations concrètes.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h4>Entretien avec l'équipe</h4>
                        <p>Rencontrez votre futur manager et des membres de l'équipe pour discuter plus en profondeur du poste et de la culture d'entreprise.</p>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">5</div>
                    <div class="step-content">
                        <h4>Décision finale et proposition</h4>
                        <p>Si votre profil correspond à nos attentes, nous vous ferons une proposition d'embauche détaillant le poste, la rémunération et les avantages.</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-us">
                <h3>Vous ne trouvez pas le poste qui vous correspond ?</h3>
                <p>Envoyez-nous une candidature spontanée ! Nous sommes toujours à la recherche de talents exceptionnels.</p>
                <a href="mailto:careers@clickngo.tn" class="btn">Envoyez votre candidature</a>
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
</body>
</html> 