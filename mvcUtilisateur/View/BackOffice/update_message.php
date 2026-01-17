<?php
require_once __DIR__ . '/../../Config.php'; // ajuste si nécessaire
require_once __DIR__ . '/ProfanityFilter.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Config::getConnexion();

        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $text = trim($data['text'] ?? '');

        if (empty($id) || $text === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID ou texte manquant.']);
            exit();
        }

        // 🔒 Filtrage anti-gros mots
        $badWords = ProfanityFilter::getListeBadWords();
        $filteredText = ProfanityFilter::filtrerTexteAvance($text, $badWords);

        $stmt = $db->prepare('UPDATE chat_messages SET message = :text WHERE id = :id');
        $stmt->execute([
            'text' => $filteredText,
            'id' => $id
        ]);

        // ✅ Réponse JSON claire pour JS
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
}
?>
<script>
    function saveEditedMessage(messageId) {
  const messageLine = document.querySelector(`[data-id='${messageId}']`);
  const input = messageLine.querySelector('.edit-input');
  const newText = input.value.trim();

  if (newText !== "") {
    fetch('update_message.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: messageId, text: newText })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload(); // ✅ Recharge immédiate
      } else {
        alert("Erreur lors de la mise à jour.");
      }
    })
    .catch(error => console.error('Erreur modification:', error));
  }
}

</script>