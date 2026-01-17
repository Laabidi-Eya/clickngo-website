<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentions Légales - Click'N'Go</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .legal-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .legal-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .legal-header h1 {
            color: #9768D1;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .legal-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .legal-section {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(151, 104, 209, 0.1);
            padding: 40px;
            margin-bottom: 40px;
        }

        .legal-section h2 {
            color: #9768D1;
            font-size: 1.8rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .legal-section h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
        }

        .legal-section h3 {
            color: #333;
            font-size: 1.4rem;
            margin: 30px 0 15px;
        }

        .legal-section p {
            color: #555;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .legal-section ul, .legal-section ol {
            margin: 20px 0;
            padding-left: 20px;
        }

        .legal-section li {
            color: #555;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .toc {
            background: linear-gradient(135deg, rgba(245, 240, 255, 0.8), rgba(255, 250, 255, 0.9));
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }

        .toc h3 {
            color: #333;
            font-size: 1.3rem;
            margin: 0 0 20px;
            text-align: center;
        }

        .toc ol {
            counter-reset: toc-counter;
            list-style-type: none;
            padding-left: 0;
        }

        .toc li {
            counter-increment: toc-counter;
            margin-bottom: 12px;
        }

        .toc li:before {
            content: counter(toc-counter) ". ";
            color: #9768D1;
            font-weight: bold;
        }

        .toc a {
            color: #555;
            text-decoration: none;
            transition: color 0.3s;
        }

        .toc a:hover {
            color: #9768D1;
        }

        .date-update {
            text-align: right;
            font-style: italic;
            color: #777;
            margin-top: 40px;
        }

        .header-legal {
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

        .header-legal h1 {
            font-size: 3rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .legal-section {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/mentions.jpg'); background-size: cover; background-position: center; padding: 20px 0;">
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
        <h1 style="text-align: center; color: white; margin-top: 40px;">Mentions Légales</h1>
    </header>

    <!-- Main Content -->
    <div class="legal-container">
        <div class="legal-header">
            <h1>Mentions Légales</h1>
            <p>Informations légales concernant Click'N'Go et l'utilisation du site.</p>
        </div>

        <div class="legal-section">
            <h2>1. Informations sur l'entreprise</h2>
            <p><strong>Raison sociale :</strong> Click'N'Go SARL</p>
            <p><strong>Forme juridique :</strong> Société à responsabilité limitée</p>
            <p><strong>Capital social :</strong> 50 000 TND</p>
            <p><strong>Numéro d'immatriculation :</strong> RC B 123456789</p>
            <p><strong>Siège social :</strong> 15 Avenue Habib Bourguiba, 1000 Tunis, Tunisie</p>
            <p><strong>Téléphone :</strong> +216 71 123 456</p>
            <p><strong>Email :</strong> contact@clickngo.tn</p>
        </div>

        <div class="legal-section">
            <h2>2. Direction de la publication</h2>
            <p><strong>Directeur de la publication :</strong> Mme Sonia Khediri</p>
            <p><strong>Responsable de la rédaction :</strong> M. Karim Bensalem</p>
        </div>

        <div class="legal-section">
            <h2>3. Hébergement du site</h2>
            <p><strong>Nom de l'hébergeur :</strong> Tunisie Hosting</p>
            <p><strong>Adresse :</strong> 7 Rue des Entrepreneurs, Zone Industrielle Charguia II, 2035 Tunis, Tunisie</p>
            <p><strong>Téléphone :</strong> +216 71 987 654</p>
        </div>

        <div class="legal-section">
            <h2>4. Protection des données personnelles</h2>
            <p>Conformément à la loi organique n° 2004-63 du 27 juillet 2004 portant sur la protection des données à caractère personnel, vous disposez d'un droit d'accès, de rectification et de suppression des données vous concernant.</p>
            
            <h3>4.1 Collecte des données</h3>
            <p>Les informations recueillies sur le site www.clickngo.tn font l'objet d'un traitement informatique destiné à :</p>
            <ul>
                <li>Gérer les réservations d'activités</li>
                <li>Améliorer notre service client</li>
                <li>Réaliser des statistiques commerciales</li>
                <li>Vous informer sur nos offres et nouveautés (sous réserve de votre accord)</li>
            </ul>
            
            <h3>4.2 Destinataires des données</h3>
            <p>Les données collectées sont destinées à Click'N'Go. Certaines informations nécessaires à la réalisation des activités peuvent être transmises aux partenaires concernés.</p>
            
            <h3>4.3 Durée de conservation</h3>
            <p>Les données sont conservées pour une durée de 3 ans à compter de la fin de la relation commerciale ou du dernier contact.</p>
            
            <h3>4.4 Exercice de vos droits</h3>
            <p>Pour exercer vos droits d'accès, de rectification ou de suppression, vous pouvez nous contacter par email à privacy@clickngo.tn ou par courrier à l'adresse du siège social indiquée ci-dessus.</p>
        </div>

        <div class="legal-section">
            <h2>5. Propriété intellectuelle</h2>
            <p>L'ensemble du contenu du site www.clickngo.tn (structure, textes, logos, images, vidéos, sons, etc.) est la propriété exclusive de Click'N'Go ou fait l'objet d'une autorisation d'utilisation.</p>
            <p>Toute reproduction, représentation, modification, publication, adaptation, totale ou partielle du site ou de son contenu, par quelque procédé que ce soit, sans l'autorisation préalable et écrite de Click'N'Go, est strictement interdite et constituerait une contrefaçon sanctionnée par les articles 50 et suivants de la loi n° 94-36 du 24 février 1994 relative à la propriété littéraire et artistique.</p>
        </div>

        <div class="legal-section">
            <h2>6. Cookies</h2>
            <p>Le site www.clickngo.tn utilise des cookies pour améliorer l'expérience utilisateur. Ces cookies permettent notamment :</p>
            <ul>
                <li>De mémoriser vos préférences de navigation</li>
                <li>D'établir des statistiques de fréquentation</li>
                <li>De vous proposer des contenus adaptés à vos centres d'intérêt</li>
            </ul>
            <p>Vous pouvez désactiver l'utilisation de cookies en modifiant les paramètres de votre navigateur. Veuillez consulter l'aide de votre navigateur pour plus d'informations.</p>
        </div>

        <div class="legal-section">
            <h2>7. Loi applicable et juridiction</h2>
            <p>Les présentes mentions légales sont soumises au droit tunisien. En cas de litige, les tribunaux de Tunis seront seuls compétents.</p>
            
            <div class="date-update">
                <p>Dernière mise à jour : 20 mai 2024</p>
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
            // Smooth scroll for table of contents links
            $('.toc a').click(function(e) {
                e.preventDefault();
                
                var target = $(this).attr('href');
                $('html, body').animate({
                    scrollTop: $(target).offset().top - 100
                }, 800);
            });
        });
    </script>
</body>
</html> 