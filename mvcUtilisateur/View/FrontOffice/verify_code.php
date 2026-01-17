<?php
session_start();
require_once '../../Config.php';
require_once '../../Model/User.php';

if (!isset($_POST['code']) || !isset($_SESSION['verification_code'])) {
    header("Location: verify_account.php");
    exit;
}

ob_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification du code</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php
// 🔐 Gestion du blocage
if (isset($_SESSION['verification_attempts']) && $_SESSION['verification_attempts'] >= 5) {
    if (!isset($_SESSION['block_until'])) {
        $_SESSION['block_until'] = time() + 300;
    }

    if (time() < $_SESSION['block_until']) {
        $wait = $_SESSION['block_until'] - time();
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Trop de tentatives',
                text: 'Veuillez réessayer dans $wait secondes.',
                confirmButtonColor: '#6c63ff'
            }).then(() => {
                window.location.href = 'profile.php';
            });
        </script>";
        exit;
    } else {
        $_SESSION['verification_attempts'] = 0;
        unset($_SESSION['block_until']);
    }
}

// ✅ Vérification du code
if ($_POST['code'] == $_SESSION['verification_code']) {
    $email = $_SESSION['user']['email'];

    // ➕ Mise à jour en base
    $db = Config::getConnexion();
    $stmt = $db->prepare("UPDATE user SET is_verified = 1 WHERE email = ?");
    $stmt->execute([$email]);

    // 🔄 Rafraîchir les données utilisateur
    $_SESSION['user'] = User::getUserByEmail($db, $email);

    // 🧹 Nettoyage
    unset($_SESSION['verification_code']);
    unset($_SESSION['verification_attempts']);

    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Succès !',
            text: 'Votre compte a été vérifié avec succès.',
            confirmButtonColor: '#6c63ff'
        }).then(() => {
            window.location.href = 'profile.php';
        });
    </script>";
} else {
    $_SESSION['verification_attempts'] = ($_SESSION['verification_attempts'] ?? 0) + 1;
    $tentativesRestantes = 5 - $_SESSION['verification_attempts'];

    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Code incorrect',
            text: 'Il vous reste $tentativesRestantes tentative(s).',
            confirmButtonColor: '#6c63ff'
        }).then(() => {
            window.location.href = 'verify_account.php';
        });
    </script>";
}
?>
</body>
</html>
