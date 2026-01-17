<?php
/**
 * Exemple d'intégration des images Cloudinary
 * Ce fichier montre comment utiliser les images Cloudinary dans vos pages
 */

// Inclure l'aide Cloudinary
require_once __DIR__ . '/../utils/cloudinary_helper.php';

// Récupérer toutes les images pour l'exemple
$cloudinaryImages = getAllCloudinaryImages();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemple d'intégration Cloudinary - Click'N'Go</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .header {
            margin-bottom: 40px;
            text-align: center;
        }
        .example-section {
            margin-bottom: 50px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .code-block {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-family: monospace;
            white-space: pre-wrap;
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
            transition: transform 0.3s;
        }
        .image-card:hover {
            transform: translateY(-5px);
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
        .transform-example img {
            max-width: 100%;
            margin-bottom: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Intégration de Cloudinary dans Click'N'Go</h1>
            <p>Exemples et méthodes d'utilisation des images Cloudinary dans votre site</p>
        </div>

        <div class="example-section">
            <h2>1. Méthode simple (URL directe)</h2>
            <p>La méthode la plus simple consiste à copier l'URL Cloudinary et à l'utiliser directement dans vos balises img :</p>
            
            <div class="code-block">
&lt;img src="https://res.cloudinary.com/dbm44rmok/image/upload/v1234567890/sample.jpg" alt="Description"&gt;
            </div>

            <p>Exemple avec une image réelle :</p>
            
            <?php if (!empty($cloudinaryImages)): ?>
                <img src="<?php echo htmlspecialchars($cloudinaryImages[0]['cloudinary_url']); ?>" 
                     alt="Exemple d'image Cloudinary" 
                     style="max-width: 300px; border-radius: 8px;">
            <?php else: ?>
                <div class="alert alert-warning">Aucune image n'a été téléchargée sur Cloudinary.</div>
            <?php endif; ?>
        </div>

        <div class="example-section">
            <h2>2. Utilisation des fonctions d'aide</h2>
            <p>Nous avons créé un fichier d'aide <code>utils/cloudinary_helper.php</code> pour simplifier l'intégration :</p>
            
            <h3>a) Récupérer l'URL d'une image par son nom</h3>
            <div class="code-block">
// Inclure le fichier d'aide
require_once 'utils/cloudinary_helper.php';

// Récupérer l'URL Cloudinary
$imageUrl = getCloudinaryUrl('logo.png');

// Utiliser l'URL dans votre HTML
echo '&lt;img src="' . $imageUrl . '" alt="Logo"&gt;';
            </div>

            <?php if (!empty($cloudinaryImages)): ?>
                <p>Exemple avec <code>getCloudinaryUrl()</code> :</p>
                <img src="<?php echo getCloudinaryUrl($cloudinaryImages[0]['original']); ?>" 
                     alt="Exemple avec getCloudinaryUrl" 
                     style="max-width: 300px; border-radius: 8px;">
            <?php endif; ?>

            <h3>b) Générer une balise img complète</h3>
            <div class="code-block">
// Générer une balise img avec l'URL Cloudinary
echo cloudinaryImage('logo.png', 'Logo du site', [], ['class' => 'img-fluid']);
            </div>

            <?php if (!empty($cloudinaryImages)): ?>
                <p>Exemple avec <code>cloudinaryImage()</code> :</p>
                <?php echo cloudinaryImage($cloudinaryImages[0]['original'], 'Exemple avec cloudinaryImage', [], ['class' => 'img-fluid', 'style' => 'max-width: 300px; border-radius: 8px;']); ?>
            <?php endif; ?>
        </div>

        <div class="example-section">
            <h2>3. Utilisation des transformations</h2>
            <p>Cloudinary permet de transformer les images à la volée. Voici quelques exemples :</p>
            
            <?php if (!empty($cloudinaryImages)): ?>
                <div class="row transform-example">
                    <div class="col-md-4">
                        <h4>Image originale</h4>
                        <?php echo cloudinaryImage($cloudinaryImages[0]['original'], 'Image originale', [], ['class' => 'img-fluid']); ?>
                    </div>
                    <div class="col-md-4">
                        <h4>Redimensionnée (300x300)</h4>
                        <?php echo cloudinaryImage($cloudinaryImages[0]['original'], 'Image redimensionnée', ['w_300', 'h_300', 'c_fill'], ['class' => 'img-fluid']); ?>
                    </div>
                    <div class="col-md-4">
                        <h4>Effet noir et blanc</h4>
                        <?php echo cloudinaryImage($cloudinaryImages[0]['original'], 'Image en noir et blanc', ['w_300', 'e_grayscale'], ['class' => 'img-fluid']); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="code-block">
// Transformations avec la fonction d'aide
echo cloudinaryImage('image.jpg', 'Image transformée', ['w_300', 'h_300', 'c_fill']);

// Transformations avec l'URL directe
// https://res.cloudinary.com/dbm44rmok/image/upload/w_300,h_300,c_fill/v1234567890/image.jpg
            </div>
        </div>

        <div class="example-section">
            <h2>4. Galerie de vos images Cloudinary</h2>
            
            <?php if (empty($cloudinaryImages)): ?>
                <div class="alert alert-warning">Aucune image n'a été téléchargée sur Cloudinary.</div>
            <?php else: ?>
                <div class="image-grid">
                    <?php foreach (array_slice($cloudinaryImages, 0, 12) as $image): ?>
                        <div class="image-card">
                            <img src="<?php echo htmlspecialchars($image['cloudinary_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($image['original']); ?>">
                            <div class="card-body">
                                <small><?php echo htmlspecialchars(substr($image['original'], 0, 20) . (strlen($image['original']) > 20 ? '...' : '')); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="example-section">
            <h2>5. Intégration dans un projet existant</h2>
            <p>Pour intégrer Cloudinary dans vos pages actuelles, voici comment procéder :</p>
            
            <h3>a) Dans les fichiers de vue front-office</h3>
            <div class="code-block">
// Au début du fichier PHP
require_once __DIR__ . '/../../utils/cloudinary_helper.php';

// Puis remplacer vos balises img
&lt;!-- Avant --&gt;
&lt;img src="images/logo.png" alt="Logo"&gt;

&lt;!-- Après --&gt;
&lt;?php echo cloudinaryImage('logo.png', 'Logo', [], ['class' => 'logo']); ?&gt;
            </div>

            <p>Cette méthode permet une fallback automatique : si l'image n'est pas trouvée sur Cloudinary, elle sera cherchée dans votre dossier local <code>images/</code>.</p>
        </div>
    </div>

    <div class="container mt-5 mb-5">
        <div class="text-center">
            <p><a href="http://localhost/clickngo/cloudinary_gallery.php" class="btn btn-primary">Retour à la galerie Cloudinary</a></p>
        </div>
    </div>
</body>
</html> 