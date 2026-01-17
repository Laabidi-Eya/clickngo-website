<?php
set_time_limit(0);
/**
 * Script d'upload d'images vers Cloudinary
 * 
 * Ce script explore un dossier d'images locales, les télécharge vers Cloudinary
 * et enregistre les URLs résultantes dans un fichier JSON.
 */

// Charger la configuration
$config = require_once 'cloudinary_config.php';

// Définir les constantes
define('IMAGE_DIRS', [
    __DIR__ . '/images/',
    __DIR__ . '/view/front office/images/',
    __DIR__ . '/view/front office/images/reviews/'
]); // Dossiers des images à télécharger
define('JSON_FILE', __DIR__ . '/cloudinary_images.json'); // Fichier JSON pour stocker les URLs
define('ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']); // Types de fichiers autorisés

// Vérifier si les clés d'API ont été configurées
if ($config['cloud_name'] === 'VOTRE_CLOUD_NAME' || 
    $config['api_key'] === 'VOTRE_API_KEY' || 
    $config['api_secret'] === 'VOTRE_API_SECRET') {
    die("Erreur : Veuillez configurer vos identifiants Cloudinary dans le fichier cloudinary_config.php");
}

// Créer les dossiers d'images s'ils n'existent pas
foreach (IMAGE_DIRS as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "Dossier d'images créé: " . $dir . "<br>";
    }
}

// Fonction pour télécharger une image vers Cloudinary
function uploadToCloudinary($imagePath, $config) {
    // Construire l'URL de l'API Cloudinary
    $timestamp = time();
    $signature = sha1("timestamp=" . $timestamp . $config['api_secret']);
    $apiUrl = "https://api.cloudinary.com/v1_1/{$config['cloud_name']}/image/upload";
    
    // Préparer les données pour le téléchargement
    $post = [
        'file' => new CURLFile($imagePath),
        'timestamp' => $timestamp,
        'api_key' => $config['api_key'],
        'signature' => $signature
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
        echo "Erreur lors du téléchargement de {$imagePath}: Code HTTP {$httpCode}<br>";
        return null;
    }
    
    // Décoder la réponse JSON
    $result = json_decode($response, true);
    
    // Vérifier si la réponse contient une URL
    if (!isset($result['secure_url'])) {
        echo "Erreur lors du téléchargement de {$imagePath}: Réponse invalide<br>";
        return null;
    }
    
    return [
        'original' => basename($imagePath),
        'cloudinary_url' => $result['secure_url'],
        'public_id' => $result['public_id'],
        'uploaded_at' => date('Y-m-d H:i:s')
    ];
}

// Fonction pour explorer le dossier d'images et télécharger les nouvelles images
function processImages($config) {
    $results = [];
    $uploadedImages = [];
    
    // Charger les images déjà téléchargées (si le fichier JSON existe)
    if (file_exists(JSON_FILE)) {
        $uploadedImages = json_decode(file_get_contents(JSON_FILE), true) ?: [];
        
        // Indexer par nom de fichier pour faciliter la recherche
        $uploadedImagesByName = [];
        foreach ($uploadedImages as $img) {
            $uploadedImagesByName[$img['original']] = $img;
        }
        $uploadedImages = $uploadedImagesByName;
    }
    
    // Explorer tous les dossiers d'images
    foreach (IMAGE_DIRS as $imageDir) {
        echo "<h3>Scan du dossier : " . $imageDir . "</h3>";
        
        // Explorer le dossier d'images
        $dirIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($imageDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($dirIterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }
            
            $fileName = $fileInfo->getFilename();
            $filePath = $fileInfo->getPathname();
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Vérifier si le fichier est une image autorisée
            if (!in_array($extension, ALLOWED_TYPES)) {
                continue;
            }
            
            // Vérifier si l'image a déjà été téléchargée
            if (isset($uploadedImages[$fileName])) {
                $results[] = $uploadedImages[$fileName];
                echo "Image déjà téléchargée: {$fileName}<br>";
                continue;
            }
            
            // Télécharger l'image vers Cloudinary
            echo "Téléchargement de {$fileName}... ";
            $uploadResult = uploadToCloudinary($filePath, $config);
            
            if ($uploadResult) {
                $results[] = $uploadResult;
                echo "Succès!<br>";
            } else {
                echo "Échec.<br>";
            }
        }
    }
    
    // Enregistrer les résultats dans le fichier JSON
    file_put_contents(JSON_FILE, json_encode($results, JSON_PRETTY_PRINT));
    
    return $results;
}

// Traiter les images et afficher les résultats
$processedImages = processImages($config);
$imagesCount = count($processedImages);

echo "<h2>Traitement terminé</h2>";
echo "<p>{$imagesCount} images traitées au total.</p>";
echo "<p>Les résultats ont été enregistrés dans <code>" . JSON_FILE . "</code></p>";
echo "<p><a href='cloudinary_gallery.php'>Voir la galerie</a></p>"; 

function uploadImage($file, $saveLocal = true) {
    $localPath = $saveLocal ? __DIR__ . '/../view/front office/images/' : null;
    $result = uploadToCloudinary($file, $localPath);
    if ($result) {
        return $result['cloudinary_url'];
    } else {
        // Affiche une erreur explicite
        error_log('Upload Cloudinary a échoué, aucune image enregistrée.');
        return '';
    }
} 