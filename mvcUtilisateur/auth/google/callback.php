<?php
// Chemin absolu normalisé pour Windows
$autoloadPath = realpath(__DIR__ . '/../../vendor/autoload.php');

if (!$autoloadPath) {
    die('Erreur : Impossible de trouver autoload.php. Exécutez "composer install".');
}

require_once $autoloadPath;

session_start();

$provider = new League\OAuth2\Client\Provider\Google([
    'clientId'     => '970041561502-a9n12c087236p4v4ravn7bkju2ld0iodkh.apps.googleusercontent.com',
    'clientSecret' => 'GOCSPX-HW6BHWHL5NPSYVLNEHa9iLq1tix',
    'redirectUri'  => 'http://localhost/mvcUtilisateur/auth/google/callback',
]);

// Génération du state s'il n'existe pas
if (empty($_SESSION['oauth2state'])) {
    $_SESSION['oauth2state'] = bin2hex(random_bytes(16));
}

if (!isset($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => ['openid', 'email', 'profile'], // Scopes nécessaires
        'state' => $_SESSION['oauth2state']
    ]);
    header('Location: ' . $authUrl);
    exit;
}

// Vérification du state
if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Erreur de sécurité: State invalide');
}