<?php
require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

$errorMessages = [];
$annonces = [];

try {
    $pdo = config::getConnexion();
    $controller = new AnnonceCovoiturageController($pdo);
    $annonces = $controller->getAllAnnonces(true); // Filter active annonces
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des covoiturages disponibles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Projet Web/mvcCovoiturage/public/css/style.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(), url('') no-repeat center center fixed;
            background-size: cover;
            background-blend-mode: overlay;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }

        


       

        

      
        
        .annonce-wrapper {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            padding: 3rem;
            border: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }

        /* Background Animation */
        .annonce-wrapper::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(162, 155, 254, 0.1) 0%, rgba(255, 177, 211, 0.1) 50%, transparent 70%);
            animation: rotate 20s linear infinite;
            z-index: -1;
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        /* Annonce Cards - Modified for horizontal scrolling */
        .annonces-list {
            display: flex;
            overflow-x: auto;
            gap: 2rem;
            padding-bottom: 1.5rem;
            scroll-behavior: smooth;
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
            scroll-snap-type: x mandatory;
            position: relative;
        }

        .annonces-list::-webkit-scrollbar {
            display: none;  /* Chrome, Safari and Opera */
        }

        /* Card Animation */
        .annonce-card {
            min-width: 320px;
            flex-shrink: 0;
            scroll-snap-align: start;
            background: linear-gradient(135deg, #F5F5F5, #E0E0E0);
            border-radius: 1rem;
            padding: 1.5rem;
            position: relative;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            animation: cardEntrance 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes cardEntrance {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .annonce-card:nth-child(1) { animation-delay: 0.1s; }
        .annonce-card:nth-child(2) { animation-delay: 0.2s; }
        .annonce-card:nth-child(3) { animation-delay: 0.3s; }
        .annonce-card:nth-child(4) { animation-delay: 0.4s; }
        .annonce-card:nth-child(5) { animation-delay: 0.5s; }
        .annonce-card:nth-child(6) { animation-delay: 0.6s; }
        .annonce-card:nth-child(7) { animation-delay: 0.7s; }
        .annonce-card:nth-child(8) { animation-delay: 0.8s; }

        .annonce-card:hover {
            transform: perspective(1000px) rotateY(5deg) translateY(-15px) scale(1.03);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }

        /* Card Glow Effect on Hover */
        .annonce-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(162, 155, 254, 0.5), rgba(255, 177, 211, 0.5));
            opacity: 0;
            transition: opacity 0.5s ease;
            pointer-events: none;
            z-index: -1;
        }

        .annonce-card:hover::after {
            opacity: 0.2;
        }

        .annonce-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(160, 155, 254, 0.3);
            padding-bottom: 1rem;
        }

        .annonce-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            transition: transform 0.3s ease;
        }

        .annonce-card:hover .annonce-title {
            transform: scale(1.05);
        }

        .annonce-price {
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(to right, #FFE082, #FFB1D3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: transform 0.3s ease;
        }

        .annonce-card:hover .annonce-price {
            transform: scale(1.1);
        }

        .annonce-details {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .annonce-detail {
            flex: 1 1 45%;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            color: #4B5563;
            transition: transform 0.3s ease;
        }

        .annonce-card:hover .annonce-detail {
            transform: translateX(5px);
        }

        .annonce-detail i {
            margin-right: 0.75rem;
            color: #A29BFE;
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .annonce-card:hover .annonce-detail i {
            transform: scale(1.2);
        }

        .annonce-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(160, 155, 254, 0.3);
        }

        .annonce-places {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
        }

        .btn-reserver {
            background: linear-gradient(to right, #A29BFE, #FFB1D3);
            color: #1F2937;
            padding: 0.6rem 1.8rem;
            border-radius: 2rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-reserver::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: all 0.5s ease;
        }

        .btn-reserver:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-reserver:hover::before {
            left: 100%;
        }

        .btn-save {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.8rem;
            color: #FFE082;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 5;
        }

        .btn-save:hover {
            color: #FFB1D3;
            transform: rotate(20deg) scale(1.3);
            filter: drop-shadow(0 0 5px rgba(255, 177, 211, 0.5));
        }

        .btn-save.saved {
            color: #FFB1D3;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Carousel Controls - Enhanced */
        .carousel-controls {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .carousel-control {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            background: linear-gradient(to right, #A29BFE, #FFB1D3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .carousel-control::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .carousel-control:hover {
            transform: scale(1.15);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .carousel-control:hover::before {
            opacity: 1;
        }

        .carousel-control:active {
            transform: scale(0.95);
        }

        .carousel-control i {
            font-size: 1.4rem;
            transition: transform 0.3s ease;
        }

        .carousel-control:hover i {
            transform: scale(1.2);
        }

        /* Floating Navigation Arrows - New */
        .floating-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            left: 0;
            transform: translateY(-50%);
            pointer-events: none;
            z-index: 20;
            display: flex;
            justify-content: space-between;
            padding: 0 1rem;
        }

        .floating-nav-btn {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            pointer-events: auto;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateX(20px);
        }

        .floating-nav-btn.prev {
            transform: translateX(-20px);
        }

        .annonce-wrapper:hover .floating-nav-btn {
            opacity: 1;
            transform: translateX(0);
        }

        .floating-nav-btn:hover {
            background: white;
            transform: scale(1.1) !important;
        }

        .floating-nav-btn i {
            color: #333;
            font-size: 1.2rem;
        }

        /* Popup */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .popup-content {
            background: #F5F5F5;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            max-width: 450px;
            width: 90%;
            animation: bounceIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.5); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); }
        }

        .popup-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            opacity: 0;
            transform: scale(0.5);
        }

        @keyframes popIn {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .popup-success .popup-icon {
            color: #22C55E;
        }

        .popup-error .popup-icon {
            color: #EF4444;
        }

        .popup-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 0.5rem;
        }

        .popup-message {
            color: #4B5563;
            margin-bottom: 1.5rem;
        }

        .popup-button {
            background: linear-gradient(to right, #A29BFE, #FFB1D3);
            color: #1F2937;
            padding: 0.6rem 2rem;
            border-radius: 2rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .popup-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: all 0.5s ease;
        }

        .popup-button:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .popup-button:hover::before {
            left: 100%;
        }

        .popup-success .popup-button {
            background: #22C55E;
            color: #fff;
        }

        .popup-error .popup-button {
            background: #EF4444;
            color: #fff;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1rem;
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(40px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .empty-state i {
            font-size: 5rem;
            color: #A29BFE;
            margin-bottom: 1.5rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        .empty-state p {
            font-size: 1.5rem;
            color: #4B5563;
        }

        /* Pagination Dots - New */
        .pagination-dots {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .pagination-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            background: #D1D5DB;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .pagination-dot.active {
            width: 1.5rem;
            border-radius: 1rem;
            background: linear-gradient(to right, #A29BFE, #FFB1D3);
        }

        /* Hidden class */
        .hidden {
            display: none !important;
        }    /* Add this to your existing styles */
    nav {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        z-index: 1000;
        padding: 1rem 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }

    .nav-links {
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .nav-links a, .nav-links button {
        color: #333;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .nav-links a:hover, .nav-links button:hover {
        color: #A29BFE;
    }

    /* Adjust the dropdown positioning */
    .dropdown {
        position: relative;
    }

    .dropdown-content {
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        min-width: 180px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        border-radius: 0.5rem;
        padding: 0.5rem 0;
        display: none;
        z-index: 1001;
    }

    .dropdown-content a {
        display: block;
        padding: 0.5rem 1rem;
        color: #333;
        transition: background 0.2s ease;
    }

    .dropdown-content a:hover {
        background: #f5f5f5;
    }

    

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .nav-links {
            gap: 1rem;
        }
    }
    .logo {
    width: 120px;
    cursor: pointer;
    margin-right: 120px; /* Even more space between logo and Accueil */
    margin-left: -200px; /* Move logo much further to the left */
    position: relative;
    left: -100px; /* Additional left shift */
}
nav {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background-color: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    z-index: 1000;
    padding: 0.5rem 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* This is important to align items properly */
.flex.items-center.justify-center {
    justify-content: center !important; /* Force center alignment */
}

/* This ensures the logo and menu items are in a row */
.flex.space-x-8.text-lg.font-bold.text-black.relative.items-center {
    display: flex;
    align-items: center;
}

    </style>
</head>
<body>
   
<!-- NAVBAR -->
<nav class="absolute top-0 left-0 w-full z-50 p-4">
    <div class="flex items-center justify-center max-w-7xl mx-auto">
        <div class="flex space-x-8 text-lg  text-black relative">
            
                <div class="flex space-x-8 text-lg  text-black relative items-center">

                        <img src="images/logo.png" class="logo"> 

            <a href="/Projet Web/mvcUtilisateur/View/FrontOffice/index.php" class="hover:text-[#be3cf0]">Accueil</a>
            <a href="/Projet Web/mvcact/view/front office/activite.php" class="hover:text-[#be3cf0]">Activités</a>
            <a href="/Projet Web/mvcEvent/View/FrontOffice/evenemant.php" class="hover:text-[#be3cf0]">Événements</a>
            <a href="/Projet Web/mvcProduit/view/front office/produit.php" class="hover:text-[#be3cf0]">Produits</a>


            <div class="group relative">
                <a href="index.php" class="hover:text-[#be3cf0]  text-lg"> 
                    Transport▾
                </a>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                    <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100"></a>
                    <a href="ListPassager.php" class="block px-4 py-2 hover:bg-gray-100">Mes demandes</a>
                    <a href="ListConducteurs.php" class="block px-4 py-2 hover:bg-gray-100">Mes annonces</a>
                    <a href="SavedTrajets.php" class="block px-4 py-2 hover:bg-gray-100">Trajets sauvegardés</a>
                </div>


            </div>


                        <a href="/Projet%20Web/mvcSponsor/crud/view/front/index.php" class="hover:text-[#be3cf0]">Sponsors</a>

                </div>
            
            <!-- LISTE DÉROULANTE SERVICES -->
           
    </div>
</nav>
<br>
<br>
<br>
<br><br><br><br><br><br><br>

    <!-- Annonce Section -->
    <div class="annonce-container">
        <div class="annonce-wrapper">
            <h2 class="text-4xl font-bold text-center mb-10 text-[#1F2937]">🌟 Trouvez votre covoiturage parfait !</h2>

            <?php if (!empty($errorMessages)): ?>
                <div id="error-popup" class="popup-overlay popup-error">
                    <div class="popup-content">
                        <div class="popup-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <h3 class="popup-title">Erreur</h3>
                        <div class="popup-message">
                            <?php foreach ($errorMessages as $error): ?>
                                <p><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                        <button onclick="hideErrorPopup()" class="popup-button">Fermer</button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($annonces)): ?>
                <div class="empty-state">
                    <i class="fas fa-car-side"></i>
                    <p>Oops ! Aucun covoiturage disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <!-- Floating Navigation - New -->
                <div class="floating-nav">
                    <div class="floating-nav-btn prev" id="float-prev">
                        <i class="fas fa-chevron-left"></i>
                    </div>
                    <div class="floating-nav-btn next" id="float-next">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>

                <div class="annonces-list" id="carousel">
                    <?php foreach ($annonces as $index => $annonce): ?>
                        <div class="annonce-card" data-id="<?php echo htmlspecialchars($annonce->getIdConducteur()); ?>" data-index="<?php echo $index; ?>">
                            <i class="fas fa-bookmark btn-save" onclick="toggleSaveAnnonce(<?php echo htmlspecialchars($annonce->getIdConducteur()); ?>, this)" title="Sauvegarder"></i>
                            <div class="annonce-header">
                                <div class="annonce-title">
                                    <?php echo htmlspecialchars($annonce->getPrenomConducteur() . ' ' . $annonce->getNomConducteur()); ?>
                                </div>
                                <div class="annonce-price">
                                    <?php echo htmlspecialchars(number_format($annonce->getPrixEstime(), 2)) ?> TND
                                </div>
                            </div>
                            <div class="annonce-details">
                                <div class="annonce-detail">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($annonce->getLieuDepart()); ?></span>
                                </div>
                                <div class="annonce-detail">
                                    <i class="fas fa-map-marked-alt"></i>
                                    <span><?php echo htmlspecialchars($annonce->getLieuArrivee()); ?></span>
                                </div>
                                <div class="annonce-detail">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?php echo htmlspecialchars($annonce->getDateDepart()->format('d/m/Y H:i')); ?></span>
                                </div>
                                <div class="annonce-detail">
                                    <i class="fas fa-car"></i>
                                    <span><?php echo htmlspecialchars($annonce->getTypeVoiture()); ?></span>
                                </div>
                            </div>
                            <div class="annonce-footer">
                                <div class="annonce-places">
                                    Places: <?php echo htmlspecialchars($annonce->getNombrePlaces()); ?>
                                </div>
                                <a href="demande_form.php?id=<?php echo htmlspecialchars($annonce->getIdConducteur()); ?>" class="btn-reserver">Réserver</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination Dots - New -->
                <div class="pagination-dots" id="pagination">
                    <?php for ($i = 0; $i < min(count($annonces), 10); $i++): ?>
                        <div class="pagination-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></div>
                    <?php endfor; ?>
                </div>
                
                <!-- Carousel Controls -->
                <div class="carousel-controls">
                    <div class="carousel-control" id="scroll-left">
                        <i class="fas fa-chevron-left"></i>
                    </div>
                    <div class="carousel-control" id="scroll-right">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Success Popup -->
    <div id="success-popup" class="popup-overlay popup-success hidden">
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="popup-title">Succès !</h3>
            <div class="popup-message" id="success-message"></div>
            <button onclick="hideSuccessPopup()" class="popup-button">Fermer</button>
        </div>
    </div>

    <!-- Error Popup -->
    <div id="error-popup" class="popup-overlay popup-error hidden">
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h3 class="popup-title">Erreur</h3>
            <div class="popup-message" id="error-message"></div>
            <button onclick="hideErrorPopup()" class="popup-button">Fermer</button>
        </div>
    </div>

    <script>
        function showSuccessPopup(message) {
            document.getElementById('success-message').textContent = message;
            document.getElementById('success-popup').classList.remove('hidden');
        }
        
        function hideSuccessPopup() {
            document.getElementById('success-popup').classList.add('hidden');
        }
        
        function showErrorPopup(message) {
            document.getElementById('error-message').textContent = message;
            document.getElementById('error-popup').classList.remove('hidden');
        }
        
        function hideErrorPopup() {
            document.getElementById('error-popup').classList.add('hidden');
        }

        let savedAnnonces = JSON.parse(localStorage.getItem('savedAnnonces')) || [];

        function toggleSaveAnnonce(id, element) {
            // Add animation class
            element.classList.add('animate__animated');
            element.classList.add('animate__rubberBand');
            
            setTimeout(() => {
                element.classList.remove('animate__animated');
                element.classList.remove('animate__rubberBand');
            }, 1000);
            
            const annonceCard = element.closest('.annonce-card');
            const annonceData = {
                id: id,
                title: annonceCard.querySelector('.annonce-title').textContent,
                price: annonceCard.querySelector('.annonce-price').textContent,
                lieuDepart: annonceCard.querySelector('.annonce-details .annonce-detail:nth-child(1) span').textContent,
                lieuArrivee: annonceCard.querySelector('.annonce-details .annonce-detail:nth-child(2) span').textContent,
                dateDepart: annonceCard.querySelector('.annonce-details .annonce-detail:nth-child(3) span').textContent,
                typeVoiture: annonceCard.querySelector('.annonce-details .annonce-detail:nth-child(4) span').textContent,
                places: annonceCard.querySelector('.annonce-places').textContent.replace('Places: ', '')
            };

            const index = savedAnnonces.findIndex(annonce => annonce.id === id);
            if (index === -1) {
                savedAnnonces.push(annonceData);
                element.classList.add('saved');
                showSuccessPopup('Trajet sauvegardé avec succès!');
            } else {
                savedAnnonces.splice(index, 1);
                element.classList.remove('saved');
                showSuccessPopup('Trajet retiré des sauvegardes.');
            }

            localStorage.setItem('savedAnnonces', JSON.stringify(savedAnnonces));
        }
     

        // Enhanced Carousel functionality
        document.addEventListener('DOMContentLoaded', () => {
            const carousel = document.getElementById('carousel');
            const scrollLeftBtn = document.getElementById('scroll-left');
            const scrollRightBtn = document.getElementById('scroll-right');
            const floatPrevBtn = document.getElementById('float-prev');
            const floatNextBtn = document.getElementById('float-next');
            const paginationDots = document.querySelectorAll('.pagination-dot');
            
            let currentIndex = 0;
            let autoScrollInterval;
            let isHovering = false;
            
            // Function to update active pagination dot
            function updateActiveDot(index) {
                paginationDots.forEach(dot => dot.classList.remove('active'));
                if (paginationDots[index]) {
                    paginationDots[index].classList.add('active');
                }
            }
            
            // Function to scroll to a specific card
            function scrollToCard(index) {
                if (!carousel) return;
                
                const cards = carousel.querySelectorAll('.annonce-card');
                if (index >= 0 && index < cards.length) {
                    const card = cards[index];
                    const cardWidth = card.offsetWidth;
                    const gap = 32; // 2rem gap
                    
                    carousel.scrollTo({
                        left: (cardWidth + gap) * index,
                        behavior: 'smooth'
                    });
                    
                    currentIndex = index;
                    updateActiveDot(index);
                }
            }
            
            // Scroll left button
            if (scrollLeftBtn) {
                scrollLeftBtn.addEventListener('click', () => {
                    const newIndex = Math.max(0, currentIndex - 1);
                    scrollToCard(newIndex);
                });
            }
            
            // Scroll right button
            if (scrollRightBtn) {
                scrollRightBtn.addEventListener('click', () => {
                    const cards = carousel?.querySelectorAll('.annonce-card') || [];
                    const newIndex = Math.min(cards.length - 1, currentIndex + 1);
                    scrollToCard(newIndex);
                });
            }
            
            // Floating navigation buttons
            if (floatPrevBtn) {
                floatPrevBtn.addEventListener('click', () => {
                    const newIndex = Math.max(0, currentIndex - 1);
                    scrollToCard(newIndex);
                });
            }
            
            if (floatNextBtn) {
                floatNextBtn.addEventListener('click', () => {
                    const cards = carousel?.querySelectorAll('.annonce-card') || [];
                    const newIndex = Math.min(cards.length - 1, currentIndex + 1);
                    scrollToCard(newIndex);
                });
            }
            
            // Pagination dots click
            paginationDots.forEach(dot => {
                dot.addEventListener('click', () => {
                    const index = parseInt(dot.dataset.index);
                    scrollToCard(index);
                });
            });
            
            // Auto-scroll functionality
            function startAutoScroll() {
                if (autoScrollInterval) clearInterval(autoScrollInterval);
                
                autoScrollInterval = setInterval(() => {
                    if (isHovering) return;
                    
                    const cards = carousel?.querySelectorAll('.annonce-card') || [];
                    if (cards.length === 0) return;
                    
                    let newIndex = currentIndex + 1;
                    if (newIndex >= cards.length) {
                        newIndex = 0;
                    }
                    
                    scrollToCard(newIndex);
                }, 5000); // Auto-scroll every 5 seconds
            }
            
            // Start auto-scroll
            startAutoScroll();
            
            // Pause auto-scroll on hover
            if (carousel) {
                carousel.addEventListener('mouseenter', () => {
                    isHovering = true;
                });
                
                carousel.addEventListener('mouseleave', () => {
                    isHovering = false;
                });
            }
            
            // Update current index on scroll
            if (carousel) {
                carousel.addEventListener('scroll', () => {
                    if (!carousel) return;
                    
                    const cards = carousel.querySelectorAll('.annonce-card');
                    if (cards.length === 0) return;
                    
                    const cardWidth = cards[0].offsetWidth;
                    const gap = 32; // 2rem gap
                    const scrollPosition = carousel.scrollLeft;
                    
                    // Calculate the current index based on scroll position
                    const index = Math.round(scrollPosition / (cardWidth + gap));
                    if (index !== currentIndex) {
                        currentIndex = index;
                        updateActiveDot(index);
                    }
                });
            }
            
            // Touch swipe support for mobile
            let startX, startScrollLeft;
            
            if (carousel) {
                carousel.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].pageX;
                    startScrollLeft = carousel.scrollLeft;
                }, { passive: true });
                
                carousel.addEventListener('touchmove', (e) => {
                    if (!startX) return;
                    const x = e.touches[0].pageX;
                    const distance = startX - x;
                    carousel.scrollLeft = startScrollLeft + distance;
                }, { passive: true });
                
                carousel.addEventListener('touchend', () => {
                    startX = null;
                    
                    // Snap to the nearest card after touch end
                    const cards = carousel.querySelectorAll('.annonce-card');
                    if (cards.length === 0) return;
                    
                    const cardWidth = cards[0].offsetWidth;
                    const gap = 32; // 2rem gap
                    const scrollPosition = carousel.scrollLeft;
                    
                    // Calculate the nearest index
                    const index = Math.round(scrollPosition / (cardWidth + gap));
                    scrollToCard(index);
                }, { passive: true });
            }

            // Dropdown toggle functionality
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                const button = dropdown.querySelector('button');
                const content = dropdown.querySelector('.dropdown-content');

                button.addEventListener('click', () => {
                    content.style.display = content.style.display === 'block' ? 'none' : 'block';
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target)) {
                        content.style.display = 'none';
                    }
                });
            });

            <?php if (!empty($errorMessages)): ?>
                showErrorPopup("<?php echo addslashes(implode('\n', $errorMessages)); ?>");
            <?php endif; ?>

            document.querySelectorAll('.annonce-card').forEach(card => {
                const id = parseInt(card.dataset.id);
                if (savedAnnonces.some(annonce => annonce.id === id)) {
                    card.querySelector('.btn-save').classList.add('saved');
                }
            });
            
            // Apply staggered animation to cards
            const cards = document.querySelectorAll('.annonce-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${0.1 * index}s`;
            });
        });
    </script>
    
    
    <div class="footer-wrapper">
       
        <div class="newsletter">
            <div class="newsletter-left">
                <h3>Abonnez-vous à notre</h3>
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
                    <a href="#" style="--color: #0072b1" class="icon"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="#" style="--color: #E1306C" class="icon"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" style="--color: #FF0050" class="icon"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="#" style="--color: #4267B2" class="icon"><i class="fa-brands fa-facebook"></i></a>
                </div>
            </div>

            <div class="links">
                <p>Moyens de paiement</p>
                <div class="payment-methods">
                    <img src="images/visa.webp" alt="Visa">
                    <img src="images/mastercard-v2.webp" alt="Mastercard">
                    <img src="images/logo-cb.webp" alt="CB" class="cb-logo">
                    <img src="images/paypal.webp" alt="Paypal" class="paypal">
                </div>
            </div>

            <div class="links">
                <p>À propos</p>
                <a href="/Projet%20Web/mvcact/view/front%20office/about.php">À propos</a>

               
                <a href="/Projet%20Web/mvcact/view/front%20office/presse.php">Presse</a>

                <a href="/Projet%20Web/mvcact/view/front%20office/nous-rejoindre.php">Nous rejoindre</a>

            </div>

            <div class="links">
                <p>Liens utiles</p>
             <a href="/Projet Web/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]">Devenir partenaire</a>



                <a href="/Projet Web/mvcact/view/front office/faq.php" class="hover:text-[#be3cf0]">FAQ</a>
               <a href="/Projet Web/mvcact/view/front office/avis.php" class="hover:text-[#be3cf0]">Avis</a>
            </div>
        </div>

        <div class="footer-section">
            <hr>
            <div class="footer-separator"></div>
            <div class="footer-bottom">
                <p>© click'N'go 2025 - tous droits réservés</p>
                <div class="footer-links-bottom">
                   <a href="/Projet Web/mvcact/view/front office/conditions-generales.php" class="hover:text-[#be3cf0]">Conditions générales</a>

<a href="/Projet Web/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]">Devenir partenaire</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

