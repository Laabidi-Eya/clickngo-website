<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>🧼 Filtrage Intelligent (MultiLangue)</title>
  <style>
    body { font-family: Arial; padding: 30px; max-width: 800px; margin: auto; }
    textarea { width: 100%; height: 120px; font-size: 16px; padding: 10px; }
    button { padding: 10px 20px; font-size: 16px; margin-top: 10px; }
    .result { margin-top: 20px; padding: 15px; background: #f3f3f3; border-left: 4px solid #666; }
  </style>
</head>
<body>

  <h2>🧼 Entrez un texte à filtrer (Français / Arabe / Anglais)</h2>

  <form method="POST">
    <textarea name="texte" placeholder="Ex : f.u.ck wallah merde..." required><?= isset($_POST['texte']) ? htmlspecialchars($_POST['texte']) : '' ?></textarea>
    <br>
    <button type="submit">🔍 Filtrer</button>
  </form>

<?php
function filtrerTexteAvance($texte, $badWords, $censChar = '*') {
    // On sépare le texte en mots + ponctuations
    $mots = preg_split('/(\s+)/u', $texte, -1, PREG_SPLIT_DELIM_CAPTURE);
    
    foreach ($mots as &$mot) {
        // Nettoyer le mot (retirer les caractères spéciaux pour comparer)
        $motNettoyé = preg_replace('/[^\\p{L}\\p{N}]/u', '', $mot); // garde lettres/nombres uniquement

        foreach ($badWords as $motInterdit) {
            if (mb_strtolower($motNettoyé) === mb_strtolower($motInterdit)) {
                // Remplacer dans le mot original par des étoiles uniquement sur les lettres
                $mot = preg_replace('/[\p{L}\p{N}]/u', $censChar, $mot);
                break;
            }
        }
    }

    return implode('', $mots);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['texte'])) {
    $texte = $_POST['texte'];

    $listeBadWords = [
        // 🔹 Français
        'putain', 'merde', 'connard', 'connasse', 'salope', 'enculé', 'encule', 'niquer', 'nique', 'batard',
        'pédé', 'pd', 'fdp', 'ta gueule', 'tg', 'chiant', 'chiotte', 'bordel', 'bite', 'couille', 'chatte',
    
        // 🔹 Anglais
        'fuck', 'fucking', 'fuckyou', 'shit', 'bitch', 'bastard', 'dick', 'asshole', 'slut', 'cunt',
        'motherfucker', 'bullshit', 'jerk', 'retard', 'dumbass',
    

    ];
    

    $texteFiltré = filtrerTexteAvance($texte, $listeBadWords);

    echo "<div class='result'><strong>🧼 Texte filtré :</strong><br><br>" . htmlspecialchars($texteFiltré) . "</div>";
}
?>

</body>
</html>
