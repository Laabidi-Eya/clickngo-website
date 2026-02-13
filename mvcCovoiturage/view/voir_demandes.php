<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Controller/DemandeCovoiturageController.php';

$annonceId = $_GET['id'] ?? 0;
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

try {
    $pdo = config::getConnexion(); // Get PDO instance
    $controller = new DemandeCovoiturageController($pdo); // Pass PDO instance
    $demandes = $controller->getDemandesByAnnonceId($annonceId);
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes pour l'annonce</title>
    <link rel="stylesheet" href="/mvcCovoiturage/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
           
            min-height: 100vh;
            margin: 0;
            color: #333;
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
        #demandes-list {
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
            justify-content: center;
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
            text-align: center;
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

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(190, 60, 240, 0.2);
        }

        .btn-sms, .btn-action {
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
        }

        .btn-sms {
            background: linear-gradient(to right, #be3cf0, #dc46d7);
        }

        .btn-action:nth-child(1) {
            background: linear-gradient(to right, #10b981, #059669);
        }

        .btn-action:nth-child(2) {
            background: linear-gradient(to right, #ef4444, #dc2626);
        }

        .btn-sms::before, .btn-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }

        .btn-sms:hover, .btn-action:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(190, 60, 240, 0.3);
        }

        .btn-sms:hover::before, .btn-action:hover::before {
            left: 100%;
        }

        .btn-sms:disabled, .btn-action:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .status-approved {
            color: #10b981;
            font-weight: bold;
            background: rgba(16, 185, 129, 0.1);
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            transition: background 0.3s ease;
        }

        .status-pending {
            color: #f59e0b;
            font-weight: bold;
            background: rgba(245, 158, 11, 0.1);
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            transition: background 0.3s ease;
        }

        .demande-card:hover .status-approved {
            background: rgba(16, 185, 129, 0.2);
        }

        .demande-card:hover .status-pending {
            background: rgba(245, 158, 11, 0.2);
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 5px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            max-width: 500px;
            width: 90%;
            animation: bounceIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.5); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); }
        }

        .modal-content h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #be3cf0;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            font-size: 1rem;
            border: 2px solid #d9e4ff;
            border-radius: 0.5rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: #be3cf0;
            outline: none;
            box-shadow: 0 0 0 3px rgba(190, 60, 240, 0.1);
        }

        .g-recaptcha {
            margin: 1.5rem auto;
            display: flex;
            justify-content: center;
        }

        .btn-verify, .btn-cancel {
            padding: 0.8rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: none;
            cursor: pointer;
            min-width: 120px;
            color: white;
        }

        .btn-verify {
            background: linear-gradient(to right, #A29BFE, #FFB1D3);
        }

        .btn-cancel {
            background: #6B7280;
        }

        .btn-verify:hover, .btn-cancel:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Message styles */
        .message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(20px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .message-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-left: 4px solid #10b981;
        }

        .message-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-left: 4px solid #ef4444;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #demandes-list {
                grid-template-columns: 1fr;
            }
            
            .demande-details {
                grid-template-columns: 1fr 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn-sms, .btn-action {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    
<!-- NAVBAR -->
<nav class="absolute top-0 left-0 w-full z-50 p-4">
    <div class="flex items-center justify-center max-w-7xl mx-auto">
        <div class="flex space-x-8 text-lg  text-black relative">
            
                <div class="flex space-x-8 text-lg text-black relative items-center">

                        <img src="images/logo.png" class="logo"> 

            <a href="/mvcUtilisateur/View/FrontOffice/index.php" class="hover:text-[#be3cf0]">Accueil</a>
            <a href="/mvcact/view/front office/activite.php" class="hover:text-[#be3cf0]">Activités</a>
            <a href="/mvcEvent/View/FrontOffice/evenemant.php" class="hover:text-[#be3cf0]">Événements</a>
            <a href="/mvcProduit/view/front office/produit.php" class="hover:text-[#be3cf0]">Produits</a>


            <div class="group relative">
                <a href="index.php" class="hover:text-[#be3cf0] text-lg"> 
                    Transport▾
                </a>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                  
    <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[220px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
         <a href="ListConducteurs.php" class="block px-4 py-2 hover:bg-gray-100">Mes annonces</a>

                </div>


            </div>


                     

                </div>
               <a href="/mvcSponsor/crud/view/front/index.php" class="hover:text-[#be3cf0]">Sponsors</a>
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
                <h2>✨ Demandes de covoiturage ✨</h2>

                <?php if ($error): ?>
                    <div class="message message-error"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="message message-success"><?= $success ?></div>
                <?php endif; ?>

                <div id="demandes-list">
                    <?php if (empty($demandes)): ?>
                        <div class="empty-state">
                            <i class="fas fa-user-times"></i>
                            <p>Aucune demande disponible pour cette annonce.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($demandes as $index => $demande): ?>
                            <div class="demande-card" id="demande-<?= $demande->id_demande ?>" data-index="<?= $index ?>">
                                <div class="demande-header">
                                    <div class="demande-title">
                                        <?= $demande->prenom_passager ?> <?= $demande->nom_passager ?>
                                    </div>
                                </div>
                                
                                <div class="demande-details">
                                    <div class="demande-detail">
                                        <i class="fas fa-phone"></i>
                                        <span><?= $demande->tel_passager ?></span>
                                    </div>
                                    <div class="demande-detail">
                                        <i class="fas fa-info-circle"></i>
                                        <span class="status-<?= $demande->status === 'approuvé' ? 'approved' : 'pending' ?>" 
                                            id="status-<?= $demande->id_demande ?>">
                                            Statut: <?= $demande->status ?>
                                        </span>
                                    </div>
                                    <div class="demande-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Date: <?= $demande->created_at->format('d/m/Y H:i') ?></span>
                                    </div>
                                </div>
                                
                                <div class="action-buttons" id="buttons-<?= $demande->id_demande ?>">
                                    <?php if ($demande->status === 'approuvé'): ?>
                                        <button onclick="showCaptchaModal('<?= urlencode($demande->tel_passager) ?>')" 
                                                class="btn-sms">
                                            <i class="fas fa-sms"></i> Envoyer SMS
                                        </button>
                                    <?php elseif ($demande->status === 'en cours'): ?>
                                        <button onclick="handleAction(<?= $demande->id_demande ?>, 'approve')" 
                                                class="btn-action">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                        <button onclick="handleAction(<?= $demande->id_demande ?>, 'reject')" 
                                                class="btn-action">
                                            <i class="fas fa-times"></i> Rejeter
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- CAPTCHA Modal -->
    <div id="captchaModal" class="modal">
        <div class="modal-content">
            <h3>Vérification CAPTCHA</h3>
            <form onsubmit="fakeVerify(event)">
                <div class="form-group">
                    <input type="text" name="nome" placeholder="Votre Nom" required>
                </div>
                
                <div class="g-recaptcha" data-sitekey="6LfNwjAUAAAAAOe47FmL1-xHp6YaKKOZ07eQxj0t"></div>
                <input type="hidden" name="phone" id="modalPhone">
                
                <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn-cancel" onclick="closeCaptchaModal()">Annuler</button>
                    <button type="submit" class="btn-verify">Vérifier</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let redirectUrl = '';
        let isCaptchaVerified = false; // Track if CAPTCHA has been verified

        function showCaptchaModal(phone) {
            if (isCaptchaVerified) {
                // If CAPTCHA is already verified, redirect immediately to test_sms.php
                console.log('CAPTCHA already verified, redirecting to:', `/mvcCovoiturage/view/test_sms.php?phone=${phone}`);
                window.location.href = `/mvcCovoiturage/view/test_sms.php?phone=${phone}`;
            } else {
                // Show CAPTCHA modal if not yet verified
                redirectUrl = `/mvcCovoiturage/view/test_sms.php?phone=${phone}`;
                console.log('Redirect URL set to:', redirectUrl); // Debugging
                document.getElementById('captchaModal').style.display = 'flex';
                document.getElementById('modalPhone').value = phone;
            }
        }

        function closeCaptchaModal() {
            document.getElementById('captchaModal').style.display = 'none';
            redirectUrl = '';
        }

        function fakeVerify(event) {
            event.preventDefault();
            // Simulate verification (no actual reCAPTCHA check)
            closeCaptchaModal();
            showMessage('success', 'Vérification simulée avec succès ! Redirection...');
            console.log('Initiating redirect to:', redirectUrl); // Debugging
            isCaptchaVerified = true; // Set CAPTCHA as verified
            if (redirectUrl) {
                window.location.href = redirectUrl;
            } else {
                // Fallback to get phone from modal input
                const phone = document.getElementById('modalPhone').value;
                if (phone) {
                    window.location.href = `/mvcCovoiturage/view/test_sms.php?phone=${phone}`;
                } else {
                    console.error('No phone number available for redirect!');
                }
            }
        }

        async function handleAction(demandeId, action) {
            const buttonsDiv = document.getElementById(`buttons-${demandeId}`);
            const originalContent = buttonsDiv.innerHTML;
            
            buttonsDiv.innerHTML = `
                <button class="btn-action" disabled>
                    <span class="loading"></span> Traitement...
                </button>
            `;

            try {
                const response = await fetch('handle_demande.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id_demande=${demandeId}&action=${action}&id_annonce=<?= $annonceId ?>`
                });

                const data = await response.text();
                
                if (response.ok) {
                    const statusSpan = document.getElementById(`status-${demandeId}`);
                    const newStatus = action === 'approve' ? 'approuvé' : 'rejeté';
                    
                    statusSpan.className = `status-${newStatus === 'approuvé' ? 'approved' : 'pending'}`;
                    statusSpan.textContent = `Statut: ${newStatus}`;
                    
                    if (newStatus === 'approuvé') {
                        buttonsDiv.innerHTML = `
                            <button onclick="showCaptchaModal('<?= urlencode($demande->tel_passager) ?>')" 
                                    class="btn-sms">
                                <i class="fas fa-sms"></i> Envoyer SMS
                            </button>
                        `;
                    } else {
                        buttonsDiv.innerHTML = '';
                    }
                    
                    showMessage('success', `Demande ${newStatus} avec succès!`);
                } else {
                    throw new Error(data || 'Erreur lors du traitement');
                }
            } catch (error) {
                buttonsDiv.innerHTML = originalContent;
                showMessage('error', error.message);
            }
        }

        function showMessage(type, message) {
            const div = document.createElement('div');
            div.className = `message message-${type}`;
            div.textContent = message;
            
            const wrapper = document.querySelector('.demande-wrapper');
            const existingMessages = document.querySelectorAll('.message');
            
            // Remove existing messages
            existingMessages.forEach(msg => msg.remove());
            
            // Insert new message after the title
            wrapper.insertBefore(div, wrapper.children[1].nextSibling);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                div.style.opacity = '0';
                div.style.transform = 'translateY(-20px)';
                div.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => div.remove(), 500);
            }, 5000);
        }
        
        // Add scroll event listener for navbar
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
        
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
                <a href="/mvcact/view/front%20office/about.php">À propos</a>

               
                <a href="/mvcact/view/front%20office/presse.php">Presse</a>

                <a href="/mvcact/view/front%20office/nous-rejoindre.php">Nous rejoindre</a>

            </div>

            <div class="links">
                <p>Liens utiles</p>
             <a href="/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]">Devenir partenaire</a>



                <a href="/mvcact/view/front office/faq.php" class="hover:text-[#be3cf0]">FAQ</a>
               <a href="/mvcact/view/front office/avis.php" class="hover:text-[#be3cf0]">Avis</a>
            </div>
        </div>

        <div class="footer-section">
            <hr>
            <div class="footer-separator"></div>
            <div class="footer-bottom">
                <p>© click'N'go 2025 - tous droits réservés</p>
                <div class="footer-links-bottom">
                   <a href="/mvcact/view/front office/conditions-generales.php" class="hover:text-[#be3cf0]">Conditions générales</a>

<a href="/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]">Devenir partenaire</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
