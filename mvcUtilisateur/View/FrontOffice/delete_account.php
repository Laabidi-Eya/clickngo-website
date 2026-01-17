<?php
session_start();

// 1. Vérification de la connexion
if (!isset($_SESSION['user'])) {
    header("Location: /Projet%20Web/mvcUtilisateur/View/BackOffice/login/login.php");
    exit;
}

// 2. Vérification de la confirmation
if (!isset($_POST['confirm_delete']) || $_POST['confirm_delete'] != 1) {
    $_SESSION['error'] = "Action non autorisée";
    header("Location: profile.php");
    exit;
}

// 3. Inclure la classe User
require_once __DIR__.'/../../Model/User.php';

// 4. Connexion à la base de données
require_once __DIR__.'/../../config/database.php';
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de connexion à la base de données";
    header("Location: profile.php");
    exit;
}

// 5. Récupération des données
$email = $_SESSION['user']['email'];
$password = $_POST['current_password'];

try {
    // 6. Vérification du mot de passe
    if (!User::verifyPassword($db, $email, $password)) {
        $_SESSION['error'] = "Mot de passe incorrect";
        header("Location: profile.php");
        exit;
    }

    // 7. Suppression du compte
    if (User::deleteByEmail($db, $email)) {
        // 8. Déconnexion
        session_unset();
        session_destroy();
        
        // 9. Redirection avec confirmation
        header("Location: /Projet%20Web/index.php?account_deleted=1");
    } else {
        $_SESSION['error'] = "La suppression a échoué";
        header("Location: profile.php");
    }
    exit;

} catch (PDOException $e) {
    error_log("Erreur suppression compte: " . $e->getMessage());
    $_SESSION['error'] = "Erreur technique lors de la suppression";
    header("Location: profile.php");
    exit;
}
?>