<?php
// Script pour supprimer une section dupliquée dans categorie.php

// Chemin du fichier à modifier
$file = __DIR__ . '/view/front office/categorie.php';

// Vérifier si le fichier existe
if (!file_exists($file)) {
    die("Le fichier $file n'existe pas.\n");
}

// Lire le contenu du fichier
$content = file_get_contents($file);

// Définir le pattern à rechercher (la section à supprimer)
$pattern = '/      <button class="nav-arrow next-arrow">❯<\/button>\s+<\/div>\s+\s+<!-- Nouvelle section personnalisée moderne -->\s+<section class="custom-solution-section">.*?<\/section>\s+\s+<!-- Section Témoignages -->/s';

// Définir le remplacement
$replacement = '      <button class="nav-arrow next-arrow">❯</button>
        </div>
        
  <!-- Section Témoignages -->';

// Faire la substitution
$newContent = preg_replace($pattern, $replacement, $content, 1);

// Vérifier si la substitution a eu lieu
if ($newContent !== $content) {
    // Sauvegarder le fichier
    if (file_put_contents($file, $newContent)) {
        echo "La section dupliquée a été supprimée avec succès.\n";
    } else {
        echo "Erreur lors de l'enregistrement du fichier.\n";
    }
} else {
    echo "Aucune section correspondante n'a été trouvée.\n";
}
?> 