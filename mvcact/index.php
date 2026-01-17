<?php
/**
 * Page d'accueil Cloudinary Uploader
 * 
 * Cette page sert de point d'entrée pour gérer les images Cloudinary
 */

// Vérifier la configuration
$config = require_once 'cloudinary_config.php';
$configError = false;

if ($config['cloud_name'] === 'VOTRE_CLOUD_NAME' || 
    $config['api_key'] === 'VOTRE_API_KEY' || 
    $config['api_secret'] === 'VOTRE_API_SECRET') {
    $configError = true;
}

// Définir le fichier JSON
define('JSON_FILE', __DIR__ . '/cloudinary_images.json');

// Obtenir des statistiques sur les images
$stats = [
    'total' => 0,
    'lastUpload' => 'Aucun téléchargement',
    'storage' => '0 KB'
];

if (file_exists(JSON_FILE)) {
    $images = json_decode(file_get_contents(JSON_FILE), true) ?: [];
    $stats['total'] = count($images);
    
    if (!empty($images)) {
        // Trier par date de téléchargement
        usort($images, function($a, $b) {
            return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
        });
        
        $stats['lastUpload'] = $images[0]['uploaded_at'] ?? 'Inconnu';
        
        // Estimation du stockage (hypothétique, car nous n'avons pas cette information via la simple API)
        $stats['storage'] = formatStorage($stats['total'] * 2); // Supposons une moyenne de 2 Mo par image
    }
}

// Fonction pour formater la taille du stockage
function formatStorage($sizeInMB) {
    if ($sizeInMB < 1) {
        return round($sizeInMB * 1024) . " KB";
    } elseif ($sizeInMB < 1024) {
        return round($sizeInMB, 1) . " MB";
    } else {
        return round($sizeInMB / 1024, 2) . " GB";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloudinary Uploader - Gestion d'images</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            color: #1C63D5;
            margin-bottom: 5px;
        }
        .header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-body {
            padding: 25px;
        }
        .card-title {
            color: #1C63D5;
            font-weight: 600;
            font-size: 1.4rem;
            margin-bottom: 15px;
        }
        .card-text {
            color: #6c757d;
            margin-bottom: 20px;
            font-size: 1rem;
        }
        .stats-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            flex: 1;
            min-width: 200px;
            margin: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }
        .btn-custom {
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s;
            letter-spacing: 0.5px;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .btn-icon {
            margin-right: 8px;
        }
        .card-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            background: -webkit-linear-gradient(45deg, #6a11cb, #2575fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .config-warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #ffeeba;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            padding: 20px 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .stat-card {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="header">
            <h1><i class="fas fa-cloud"></i> Cloudinary Uploader</h1>
            <p>Gestion simplifiée de vos images cloud</p>
        </div>
        
        <?php if ($configError): ?>
            <div class="config-warning">
                <h4><i class="fas fa-exclamation-triangle"></i> Configuration requise</h4>
                <p>Vous devez configurer vos identifiants Cloudinary dans le fichier <code>cloudinary_config.php</code> avant de pouvoir utiliser cette application.</p>
                <ol>
                    <li>Créez un compte sur <a href="https://cloudinary.com/" target="_blank">Cloudinary</a> si vous n'en avez pas déjà un</li>
                    <li>Récupérez vos identifiants (Cloud Name, API Key et API Secret) depuis votre tableau de bord</li>
                    <li>Modifiez le fichier <code>cloudinary_config.php</code> avec vos identifiants</li>
                </ol>
            </div>
        <?php endif; ?>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Images téléchargées</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['storage']; ?></div>
                <div class="stat-label">Stockage estimé</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['lastUpload'] != 'Aucun téléchargement' ? date('d/m/Y', strtotime($stats['lastUpload'])) : 'N/A'; ?></div>
                <div class="stat-label">Dernier téléchargement</div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-upload card-icon"></i>
                        <h5 class="card-title">Télécharger une image</h5>
                        <p class="card-text">Téléchargez rapidement une image depuis votre appareil vers Cloudinary.</p>
                        <a href="cloudinary_form.php" class="btn btn-primary btn-custom">
                            <i class="fas fa-cloud-upload-alt btn-icon"></i>Télécharger
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-folder-open card-icon"></i>
                        <h5 class="card-title">Scanner un dossier</h5>
                        <p class="card-text">Analysez un dossier local et téléchargez toutes les images vers Cloudinary.</p>
                        <a href="cloudinary_upload.php" class="btn btn-primary btn-custom">
                            <i class="fas fa-sync btn-icon"></i>Scanner
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-images card-icon"></i>
                        <h5 class="card-title">Galerie d'images</h5>
                        <p class="card-text">Visualisez et gérez toutes vos images déjà téléchargées sur Cloudinary.</p>
                        <a href="cloudinary_gallery.php" class="btn btn-primary btn-custom">
                            <i class="fas fa-th btn-icon"></i>Voir la galerie
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-lightbulb"></i> Pourquoi utiliser Cloudinary ?</h5>
                <p class="card-text">Cloudinary est un service de gestion d'images et de vidéos dans le cloud qui vous permet de:</p>
                <div class="row">
                    <div class="col-md-6">
                        <ul>
                            <li>Stocker et organiser vos médias</li>
                            <li>Redimensionner et transformer vos images à la volée</li>
                            <li>Optimiser automatiquement vos médias pour le web</li>
                            <li>Servir vos images via un CDN mondial</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul>
                            <li>Appliquer des filtres et effets sans modifier les originaux</li>
                            <li>Générer des miniatures automatiquement</li>
                            <li>Délivrer des images adaptées à chaque appareil</li>
                            <li>Réduire le temps de chargement de vos sites web</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Cloudinary Uploader &copy; <?php echo date('Y'); ?> - Développé avec <i class="fas fa-heart" style="color: #e25555;"></i></p>
            <p><a href="https://cloudinary.com/" target="_blank">En savoir plus sur Cloudinary</a></p>
        </div>
    </div>
</body>
</html> 