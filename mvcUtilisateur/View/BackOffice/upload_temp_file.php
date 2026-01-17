<?php
require_once __DIR__ . '/../../Config.php';
session_start();

if (!isset($_SESSION['user']['id_user'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$uploadDir = __DIR__ . '/../../uploads/chat_files/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $safeName = uniqid('file_', true) . '.' . $extension;
    $destination = $uploadDir . $safeName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
        echo '/uploads/chat_files/' . $safeName;
        exit;
    }
}

http_response_code(400);
exit('Erreur upload');
?>
