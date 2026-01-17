<?php
header('Content-Type: application/json');

// Vérification clé API
define('GROQ_API_KEY', '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'); // À remplacer

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userMessage = trim($_POST['message']);
    
    // Debug: Afficher la clé API
    error_log("Clé API: " . (defined('GROQ_API_KEY') ? 'définie' : 'indéfinie'));
    
    $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    $payload = [
        'model' => 'llama3-70b-8192', // Modèle mis à jour
        'messages' => [['role' => 'user', 'content' => $userMessage]],
        'max_tokens' => 500
    ];

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_VERBOSE => true // Mode verbose pour debug
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Journalisation des erreurs
    error_log("Code HTTP: $httpCode, Erreur: $error, Réponse: $response");

    if ($response === false) {
        echo json_encode(['error' => "Erreur cURL: $error"]);
    } elseif ($httpCode !== 200) {
        echo json_encode(['error' => "Erreur API (HTTP $httpCode)"]);
    } else {
        $data = json_decode($response, true);
        $reply = $data['choices'][0]['message']['content'] ?? 'Réponse inattendue';
        echo json_encode(['reply' => trim($reply)]);
    }
} else {
    echo json_encode(['error' => 'Méthode non autorisée']);
}
?>