<?php
require_once '../../config.php';

if (isset($_GET['id_conducteur'])) {
    try {
        $pdo = config::getConnexion();
        
        // Restaurer l'annonce en changeant son statut
        $query = "UPDATE annonce_covoiturage 
                 SET statut = 'active', 
                     date_modification = NOW() 
                 WHERE id_conducteur = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_GET['id_conducteur']]);
        
        // Redirection avec message de succès
        header("Location: annonces.php?success=1&filter=archived");
        exit;
        
    } catch (PDOException $e) {
        // Redirection avec message d'erreur
        header("Location: annonces.php?error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: annonces.php");
    exit;
}
?>