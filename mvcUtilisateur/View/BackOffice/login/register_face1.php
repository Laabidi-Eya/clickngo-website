<?php
session_start();
require_once 'C:/xampp/htdocs/Projet Web/mvcUtilisateur/config.php';

// Récupérer l'ID utilisateur depuis l'URL ou la session
$user_id = isset($_GET['user_id']) ? filter_var($_GET['user_id'], FILTER_VALIDATE_INT) : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

// Vérifier si l'ID utilisateur est valide
if (!$user_id) {
    header('Location: login.php?error=' . urlencode('ID utilisateur non valide ou session expirée.'));
    exit;
}

// Chemin vers le script Python d'enregistrement
define('PYTHON_SCRIPT', 'register_face.py');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $python_cmd = 'python ' . escapeshellarg(PYTHON_SCRIPT) . ' ' . escapeshellarg($user_id) . ' 2>&1';
    $output = shell_exec($python_cmd);
    
    // Journaliser la sortie pour le débogage
    file_put_contents('debug.log', "Register face output: $output\n", FILE_APPEND);
    
    if (strpos($output, 'success') !== false || strpos($output, 'Visage enregistre') !== false) {
        $success = 'Votre visage a été enregistré avec succès pour la connexion Face ID';
        // Rediriger vers la page de connexion après un enregistrement réussi
        header('Location: login.php?success=' . urlencode($success));
        exit;
    } else {
        $error = 'Erreur lors de l\'enregistrement du visage: ' . htmlspecialchars($output);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrement Face ID</title>
    <style>
        /* Le style reste inchangé */
    </style>
</head>
<body>
    <div class="container">
        <h1>Enregistrement Face ID</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="instructions">
            <p>Pour enregistrer votre visage :</p>
            <ol>
                <li>Assurez-vous d'être dans un endroit bien éclairé</li>
                <li>Positionnez-vous face à la caméra</li>
                <li>Votre visage doit être clairement visible</li>
                <li>Cliquez sur le bouton ci-dessous pour démarrer l'enregistrement</li>
            </ol>
        </div>
        
        <form method="post">
            <button type="submit">Commencer l'enregistrement</button>
        </form>
        
        <a href="login.php" class="back-link">Retour à la connexion</a>
    </div>
</body>
</html>