<?php
require_once __DIR__ . '/Database.php';

// Charger la correspondance images locales → Cloudinary
$json = file_get_contents(__DIR__ . '/cloudinary_images.json');
$images = json_decode($json, true);

// Créer un tableau associatif insensible à la casse et à l'extension
$map = [];
foreach ($images as $img) {
    $map[strtolower($img['original'])] = $img['cloudinary_url'];
}

// Connexion à la base
$db = (new Database())->connect();

// Récupérer toutes les activités
$sql = "SELECT id, image FROM activities";
$stmt = $db->query($sql);
$rows = $stmt->fetchAll();

$updated = 0;
foreach ($rows as $row) {
    $localPath = strtolower(basename($row['image']));
    if (isset($map[$localPath])) {
        $cloudUrl = $map[$localPath];
        // Mettre à jour la base
        $update = $db->prepare("UPDATE activities SET image = ? WHERE id = ?");
        $update->execute([$cloudUrl, $row['id']]);
        $updated++;
        echo "Activité ID {$row['id']} : $localPath → $cloudUrl<br>";
    } else {
        echo "Non trouvé : {$row['image']} (basename: $localPath)<br>";
    }
}
echo "<br><b>$updated images mises à jour dans la base.</b>"; 