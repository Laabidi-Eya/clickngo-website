<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $imageUrl = $data['image_url'] ?? null;

    if (!$imageUrl) {
        echo json_encode(['error' => 'URL non fournie.']);
        exit;
    }

    $filename = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_FILENAME) . '.jpg';
    $saveDir = __DIR__ . "/images_unsplash/";
    if (!file_exists($saveDir)) {
        mkdir($saveDir, 0777, true);
    }

    $savePath = $saveDir . $filename;

    try {
        $imageData = file_get_contents($imageUrl);
        file_put_contents($savePath, $imageData);
        echo json_encode(['message' => "✅ Image enregistrée avec succès."]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erreur lors du téléchargement : ' . $e->getMessage()]);
    }
}
?>
