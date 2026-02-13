<?php
// Chemin absolu normalisé pour Windows
$autoloadPath = realpath(dirname(__DIR__, 2) . '/vendor/autoload.php');

if (!$autoloadPath) {
    die('Erreur : Impossible de trouver autoload.php. Exécutez "composer install"');
}

require_once $autoloadPath;

session_start();

$provider = new League\OAuth2\Client\Provider\Facebook([
    'clientId'          => '1177061883901068',
    'clientSecret'      => '6e82fc857150f69d6a241600d0b1f7c9',
    'redirectUri'       => 'http://localhost/mvcUtilisateur/auth/facebook/callback',
    'graphApiVersion'   => 'v19.0',
]);

// Génération du state s'il n'existe pas
if (empty($_SESSION['oauth2state'])) {
    $_SESSION['oauth2state'] = bin2hex(random_bytes(16));
}

if (!isset($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => ['email', 'public_profile'],
        'state' => $_SESSION['oauth2state'] // Important: Ajout du state
    ]);
    header('Location: ' . $authUrl);
    exit;
}

// Vérification CRUCIALE du state
if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Erreur de sécurité: State invalide');
}

// Si pas de code, on redirige vers Facebook
if (!isset($_GET['code'])) {
    $options = [
        'scope' => ['email', 'public_profile'] // Permissions demandées
    ];
    $authUrl = $provider->getAuthorizationUrl($options);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;
}