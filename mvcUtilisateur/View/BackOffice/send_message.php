<?php
require_once __DIR__ . '/../../Config.php';
require_once __DIR__ . '/ProfanityFilter.php';
session_start();

try {
    if (!isset($_SESSION['user']['id_user'])) {
        http_response_code(401);
        exit('Unauthorized');
    }

    $db = Config::getConnexion();
    $userId = $_SESSION['user']['id_user'];

    $originalMessage = isset($_POST['message']) ? trim($_POST['message']) : '';
    if ($originalMessage === 'undefined') {
        $originalMessage = '';
    }

    $badWords = ProfanityFilter::getListeBadWords();
    $filteredMessage = ProfanityFilter::filtrerTexteAvance($originalMessage, $badWords);

    $filePath = null;

    if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'audio/webm', 'audio/mpeg', 'audio/mp3',
            'video/mp4',
            'application/pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip', 'application/x-zip-compressed',
            'text/plain',
        ];

        $type = $_FILES['file']['type'];

        if (in_array($type, $allowedTypes)) {
            $uploadDir = __DIR__ . '/../../uploads/chat_files/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $safeName = uniqid('file_', true) . '.' . $extension;
            $destination = $uploadDir . $safeName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
                $filePath = 'uploads/chat_files/' . $safeName;
            }
        } else {
            exit('Type de fichier non autorisé');
        }
    }

    // ✅ Corrigé ici : tester sur $originalMessage et enregistrer $filteredMessage
    if ($originalMessage !== '' || $filePath !== null) {
        $stmt = $db->prepare("
            INSERT INTO chat_messages (user_id, message, file_path, created_at, seen_by)
            VALUES (:user_id, :message, :file_path, NOW(), :seen_by)
        ");
        $stmt->execute([
            'user_id'   => $userId,
            'message'   => $filteredMessage,
            'file_path' => $filePath,
            'seen_by'   => $userId
        ]);

        echo "✅ Message enregistré avec filtrage";
    } else {
        echo "❌ Rien à enregistrer";
    }

} catch (PDOException $e) {
    echo "❌ Erreur SQL : " . $e->getMessage();
}
?>
