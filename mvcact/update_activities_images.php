<?php
/**
 * Script de mise à jour des images d'activités vers Cloudinary
 * 
 * Ce script met à jour les chemins d'images dans la table 'activities' pour utiliser
 * les URLs Cloudinary au lieu des chemins locaux.
 */

// Charger les fichiers nécessaires
require_once 'utils/cloudinary_helper.php';

// Configuration de la base de données
$dbHost = 'localhost';
$dbName = 'clickngo_db';
$dbUser = 'root';
$dbPass = '';

try {
    // Connexion à la base de données
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer toutes les activités
    $stmt = $db->query("SELECT id, name, image FROM activities");
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>Mise à jour des images d'activités vers Cloudinary</h1>";
    echo "<p>Nombre d'activités trouvées: " . count($activities) . "</p>";
    
    // Récupérer toutes les images Cloudinary
    $cloudinaryImages = getAllCloudinaryImages();
    
    if (empty($cloudinaryImages)) {
        die("<p>Erreur: Aucune image n'a été téléchargée sur Cloudinary.</p>");
    }
    
    $updatedCount = 0;
    $failedCount = 0;
    $skippedCount = 0;
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Activité</th><th>Image actuelle</th><th>Nouvelle image</th><th>Statut</th></tr>";
    
    // Parcourir chaque activité
    foreach ($activities as $activity) {
        $currentImage = $activity['image'];
        $activityId = $activity['id'];
        $activityName = $activity['name'];
        
        // Vérifier si l'image est déjà une URL Cloudinary
        if (strpos($currentImage, 'cloudinary.com') !== false) {
            echo "<tr><td>$activityId</td><td>$activityName</td><td>$currentImage</td><td>$currentImage</td><td>Déjà mis à jour</td></tr>";
            $skippedCount++;
            continue;
        }
        
        // Extraire le nom du fichier d'image
        $imageName = basename($currentImage);
        
        // Rechercher l'URL Cloudinary correspondante
        $cloudinaryUrl = getCloudinaryUrl($imageName, ['q_auto']);
        
        if (empty($cloudinaryUrl)) {
            // Si le nom exact n'est pas trouvé, chercher parmi les images qui contiennent ce nom
            $foundImage = false;
            foreach ($cloudinaryImages as $image) {
                // Si le nom de l'image actuelle est contenu dans l'une des images Cloudinary
                if (strpos($image['original'], $imageName) !== false) {
                    $cloudinaryUrl = $image['cloudinary_url'];
                    $foundImage = true;
                    break;
                }
            }
            
            if (!$foundImage) {
                echo "<tr><td>$activityId</td><td>$activityName</td><td>$currentImage</td><td>Non trouvée</td><td>Échec</td></tr>";
                $failedCount++;
                continue;
            }
        }
        
        // Mettre à jour l'activité avec l'URL Cloudinary
        try {
            $updateStmt = $db->prepare("UPDATE activities SET image = ? WHERE id = ?");
            $updateStmt->execute([$cloudinaryUrl, $activityId]);
            
            echo "<tr><td>$activityId</td><td>$activityName</td><td>$currentImage</td><td>$cloudinaryUrl</td><td>Mise à jour réussie</td></tr>";
            $updatedCount++;
        } catch (Exception $e) {
            echo "<tr><td>$activityId</td><td>$activityName</td><td>$currentImage</td><td>$cloudinaryUrl</td><td>Erreur: " . $e->getMessage() . "</td></tr>";
            $failedCount++;
        }
    }
    
    echo "</table>";
    
    // Afficher le résumé
    echo "<h2>Résumé</h2>";
    echo "<ul>";
    echo "<li>Activités mises à jour: $updatedCount</li>";
    echo "<li>Activités déjà à jour: $skippedCount</li>";
    echo "<li>Échecs: $failedCount</li>";
    echo "</ul>";
    
    echo "<p><a href='index.php' class='btn btn-primary'>Retour à l'accueil</a></p>";
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?> 