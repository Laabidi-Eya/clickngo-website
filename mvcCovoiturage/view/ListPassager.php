<?php
session_start();
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = null;
}

require_once '../config.php';
require_once '../Controller/DemandeCovoiturageController.php';

$errorMessages = [];
$successMessage = '';
$demandes = [];

$user_id = $_SESSION['user']['id_user'] ?? null;

if (!$user_id) {
    // Redirection ou message d'erreur si l'utilisateur n'est pas connecté
    header("Location: /login.php");
    exit();
}

// Gestion de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        $pdo = config::getConnexion(); // Get PDO instance
        $controller = new DemandeCovoiturageController($pdo); // Pass PDO instance
        $success = $controller->deleteDemande($_POST['delete_id']);
        
        if ($success) {
            $_SESSION['success_message'] = "Demande supprimée avec succès!";
            header("Location: ListPassager.php");
            exit();
        } else {
            $errorMessages[] = "Échec de la suppression de la demande";
        }
    } catch (Exception $e) {
        $errorMessages[] = $e->getMessage();
    }
}

try {
    $pdo = config::getConnexion(); // Get PDO instance
    $controller = new DemandeCovoiturageController($pdo); // Pass PDO instance
    $demandes = $controller->getDemandesByUserId($user_id); // Filter by user_id
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
}

if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes demandes de covoiturage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Projet Web/mvcCovoiturage/public/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }

        /* Navbar */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 50;
            padding: 1rem 2rem;
            background: transparent;
            transition: all 0.3s ease;
        }

        /* Add this new rule for when scrolling */
        nav.scrolled {
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.8);
        }

        .nav-links {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            gap: 2rem;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .nav-links a, .nav-links button {
            color: #333;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links a:hover, .nav-links button:hover {
            color: #be3cf0;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background: rgba(255, 255, 255, 0.9);
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            z-index: 1;
            border-radius: 8px;
            margin-top: 0.5rem;
        }

        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background 0.3s;
        }

        .dropdown-content a:hover {
            background: #f5f5f5;
        }

        /* Main Container */
        .form-background {
            min-height: calc(100vh - 200px);
        }

        .demande-container {
            padding: 6rem 2rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
        }

        .demande-wrapper {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            padding: 3rem;
            border: 1px solid rgba(190, 60, 240, 0.3);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(190, 60, 240, 0.1);
        }

        /* Background Animation */
        .demande-wrapper::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(190, 60, 240, 0.05) 0%, rgba(255, 80, 170, 0.05) 50%, transparent 70%);
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

        .demande-wrapper h2 {
            text-align: center;
            font-size: 2.25rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 2.5rem;
            position: relative;
            display: inline-block;
            left: 50%;
            transform: translateX(-50%);
        }

        .demande-wrapper h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(to right, #be3cf0, #ff50aa);
            animation: lineGrow 1.5s ease-out forwards;
        }

        @keyframes lineGrow {
            to {
                width: 100%;
            }
        }

        /* Demande Cards - Updated to show 2 per row */
        .demandes-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Exactly 2 per row */
            gap: 2rem;
        }

        .demande-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            position: relative;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #be3cf0;
            animation: cardEntrance 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
            overflow: hidden;
        }

        @keyframes cardEntrance {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .demande-card:nth-child(1) { animation-delay: 0.1s; }
        .demande-card:nth-child(2) { animation-delay: 0.2s; }
        .demande-card:nth-child(3) { animation-delay: 0.3s; }
        .demande-card:nth-child(4) { animation-delay: 0.4s; }
        .demande-card:nth-child(5) { animation-delay: 0.5s; }
        .demande-card:nth-child(6) { animation-delay: 0.6s; }
        .demande-card:nth-child(7) { animation-delay: 0.7s; }
        .demande-card:nth-child(8) { animation-delay: 0.8s; }

        .demande-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        /* Card Glow Effect on Hover */
        .demande-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(190, 60, 240, 0.3), rgba(255, 80, 170, 0.3));
            opacity: 0;
            transition: opacity 0.5s ease;
            pointer-events: none;
            z-index: -1;
        }

        .demande-card:hover::after {
            opacity: 0.05;
        }

        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(190, 60, 240, 0.2);
            padding-bottom: 1rem;
        }

        .demande-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            transition: transform 0.3s ease;
        }

        .demande-card:hover .demande-title {
            transform: scale(1.05);
            color: #be3cf0;
        }

        .demande-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .demande-detail {
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            color: #4B5563;
            transition: transform 0.3s ease;
        }

        .demande-card:hover .demande-detail {
            transform: translateX(5px);
        }

        .demande-detail i {
            margin-right: 0.75rem;
            color: #be3cf0;
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .demande-card:hover .demande-detail i {
            transform: scale(1.2);
        }

        .demande-footer {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(190, 60, 240, 0.2);
        }

        /* Updated button styles to have same size */
        .btn-edit, .btn-delete, .btn-rate {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            min-width: 140px;
            height: 40px;
            flex: 1;
        }

        .btn-edit, .btn-delete, .btn-rate {
            background: linear-gradient(to right, #A29BFE, #FFB1D3);
        }

        .btn-edit::before, .btn-delete::before, .btn-rate::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }

        .btn-edit:hover, .btn-delete:hover, .btn-rate:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(190, 60, 240, 0.3);
        }

        .btn-edit:hover::before, .btn-delete:hover::before, .btn-rate:hover::before {
            left: 100%;
        }

        .status-encours, .status-pending {
            color: #ff9800;
            font-weight: bold;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            display: inline-block;
            transition: all 0.3s ease;
            background: rgba(255, 152, 0, 0.1);
        }

        .status-acceptée, .status-accepted {
            color: #10b981;
            font-weight: bold;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            display: inline-block;
            transition: all 0.3s ease;
            background: rgba(16, 185, 129, 0.1);
        }

        .status-rejetée, .status-rejected {
            color: #ef4444;
            font-weight: bold;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            display: inline-block;
            transition: all 0.3s ease;
            background: rgba(239, 68, 68, 0.1);
        }

        .status-annulée {
            color: #9e9e9e;
            font-weight: bold;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            display: inline-block;
            transition: all 0.3s ease;
            background: rgba(158, 158, 158, 0.1);
        }

        .demande-card:hover .status-encours, .demande-card:hover .status-pending {
            background: rgba(255, 152, 0, 0.2);
        }

        .demande-card:hover .status-acceptée, .demande-card:hover .status-accepted {
            background: rgba(16, 185, 129, 0.2);
        }

        .demande-card:hover .status-rejetée, .demande-card:hover .status-rejected {
            background: rgba(239, 68, 68, 0.2);
        }

        .demande-card:hover .status-annulée {
            background: rgba(158, 158, 158, 0.2);
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
            color: #10b981;
        }

        .popup-error .popup-icon {
            color: #ef4444;
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
            background: linear-gradient(to right, #be3cf0, #ff50aa);
            color: white;
            padding: 0.6rem 2rem;
            border-radius: 2rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
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
            background: linear-gradient(to right, #059669, #10b981);
        }

        .popup-error .popup-button {
            background: linear-gradient(to right, #dc2626, #ef4444);
        }

        /* Delete confirmation popup buttons */
        .popup-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .btn-cancel {
            background: #6B7280;
        }

        .btn-confirm-delete {
            background: linear-gradient(to right, #dc2626, #ef4444);
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
            color: #be3cf0;
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

        /* Avis form */
        .avis-form {
            display: none;
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border-left: 4px solid #be3cf0;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            animation: slideDown 0.5s ease-out forwards;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .avis-form.active {
            display: block;
        }

        .avis-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .avis-form input, .avis-form textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #ddd;
            margin-bottom: 1rem;
            transition: border-color 0.3s ease;
        }

        .avis-form input:focus, .avis-form textarea:focus {
            outline: none;
            border-color: #be3cf0;
            box-shadow: 0 0 0 2px rgba(190, 60, 240, 0.2);
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 2rem;
            color: #ddd;
            transition: color 0.3s ease;
            margin: 0 0.2rem;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #ffd700;
        }

        .btn-submit {
            background: linear-gradient(to right, #be3cf0, #ff50aa);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: block;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(190, 60, 240, 0.3);
        }

        /* Hidden class */
        .hidden {
            display: none !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .demandes-list {
                grid-template-columns: 1fr; /* 1 per row on small screens */
            }
            
            .demande-details {
                grid-template-columns: 1fr 1fr;
            }
            
            .demande-footer {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn-edit, .btn-delete, .btn-rate {
                justify-content: center;
            }
        }  .logo {
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
                <a href="index.php" class="hover:text-[#be3cf0] text-lg"> 
                    Transport▾
                </a>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                  
    <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[220px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
        <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100 ">Trouver un covoiturage</a>

                </div>


            </div>


                     

                </div>
               <a href="/Projet%20Web/mvcSponsor/crud/view/front/index.php" class="hover:text-[#be3cf0]">Sponsors</a>
            <!-- LISTE DÉROULANTE SERVICES -->
           
    </div>
</nav>
<br>
<br>
<br>
<br><br><br><br><br>

    <!-- Demandes Section -->
    <div class="form-background">
        <div class="demande-container">
            <div class="demande-wrapper">
                <h2>✨ Mes demandes de covoiturage ✨</h2>

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

                <?php if (empty($demandes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-friends"></i>
                        <p>Aucune demande de covoiturage disponible pour le moment.</p>
                    </div>
                <?php else: ?>
                    <!-- Formulaire caché pour la suppression -->
                    <form id="delete-form" method="POST" style="display: none;">
                        <input type="hidden" name="delete_id" id="delete-id-input">
                    </form>

                    <div class="demandes-list">
                        <?php foreach ($demandes as $index => $demande): ?>
                            <div class="demande-card" data-index="<?php echo $index; ?>">
                                <div class="demande-header">
                                    <div class="demande-title">
                                        <?php echo htmlspecialchars($demande['prenom_passager'] . ' ' . $demande['nom_passager']); ?>
                                    </div>
                                    <div>
                                        <span class="status-<?php echo str_replace(' ', '', strtolower($demande['status_demande'])); ?>">
                                            <?php echo htmlspecialchars($demande['status_demande']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="demande-details">
                                    <div class="demande-detail">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo htmlspecialchars($demande['tel_passager']); ?></span>
                                    </div>
                                    
                                    <div class="demande-detail">
                                        <i class="fas fa-car"></i>
                                        <span>ID Conducteur: <?php echo htmlspecialchars($demande['id_conducteur']); ?></span>
                                    </div>
                                    
                                    <div class="demande-detail">
                                        <i class="fas fa-users"></i>
                                        <span>Places: <?php echo htmlspecialchars($demande['nbr_places_reservees']); ?></span>
                                    </div>
                                    
                                    <div class="demande-detail">
                                        <i class="fas fa-euro-sign"></i>
                                        <span>Prix: <?php echo htmlspecialchars($demande['prix_total']); ?> Dt</span>
                                    </div>
                                    
                                    <div class="demande-detail">
                                        <i class="fas fa-credit-card"></i>
                                        <span><?php echo htmlspecialchars($demande['moyen_paiement']); ?></span>
                                    </div>
                                    
                                    <div class="demande-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($demande['date_demande']))); ?></span>
                                    </div>
                                    
                                    <?php if (!empty($demande['message'])): ?>
                                        <div class="demande-detail">
                                            <i class="fas fa-comment"></i>
                                            <span><?php echo htmlspecialchars($demande['message']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="demande-footer">
                                    <a href="edit_demande.php?id=<?php echo $demande['id_passager']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <button onclick="confirmDelete(<?php echo $demande['id_passager']; ?>)" class="btn-delete">
                                        <i class="fas fa-trash-alt"></i> Supprimer
                                    </button>
                                    <?php if (in_array(strtolower($demande['status_demande']), ['acceptée', 'approuvée'])): ?>
                                        <button onclick="toggleAvisForm(<?php echo $demande['id_passager']; ?>, <?php echo $demande['id_conducteur']; ?>)" class="btn-rate">
                                            <i class="fas fa-star"></i> Noter
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div id="avis-form-<?php echo $demande['id_passager']; ?>" class="avis-form">
                                    <form id="avis-form-<?php echo $demande['id_passager']; ?>-form" action="../Controller/AvisController.php" method="POST">
                                        <input type="hidden" name="action" value="create">
                                        <input type="hidden" name="id_passager" value="<?php echo $demande['id_passager']; ?>">
                                        <input type="hidden" name="id_conducteur" value="<?php echo $demande['id_conducteur']; ?>">
                                        <input type="hidden" name="auteur" value="<?php echo htmlspecialchars($demande['prenom_passager'] . ' ' . $demande['nom_passager']); ?>">
                                        
                                        <label for="titre-<?php echo $demande['id_passager']; ?>">Titre de l'avis :</label>
                                        <input type="text" id="titre-<?php echo $demande['id_passager']; ?>" name="titre" required>
                                        
                                        <label>Note (1-5) :</label>
                                        <div class="star-rating">
                                            <input type="radio" id="star5-<?php echo $demande['id_passager']; ?>" name="note" value="5" required />
                                            <label for="star5-<?php echo $demande['id_passager']; ?>" title="5 étoiles">★</label>
                                            <input type="radio" id="star4-<?php echo $demande['id_passager']; ?>" name="note" value="4" />
                                            <label for="star4-<?php echo $demande['id_passager']; ?>" title="4 étoiles">★</label>
                                            <input type="radio" id="star3-<?php echo $demande['id_passager']; ?>" name="note" value="3" />
                                            <label for="star3-<?php echo $demande['id_passager']; ?>" title="3 étoiles">★</label>
                                            <input type="radio" id="star2-<?php echo $demande['id_passager']; ?>" name="note" value="2" />
                                            <label for="star2-<?php echo $demande['id_passager']; ?>" title="2 étoiles">★</label>
                                            <input type="radio" id="star1-<?php echo $demande['id_passager']; ?>" name="note" value="1" />
                                            <label for="star1-<?php echo $demande['id_passager']; ?>" title="1 étoile">★</label>
                                        </div>
                                        
                                        <label for="commentaire-<?php echo $demande['id_passager']; ?>">Commentaire :</label>
                                        <textarea id="commentaire-<?php echo $demande['id_passager']; ?>" name="commentaire" rows="4" required></textarea>
                                        
                                        <button type="submit" class="btn-submit">Soumettre l'avis</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Success Popup -->
    <div id="success-popup" class="popup-overlay popup-success <?php echo empty($successMessage) ? 'hidden' : ''; ?>">
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="popup-title">Succès!</h3>
            <div class="popup-message" id="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
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

    <!-- Delete Confirmation Popup -->
    <div id="delete-confirm-popup" class="popup-overlay hidden">
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i>
            </div>
            <h3 class="popup-title">Confirmer la suppression</h3>
            <div class="popup-message">Êtes-vous sûr de vouloir supprimer cette demande?</div>
            <div class="popup-buttons">
                <button onclick="hideDeleteConfirmPopup()" class="popup-button btn-cancel">Annuler</button>
                <button onclick="processDelete()" class="popup-button btn-confirm-delete">Supprimer</button>
            </div>
        </div>
    </div>

    <script>
        let demandeToDelete = null;
        
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
        
        function confirmDelete(id) {
            demandeToDelete = id;
            document.getElementById('delete-confirm-popup').classList.remove('hidden');
        }
        
        function hideDeleteConfirmPopup() {
            document.getElementById('delete-confirm-popup').classList.add('hidden');
            demandeToDelete = null;
        }
        
        function processDelete() {
            if (!demandeToDelete) return;
            
            // Add animation to the card being deleted
            const cards = document.querySelectorAll('.demande-card');
            cards.forEach(card => {
                const deleteBtn = card.querySelector('.btn-delete');
                if (deleteBtn && deleteBtn.getAttribute('onclick').includes(demandeToDelete)) {
                    card.style.animation = 'fadeOutDown 0.5s forwards';
                }
            });
            
            // Add keyframe animation dynamically
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes fadeOutDown {
                    from {
                        opacity: 1;
                        transform: translateY(0);
                    }
                    to {
                        opacity: 0;
                        transform: translateY(50px) scale(0.8);
                    }
                }
            `;
            document.head.appendChild(style);
            
            // Remplir le formulaire caché et le soumettre
            document.getElementById('delete-id-input').value = demandeToDelete;
            document.getElementById('delete-form').submit();
        }
        
        function toggleAvisForm(idPassager) {
            const form = document.getElementById(`avis-form-${idPassager}`);
            if (form.classList.contains('active')) {
                form.classList.remove('active');
            } else {
                // Close any other open forms first
                document.querySelectorAll('.avis-form.active').forEach(openForm => {
                    openForm.classList.remove('active');
                });
                form.classList.add('active');
            }
        }
        
      
        
        // Dropdown toggle functionality
        document.addEventListener('DOMContentLoaded', () => {
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
            
            // Apply staggered animation to cards
            const cards = document.querySelectorAll('.demande-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${0.1 * index}s`;
            });

            // Gestion de la soumission des avis
            document.querySelectorAll('[id^="avis-form-"] form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const noteSelected = form.querySelector('input[name="note"]:checked');
                    if (!noteSelected) {
                        showErrorPopup('Veuillez sélectionner une note entre 1 et 5');
                        return;
                    }

                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessPopup(data.message);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showErrorPopup(data.message || 'Erreur lors de la soumission');
                        }
                    })
                    .catch(error => showErrorPopup('Erreur réseau: ' + error.message));
                });
            });

            <?php if (!empty($errorMessages)): ?>
                showErrorPopup("<?php echo addslashes(implode('\n', $errorMessages)); ?>");
            <?php endif; ?>
            
            <?php if (!empty($successMessage)): ?>
                showSuccessPopup("<?php echo addslashes($successMessage); ?>");
            <?php endif; ?>
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
