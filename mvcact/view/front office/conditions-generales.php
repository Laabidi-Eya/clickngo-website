<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions Générales - Click'N'Go</title>
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
    <header class="header" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/conditions.jpg'); background-size: cover; background-position: center; padding: 20px 0;">
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
        <h1 style="text-align: center; color: white; margin-top: 40px;">Conditions Générales</h1>
    </header>

    <!-- Main Content -->
    <div class="legal-container">
        <div class="legal-header">
            <h1>Conditions Générales d'Utilisation</h1>
            <p>Merci de lire attentivement ces conditions générales avant d'utiliser les services de Click'N'Go.</p>
        </div>

        <div class="legal-section">
            <div class="toc">
                <h3>Table des matières</h3>
                <ol>
                    <li><a href="#article1">Définitions</a></li>
                    <li><a href="#article2">Objet et champ d'application</a></li>
                    <li><a href="#article3">Inscription et compte utilisateur</a></li>
                    <li><a href="#article4">Réservation et paiement</a></li>
                    <li><a href="#article5">Annulation et remboursement</a></li>
                    <li><a href="#article6">Responsabilités et obligations</a></li>
                    <li><a href="#article7">Propriété intellectuelle</a></li>
                    <li><a href="#article8">Protection des données personnelles</a></li>
                    <li><a href="#article9">Réclamations et litiges</a></li>
                    <li><a href="#article10">Modifications des conditions générales</a></li>
                </ol>
            </div>

            <h2 id="article1">1. Définitions</h2>
            <p>Dans les présentes conditions générales, les termes suivants ont la signification indiquée ci-après :</p>
            <ul>
                <li><strong>« Click'N'Go »</strong> : désigne la société Click'N'Go SARL, immatriculée au Registre du Commerce et des Sociétés de Tunis sous le numéro XXX XXX XXX, dont le siège social est situé au [Adresse complète], exploitant le site internet accessible à l'adresse www.clickngo.tn.</li>
                <li><strong>« Site »</strong> : désigne le site internet de Click'N'Go accessible à l'adresse www.clickngo.tn, y compris ses sous-domaines, ainsi que l'application mobile associée.</li>
                <li><strong>« Utilisateur »</strong> : désigne toute personne qui accède au Site et/ou utilise les services proposés par Click'N'Go.</li>
                <li><strong>« Client »</strong> : désigne tout Utilisateur qui effectue une réservation sur le Site.</li>
                <li><strong>« Partenaire »</strong> : désigne tout professionnel proposant des activités par l'intermédiaire du Site.</li>
                <li><strong>« Activité »</strong> : désigne toute prestation (activité, événement, etc.) proposée par un Partenaire et réservable sur le Site.</li>
            </ul>

            <h2 id="article2">2. Objet et champ d'application</h2>
            <p>Les présentes conditions générales d'utilisation (CGU) ont pour objet de définir les modalités et conditions dans lesquelles Click'N'Go met à disposition des Utilisateurs ses services de mise en relation avec des Partenaires proposant diverses Activités.</p>
            <p>Elles s'appliquent, sans restriction ni réserve, à l'ensemble des services proposés par Click'N'Go sur son Site. En accédant au Site et en utilisant ses services, l'Utilisateur accepte expressément et sans réserve les présentes CGU.</p>
            <p>Click'N'Go se réserve le droit de modifier les présentes CGU à tout moment. Les CGU applicables sont celles en vigueur au moment de l'utilisation du Site ou de la réservation d'une Activité.</p>

            <h2 id="article3">3. Inscription et compte utilisateur</h2>
            <h3>3.1 Création de compte</h3>
            <p>Pour réserver une Activité, l'Utilisateur doit créer un compte personnel sur le Site. La création de ce compte nécessite la communication d'informations personnelles telles que nom, prénom, adresse email et numéro de téléphone.</p>
            <p>L'Utilisateur s'engage à fournir des informations exactes, complètes et à jour. Toute information erronée ou incomplète pourra entraîner l'annulation de la réservation ou la suppression du compte.</p>
            
            <h3>3.2 Sécurité du compte</h3>
            <p>L'Utilisateur est seul responsable de la préservation de la confidentialité de ses identifiants de connexion. Toute utilisation du compte avec les identifiants de l'Utilisateur sera réputée avoir été effectuée par celui-ci.</p>
            <p>En cas de suspicion d'utilisation frauduleuse de son compte, l'Utilisateur doit immédiatement en informer Click'N'Go.</p>

            <h2 id="article4">4. Réservation et paiement</h2>
            <h3>4.1 Processus de réservation</h3>
            <p>Le Site permet aux Utilisateurs de rechercher, comparer et réserver des Activités proposées par les Partenaires. La réservation n'est définitive qu'après confirmation par email et réception du paiement.</p>
            
            <h3>4.2 Prix</h3>
            <p>Les prix des Activités sont indiqués en dinars tunisiens (TND), toutes taxes comprises. Click'N'Go se réserve le droit de modifier les prix à tout moment, mais les Activités seront facturées sur la base des tarifs en vigueur au moment de la réservation.</p>
            
            <h3>4.3 Modalités de paiement</h3>
            <p>Le paiement s'effectue en ligne par carte bancaire ou par les autres moyens de paiement proposés sur le Site. La transaction est sécurisée selon les normes en vigueur.</p>
            <p>Une facture électronique est automatiquement générée et envoyée par email après chaque paiement.</p>

            <h2 id="article5">5. Annulation et remboursement</h2>
            <h3>5.1 Conditions d'annulation</h3>
            <p>Les conditions d'annulation varient selon les Activités et sont clairement indiquées sur la page de chaque Activité avant la confirmation de la réservation. D'une manière générale :</p>
            <ul>
                <li>Annulation plus de 48h avant le début de l'Activité : remboursement à 100%</li>
                <li>Annulation entre 24h et 48h avant le début de l'Activité : remboursement à 50%</li>
                <li>Annulation moins de 24h avant le début de l'Activité : aucun remboursement</li>
            </ul>
            <p>Certaines Activités peuvent proposer des conditions d'annulation spécifiques plus ou moins restrictives.</p>
            
            <h3>5.2 Procédure de remboursement</h3>
            <p>En cas d'annulation donnant droit à un remboursement, celui-ci sera effectué par le même moyen de paiement que celui utilisé lors de la réservation, dans un délai maximum de 14 jours calendaires à compter de la date d'annulation.</p>
            
            <h3>5.3 Annulation par le Partenaire</h3>
            <p>En cas d'annulation par le Partenaire, le Client sera remboursé intégralement et pourra, selon disponibilité, se voir proposer une Activité alternative.</p>

            <h2 id="article6">6. Responsabilités et obligations</h2>
            <h3>6.1 Rôle de Click'N'Go</h3>
            <p>Click'N'Go agit en tant qu'intermédiaire entre les Clients et les Partenaires. À ce titre, Click'N'Go n'est pas responsable de l'exécution des Activités, qui relève de la seule responsabilité des Partenaires.</p>
            
            <h3>6.2 Obligations des Utilisateurs</h3>
            <p>L'Utilisateur s'engage à :</p>
            <ul>
                <li>Respecter les présentes CGU et toutes les instructions spécifiques communiquées pour chaque Activité</li>
                <li>Fournir des informations exactes et complètes lors de la création de son compte et de la réservation d'Activités</li>
                <li>Se présenter à l'heure et au lieu indiqués pour l'Activité réservée</li>
                <li>Respecter les consignes de sécurité données par le Partenaire</li>
                <li>Adopter un comportement respectueux envers le Partenaire et les autres participants</li>
            </ul>
            
            <h3>6.3 Limitations de responsabilité</h3>
            <p>Click'N'Go ne pourra être tenue responsable :</p>
            <ul>
                <li>Des dommages directs ou indirects résultant de l'utilisation du Site ou de la participation à une Activité</li>
                <li>Des éventuels dysfonctionnements du Site ou des services de télécommunications</li>
                <li>De l'inexécution ou de la mauvaise exécution d'une Activité par un Partenaire</li>
                <li>Des conséquences liées à la communication d'informations erronées par l'Utilisateur</li>
            </ul>

            <h2 id="article7">7. Propriété intellectuelle</h2>
            <p>L'ensemble des éléments du Site (textes, images, logos, vidéos, etc.) sont protégés par le droit de la propriété intellectuelle et appartiennent à Click'N'Go ou font l'objet d'une autorisation d'utilisation.</p>
            <p>Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des éléments du Site, quel que soit le moyen ou le procédé utilisé, est interdite, sauf autorisation écrite préalable de Click'N'Go.</p>

            <h2 id="article8">8. Protection des données personnelles</h2>
            <p>Click'N'Go s'engage à protéger la vie privée des Utilisateurs et à respecter la réglementation en vigueur concernant la protection des données personnelles.</p>
            <p>Les informations collectées sont nécessaires à la gestion des réservations, à l'amélioration des services et, sous réserve de l'accord de l'Utilisateur, à l'envoi d'informations commerciales.</p>
            <p>Conformément à la réglementation, l'Utilisateur dispose d'un droit d'accès, de rectification, d'effacement et de portabilité de ses données personnelles, ainsi que d'un droit d'opposition et de limitation du traitement. Ces droits peuvent être exercés en contactant Click'N'Go à l'adresse email suivante : privacy@clickngo.tn.</p>
            <p>Pour plus d'informations, veuillez consulter notre <a href="mentions-legales.php">Politique de confidentialité</a>.</p>

            <h2 id="article9">9. Réclamations et litiges</h2>
            <h3>9.1 Service client</h3>
            <p>Pour toute question ou réclamation concernant une réservation ou l'utilisation du Site, l'Utilisateur peut contacter le service client de Click'N'Go :</p>
            <ul>
                <li>Par email : support@clickngo.tn</li>
                <li>Par téléphone : +216 71 123 456 (du lundi au vendredi, de 9h à 18h)</li>
            </ul>
            
            <h3>9.2 Médiation</h3>
            <p>En cas de litige non résolu avec le service client, l'Utilisateur peut recourir à un médiateur de la consommation, conformément aux dispositions légales en vigueur.</p>
            
            <h3>9.3 Droit applicable et juridiction compétente</h3>
            <p>Les présentes CGU sont soumises au droit tunisien. Tout litige relatif à l'interprétation ou à l'exécution des présentes CGU sera soumis aux tribunaux compétents de Tunis, sauf disposition légale contraire.</p>

            <h2 id="article10">10. Modifications des conditions générales</h2>
            <p>Click'N'Go se réserve le droit de modifier les présentes CGU à tout moment, notamment pour les adapter aux évolutions du Site ou à l'évolution de la législation.</p>
            <p>Les Utilisateurs seront informés de toute modification substantielle des CGU par affichage sur le Site et, pour les Utilisateurs disposant d'un compte, par email à l'adresse indiquée lors de l'inscription.</p>
            <p>La poursuite de l'utilisation du Site après notification des nouvelles CGU vaut acceptation de celles-ci.</p>

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