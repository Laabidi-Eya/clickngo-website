<?php
// Chemin vers le fichier categorie.php
$file = __DIR__ . '/view/front office/categorie.php';

// Lire le contenu du fichier
$content = file_get_contents($file);

// Chercher où commence le footer
$footerStartPos = strpos($content, '<!-- Nouveau Footer -->');
$footerEndPos = strpos($content, '</html>') + 7;

if ($footerStartPos !== false) {
    // Remplacer tout le contenu depuis le début du footer jusqu'à la fin du fichier
    $newContent = substr($content, 0, $footerStartPos);
    $newContent .= "\n</body>\n</html>";
    
    // Écrire le contenu modifié dans le fichier
    if (file_put_contents($file, $newContent)) {
        echo "Le footer a été supprimé avec succès.\n";
    } else {
        echo "Erreur lors de l'enregistrement du fichier.\n";
    }
} else {
    echo "Le footer n'a pas été trouvé dans le fichier.\n";
}
?> 