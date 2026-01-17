<?php
require_once __DIR__ . '/../../Config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Config::getConnexion();
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];

    $stmt = $db->prepare('DELETE FROM chat_messages WHERE id = :id');
    $stmt->execute(['id' => $id]);

    echo 'Message supprimé';
}
?>
