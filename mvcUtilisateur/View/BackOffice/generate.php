<?php
header('Content-Type: application/json');

// Récupérer les données POST
$data = json_decode(file_get_contents('php://input'), true);

// Valider les données
if (empty($data['key']) || empty($data['prompt'])) {
    echo json_encode(['status' => 'error', 'message' => 'Clé API et prompt sont requis']);
    exit;
}

$payload = [
    "key" => $data['key'],
    "prompt" => $data['prompt'],
    "negative_prompt" => $data['negative_prompt'] ?? 'bad quality',
    "width" => $data['width'] ?? 512,
    "height" => $data['height'] ?? 512,
    "safety_checker" => false,
    "seed" => null,
    "samples" => 1,
    "base64" => false,
    "webhook" => null,
    "track_id" => null
];

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://modelslab.com/api/v6/realtime/text2img',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
]);

$response = curl_exec($curl);
curl_close($curl);

// Retourner la réponse telle quelle
echo $response;