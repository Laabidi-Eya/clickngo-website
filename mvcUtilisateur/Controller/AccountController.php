<?php
session_start();
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../Model/User.php';

if (!isset($_GET['action'])) {
    header("Location: /mvcUtilisateur/View/FrontOffice/index.php");
    exit;
}







$action = $_GET['action'];
$db = Config::getConnexion();

// --- SUPPRESSION DE COMPTE ---
if ($action === 'delete') {
    if (!isset($_SESSION['user'])) {
        header("Location: /mvcUtilisateur/View/FrontOffice/index.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['error'] = "Requête invalide.";
        header("Location: /mvcUtilisateur/View/FrontOffice/profile.php");
        exit;
    }

    $user = $_SESSION['user'];
    $email = $user['email'];
    $currentPassword = $_POST['current_password'] ?? '';
    $confirmDelete = isset($_POST['confirm_delete']);

    if (!$confirmDelete) {
        $_SESSION['error'] = "Vous devez confirmer la suppression de votre compte.";
        header("Location: /mvcUtilisateur/View/FrontOffice/profile.php");
        exit;
    }

    if (!User::verifyPassword($db, $email, $currentPassword)) {
        $_SESSION['error'] = "Mot de passe incorrect.";
        header("Location: /mvcUtilisateur/View/FrontOffice/profile.php");
        exit;
    }

    if (User::deleteByEmail($db, $email)) {
        session_unset();
        session_destroy();
        header("Location: /mvcUtilisateur/View/FrontOffice/index.php?account_deleted=1");
    } else {
        $_SESSION['error'] = "Une erreur est survenue lors de la suppression du compte.";
        header("Location: /mvcUtilisateur/View/FrontOffice/profile.php");
    }
    exit;
}

// --- MISE À JOUR DU PROFIL ---
if ($action === 'update') {
    if (!isset($_SESSION['user'])) {
        header("Location: /mvcUtilisateur/View/FrontOffice/index.php");
        exit;
    }

    $user = $_SESSION['user'];
    $userId = $user['id_user'];
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $numUser = $_POST['num_user'];
    $originalEmail = $user['email'];
    $profilePicturePath = $user['profile_picture'] ?? null;

    // Récupérer mot de passe actuel (depuis la base)
    $passwordHash = User::getPasswordHashById($db, $userId);

    $updateEmail = false;
    $updatePassword = false;
    $newPassword = '';

    // --- Vérification email
    if ($email !== $originalEmail) {
        if (empty($_POST['confirm_password'])) {
            $_SESSION['error'] = "Mot de passe requis pour changer l'email.";
            header("Location: /mvcUtilisateur/View/FrontOffice/edit_profile.php");
            exit;
        }
        if (!User::verifyPassword($db, $originalEmail, $_POST['confirm_password'])) {
            $_SESSION['error'] = "Mot de passe incorrect pour changer l'email.";
            header("Location: /mvcUtilisateur/View/FrontOffice/edit_profile.php");
            exit;
        }
        $updateEmail = true;
    }

    // --- Vérification mot de passe
    if (!empty($_POST['old_password']) && !empty($_POST['new_password'])) {
        if (!User::verifyPassword($db, $originalEmail, $_POST['old_password'])) {
            $_SESSION['error'] = "Mot de passe actuel incorrect.";
            header("Location: /mvcUtilisateur/View/FrontOffice/edit_profile.php");
            exit;
        }
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $updatePassword = true;
    }

    // --- Upload de la photo
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../View/FrontOffice/uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $tmpName = $_FILES['profile_picture']['tmp_name'];
        $fileName = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $destination = $uploadDir . $fileName;

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = "Format non autorisé.";
            header("Location: /mvcUtilisateur/View/FrontOffice/edit_profile.php");
            exit;
        }

        if (move_uploaded_file($tmpName, $destination)) {
            $profilePicturePath = '/mvcUtilisateur/View/FrontOffice/uploads/profiles/' . $fileName;
        }
    }

    // --- Mise à jour en base
    $userModel = new User();
    $userModel->setIdUser($userId);
    $userModel->setFullName($fullName);
    $userModel->setNumUser($numUser);
    $userModel->setProfilePicture($profilePicturePath);
    $userModel->setEmail($updateEmail ? $email : $originalEmail);
    $userModel->setPassword($updatePassword ? $newPassword : $passwordHash);

    if ($userModel->updateUserInfo($db)) {
        $_SESSION['user']['full_name'] = $fullName;
        $_SESSION['user']['num_user'] = $numUser;
        $_SESSION['user']['profile_picture'] = $profilePicturePath;
        $_SESSION['user']['email'] = $updateEmail ? $email : $originalEmail;
        $_SESSION['user']['password'] = $updatePassword ? $newPassword : $passwordHash;
        $_SESSION['success'] = "Profil mis à jour avec succès !";
    } else {
        $_SESSION['error'] = "Erreur lors de la mise à jour.";
    }

    header("Location: /mvcUtilisateur/View/FrontOffice/profile.php");
    exit;
}

