<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Click'N'Go</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .faq-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .faq-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .faq-header h1 {
            color: #9768D1;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .faq-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .faq-section {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(151, 104, 209, 0.1);
            padding: 40px;
            margin-bottom: 40px;
        }

        .faq-section h2 {
            color: #9768D1;
            font-size: 1.8rem;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 10px;
        }

        .faq-section h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #9768D1 0%, #D48DD8 100%);
        }

        .accordion {
            margin-top: 20px;
        }

        .accordion-item {
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(151, 104, 209, 0.1);
        }

        .accordion-header {
            background: linear-gradient(135deg, rgba(245, 240, 255, 0.8), rgba(255, 250, 255, 0.9));
            padding: 15px 20px;
            cursor: pointer;
            position: relative;
            font-weight: 600;
            color: #333;
            transition: all 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .accordion-header:hover {
            background: linear-gradient(135deg, rgba(235, 230, 255, 0.9), rgba(245, 240, 255, 1));
        }

        .accordion-header i {
            color: #9768D1;
            font-size: 1rem;
            transition: transform 0.3s;
        }

        .accordion-content {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s, padding 0.3s;
            background-color: #fff;
        }

        .accordion-content-inner {
            padding: 20px;
            color: #555;
            line-height: 1.6;
        }

        .accordion-item.active .accordion-header {
            background: linear-gradient(135deg, #9768D1, #D48DD8);
            color: white;
        }

        .accordion-item.active .accordion-header i {
            color: white;
            transform: rotate(180deg);
        }

        .accordion-item.active .accordion-content {
            max-height: 500px;
            padding: 20px;
        }

        .contact-section {
            background: linear-gradient(135deg, #9768D1, #D48DD8);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-top: 50px;
        }

        .contact-section h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .contact-section p {
            font-size: 1.1rem;
            margin-bottom: 25px;
            color: rgba(255, 255, 255, 0.9);
        }

        .contact-section .btn {
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

        .contact-section .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .search-box {
            display: flex;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(151, 104, 209, 0.1);
            border-radius: 30px;
            overflow: hidden;
        }

        .search-box input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            font-size: 1rem;
            outline: none;
        }

        .search-box button {
            background: linear-gradient(135deg, #9768D1, #D48DD8);
            color: white;
            border: none;
            padding: 0 25px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-box button:hover {
            background: linear-gradient(135deg, #8A57C0, #C77BC7);
        }

        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }

        .category-tab {
            padding: 10px 20px;
            background: linear-gradient(135deg, rgba(245, 240, 255, 0.8), rgba(255, 250, 255, 0.9));
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            color: #333;
        }

        .category-tab:hover, .category-tab.active {
            background: linear-gradient(135deg, #9768D1, #D48DD8);
            color: white;
            transform: translateY(-2px);
        }

        .header-faq {
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/banner.jpg');
            background-size: cover;
            background-position: center;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .header-faq h1 {
            font-size: 3rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .category-tabs {
                justify-content: center;
            }
            
            .faq-section {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('images/logofaq.jpg'); background-size: cover; background-position: center; padding: 20px 0;">
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
        <h1 style="text-align: center; color: white; margin-top: 40px;">Besoin d'aide ?</h1>
    </header>

    <!-- Main Content -->
    <div class="faq-container">
        <div class="faq-header">
            <h1>Foire Aux Questions</h1>
            <p>Retrouvez les réponses aux questions les plus fréquemment posées sur Click'N'Go.</p>
        </div>

        <div class="search-box">
            <input type="text" id="faq-search" placeholder="Rechercher dans nos questions fréquentes...">
            <button><i class="fas fa-search"></i></button>
        </div>

        <div class="category-tabs">
            <div class="category-tab active" data-category="all">Toutes les catégories</div>
            <div class="category-tab" data-category="reservations">Réservations</div>
            <div class="category-tab" data-category="paiements">Paiements</div>
            <div class="category-tab" data-category="activites">Activités</div>
            <div class="category-tab" data-category="compte">Compte</div>
            <div class="category-tab" data-category="partenaires">Partenaires</div>
        </div>

        <div class="faq-section">
            <h2>Questions sur les réservations</h2>
            <div class="accordion" id="reservations">
                <div class="accordion-item">
                    <div class="accordion-header">
                        Comment annuler ou modifier ma réservation ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Pour annuler ou modifier votre réservation, connectez-vous à votre compte Click'N'Go et accédez à la section "Mes réservations". Sélectionnez la réservation concernée et cliquez sur "Annuler" ou "Modifier".</p>
                            <p>Attention, les conditions d'annulation varient selon les activités. Certaines peuvent être annulées gratuitement jusqu'à 24h avant, tandis que d'autres peuvent avoir des frais d'annulation. Les détails sont précisés sur la page de chaque activité avant la confirmation de votre réservation.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">
                        Puis-je réserver pour quelqu'un d'autre ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Oui, vous pouvez tout à fait réserver une activité pour une autre personne. Lors de la réservation, vous aurez la possibilité de saisir les informations des participants. Assurez-vous simplement que les informations fournies sont correctes, surtout pour les activités nécessitant des vérifications d'identité ou des conditions particulières (âge, état de santé, etc.).</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">
                        Comment obtenir ma confirmation de réservation ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Après avoir effectué votre réservation, vous recevrez automatiquement un email de confirmation à l'adresse fournie lors de l'inscription. Cet email contient toutes les informations nécessaires concernant votre activité : date, heure, lieu, code de réservation, etc.</p>
                            <p>Si vous n'avez pas reçu votre confirmation, vérifiez votre dossier de spam ou contactez notre service client au +216 71 123 456 ou par email à support@clickngo.tn.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-section">
            <h2>Questions sur les paiements</h2>
            <div class="accordion" id="paiements">
                <div class="accordion-item">
                    <div class="accordion-header">
                        Quels moyens de paiement sont acceptés ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Click'N'Go accepte plusieurs moyens de paiement :</p>
                            <ul>
                                <li>Cartes bancaires (Visa, Mastercard)</li>
                                <li>Cartes prépayées</li>
                                <li>PayPal</li>
                                <li>Virement bancaire (pour certaines activités)</li>
                            </ul>
                            <p>Tous les paiements sont sécurisés avec un système de cryptage SSL pour garantir la protection de vos données.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">
                        Est-ce que je dois payer la totalité lors de la réservation ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Cela dépend de l'activité choisie. Pour la plupart des activités, un paiement intégral est requis au moment de la réservation. Cependant, pour certaines activités plus onéreuses ou des événements spécifiques, un acompte peut être suffisant avec le solde à régler avant la date de l'activité.</p>
                            <p>Les conditions de paiement sont clairement indiquées sur la page de chaque activité avant de procéder à la réservation.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">
                        Comment obtenir une facture pour ma réservation ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Une facture électronique est automatiquement générée et envoyée par email après chaque réservation confirmée. Vous pouvez également retrouver toutes vos factures dans votre espace client, section "Mes factures".</p>
                            <p>Si vous avez besoin d'une facture avec des informations spécifiques (par exemple pour une entreprise), vous pouvez modifier vos informations de facturation dans votre profil ou contacter notre service client.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-section">
            <h2>Questions sur les activités</h2>
            <div class="accordion" id="activites">
                <div class="accordion-item">
                    <div class="accordion-header">
                        Y a-t-il des limites d'âge pour les activités ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Oui, certaines activités ont des restrictions d'âge pour des raisons de sécurité ou d'assurance. Ces informations sont clairement indiquées dans la description de chaque activité.</p>
                            <p>Nous proposons une large gamme d'activités adaptées à tous les âges, y compris des activités spécifiquement conçues pour les enfants, les adolescents, ou à faire en famille.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">
                        Que faire en cas de mauvais temps pour une activité en extérieur ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Pour les activités extérieures, nos partenaires ont généralement une politique en cas de mauvais temps. Selon l'activité, plusieurs options peuvent être proposées :</p>
                            <ul>
                                <li>Report de l'activité à une date ultérieure</li>
                                <li>Activité alternative en intérieur</li>
                                <li>Remboursement total ou partiel</li>
                            </ul>
                            <p>En cas de prévisions météorologiques défavorables, nous vous conseillons de contacter directement le prestataire ou notre service client la veille de votre activité.</p>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">
                        Les activités sont-elles accessibles aux personnes à mobilité réduite ?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <div class="accordion-content-inner">
                            <p>Nous nous efforçons de rendre nos activités accessibles au plus grand nombre. Sur chaque fiche d'activité, vous trouverez des informations sur l'accessibilité pour les personnes à mobilité réduite ou avec des besoins spécifiques.</p>
                            <p>Si vous avez des questions particulières concernant l'accessibilité d'une activité, n'hésitez pas à contacter notre service client qui vous guidera vers les activités les mieux adaptées à vos besoins.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact-section">
            <h3>Vous n'avez pas trouvé votre réponse ?</h3>
            <p>Notre équipe de support est disponible 7j/7 pour répondre à toutes vos questions.</p>
            <a href="mailto:support@clickngo.tn" class="btn">Contactez-nous</a>
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
            // Accordion functionality
            $('.accordion-header').click(function() {
                const parent = $(this).parent();
                const content = $(this).next('.accordion-content');
                
                if (parent.hasClass('active')) {
                    parent.removeClass('active');
                    content.css('max-height', '0');
                    content.css('padding', '0');
                } else {
                    $('.accordion-item').removeClass('active');
                    $('.accordion-content').css('max-height', '0');
                    $('.accordion-content').css('padding', '0');
                    
                    parent.addClass('active');
                    content.css('max-height', '500px');
                    content.css('padding', '20px');
                }
            });
            
            // Category tab filtering
            $('.category-tab').click(function() {
                const category = $(this).data('category');
                
                // Update active tab
                $('.category-tab').removeClass('active');
                $(this).addClass('active');
                
                if (category === 'all') {
                    $('.faq-section').show();
                } else {
                    $('.faq-section').hide();
                    $(`#${category}`).parents('.faq-section').show();
                }
            });
            
            // Search functionality
            $('#faq-search').on('input', function() {
                const searchValue = $(this).val().toLowerCase();
                
                if (searchValue.length > 2) {
                    $('.accordion-item').hide();
                    $('.accordion-item').each(function() {
                        const headerText = $(this).find('.accordion-header').text().toLowerCase();
                        const contentText = $(this).find('.accordion-content-inner').text().toLowerCase();
                        
                        if (headerText.includes(searchValue) || contentText.includes(searchValue)) {
                            $(this).show();
                            $(this).parents('.faq-section').show();
                        }
                    });
                } else if (searchValue.length === 0) {
                    $('.accordion-item').show();
                    // Restore category filtering if active
                    const activeCategory = $('.category-tab.active').data('category');
                    if (activeCategory !== 'all') {
                        $('.faq-section').hide();
                        $(`#${activeCategory}`).parents('.faq-section').show();
                    } else {
                        $('.faq-section').show();
                    }
                }
            });
        });
    </script>
</body>
</html> 