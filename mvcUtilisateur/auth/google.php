<?php
// Chemin absolu normalisé pour Windows
$autoloadPath = realpath(__DIR__ . '/../vendor/autoload.php');
if (!$autoloadPath) {
    die('Erreur : Impossible de trouver autoload.php. Exécutez "composer install".');
}
require_once $autoloadPath;

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Informations de l'image
$clientID = '970041561502-a9n12c087236p4v4ravn7bkju2ld0iodkh.app.s.googleusercontent.com';
$clientSecret = 'GOCSPX-HW6BHWHL5NPSYVLNEHa9iLq1tix';
$redirectUri = 'http://localhost/mvcUtilisateur/auth/google/callback.php'; // URI corrigé

// Créer un client Google
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

// Générer un state pour la sécurité
if (empty($_SESSION['oauth2state'])) {
    $_SESSION['oauth2state'] = bin2hex(random_bytes(16));
}

// Générer l'URL de connexion Google
$authUrl = $client->createAuthUrl();

// Rediriger l'utilisateur vers Google
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();