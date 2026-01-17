<?php
require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../Controller/UserController.php';
session_start();

// Vérification initiale du state
if (empty($_SESSION['oauth2state']) || empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Erreur de sécurité: State invalide ou session expirée');
}

$provider = new League\OAuth2\Client\Provider\Facebook([
    'clientId'          => '1177061883901068',
    'clientSecret'      => '6e82fc857150f69d6a241600d0b1f7c9',
    'redirectUri'       => 'http://localhost/mvcUtilisateur/auth/facebook/callback',
    'graphApiVersion'   => 'v19.0',
]);

try {
    // 1. Récupère le token
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);
    
    // 2. Récupère les infos utilisateur
    $user = $provider->getResourceOwner($token);
    $userData = $user->toArray();
    
    // 3. Formatage des données
    $facebookUser = [
        'facebook_id' => $userData['id'],
        'email' => $userData['email'] ?? null,
        'full_name' => $userData['name'] ?? 'Utilisateur Facebook',
        'avatar' => $user->getPictureUrl() ?? null
    ];
    
    // 4. Traitement via votre UserController
    $userController = new UserController();
    $userController->handleSocialLogin($facebookUser, 'facebook');
    
    // 5. Redirection vers la page sécurisée
    header('Location: ../../../View/BackOffice/login/handle_user.php');
    exit;

} catch (Exception $e) {
    // En cas d'erreur
    error_log('Facebook Login Error: '.$e->getMessage());
    header('Location: ../../../View/BackOffice/login/login.php?error=facebook');
    exit;
}