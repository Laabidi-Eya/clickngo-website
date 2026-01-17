<?php
require_once __DIR__ . '/Database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Supprimer la table reviews si elle existe
    $conn->exec("DROP TABLE IF EXISTS reviews");
    
    // Créer la table reviews sans contrainte de clé étrangère
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($createReviews);
    
    // Créer la table activities si elle n'existe pas
    $conn->exec("CREATE TABLE IF NOT EXISTS activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2),
        image VARCHAR(255),
        category VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    echo "Tables réinitialisées avec succès.";
    
} catch (PDOException $e) {
    echo "ERREUR: " . $e->getMessage();
}
?> 