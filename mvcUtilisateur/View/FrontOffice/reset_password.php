<?php
session_start();
require_once __DIR__ . '/../../Config.php';
require_once __DIR__ . '/../../Model/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['new_password'] ?? '';

    if (!empty($password)) {
        $db = Config::getConnexion();
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Récupération de l'email depuis la session (connecté OU invité)
        $email = $_SESSION['user']['email'] ?? $_SESSION['reset_email'] ?? null;

        if ($email && User::updatePasswordByEmail($db, $email, $hashed)) {

            // ✅ Si c'est un utilisateur invité (non connecté)
            if (!isset($_SESSION['user']) && isset($_SESSION['reset_email'])) {
                unset($_SESSION['reset_email']); // supprimer l'email temporaire
            }

            // ✅ Si utilisateur connecté : garder la session (accès à edit_profile possible)
            echo "done";
        } else {
            echo "Erreur lors de la mise à jour";
        }
    } else {
        echo "Données invalides";
    }
}
?>
