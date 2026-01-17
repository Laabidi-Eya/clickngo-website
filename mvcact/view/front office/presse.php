<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presse - Click'N'Go</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Style spécifique à la page "Presse" */
        .press-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .press-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .press-header h1 {
            color: #9768D1;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .press-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .press-section {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(151, 104, 209, 0.1);
            padding: 40px;
            margin-bottom: 40px;
        }

        .press-section h2 {
            color: #9768D1;
            font-size: 1.8rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .press-section h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
        }

        .press-section p {
            color: #555;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        
        .press-releases {
            margin-top: 30px;
        }
        
        .press-release {
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        
        .press-release:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        
        .press-release h3 {
            color: #333;
            font-size: 1.4rem;
            margin-bottom: 10px;
        }
        
        .press-release .date {
            display: inline-block;
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .press-release p {
            margin-bottom: 15px;
        }
        
        .press-release .read-more {
            display: inline-block;
            color: #9768D1;
            font-weight: 600;
            text-decoration: none;
            margin-top: 5px;
            transition: all 0.3s;
        }
        
        .press-release .read-more:hover {
            color: #D48DD8;
            text-decoration: underline;
        }
        
        .press-kit {
            margin-top: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .kit-item {
            background: linear-gradient(135deg, rgba(245, 240, 255, 0.8), rgba(255, 250, 255, 0.9));
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .kit-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(151, 104, 209, 0.2);
        }
        
        .kit-item i {
            font-size: 35px;
            color: #9768D1;
            margin-bottom: 15px;
        }
        
        .kit-item h4 {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        .kit-item p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .kit-item .btn-download {
            background: linear-gradient(135deg, #9768D1 0%, #D48DD8 100%);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .kit-item .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(151, 104, 209, 0.3);
        }
        
        .media-coverage {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .media-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(151, 104, 209, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .media-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(151, 104, 209, 0.2);
        }
        
        .media-logo {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .media-logo img {
            max-height: 50px;
            max-width: 100%;
        }
        
        .media-content {
            padding: 20px;
        }
        
        .media-content h4 {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        .media-content .date {
            color: #9768D1;
            font-size: 0.85rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .media-content p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .media-content a {
            color: #9768D1;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .media-content a:hover {
            color: #D48DD8;
            text-decoration: underline;
        }
        
        .contact-info {
            background: linear-gradient(135deg, rgba(245, 240, 255, 0.8), rgba(255, 250, 255, 0.9));
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
        }
        
        .contact-details {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-top: 20px;
        }
        
        .contact-person {
            flex: 1;
            min-width: 250px;
        }
        
        .contact-person h4 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        
        .contact-item {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .contact-item i {
            color: #9768D1;
            font-size: 1.2rem;
        }
        
        .contact-item span {
            color: #555;
            font-size: 0.95rem;
        }
        
        @media (max-width: 768px) {
            .press-kit, .media-coverage {
                grid-template-columns: 1fr;
            }
            
            .press-section {
                padding: 30px 20px;
            }
            
            .contact-person {
                flex: 100%;
            }
        }
        
        /* Header style for this page */
        .header-press {
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/logopresse.jpg');
            background-size: cover;
            background-position: center;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        
        .header-press h1 {
            font-size: 3rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/logopresse.jpg'); background-size: cover; background-position: center; padding: 20px 0;">
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
        <h1 style="text-align: center; color: white; margin-top: 40px;">Presse</h1>
    </header>

    <!-- Main Content -->
    <div class="press-container">
        <div class="press-header">
            <h1>Espace Presse</h1>
            <p>Bienvenue dans notre espace dédié aux médias. Retrouvez ici toutes les actualités, communiqués de presse et ressources concernant Click'N'Go.</p>
        </div>

        <div class="press-section">
            <h2>Communiqués de Presse</h2>
            <p>Découvrez les dernières actualités de Click'N'Go à travers nos communiqués de presse officiels.</p>
            
            <div class="press-releases">
                <div class="press-release">
                    <h3>Click'N'Go enregistre une croissance de 150% en 2024</h3>
                    <span class="date">15 Mai 2024</span>
                    <p>La plateforme tunisienne Click'N'Go a annoncé aujourd'hui une croissance de 150% de son chiffre d'affaires au premier trimestre 2024 par rapport à la même période en 2023. Cette augmentation significative reflète l'engouement croissant des Tunisiens et des touristes pour les activités de loisirs locales.</p>
                    <p>Avec l'ajout de plus de 150 nouvelles activités et l'expansion vers 5 nouvelles régions du pays, Click'N'Go renforce sa position de leader dans le secteur des loisirs et du tourisme expérientiel en Tunisie.</p>
                    <a href="#" class="read-more">Lire la suite <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="press-release">
                    <h3>Click'N'Go lance son programme "Découverte de l'artisanat local"</h3>
                    <span class="date">3 Avril 2024</span>
                    <p>Click'N'Go est fier d'annoncer le lancement de son nouveau programme "Découverte de l'artisanat local", une initiative visant à promouvoir et préserver le patrimoine artisanal tunisien en connectant les voyageurs avec des artisans locaux à travers des expériences immersives.</p>
                    <p>Ce programme, développé en partenariat avec le Ministère du Tourisme et de l'Artisanat, permettra aux utilisateurs de Click'N'Go de participer à des ateliers authentiques et d'apprendre directement auprès de maîtres artisans dans les domaines de la poterie, du tissage, de la mosaïque et bien d'autres traditions artisanales.</p>
                    <a href="#" class="read-more">Lire la suite <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="press-release">
                    <h3>Click'N'Go et le Ministère du Tourisme s'associent pour promouvoir le tourisme expérientiel</h3>
                    <span class="date">12 Février 2024</span>
                    <p>Click'N'Go et le Ministère du Tourisme annoncent aujourd'hui la signature d'un partenariat stratégique visant à promouvoir le tourisme expérientiel en Tunisie. Cette collaboration permettra de développer et de mettre en valeur des expériences uniques et authentiques à travers le pays.</p>
                    <p>Le partenariat comprend la création d'une campagne de promotion internationale, le développement de nouvelles offres de tourisme durable, ainsi que des formations pour les prestataires locaux afin d'améliorer la qualité des services proposés.</p>
                    <a href="#" class="read-more">Lire la suite <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="press-section">
            <h2>Kit de Presse</h2>
            <p>Téléchargez nos ressources médias officielles pour illustrer vos articles sur Click'N'Go.</p>
            
            <div class="press-kit">
                <div class="kit-item">
                    <i class="fas fa-images"></i>
                    <h4>Logos officiels</h4>
                    <p>Formats PNG et SVG haute résolution</p>
                    <a href="#" class="btn-download">Télécharger</a>
                </div>
                
                <div class="kit-item">
                    <i class="fas fa-camera"></i>
                    <h4>Photos produit</h4>
                    <p>Interface utilisateur et captures d'écran</p>
                    <a href="#" class="btn-download">Télécharger</a>
                </div>
                
                <div class="kit-item">
                    <i class="fas fa-file-pdf"></i>
                    <h4>Dossier de presse</h4>
                    <p>Présentation complète de Click'N'Go</p>
                    <a href="#" class="btn-download">Télécharger</a>
                </div>
                
                <div class="kit-item">
                    <i class="fas fa-chart-pie"></i>
                    <h4>Statistiques</h4>
                    <p>Chiffres clés et infographies</p>
                    <a href="#" class="btn-download">Télécharger</a>
                </div>
            </div>
        </div>

        <div class="press-section">
            <h2>Couverture Médiatique</h2>
            <p>Découvrez ce que la presse dit de Click'N'Go.</p>
            
            <div class="media-coverage">
                <div class="media-item">
                    <div class="media-logo">
                        <img src="images/Presse_Tunisie.jpg" alt="Logo média" onerror="this.src='images/default-media.png'">
                    </div>
                    <div class="media-content">
                        <h4>Le tourisme tunisien se réinvente grâce aux startups locales</h4>
                        <span class="date">15 Avril 2024</span>
                        <p>Le Temps met en lumière comment des startups comme Click'N'Go révolutionnent l'offre touristique en Tunisie en proposant des expériences authentiques et personnalisées.</p>
                        <a href="#" target="_blank">Lire l'article <i class="fas fa-external-link-alt"></i></a>
                    </div>
                </div>
                
                <div class="media-item">
                    <div class="media-logo">
                        <img src="images/presse2.GIF" alt="Logo média" onerror="this.src='images/default-media.png'">
                    </div>
                    <div class="media-content">
                        <h4>Click'N'Go : la success story tunisienne qui inspire</h4>
                        <span class="date">28 Mars 2024</span>
                        <p>La Presse explore le succès de Click'N'Go et son impact sur l'économie locale, notamment dans les régions moins touristiques de la Tunisie.</p>
                        <a href="#" target="_blank">Lire l'article <i class="fas fa-external-link-alt"></i></a>
                    </div>
                </div>
                
                <div class="media-item">
                    <div class="media-logo">
                        <img src="images/le-journal.jpg" alt="Logo média" onerror="this.src='images/default-media.png'">
                    </div>
                    <div class="media-content">
                        <h4>Top 10 des plateformes de tourisme innovantes en Afrique du Nord</h4>
                        <span class="date">10 Février 2024</span>
                        <p>Jeune Afrique classe Click'N'Go parmi les 10 plateformes les plus innovantes dans le secteur du tourisme en Afrique du Nord, soulignant son approche centrée sur l'expérience utilisateur.</p>
                        <a href="#" target="_blank">Lire l'article <i class="fas fa-external-link-alt"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="press-section">
            <h2>Contact Presse</h2>
            <p>Pour toute demande d'interview, d'information ou de partenariat média, n'hésitez pas à contacter notre équipe de relations presse.</p>
            
            <div class="contact-info">
                <div class="contact-details">
                    <div class="contact-person">
                        <h4>Relations Presse</h4>
                        <div class="contact-item">
                            <i class="fas fa-user"></i>
                            <span>Sonia Khediri</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>presse@clickngo.tn</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+216 71 123 456</span>
                        </div>
                    </div>
                    
                    <div class="contact-person">
                        <h4>Relations Médias Internationaux</h4>
                        <div class="contact-item">
                            <i class="fas fa-user"></i>
                            <span>Karim Bensalem</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>international@clickngo.tn</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+216 71 456 789</span>
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