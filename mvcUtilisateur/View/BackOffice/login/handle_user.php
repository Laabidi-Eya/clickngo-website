<?php
session_start();
include '../../../Controller/UserController.php'; // adapter selon l'organisation de tes fichiers

$userC = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'register') {
        // Exemple de traitement d'inscription
        $fullName = $_POST['full_name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Enregistre dans la base de données (à adapter à ton projet)
        // ...

        header("Location: login.php?success=1");
        exit;
    } elseif ($_POST['action'] === 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];
    
        if ($userC->login($email, $password)) {
            header("Location: login.php?success=1");
            exit;
        } else {
            header("Location: login.php?error=1");
            exit;
        }
    }
}

?>
