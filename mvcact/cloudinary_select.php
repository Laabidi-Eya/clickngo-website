<?php
/**
 * Sélecteur d'images Cloudinary pour les activités
 * 
 * Ce script permet de sélectionner une image Cloudinary pour une activité spécifique
 */

// Charger les fichiers nécessaires
require_once 'utils/cloudinary_helper.php';

// Configuration de la base de données
$dbHost = 'localhost';
$dbName = 'clickngo_db';
$dbUser = 'root';
$dbPass = '';

// Récupérer l'ID de l'activité depuis l'URL
$activityId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Initialiser les variables
$activity = null;
$message = null;
$messageType = null;
$cloudinaryImages = getAllCloudinaryImages();

// Connexion à la base de données
try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Si un ID d'activité est fourni, récupérer l'activité
    if ($activityId) {
        $stmt = $db->prepare("SELECT * FROM activities WHERE id = ?");
        $stmt->execute([$activityId]);
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$activity) {
            $message = "Activité non trouvée";
            $messageType = "danger";
        }
    }
    
    // Si une image est sélectionnée
    if (isset($_POST['image_url']) && $activityId) {
        $newImageUrl = $_POST['image_url'];
        
        // Mettre à jour l'activité avec l'URL de l'image
        $updateStmt = $db->prepare("UPDATE activities SET image = ? WHERE id = ?");
        $updateStmt->execute([$newImageUrl, $activityId]);
        
        $message = "Image mise à jour avec succès";
        $messageType = "success";
        
        // Recharger l'activité avec la nouvelle image
        $stmt = $db->prepare("SELECT * FROM activities WHERE id = ?");
        $stmt->execute([$activityId]);
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Si pas d'ID, récupérer toutes les activités pour la liste
    if (!$activityId) {
        $stmt = $db->query("SELECT id, name, image FROM activities ORDER BY id DESC");
        $allActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    $message = "Erreur de base de données: " . $e->getMessage();
    $messageType = "danger";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sélecteur d'images Cloudinary - Click'N'Go</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .header {
            margin-bottom: 30px;
        }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .image-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .image-card.selected {
            border: 3px solid #0d6efd;
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.5);
        }
        .image-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .image-card .card-body {
            padding: 10px;
            font-size: 0.9rem;
        }
        .preview-container {
            margin-bottom: 20px;
            text-align: center;
        }
        .preview-container img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sélecteur d'images Cloudinary</h1>
            <p>Choisissez une image Cloudinary pour vos activités</p>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!$activityId): ?>
            <!-- Liste des activités -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2>Choisir une activité</h2>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($allActivities as $act): ?>
                            <a href="?id=<?php echo $act['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($act['name']); ?></h5>
                                    <small>ID: <?php echo $act['id']; ?></small>
                                </div>
                                <small><?php echo basename($act['image']); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <p><a href="cloudinary_gallery.php" class="btn btn-primary">Galerie Cloudinary</a> <a href="update_activities_images.php" class="btn btn-warning">Mise à jour automatique</a></p>
        
        <?php else: ?>
            <!-- Détails de l'activité et sélection d'image -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h2>Activité</h2>
                        </div>
                        <div class="card-body">
                            <h3><?php echo htmlspecialchars($activity['name']); ?></h3>
                            <p><strong>ID:</strong> <?php echo $activity['id']; ?></p>
                            <p><strong>Image actuelle:</strong> <br><?php echo basename($activity['image']); ?></p>
                            
                            <div class="preview-container">
                                <img src="<?php echo htmlspecialchars($activity['image']); ?>" alt="<?php echo htmlspecialchars($activity['name']); ?>">
                            </div>
                            
                            <a href="cloudinary_select.php" class="btn btn-secondary">Retour à la liste</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h2>Sélectionner une image Cloudinary</h2>
                        </div>
                        <div class="card-body">
                            <form method="post" id="imageForm">
                                <input type="hidden" name="image_url" id="selectedImageUrl" value="">
                                
                                <?php if (empty($cloudinaryImages)): ?>
                                    <div class="alert alert-warning">
                                        Aucune image n'a été téléchargée sur Cloudinary.
                                        <a href="cloudinary_upload.php" class="btn btn-sm btn-primary">Télécharger des images</a>
                                    </div>
                                <?php else: ?>
                                    <div class="image-grid">
                                        <?php foreach ($cloudinaryImages as $image): ?>
                                            <div class="image-card" data-url="<?php echo htmlspecialchars($image['cloudinary_url']); ?>">
                                                <img src="<?php echo htmlspecialchars($image['cloudinary_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($image['original']); ?>">
                                                <div class="card-body">
                                                    <small><?php echo htmlspecialchars(substr($image['original'], 0, 20) . (strlen($image['original']) > 20 ? '...' : '')); ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Appliquer l'image sélectionnée</button>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // JavaScript pour gérer la sélection des images
        document.addEventListener('DOMContentLoaded', function() {
            // Sélectionner toutes les cartes d'images
            const imageCards = document.querySelectorAll('.image-card');
            const submitBtn = document.getElementById('submitBtn');
            const selectedImageUrl = document.getElementById('selectedImageUrl');
            
            // Ajouter un écouteur d'événement à chaque carte
            imageCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Supprimer la classe 'selected' de toutes les cartes
                    imageCards.forEach(c => c.classList.remove('selected'));
                    
                    // Ajouter la classe 'selected' à la carte cliquée
                    this.classList.add('selected');
                    
                    // Mettre à jour l'URL de l'image sélectionnée
                    selectedImageUrl.value = this.dataset.url;
                    
                    // Activer le bouton de soumission
                    submitBtn.disabled = false;
                });
            });
        });
    </script>
</body>
</html> 