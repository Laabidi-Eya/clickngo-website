<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - Click'N'Go</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Style spécifique à la page "À propos" */
        .about-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .about-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .about-header h1 {
            color: #9768D1;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .about-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .about-section {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(151, 104, 209, 0.1);
            padding: 40px;
            margin-bottom: 40px;
        }

        .about-section h2 {
            color: #9768D1;
            font-size: 1.8rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .about-section h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
        }

        .about-section p {
            color: #555;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .about-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .about-card {
            background: linear-gradient(135deg, rgba(245, 240, 255, 0.8), rgba(255, 250, 255, 0.9));
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(151, 104, 209, 0.1);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .about-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(151, 104, 209, 0.2);
        }

        .about-card i {
            color: #9768D1;
            font-size: 40px;
            margin-bottom: 15px;
        }

        .about-card h3 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        .about-card p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .team-section {
            margin-top: 50px;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .team-member {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(151, 104, 209, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(151, 104, 209, 0.2);
        }

        .team-member img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .team-info {
            padding: 20px;
            text-align: center;
        }

        .team-info h3 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .team-info p {
            color: #9768D1;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .team-social {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .team-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #f4f4f4;
            color: #9768D1;
            font-size: 16px;
            transition: all 0.3s;
        }

        .team-social a:hover {
            background: #9768D1;
            color: white;
            transform: translateY(-3px);
        }

        .stats-section {
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            padding: 60px 20px;
            border-radius: 15px;
            margin: 60px 0;
            color: white;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .stats-item {
            padding: 20px;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stats-label {
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .about-grid, .team-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .about-section {
                padding: 30px 20px;
            }
        }

        /* Header style for this page */
        .header-about {
            background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('images/banner.jpg');
            background-size: cover;
            background-position: center;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .header-about h1 {
            font-size: 3rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/logoaboutus.jpg'); background-size: cover; background-position: center; padding: 20px 0;">
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
        <h1 style="text-align: center; color: white; margin-top: 40px;">À propos de Click'N'Go</h1>
    </header>

    <!-- Main Content -->
    <div class="about-container">
        <div class="about-header">
            <h1>Notre Histoire</h1>
            <p>Click'N'Go est né en 2020 avec l'ambition de révolutionner le secteur du tourisme et des loisirs en Tunisie. Notre mission : faciliter l'accès aux activités de loisirs pour tous.</p>
        </div>

        <div class="about-section">
            <h2>Qui sommes-nous ?</h2>
            <p>Click'N'Go est une plateforme tunisienne innovante qui connecte les voyageurs et les locaux avec les meilleures expériences et activités à travers tout le pays. Notre objectif est de simplifier la découverte et la réservation d'activités de loisirs, tout en soutenant les prestataires locaux et en valorisant le patrimoine culturel et naturel de la Tunisie.</p>
            <p>Notre équipe est composée de passionnés de voyage, de technologie et de service client, tous déterminés à offrir les meilleures expériences possibles à nos utilisateurs. Nous croyons que chaque moment de loisir doit être mémorable, accessible et authentique.</p>
            
            <div class="about-grid">
                <div class="about-card">
                    <i class="fas fa-compass"></i>
                    <h3>Notre Vision</h3>
                    <p>Devenir la référence incontournable pour la découverte et la réservation d'activités de loisirs en Tunisie et au-delà.</p>
                </div>
                <div class="about-card">
                    <i class="fas fa-star"></i>
                    <h3>Notre Mission</h3>
                    <p>Connecter les gens avec des expériences exceptionnelles qui enrichissent leur vie et soutiennent l'économie locale.</p>
                </div>
                <div class="about-card">
                    <i class="fas fa-heart"></i>
                    <h3>Nos Valeurs</h3>
                    <p>Excellence, authenticité, innovation, durabilité et passion pour le service client.</p>
                </div>
            </div>
        </div>

        <div class="about-section">
            <h2>Ce qui nous différencie</h2>
            <p>Click'N'Go se distingue par sa connaissance approfondie du marché tunisien et son engagement envers la qualité. Chaque activité proposée sur notre plateforme est soigneusement sélectionnée et évaluée pour garantir une expérience exceptionnelle.</p>
            <p>Nous collaborons étroitement avec nos partenaires locaux pour offrir des expériences authentiques qui mettent en valeur la richesse culturelle, historique et naturelle de la Tunisie. Notre plateforme intuitive et notre service client dévoué sont conçus pour rendre votre expérience de réservation aussi agréable que l'activité elle-même.</p>
            
            <div class="about-grid">
                <div class="about-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Sélection rigoureuse</h3>
                    <p>Toutes nos activités sont vérifiées et approuvées par notre équipe pour garantir qualité et sécurité.</p>
                </div>
                <div class="about-card">
                    <i class="fas fa-hand-holding-heart"></i>
                    <h3>Soutien local</h3>
                    <p>Nous valorisons les prestataires locaux et contribuons au développement du tourisme durable en Tunisie.</p>
                </div>
                <div class="about-card">
                    <i class="fas fa-headset"></i>
                    <h3>Service client</h3>
                    <p>Notre équipe dédiée est disponible 7j/7 pour vous accompagner avant, pendant et après votre activité.</p>
                </div>
                <div class="about-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Technologie</h3>
                    <p>Notre plateforme innovante vous permet de découvrir, comparer et réserver facilement en quelques clics.</p>
                </div>
            </div>
        </div>

        <div class="stats-section">
            <h2 style="color: white; margin-bottom: 20px;">Click'N'Go en chiffres</h2>
            <div class="stats-grid">
                <div class="stats-item">
                    <div class="stats-number">500+</div>
                    <div class="stats-label">Activités</div>
                </div>
                <div class="stats-item">
                    <div class="stats-number">50+</div>
                    <div class="stats-label">Villes</div>
                </div>
                <div class="stats-item">
                    <div class="stats-number">10k+</div>
                    <div class="stats-label">Réservations</div>
                </div>
                <div class="stats-item">
                    <div class="stats-number">95%</div>
                    <div class="stats-label">Satisfaction</div>
                </div>
            </div>
        </div>

        <div class="about-section team-section">
            <h2>Notre équipe</h2>
            <p>Click'N'Go est porté par une équipe passionnée et diversifiée, unie par l'amour du voyage et de l'innovation. Chaque membre apporte son expertise unique pour créer la meilleure expérience possible pour nos utilisateurs.</p>
            
            <div class="team-grid">
                <div class="team-member">
                    <img src="images/team1.jpg" alt="Sarah Benali" onerror="this.src='images/default-team.jpg'">
                    <div class="team-info">
                        <h3>Karim Mlayah</h3>
                        <p>Responsable Utilisateurs</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <img src="images/team2.jpg" alt="Mehdi Trabelsi" onerror="this.src='images/default-team.jpg'">
                    <div class="team-info">
                        <h3>Yessmine Rezgui</h3>
                        <p>Responsable d'Activités</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <img src="images/team3.jpg" alt="Lina Marzougui" onerror="this.src='images/default-team.jpg'">
                    <div class="team-info">
                        <h3>Firas Houass</h3>
                        <p>Responsable Evenements</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <img src="images/team1.jpg" alt="Sarah Benali" onerror="this.src='images/default-team.jpg'">
                    <div class="team-info">
                        <h3>Eya Laabidi</h3>
                        <p>Responsable Produits</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <img src="images/team2.jpg" alt="Mehdi Trabelsi" onerror="this.src='images/default-team.jpg'">
                    <div class="team-info">
                        <h3>Nourhen Dhaker</h3>
                        <p>Responsable Transports</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-member">
                    <img src="images/team4.jpg" alt="Karim Jendoubi" onerror="this.src='images/default-team.jpg'">
                    <div class="team-info">
                        <h3>Yassmine Chourou</h3>
                        <p>Responsable SPONSORING</p>
                        <div class="team-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
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