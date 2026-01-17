<?php
/**
 * Formulaire d'upload d'images vers Cloudinary
 * 
 * Ce script fournit une interface conviviale pour télécharger des images
 * directement vers Cloudinary sans passer par le dossier local.
 */

// Charger la configuration
$config = require_once 'cloudinary_config.php';
define('JSON_FILE', __DIR__ . '/cloudinary_images.json');

// Vérifier si les clés d'API ont été configurées
$configError = false;
if ($config['cloud_name'] === 'VOTRE_CLOUD_NAME' || 
    $config['api_key'] === 'VOTRE_API_KEY' || 
    $config['api_secret'] === 'VOTRE_API_SECRET') {
    $configError = true;
}

// Traiter le formulaire de téléchargement
$uploadResult = null;
$errorMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    // Vérifier s'il y a une erreur lors du téléchargement
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        switch ($_FILES['image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = "L'image est trop volumineuse.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg = "Le téléchargement a été interrompu.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = "Aucun fichier n'a été téléchargé.";
                break;
            default:
                $errorMsg = "Une erreur est survenue lors du téléchargement.";
        }
    } else {
        // Vérifier le type de fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errorMsg = "Type de fichier non autorisé. Seuls les formats JPG, PNG, GIF et WEBP sont acceptés.";
        } else {
            // Télécharger l'image vers Cloudinary
            $uploadResult = uploadToCloudinary($_FILES['image']['tmp_name'], $config, $_FILES['image']['name']);
            
            if ($uploadResult) {
                // Enregistrer les résultats dans le fichier JSON
                saveToJson($uploadResult);
            } else {
                $errorMsg = "Échec du téléchargement vers Cloudinary.";
            }
        }
    }
}

// Fonction pour télécharger une image vers Cloudinary
function uploadToCloudinary($imagePath, $config, $originalName) {
    // Construire l'URL de l'API Cloudinary
    $timestamp = time();
    $signature = sha1("timestamp=" . $timestamp . $config['api_secret']);
    $apiUrl = "https://api.cloudinary.com/v1_1/{$config['cloud_name']}/image/upload";
    
    // Préparer les données pour le téléchargement
    $post = [
        'file' => new CURLFile($imagePath),
        'timestamp' => $timestamp,
        'api_key' => $config['api_key'],
        'signature' => $signature,
        'public_id' => pathinfo($originalName, PATHINFO_FILENAME) . '_' . uniqid() // Éviter les collisions de noms
    ];
    
    // Initialiser cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Exécuter la requête
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Vérifier si la requête a réussi
    if ($httpCode != 200) {
        return null;
    }
    
    // Décoder la réponse JSON
    $result = json_decode($response, true);
    
    // Vérifier si la réponse contient une URL
    if (!isset($result['secure_url'])) {
        return null;
    }
    
    return [
        'original' => $originalName,
        'cloudinary_url' => $result['secure_url'],
        'public_id' => $result['public_id'],
        'uploaded_at' => date('Y-m-d H:i:s')
    ];
}

// Fonction pour enregistrer les données dans le fichier JSON
function saveToJson($uploadResult) {
    $images = [];
    if (file_exists(JSON_FILE)) {
        $images = json_decode(file_get_contents(JSON_FILE), true) ?: [];
    }
    
    $images[] = $uploadResult;
    file_put_contents(JSON_FILE, json_encode($images, JSON_PRETTY_PRINT));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Télécharger une image vers Cloudinary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .page-header {
            margin-bottom: 30px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
        }
        .upload-container {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .preview-container {
            margin-top: 20px;
            text-align: center;
        }
        #imagePreview {
            max-width: 100%;
            max-height: 300px;
            display: none;
            margin: 0 auto;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .drag-area {
            border: 2px dashed #9BBEF4;
            padding: 40px 20px;
            border-radius: 8px;
            text-align: center;
            background-color: #F0F6FF;
            color: #1C63D5;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 20px;
        }
        .drag-area:hover, .drag-active {
            background-color: #E2EEFF;
            border-color: #1C63D5;
        }
        .drag-area i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #1C63D5;
        }
        .result-container {
            margin-top: 30px;
            padding: 20px;
            border-radius: 8px;
            background-color: #F0F6FF;
        }
        .config-warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffeeba;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Télécharger une image vers Cloudinary</h1>
            <p>Utilisez ce formulaire pour télécharger directement vos images vers Cloudinary</p>
            <div class="d-flex gap-2">
                <a href="cloudinary_upload.php" class="btn btn-outline-secondary">Scanner un dossier</a>
                <a href="cloudinary_gallery.php" class="btn btn-primary">Voir la galerie</a>
            </div>
        </div>
        
        <?php if ($configError): ?>
            <div class="config-warning">
                <h4><i class="fas fa-exclamation-triangle"></i> Configuration requise</h4>
                <p>Vous devez configurer vos identifiants Cloudinary dans le fichier <code>cloudinary_config.php</code> avant de pouvoir télécharger des images.</p>
            </div>
        <?php endif; ?>
        
        <div class="upload-container">
            <form method="post" enctype="multipart/form-data" id="uploadForm">
                <div class="drag-area" id="dragArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <h4>Glissez et déposez votre image ici</h4>
                    <p>ou</p>
                    <button type="button" class="btn btn-primary" id="browseBtn">Parcourir</button>
                    <input type="file" name="image" id="fileInput" accept="image/*" style="display: none;" required>
                </div>
                
                <div class="preview-container">
                    <img id="imagePreview" src="#" alt="Aperçu de l'image">
                </div>
                
                <div class="d-grid gap-2 mt-3">
                    <button type="submit" class="btn btn-success" id="uploadBtn" <?php echo $configError ? 'disabled' : ''; ?>>
                        <i class="fas fa-upload"></i> Télécharger vers Cloudinary
                    </button>
                </div>
            </form>
            
            <?php if ($errorMsg): ?>
                <div class="alert alert-danger mt-4">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMsg); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($uploadResult): ?>
                <div class="result-container">
                    <h4 class="text-success"><i class="fas fa-check-circle"></i> Téléchargement réussi!</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <img src="<?php echo htmlspecialchars($uploadResult['cloudinary_url']); ?>" 
                                 alt="Image téléchargée" 
                                 class="img-fluid img-thumbnail">
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Nom du fichier:</strong></label>
                                <p><?php echo htmlspecialchars($uploadResult['original']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>URL Cloudinary:</strong></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($uploadResult['cloudinary_url']); ?>" id="urlInput" readonly>
                                    <button class="btn btn-outline-secondary" type="button" id="copyBtn">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Date de téléchargement:</strong></label>
                                <p><?php echo htmlspecialchars($uploadResult['uploaded_at']); ?></p>
                            </div>
                            <a href="cloudinary_gallery.php" class="btn btn-primary">Voir dans la galerie</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Prévisualisation de l'image
        const fileInput = document.getElementById('fileInput');
        const imagePreview = document.getElementById('imagePreview');
        const dragArea = document.getElementById('dragArea');
        const browseBtn = document.getElementById('browseBtn');
        
        // Ouvrir la boîte de dialogue de fichier
        browseBtn.addEventListener('click', () => {
            fileInput.click();
        });
        
        // Afficher l'aperçu de l'image sélectionnée
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            }
        });
        
        // Gestion du glisser-déposer
        dragArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dragArea.classList.add('drag-active');
        });
        
        dragArea.addEventListener('dragleave', () => {
            dragArea.classList.remove('drag-active');
        });
        
        dragArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dragArea.classList.remove('drag-active');
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                fileInput.files = e.dataTransfer.files;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Copier l'URL
        const copyBtn = document.getElementById('copyBtn');
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                const urlInput = document.getElementById('urlInput');
                urlInput.select();
                document.execCommand('copy');
                
                // Changer l'icône temporairement
                const originalHTML = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    copyBtn.innerHTML = originalHTML;
                }, 2000);
            });
        }
    </script>
</body>
</html> 