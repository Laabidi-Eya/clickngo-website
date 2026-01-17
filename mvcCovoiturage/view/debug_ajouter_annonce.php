<?php
// debug_ajouter_annonce.php
// Script de débogage pour tester l'ajout d'une annonce dans la base de données

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurer le fichier de log
ini_set('log_errors', 1);
ini_set('error_log', 'debug_ajouter_annonce.log');

// Inclure les fichiers nécessaires
require_once '../config.php';
require_once '../Controller/AnnonceCovoiturageController.php';

try {
    // Étape 1 : Tester la connexion à la base de données
    $pdo = config::getConnexion();
    echo "Connexion à la base de données réussie.\n";
    error_log("Connexion à la base de données réussie.");

    // Étape 2 : Vérifier la structure de la table annonce_covoiturage
    $query = "DESCRIBE annonce_covoiturage";
    $stmt = $pdo->query($query);
    $tableStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Structure de la table annonce_covoiturage :\n";
    print_r($tableStructure);
    error_log("Structure de la table : " . print_r($tableStructure, true));

    // Étape 3 : Créer une instance du contrôleur avec id_user explicite
    $testUserId = 12345; // ID utilisateur à tester
    $controller = new AnnonceCovoiturageController($pdo, $testUserId);
    echo "Contrôleur initialisé avec id_user=$testUserId.\n";
    error_log("Contrôleur initialisé avec id_user=$testUserId.");

    // Étape 4 : Préparer les données de test
    $testData = [
        'prenom_conducteur' => 'John',
        'nom_conducteur' => 'Doe',
        'tel_conducteur' => '12345678',
        'date_depart' => '2025-05-12 10:00:00',
        'lieu_depart' => 'Tunis',
        'lieu_arrivee' => 'Sousse',
        'nombre_places' => 3,
        'type_voiture' => 'Sedan',
        'prix_estime' => 20.00,
        'description' => 'Voyage confortable',
        'submit' => '' // Simuler le champ submit
    ];
    echo "Données de test préparées :\n";
    print_r($testData);
    error_log("Données de test : " . print_r($testData, true));

    // Étape 5 : Appeler la méthode ajouterAnnonce
    $result = $controller->ajouterAnnonce($testData);
    echo "Annonce ajoutée avec succès ! Résultat : " . ($result ? 'Succès' : 'Échec') . "\n";
    error_log("Annonce ajoutée avec succès. Résultat : " . ($result ? 'Succès' : 'Échec'));

    // Étape 6 : Vérifier si l'annonce a été insérée
    $query = "SELECT * FROM annonce_covoiturage WHERE prenom_conducteur = :prenom AND nom_conducteur = :nom";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['prenom' => 'John', 'nom' => 'Doe']);
    $annonce = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($annonce) {
        echo "Annonce trouvée dans la base de données :\n";
        print_r($annonce);
        error_log("Annonce trouvée : " . print_r($annonce, true));
    } else {
        echo "Aucune annonce trouvée dans la base de données.\n";
        error_log("Aucune annonce trouvée.");
    }

} catch (PDOException $e) {
    echo "Erreur PDO : " . $e->getMessage() . "\n";
    error_log("Erreur PDO : " . $e->getMessage() . "\nTrace : " . $e->getTraceAsString());
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    error_log("Erreur : " . $e->getMessage() . "\nTrace : " . $e->getTraceAsString());
}

echo "Débogage terminé.\n";
error_log("Débogage terminé.");
?>