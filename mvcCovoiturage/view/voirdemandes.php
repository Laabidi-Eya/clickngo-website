<?php
require_once '../config.php';
require_once '../Controller/DemandeCovoiturageController.php';
require_once '../Controller/AnnonceCovoiturageController.php';
require_once '../Model/DemandeCovoiturage.php';

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'ID de l'annonce est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ListConducteurs.php');
    exit;
}

$idAnnonce = $_GET['id'];
$errorMessages = [];
$demandes = [];

try {
    // Obtenir la connexion PDO
    $pdo = config::getConnexion();
    
    // Créer une instance du contrôleur de demandes
    $controller = new DemandeCovoiturageController();
    
    // Récupérer toutes les demandes pour cette annonce
    $demandes = $controller->getDemandesByAnnonceId($idAnnonce);
    
    // Récupérer les détails de l'annonce - PASSER LA CONNEXION PDO
    $annonceController = new AnnonceCovoiturageController($pdo);
    $annonce = $annonceController->getAnnonceById($idAnnonce);
    
} catch (Exception $e) {
    $errorMessages = explode('<br>', $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demandes de covoiturage</title>
    <link rel="stylesheet" href="/Projet Web/mvcCovoiturage/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* === FOND D'ÉCRAN === */
        .form-background {
            background-image: url('');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: calc(100vh - 200px);
        }

        /* === GRADIENT PERSONNALISÉ === */
        .elsa-gradient-primary {
            background: linear-gradient(to right, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
        }

        .elsa-gradient-primary-hover:hover {
            background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
        }

        /* === STYLE DE LA LISTE === */
        .demande-container {
            display: flex;
            justify-content: center;
            padding: 40px 0;
        }

        .demande-wrapper {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            width: 80%;
            max-width: 1000px;
            border: 2px solid #be3cf0;
            transition: 0.3s;
            margin: 20px 0;
        }

        .demande-wrapper h2 {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        .annonce-summary {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid #be3cf0;
        }

        .demande-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #be3cf0;
            transition: transform 0.3s;
        }

        .demande-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .demande-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .demande-id {
            font-size: 16px;
            color: #666;
        }

        .demande-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .demande-detail {
            display: flex;
            align-items: center;
        }

        .demande-detail i {
            margin-right: 10px;
            color: #be3cf0;
        }

        .demande-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-top: 15px;
            gap: 10px;
        }

        .btn-accept {
            padding: 8px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-accept:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }

        .btn-reject {
            padding: 8px 20px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-reject:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
        }

        .status-pending {
            background-color: #ffeeba;
            color: #856404;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-accepted {
            background-color: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
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

        /* Popup styles */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .popup-content {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
            padding: 1.5rem;
            text-align: center;
        }

        .popup-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .popup-success .popup-icon {
            color: #10b981;
        }

        .popup-error .popup-icon {
            color: #ef4444;
        }

        .popup-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .popup-message {
            margin-bottom: 1.5rem;
        }

        .popup-button {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            cursor: pointer;
        }

        .popup-success .popup-button {
            background-color: #10b981;
            color: white;
        }

        .popup-error .popup-button {
            background-color: #ef4444;
            color: white;
        }

        .hidden {
            display: none;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background-color: #6c757d;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            transition: 0.3s;
        }

        .back-button:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>   

<!-- NAVBAR -->
<nav class="absolute top-0 left-0 w-full z-50 p-4">
    <div class="flex items-center justify-center max-w-7xl mx-auto">
        <div class="flex space-x-8 text-lg font-bold text-black relative">
           
            <div class="group relative">
                <a href="index.php" class="hover:text-[#be3cf0] font-bold text-lg">
                    Transport▾
                </a>
                <div class="absolute left-0 mt-2 bg-white rounded shadow-md z-50 min-w-[200px] opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                
                    <a href="ListConducteurs.php" class="block px-4 py-2 hover:bg-gray-100">Mes annonces</a>
                     
                </div>
            </div>
           
            
          
        </div>
    </div>
</nav>
<br>
<br>
<br>

<!-- DEMANDES SECTION -->
<div class="form-background">
    <div class="demande-container">
        <div class="demande-wrapper">
            <a href="ListConducteurs.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Retour aux annonces
            </a>
            
            <h2>Demandes pour votre annonce de covoiturage</h2>
            
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

            <?php if (isset($annonce)): ?>
                <div class="annonce-summary">
                    <h3 class="text-lg font-semibold mb-2">Détails de l'annonce</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-[#be3cf0]"></i>
                            <span>De: <?php echo htmlspecialchars($annonce->getLieuDepart()); ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marked-alt mr-2 text-[#be3cf0]"></i>
                            <span>À: <?php echo htmlspecialchars($annonce->getLieuArrivee()); ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt mr-2 text-[#be3cf0]"></i>
                            <span>Date: <?php echo htmlspecialchars($annonce->getDateDepart()->format('d/m/Y H:i')); ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-users mr-2 text-[#be3cf0]"></i>
                            <span>Places: <?php echo htmlspecialchars($annonce->getNombrePlaces()); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($demandes)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Aucune demande de covoiturage pour cette annonce.</p>
                </div>
            <?php else: ?>
                <div class="demandes-list">
                    <?php foreach ($demandes as $demande): ?>
                        <div class="demande-card">
                            <div class="demande-header">
                                <div class="demande-title">
                                    <?php echo htmlspecialchars($demande->getPrenomPassager() . ' ' . $demande->getNomPassager()); ?>
                                </div>
                                <div class="demande-id">
                                    <?php 
                                    $statusClass = '';
                                    $statusText = '';
                                    
                                    switch($demande->getStatusDemande()) {
                                        case 'en_attente':
                                        case 'en cours':
                                            $statusClass = 'status-pending';
                                            $statusText = 'En attente';
                                            break;
                                        case 'accepte':
                                            $statusClass = 'status-accepted';
                                            $statusText = 'Acceptée';
                                            break;
                                        case 'refuse':
                                            $statusClass = 'status-rejected';
                                            $statusText = 'Refusée';
                                            break;
                                        default:
                                            $statusClass = 'status-pending';
                                            $statusText = 'En attente';
                                    }
                                    ?>
                                    <span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </div>
                            </div>
                            
                            <div class="demande-details">
                                <div class="demande-detail">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo htmlspecialchars($demande->getTelPassager()); ?></span>
                                </div>
                                
                                <div class="demande-detail">
                                    <i class="fas fa-user-friends"></i>
                                    <span>Places demandées: <?php echo htmlspecialchars($demande->getNbrPlacesReservees()); ?></span>
                                </div>
                                
                                <div class="demande-detail">
                                    <i class="fas fa-calendar-plus"></i>
                                    <span>Date de demande: <?php echo htmlspecialchars($demande->getDateDemande()->format('d/m/Y H:i')); ?></span>
                                </div>
                                
                                <div class="demande-detail">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Paiement: <?php echo htmlspecialchars($demande->getMoyenPaiement()); ?></span>
                                </div>
                                
                                <div class="demande-detail">
                                    <i class="fas fa-euro-sign"></i>
                                    <span>Prix total: <?php echo htmlspecialchars($demande->getPrixTotal() ?? ''); ?> €</span>

                                </div>
                            </div>
                            
                            <?php if ($demande->getMessage()): ?>
                                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                    <p class="text-sm font-medium mb-1">Message:</p>
                                    <p class="text-sm"><?php echo htmlspecialchars($demande->getMessage()); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($demande->getStatusDemande() === 'en cours' || $demande->getStatusDemande() === 'en_attente'): ?>
                               
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Success Popup -->
<div id="success-popup" class="popup-overlay popup-success hidden">
    <div class="popup-content">
        <div class="popup-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 class="popup-title">Succès!</h3>
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
        location.reload(); // Recharger la page pour voir les changements
    }
    
    function showErrorPopup(message) {
        document.getElementById('error-message').textContent = message;
        document.getElementById('error-popup').classList.remove('hidden');
    }
    
    function hideErrorPopup() {
        document.getElementById('error-popup').classList.add('hidden');
    }
    
    function acceptDemande(id) {
        updateDemandeStatus(id, 'accepte');
    }
    
    function rejectDemande(id) {
        updateDemandeStatus(id, 'refuse');
    }
    
    function updateDemandeStatus(id, status) {
        fetch('/clickngo/view/update_demande_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&status=${status}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showSuccessPopup(data.message || 'Statut de la demande mis à jour avec succès!');
            } else {
                showErrorPopup(data.message || 'Erreur lors de la mise à jour du statut');
            }
        })
        .catch(error => {
            showErrorPopup('Erreur réseau: ' + error.message);
        });
    }
    
    window.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($errorMessages)): ?>
            showErrorPopup("<?php echo addslashes(implode('\n', $errorMessages)); ?>");
        <?php endif; ?>
    });
</script>
</body>
</html>