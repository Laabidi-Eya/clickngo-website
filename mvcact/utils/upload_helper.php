<?php
/**
 * Fonctions utilitaires pour télécharger des images vers Cloudinary
 * Ce fichier facilite l'intégration de Cloudinary dans le processus d'upload d'images
 */

/**
 * Télécharge une image vers Cloudinary et retourne l'URL
 * 
 * @param array $file Fichier téléchargé ($_FILES['image'])
 * @param string $localPath Chemin local où sauvegarder une copie (facultatif)
 * @return array|false Les informations de l'image téléchargée ou false en cas d'erreur
 */
function uploadToCloudinary($file, $localPath = null) {
    // Charger la configuration Cloudinary
    $config = require __DIR__ . '/../cloudinary_config.php';
    
    // Vérifier si les clés d'API ont été configurées
    if ($config['cloud_name'] === 'VOTRE_CLOUD_NAME' || 
        $config['api_key'] === 'VOTRE_API_KEY' || 
        $config['api_secret'] === 'VOTRE_API_SECRET') {
        error_log("Erreur: Configuration Cloudinary incomplète");
        return false;
    }
    
    // Vérifier le fichier
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        error_log("Erreur: Fichier invalide");
        return false;
    }
    
    try {
        // Construire l'URL de l'API Cloudinary
        $timestamp = time();
        $signature = sha1("timestamp=" . $timestamp . $config['api_secret']);
        $apiUrl = "https://api.cloudinary.com/v1_1/{$config['cloud_name']}/image/upload";
        
        // Préparer les données pour le téléchargement
        $post = [
            'file' => new CURLFile($file['tmp_name']),
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
        
        // Vérifier si cURL a rencontré une erreur
        if ($response === false) {
            $curlError = curl_error($ch);
            error_log("Erreur cURL lors de l'upload vers Cloudinary: " . $curlError);
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        error_log('Réponse Cloudinary: ' . $response);
        
        // Vérifier si la requête a réussi
        if ($httpCode != 200) {
            error_log("Erreur Cloudinary: Code HTTP $httpCode");
            return false;
        }
        
        // Décoder la réponse JSON
        $result = json_decode($response, true);
        
        // Vérifier si la réponse contient une URL
        if (!isset($result['secure_url'])) {
            error_log("Erreur Cloudinary: Pas d'URL dans la réponse");
            return false;
        }
        
        // Sauvegarder également en local si demandé
        if ($localPath) {
            // Générer un nom de fichier unique
            $fileName = time() . '_' . basename($file['name']);
            $targetPath = $localPath . $fileName;
            
            if (!file_exists($localPath)) {
                mkdir($localPath, 0777, true);
            }
            
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                error_log("Erreur lors de la sauvegarde locale de l'image");
                // On continue car l'upload Cloudinary a réussi
            }
        }
        
        // Ajouter l'entrée dans le fichier JSON
        $imageInfo = [
            'original' => basename($file['name']),
            'cloudinary_url' => $result['secure_url'],
            'public_id' => $result['public_id'],
            'uploaded_at' => date('Y-m-d H:i:s')
        ];
        
        // Mettre à jour le fichier JSON
        $jsonFile = __DIR__ . '/../cloudinary_images.json';
        $cloudinaryImages = [];
        
        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            if ($jsonContent) {
                $cloudinaryImages = json_decode($jsonContent, true) ?: [];
            }
        }
        
        $cloudinaryImages[] = $imageInfo;
        if (!file_put_contents($jsonFile, json_encode($cloudinaryImages, JSON_PRETTY_PRINT))) {
            error_log("Erreur lors de l'écriture dans le fichier JSON");
            // On continue car l'upload Cloudinary a réussi
        }
        
        return $imageInfo;
        
    } catch (Exception $e) {
        error_log("Exception lors de l'upload vers Cloudinary: " . $e->getMessage());
        return false;
    }
}

/**
 * Télécharge une image et retourne l'URL Cloudinary
 * 
 * Cette fonction est plus simple à utiliser dans les contrôleurs
 * 
 * @param array $file Fichier téléchargé ($_FILES['image'])
 * @param bool $saveLocal Sauvegarder aussi en local
 * @return string|null URL de l'image téléchargée ou null en cas d'échec
 */
function uploadImage($file, $saveLocal = true) {
    try {
        $localPath = $saveLocal ? __DIR__ . '/../view/front office/images/' : null;
        
        $result = uploadToCloudinary($file, $localPath);
        
        if ($result && isset($result['cloudinary_url'])) {
            return $result['cloudinary_url'];
        } else {
            error_log('Upload Cloudinary a échoué - pas de résultat retourné');
            return null;
        }
    } catch (Exception $e) {
        error_log('Exception dans uploadImage: ' . $e->getMessage());
        return null;
    }
} 