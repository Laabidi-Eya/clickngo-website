<?php
// Script pour vérifier et corriger la structure de la base de données
require_once __DIR__ . '/Database.php';

header('Content-Type: text/plain; charset=utf-8');
echo "Vérification et correction de la structure de la base de données...\n\n";

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Activer l'affichage des erreurs
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Vérifier si la table activities existe
    $checkActivities = $conn->query("SHOW TABLES LIKE 'activities'");
    
    if ($checkActivities->rowCount() === 0) {
        echo "La table 'activities' n'existe pas. Création...\n";
        $createActivities = "CREATE TABLE activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2),
            image VARCHAR(255),
            category VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $conn->exec($createActivities);
        echo "Table 'activities' créée avec succès.\n";
    } else {
        echo "La table 'activities' existe déjà.\n";
    }
    
    // 2. Vérifier si la table reviews existe
    $checkReviews = $conn->query("SHOW TABLES LIKE 'reviews'");
    
    if ($checkReviews->rowCount() === 0) {
        echo "La table 'reviews' n'existe pas. Création...\n";
        $createReviews = "CREATE TABLE reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            activity_id INT,
            activity_name VARCHAR(255) NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            rating INT NOT NULL,
            comment TEXT NOT NULL,
            image_path VARCHAR(255),
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (activity_id)
        )";
        
        $conn->exec($createReviews);
        echo "Table 'reviews' créée avec succès.\n";
    } else {
        echo "La table 'reviews' existe déjà.\n";
    }
    
    // 3. Vérifier la structure de la table reviews et ajuster si nécessaire
    try {
        // Vérifier si la clé étrangère existe
        $checkFK = $conn->query("
            SELECT * 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = 'clickngo_db' 
            AND TABLE_NAME = 'reviews' 
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        
        // Si la clé étrangère existe, la supprimer
        if ($checkFK->rowCount() > 0) {
            echo "Suppression de la clé étrangère existante...\n";
            $constraint = $checkFK->fetch(PDO::FETCH_ASSOC);
            $constraintName = $constraint['CONSTRAINT_NAME'];
            
            $conn->exec("ALTER TABLE reviews DROP FOREIGN KEY $constraintName");
            echo "Clé étrangère supprimée.\n";
        }
        
        // Ajouter une nouvelle clé étrangère avec ON DELETE SET NULL
        echo "Ajout d'une nouvelle clé étrangère avec ON DELETE SET NULL...\n";
        $conn->exec("
            ALTER TABLE reviews 
            ADD CONSTRAINT fk_reviews_activity_id 
            FOREIGN KEY (activity_id) 
            REFERENCES activities(id) 
            ON DELETE SET NULL 
            ON UPDATE CASCADE
        ");
        echo "Nouvelle clé étrangère ajoutée avec succès.\n";
    } catch (PDOException $e) {
        echo "Erreur lors de la modification de la clé étrangère: " . $e->getMessage() . "\n";
        echo "La table reviews sera utilisée sans clé étrangère.\n";
    }
    
    // 4. Si la table reviews est vide, insérer des données de test
    $checkReviewsData = $conn->query("SELECT COUNT(*) FROM reviews");
    $reviewCount = $checkReviewsData->fetchColumn();
    
    if ($reviewCount == 0) {
        echo "Aucun avis trouvé. Ajout de données de test...\n";
        
        // Insérer une activité de test si activities est vide
        $checkActivitiesData = $conn->query("SELECT COUNT(*) FROM activities");
        $activityCount = $checkActivitiesData->fetchColumn();
        
        if ($activityCount == 0) {
            $conn->exec("INSERT INTO activities (name, description, category) VALUES 
                ('Atelier de cuisine locale', 'Découvrez les saveurs locales', 'team-building'),
                ('Safari en 4x4', 'Aventure en nature', 'outdoor'),
                ('Course d\'orientation', 'Challenge d\'équipe en forêt', 'team-building')
            ");
            echo "Activités de test ajoutées.\n";
        }
        
        // Récupérer la première activité
        $getActivity = $conn->query("SELECT id, name FROM activities LIMIT 1");
        $activity = $getActivity->fetch(PDO::FETCH_ASSOC);
        
        if ($activity) {
            $activityId = $activity['id'];
            $activityName = $activity['name'];
            
            $conn->exec("INSERT INTO reviews (activity_id, activity_name, customer_name, customer_email, rating, comment, status) VALUES 
                ($activityId, '$activityName', 'Client Test', 'test@example.com', 5, 'Super expérience !', 'approved'),
                ($activityId, '$activityName', 'Autre Client', 'autre@example.com', 4, 'Très bon moment', 'approved')
            ");
            echo "Avis de test ajoutés.\n";
        }
    } else {
        echo "Il y a déjà $reviewCount avis dans la base de données.\n";
    }
    
    echo "\nStructure de la base de données vérifiée et corrigée avec succès.\n";
    echo "Vous pouvez maintenant ajouter des avis via l'application.\n";
    
} catch (PDOException $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}
?> 