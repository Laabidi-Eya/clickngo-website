<?php
/**
 * Fonction utilitaire pour récupérer les URLs Cloudinary
 * Ce fichier facilite l'intégration des images Cloudinary dans le site
 */

// Fichier JSON contenant les URLs des images
define('CLOUDINARY_JSON', __DIR__ . '/../cloudinary_images.json');

/**
 * Récupère l'URL Cloudinary d'une image par son nom original
 * 
 * @param string $imageName Nom du fichier image original
 * @param array $transformations Transformations Cloudinary (optionnel)
 * @param string $fallbackUrl URL par défaut si l'image n'est pas trouvée
 * @return string URL Cloudinary de l'image ou URL par défaut
 */
function getCloudinaryUrl($imageName, $transformations = [], $fallbackUrl = '') {
    // Vérifier si le fichier JSON existe
    if (!file_exists(CLOUDINARY_JSON)) {
        return $fallbackUrl;
    }
    
    // Charger les données
    $images = json_decode(file_get_contents(CLOUDINARY_JSON), true) ?: [];
    
    // Chercher l'image par son nom
    foreach ($images as $image) {
        if ($image['original'] === $imageName) {
            $url = $image['cloudinary_url'];
            
            // Appliquer des transformations si demandées
            if (!empty($transformations)) {
                $parts = explode('/upload/', $url);
                if (count($parts) === 2) {
                    $transformString = implode(',', $transformations);
                    $url = $parts[0] . '/upload/' . $transformString . '/' . $parts[1];
                }
            }
            
            return $url;
        }
    }
    
    // Image non trouvée, retourner l'URL par défaut
    return $fallbackUrl;
}

/**
 * Génère un tag img avec l'URL Cloudinary
 * 
 * @param string $imageName Nom du fichier image original
 * @param string $alt Texte alternatif pour l'image
 * @param array $transformations Transformations Cloudinary (optionnel)
 * @param array $attributes Attributs HTML supplémentaires
 * @return string Tag img HTML
 */
function cloudinaryImage($imageName, $alt = '', $transformations = [], $attributes = []) {
    $url = getCloudinaryUrl($imageName, $transformations);
    
    // Si l'image n'est pas trouvée, utiliser une image locale
    if (empty($url)) {
        $url = "images/" . $imageName;
    }
    
    // Construire les attributs HTML
    $htmlAttributes = '';
    foreach ($attributes as $key => $value) {
        $htmlAttributes .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    return '<img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($alt) . '"' . $htmlAttributes . '>';
}

/**
 * Récupère toutes les images Cloudinary
 * 
 * @return array Liste des images Cloudinary avec leurs URLs
 */
function getAllCloudinaryImages() {
    if (!file_exists(CLOUDINARY_JSON)) {
        return [];
    }
    
    return json_decode(file_get_contents(CLOUDINARY_JSON), true) ?: [];
}

/**
 * Optimise une URL d'image (qui peut être locale ou Cloudinary)
 * 
 * Cette fonction est utile pour les fichiers existants où les URLs peuvent être mélangées
 * Elle assure que les chemins locaux ou les URLs Cloudinary sont tous gérés correctement
 * 
 * @param string $imageUrl URL ou chemin de l'image
 * @param array $transformations Transformations Cloudinary (si applicable)
 * @return string URL optimisée de l'image
 */
function optimizeImageUrl($imageUrl, $transformations = ['q_auto', 'f_auto']) {
    // Si c'est déjà une URL Cloudinary
    if (strpos($imageUrl, 'cloudinary.com') !== false) {
        // Appliquer des transformations si demandées
        if (!empty($transformations)) {
            $parts = explode('/upload/', $imageUrl);
            if (count($parts) === 2) {
                $transformString = implode(',', $transformations);
                return $parts[0] . '/upload/' . $transformString . '/' . $parts[1];
            }
        }
        return $imageUrl;
    }
    
    // Si c'est une URL commençant par 'images/'
    if (strpos($imageUrl, 'images/') === 0) {
        $imageName = basename($imageUrl);
        $cloudinaryUrl = getCloudinaryUrl($imageName, $transformations);
        return !empty($cloudinaryUrl) ? $cloudinaryUrl : $imageUrl;
    }
    
    // Si c'est juste un nom de fichier
    if (strpos($imageUrl, '/') === false && strpos($imageUrl, '://') === false) {
        $cloudinaryUrl = getCloudinaryUrl($imageUrl, $transformations);
        return !empty($cloudinaryUrl) ? $cloudinaryUrl : "images/" . $imageUrl;
    }
    
    // Pour tout autre type d'URL
    return $imageUrl;
}

/**
 * Fonction d'affichage d'image qui gère automatiquement Cloudinary
 * 
 * Cette fonction est un remplacement direct pour les balises img dans les vues
 * Elle tente d'utiliser Cloudinary, mais revient à l'image locale si nécessaire
 * 
 * @param string $src Source de l'image (URL ou chemin)
 * @param string $alt Texte alternatif
 * @param array $transformations Transformations Cloudinary
 * @param array $attributes Attributs HTML
 * @return string Tag img HTML
 */
function cngo_img($src, $alt = '', $transformations = ['q_auto'], $attributes = []) {
    // Optimiser l'URL de l'image
    $optimizedSrc = optimizeImageUrl($src, $transformations);
    
    // Construire les attributs HTML
    $htmlAttributes = '';
    foreach ($attributes as $key => $value) {
        $htmlAttributes .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
    }
    
    return '<img src="' . htmlspecialchars($optimizedSrc) . '" alt="' . htmlspecialchars($alt) . '"' . $htmlAttributes . '>';
} 