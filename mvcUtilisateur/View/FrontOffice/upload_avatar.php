<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../Config.php';
require_once __DIR__ . '/../../Model/User.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecté']);
    exit;
}

if (!isset($_FILES['photo'])) {
    echo json_encode(['success' => false, 'error' => 'Aucun fichier reçu']);
    exit;
}

$photo = $_FILES['photo'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
$extension = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
$mime = mime_content_type($photo['tmp_name']);

if (!in_array($mime, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Type de fichier non supporté']);
    exit;
}

$uploadDir = __DIR__ . '/../../View/FrontOffice/uploads/profiles/';
if (!$uploadDir) {
    mkdir(__DIR__ . '/../View/FrontOffice/uploads/profiles/', 0777, true);
    $uploadDir = __DIR__ . '/../../View/FrontOffice/uploads/profiles/';
}

$filename = 'avatar_' . uniqid('', true) . '.' . $extension;
$fullPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
$relativePath = 'uploads/profiles/' . $filename;

if (!move_uploaded_file($photo['tmp_name'], $fullPath)) {
    echo json_encode(['success' => false, 'error' => 'Échec du transfert']);
    exit;
}

// ✅ Mise à jour DB
try {
    $db = Config::getConnexion();
    $user = new User();
    $user->setIdUser($_SESSION['user']['id_user']);
    $user->setProfilePicture($relativePath);

    if ($user->updateProfilePicture($db)) {
        $_SESSION['user']['profile_picture'] = $relativePath;
        echo json_encode(['success' => true, 'path' => $relativePath]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur BDD']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
