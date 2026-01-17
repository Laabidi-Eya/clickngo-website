<?php
require_once __DIR__.'/../../Config.php';
require_once __DIR__.'/../../Model/User.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: /Projet%20Web/mvcUtilisateur/View/BackOffice/login/login.php");
    exit;
}

$user = $_SESSION['user'];

function stringToColor($str) {
    $colors = ['#FF6B6B', '#FF8E53', '#6B5B95', '#88B04B', '#F7CAC9', '#92A8D1', '#955251', '#B565A7'];
    $hash = crc32($str);
    return $colors[$hash % count($colors)];
}

function displayProfilePicture($user, $size = 100)
{
    $initial = strtoupper(substr($user['full_name'], 0, 1));
    $color = stringToColor($user['full_name']);

    if (!empty($user['is_verified']) && $user['is_verified'] == 1) {
        $badge = '<img src="/Projet Web/mvcUtilisateur/assets/icons/verified.png" title="Compte vérifié" style="width:18px; height:18px; position:absolute; bottom:-5px; right:-5px;">';
    } else {
        $badge = '<img src="/Projet Web/mvcUtilisateur/assets/icons/not_verified.png" title="Compte non vérifié" style="width:18px; height:18px; position:absolute; bottom:-5px; right:-5px;">';
    }

    if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
        $content = '<img src="' . $user['profile_picture'] . '" style="width:' . $size . 'px;height:' . $size . 'px;border-radius:50%;object-fit:cover;">';
    } else {
        $content = '<div class="avatar-default" style="background-color:' . $color . ';width:' . $size . 'px;height:' . $size . 'px;line-height:' . $size . 'px;text-align:center;border-radius:50%;color:#fff;font-weight:bold;font-size:20px;">' . $initial . '</div>';
    }

    return '<div style="position:relative; display:inline-block;">' . $content . $badge . '</div>';
}

// Traitement de l'upload de photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $uploadDir = 'uploads/profiles/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($extension, $allowedTypes)) {
        $filename = uniqid() . '.' . $extension;
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
            $db = Config::getConnexion();
            $userModel = new User();
            $userModel->setIdUser($user['id_user']);
            $userModel->setProfilePicture($targetFile);

            if ($userModel->updateProfilePicture($db)) {
                $_SESSION['user']['profile_picture'] = $targetFile;
                $_SESSION['success'] = "Photo de profil mise à jour avec succès !";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour en base de données.";
            }
        } else {
            $_SESSION['error'] = "Erreur lors du téléchargement du fichier.";
        }
    } else {
        $_SESSION['error'] = "Format de fichier non supporté. Formats acceptés : JPG, PNG, GIF.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Mon Profil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .profile-picture-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .profile-picture-container:hover::after {
            content: "Changer";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.6);
            color: white;
            text-align: center;
            padding: 5px;
            border-radius: 0 0 50% 50%;
            font-size: 12px;
        }
        .profile-photo, .avatar-default {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #8e44ad;
        }
        .hidden { display: none; }
        .form-group {
            margin-top: 20px;
            text-align: left;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .actions {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .btn {
            background: #8e44ad;
            color: white;
            padding: 12px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn:hover { background: #732d91; }
        small { font-size: 12px; color: #777; }
        a.return-link {
            color: #8e44ad;
            text-decoration: underline;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="profile.php" class="return-link">⬅ Retour au profil</a>
    <h2 style="color:#8e44ad;">Modifier Mon Profil</h2>

    <form id="profileForm" action="/Projet%20Web/mvcUtilisateur/Controller/AccountController.php?action=update" method="post" enctype="multipart/form-data">
        <label class="profile-picture-container">
            <?= displayProfilePicture($user, 100); ?>
            <input type="file" id="profile-picture-input" name="profile_picture" accept="image/*" class="hidden">
        </label>

        <div class="form-group">
            <label>Nom complet</label>
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Numéro de téléphone</label>
            <input type="text" id="phoneField" name="num_user" value="<?= htmlspecialchars($user['num_user']) ?>" required>
            <small>Le changement de numéro nécessite une confirmation par mot de passe.</small>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" id="emailField" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            <small>Le changement d’email nécessite une confirmation par mot de passe.</small>
        </div>

        <div class="actions">
            <button type="button" class="btn" onclick="confirmPasswordBeforeSubmit()">Enregistrer</button>
            <button type="button" class="btn" onclick="changePassword()">Changer mot de passe</button>
        </div>
    </form>
</div>

<script>
document.getElementById('profile-picture-input').addEventListener('change', function () {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById('previewPhoto');
            const initialCircle = document.getElementById('initialCircle');
            if (preview) {
                preview.src = e.target.result;
            } else if (initialCircle) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'profile-photo';
                img.id = 'previewPhoto';
                initialCircle.replaceWith(img);
            }
        };
        reader.readAsDataURL(this.files[0]);
    }
});

function confirmPasswordBeforeSubmit() {
    const originalEmail = "<?= htmlspecialchars($user['email']) ?>";
    const originalPhone = "<?= htmlspecialchars($user['num_user']) ?>";
    const newEmail = document.getElementById('emailField').value.trim();
    const newPhone = document.getElementById('phoneField').value.trim();

    if (newEmail !== originalEmail || newPhone !== originalPhone) {
        Swal.fire({
            title: 'Confirmez votre mot de passe 🔐',
            input: 'password',
            inputPlaceholder: 'Mot de passe actuel',
            showCancelButton: true,
            confirmButtonText: 'Confirmer',
            confirmButtonColor: '#8e44ad',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                const hiddenField = document.createElement('input');
                hiddenField.type = 'hidden';
                hiddenField.name = 'confirm_password';
                hiddenField.value = result.value;
                document.getElementById('profileForm').appendChild(hiddenField);
                document.getElementById('profileForm').submit();
            }
        });
    } else {
        document.getElementById('profileForm').submit();
    }
}

function changePassword() {
    Swal.fire({
        title: 'Changer votre mot de passe',
        html:
            '<input id="old-password" type="password" class="swal2-input" placeholder="Mot de passe actuel">' +
            '<input id="new-password" type="password" class="swal2-input" placeholder="Nouveau mot de passe">' +
            '<input id="confirm-new-password" type="password" class="swal2-input" placeholder="Confirmer le mot de passe">',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Confirmer',
        cancelButtonText: 'Annuler',
        denyButtonText: 'Mot de passe oublié ?',
        focusConfirm: false,
        preConfirm: () => {
            const oldPassword = document.getElementById('old-password').value.trim();
            const newPassword = document.getElementById('new-password').value.trim();
            const confirmPassword = document.getElementById('confirm-new-password').value.trim();

            if (!oldPassword || !newPassword || !confirmPassword) {
                Swal.showValidationMessage('Tous les champs sont obligatoires');
            } else if (newPassword !== confirmPassword) {
                Swal.showValidationMessage('Les mots de passe ne correspondent pas');
            } else {
                const form = document.getElementById('profileForm');
                const hiddenOld = document.createElement('input');
                hiddenOld.type = 'hidden';
                hiddenOld.name = 'old_password';
                hiddenOld.value = oldPassword;

                const hiddenNew = document.createElement('input');
                hiddenNew.type = 'hidden';
                hiddenNew.name = 'new_password';
                hiddenNew.value = newPassword;

                form.appendChild(hiddenOld);
                form.appendChild(hiddenNew);
                form.submit();
            }
        }
    }).then((result) => {
        if (result.isDenied) {
            window.location.href = "/Projet Web/mvcUtilisateur/View/FrontOffice/reset_request.php";
        }
    });
}
</script>
</body>
</html>