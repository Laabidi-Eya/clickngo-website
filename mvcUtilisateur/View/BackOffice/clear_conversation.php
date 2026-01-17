<?php
require_once __DIR__ . '/../../Config.php'; // adapte le chemin selon ton projet
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Config::getConnexion();
        $stmt = $db->prepare('DELETE FROM chat_messages');
        $stmt->execute();
        echo 'Conversation vidée avec succès.';
    } catch (Exception $e) {
        http_response_code(500);
        echo 'Erreur serveur : ' . $e->getMessage();
    }
} else {
    http_response_code(405);
    echo 'Méthode non autorisée.';
}
?>
