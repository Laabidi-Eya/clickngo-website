<?php
/**
 * Galerie d'images Cloudinary
 * 
 * Ce script affiche une galerie des images téléchargées vers Cloudinary
 * avec leurs URLs et quelques options de manipulation d'image.
 */

// Définir le chemin vers le fichier JSON
define('JSON_FILE', __DIR__ . '/cloudinary_images.json');

// Charger les données des images
$images = [];
if (file_exists(JSON_FILE)) {
    $images = json_decode(file_get_contents(JSON_FILE), true) ?: [];
}

// Trier les images par date de téléchargement (les plus récentes d'abord)
usort($images, function($a, $b) {
    return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
});

// Fonction pour générer des transformations d'URL Cloudinary
function generateTransformedUrl($url, $transformation) {
    // Exemple: https://res.cloudinary.com/demo/image/upload/sample.jpg
    // Devient: https://res.cloudinary.com/demo/image/upload/w_300,h_300,c_fill/sample.jpg
    $parts = explode('/upload/', $url);
    if (count($parts) !== 2) {
        return $url;
    }
    
    return $parts[0] . '/upload/' . $transformation . '/' . $parts[1];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie d'images Cloudinary</title>
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
        .gallery-container {
            margin-top: 20px;
        }
        .image-card {
            margin-bottom: 25px;
            transition: transform 0.3s;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .url-container {
            background-color: #f1f1f1;
            padding: 8px;
            border-radius: 4px;
            margin-top: 10px;
            position: relative;
        }
        .url-text {
            font-size: 12px;
            word-break: break-all;
            margin-right: 30px;
            font-family: monospace;
        }
        .copy-btn {
            position: absolute;
            right: 5px;
            top: 5px;
            background: transparent;
            border: none;
            cursor: pointer;
            color: #007bff;
        }
        .empty-gallery {
            text-align: center;
            padding: 50px;
            background-color: #fff;
            border-radius: 8px;
            margin-top: 30px;
        }
        .transformation-options {
            margin-top: 10px;
        }
        .transformation-options button {
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Galerie d'images Cloudinary</h1>
            <p>Visualisez vos images téléchargées et leurs URLs Cloudinary</p>
            <div class="d-flex gap-2">
                <a href="cloudinary_upload.php" class="btn btn-primary">Télécharger plus d'images</a>
                <button class="btn btn-outline-secondary" id="toggleUrlsBtn">Afficher/Masquer les URLs</button>
            </div>
        </div>
        
        <?php if (empty($images)): ?>
            <div class="empty-gallery">
                <h3>Aucune image n'a été téléchargée</h3>
                <p>Utilisez la page de téléchargement pour ajouter des images à votre galerie.</p>
                <a href="cloudinary_upload.php" class="btn btn-primary">Télécharger des images</a>
            </div>
        <?php else: ?>
            <div class="row gallery-container">
                <?php foreach ($images as $image): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card image-card">
                            <img src="<?php echo htmlspecialchars($image['cloudinary_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($image['original']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($image['original']); ?></h5>
                                <p class="card-text">
                                    <small class="text-muted">Téléchargé le <?php echo htmlspecialchars($image['uploaded_at']); ?></small>
                                </p>
                                
                                <div class="transformation-options">
                                    <strong>Transformations:</strong><br>
                                    <button class="btn btn-sm btn-outline-info apply-transform" 
                                            data-url="<?php echo htmlspecialchars($image['cloudinary_url']); ?>"
                                            data-transform="w_300,h_300,c_fill">300x300 Fill</button>
                                    <button class="btn btn-sm btn-outline-info apply-transform" 
                                            data-url="<?php echo htmlspecialchars($image['cloudinary_url']); ?>"
                                            data-transform="w_300,h_300,c_fit">300x300 Fit</button>
                                    <button class="btn btn-sm btn-outline-info apply-transform" 
                                            data-url="<?php echo htmlspecialchars($image['cloudinary_url']); ?>"
                                            data-transform="w_300,e_grayscale">Grayscale</button>
                                    <button class="btn btn-sm btn-outline-info apply-transform" 
                                            data-url="<?php echo htmlspecialchars($image['cloudinary_url']); ?>"
                                            data-transform="w_300,e_sepia">Sepia</button>
                                    <button class="btn btn-sm btn-outline-info apply-transform" 
                                            data-url="<?php echo htmlspecialchars($image['cloudinary_url']); ?>"
                                            data-transform="w_300,a_90">Rotate 90°</button>
                                </div>
                                
                                <div class="url-container url-box">
                                    <div class="url-text"><?php echo htmlspecialchars($image['cloudinary_url']); ?></div>
                                    <button class="copy-btn" data-url="<?php echo htmlspecialchars($image['cloudinary_url']); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard" viewBox="0 0 16 16">
                                            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                                            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Fonction pour copier l'URL dans le presse-papiers
        document.querySelectorAll('.copy-btn').forEach(button => {
            button.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                navigator.clipboard.writeText(url).then(() => {
                    // Changer temporairement l'icône pour indiquer la réussite
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="green" class="bi bi-check" viewBox="0 0 16 16"><path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/></svg>';
                    setTimeout(() => {
                        this.innerHTML = originalHTML;
                    }, 2000);
                });
            });
        });
        
        // Fonction pour afficher/masquer les URLs
        document.getElementById('toggleUrlsBtn').addEventListener('click', function() {
            document.querySelectorAll('.url-container').forEach(container => {
                container.style.display = container.style.display === 'none' ? 'block' : 'none';
            });
        });
        
        // Fonction pour appliquer les transformations
        document.querySelectorAll('.apply-transform').forEach(button => {
            button.addEventListener('click', function() {
                const baseUrl = this.getAttribute('data-url');
                const transform = this.getAttribute('data-transform');
                
                // Générer l'URL transformée
                const parts = baseUrl.split('/upload/');
                if (parts.length === 2) {
                    const transformedUrl = parts[0] + '/upload/' + transform + '/' + parts[1];
                    
                    // Ouvrir l'image transformée dans une nouvelle fenêtre
                    window.open(transformedUrl, '_blank');
                }
            });
        });
    </script>
</body>
</html> 