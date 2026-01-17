<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../Config.php';
require_once __DIR__ . '/../../Model/User.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$avatarUrl = $input['avatar_url'] ?? '';

if (empty($avatarUrl)) {
    echo json_encode(['success' => false, 'error' => 'URL d\'avatar manquante']);
    exit;
}

// ✅ Fonction améliorée : copy() + extension svg forcée
function saveRemoteAvatar(string $url): ?string {
    $relativePath = 'uploads/profiles/';
    $targetDir = realpath(__DIR__ . '/../../View/FrontOffice/uploads/profiles/');
    
    if (!$targetDir) {
        mkdir(__DIR__ . '/../../View/FrontOffice/uploads/profiles/', 0777, true);
        $targetDir = realpath(__DIR__ . '/../../View/FrontOffice/uploads/profiles/');
    }    

    $filename = 'avatar_' . uniqid('', true) . '.svg';
    $fullPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

    // ⚠️ Utilise CURL pour fiabilité maximale
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $imageData !== false) {
        file_put_contents($fullPath, $imageData);
        return $relativePath . $filename;
    }

    return null;
}


$savedPath = saveRemoteAvatar($avatarUrl);

if (!$savedPath) {
    echo json_encode(['success' => false, 'error' => 'Téléchargement échoué']);
    exit;
}

try {
    $db = Config::getConnexion();
    $userModel = new User();
    $userModel->setIdUser($_SESSION['user']['id_user']);
    $userModel->setProfilePicture($savedPath);

    if ($userModel->updateProfilePicture($db)) {
        $_SESSION['user']['profile_picture'] = $savedPath;
        echo json_encode(['success' => true, 'path' => $savedPath]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Échec de mise à jour en base']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}
