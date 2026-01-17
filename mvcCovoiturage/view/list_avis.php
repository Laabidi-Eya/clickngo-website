<?php
require_once '../config.php';
require_once '../Controller/avisController.php';

$avisController = new avisController();
$avisList = $avisController->getAllAvis();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Liste des Avis</title>
    <link rel="stylesheet" href="/clickngo/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Réutilisation des styles de ListPassager.php */
        body {
            background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
        }

        .form-background {
            background-image: url('');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: calc(100vh - 200px);
        }

        .avis-container {
            display: flex;
            justify-content: center;
            padding: 40px 0;
        }

        .avis-wrapper {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            width: 80%;
            max-width: 1000px;
            border: 2px solid #be3cf0;
            transition: 0.3s;
            margin: 20px 0;
        }

        .avis-wrapper h2 {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        .avis-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #be3cf0;
            transition: transform 0.3s;
        }

        .avis-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .avis-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .avis-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .avis-id {
            font-size: 16px;
            color: #666;
        }

        .avis-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .avis-detail {
            display: flex;
            align-items: center;
        }

        .avis-detail i {
            margin-right: 10px;
            color: #be3cf0;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
        }

        .empty-state i {
            font-size: 50px;
            color: #be3cf0;
            margin-bottom: 20px;
        }

        .empty-state p {
            font-size: 18px;
            color: #555;
        }
    </style>
</head>
<body>
    <!-- NAVBAR (similaire à ListPassager.php) -->
    <nav class="absolute top-0 left-0 w-full z-50 p-4">
        <div class="flex items-center justify-center max-w-7xl mx-auto">
            <div class="flex space-x-8 text-lg font-bold text-black relative">
                <a href="index.php" class="hover:text-[#be3cf0]">Accueil</a>
                <a href="#about" class="hover:text-[#be3cf0]">À propos</a>
                <div class="group relative">
                    <button class="hover:text-[#be3cf0] font-bold text-lg">
                        Nos Détails ▾
                    </button>
                    <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                        <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Nos trajets</a>
                        <a href="ListPassager.php" class="block px-4 py-2 hover:bg-gray-100">Mes demandes</a>
                    </div>
                </div>
                <div class="relative group">
                    <button class="hover:text-[#be3cf0]">Services ▾</button>
                    <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[220px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                        <a href="DisplayConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Trouver un covoiturage</a>
                        <a href="AjouterConducteur.php" class="block px-4 py-2 hover:bg-gray-100">Proposer un covoiturage</a>
                    </div>
                </div>
                <div class="relative group">
                    <button class="hover:text-[#be3cf0]">Contact ▾</button>
                    <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[180px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                        <a href="/clickngo/view/reclamation.php" class="block px-4 py-2 hover:bg-gray-100">Réclamation</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <br><br><br>

    <!-- AVIS SECTION -->
    <div class="form-background">
        <div class="avis-container">
            <div class="avis-wrapper">
                <h2>✨Liste des Avis✨</h2>

                <?php if (empty($avisList)): ?>
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <p>Aucun avis disponible pour le moment.</p>
                    </div>
                <?php else: ?>
                    <div class="avis-list">
                        <?php foreach ($avisList as $avis): ?>
                            <div class="avis-card">
                                <div class="avis-header">
                                    <div class="avis-title">
                                        Avis #<?php echo htmlspecialchars($avis['id_avis']); ?>
                                    </div>
                                    <div class="avis-id">
                                        Passager ID: <?php echo htmlspecialchars($avis['id_passager']); ?>
                                    </div>
                                </div>
                                <div class="avis-details">
                                    <div class="avis-detail">
                                        <i class="fas fa-car"></i>
                                        <span>Conducteur ID: <?php echo htmlspecialchars($avis['id_conducteur']); ?></span>
                                    </div>
                                    <div class="avis-detail">
                                        <i class="fas fa-star"></i>
                                        <span>Note: <?php echo htmlspecialchars($avis['note']); ?>/5</span>
                                    </div>
                                    <div class="avis-detail">
                                        <i class="fas fa-comment"></i>
                                        <span>Commentaire: <?php echo htmlspecialchars($avis['commentaire']); ?></span>
                                    </div>
                                    <div class="avis-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Date: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($avis['date_avis']))); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>