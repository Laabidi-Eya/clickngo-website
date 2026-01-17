<?php
session_start();
require_once '../../../Controller/UserController.php';

// Activer l'affichage des erreurs pour le debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function debug_log($message) {
    file_put_contents('face_login_debug.log', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

debug_log("=== Début du test de reconnaissance faciale ===");

try {
    debug_log("Initialisation du UserController");
    $userController = new UserController();
    
    debug_log("Appel de faceLogin()");
    $result = $userController->faceLogin();
    
    debug_log("Résultat: " . json_encode($result));
    echo "<h1>Succès!</h1>";
    echo "<pre>" . print_r($result, true) . "</pre>";
    
} catch (Exception $e) {
    debug_log("ERREUR: " . $e->getMessage());
    echo "<h1>Erreur</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h2>Debug:</h2>";
    echo "<pre>Vérifiez le fichier face_login_debug.log pour les détails</pre>";
}

debug_log("=== Fin du test ===");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Reconnaissance Faciale</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>Options de test</h2>
    
    <form method="post" action="test_face_login.php">
        <button type="submit" name="action" value="test">Tester à nouveau</button>
    </form>
    
    <h2>Vérifications manuelles</h2>
    <ol>
        <li>Ouvrez CMD et exécutez: 
            <pre>python "C:/xampp/htdocs/Projet Web/mvcUtilisateur/View/BackOffice/login/facial_recognition.py"</pre>
        </li>
        <li>Vérifiez que le dossier existe: 
            <pre>C:/xampp/htdocs/face/database/</pre>
        </li>
        <li>Vérifiez les permissions sur:
            <ul>
                <li>Le script Python</li>
                <li>Le dossier database</li>
                <li>Python.exe</li>
            </ul>
        </li>
    </ol>
    
    <h2>Logs PHP</h2>
    <pre><?php 
        if(file_exists('face_login_debug.log')) {
            echo htmlspecialchars(file_get_contents('face_login_debug.log'));
        } else {
            echo "Aucun log trouvé. Vérifiez les permissions d'écriture.";
        }
    ?></pre>
</body>
</html>