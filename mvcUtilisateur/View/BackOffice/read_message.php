<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data['text'])) {
        $text = escapeshellarg($data['text']); // sécuriser le texte
        // Appeler un script Python (pyttsx3) localement
        shell_exec("python C:/xampp/htdocs/Projet Web/mvcUtilisateur/PythonScripts/text_to_speech.py $text");

        echo "Lu: " . $data['text'];
    } else {
        echo "Aucun texte reçu.";
    }
} else {
    echo "Méthode non autorisée.";
}
?>
