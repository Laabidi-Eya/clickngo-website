<?php
session_start();
require_once __DIR__ . '/../../Config.php';
require_once __DIR__ . '/../../Model/User.php';

$error = null;

if (isset($_SESSION['user'])) {
    header("Location: reset_page.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $_SESSION['reset_email'] = $email;

    $db = Config::getConnexion();
    $user = User::getUserByEmail($db, $email);

    if ($user) {
        header("Location: reset_page.php");
        exit;
    } else {
        $error = "Aucun compte trouvé avec cet email.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de réinitialisation</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 40px; }
        .container { max-width: 400px; margin: auto; background: white; padding: 30px; border-radius: 10px; text-align: center; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input[type="email"] { padding: 10px; width: 250px; border-radius: 5px; border: 1px solid #ccc; margin-top: 10px; }
        button { margin-top: 20px; padding: 10px 20px; background: #8e44ad; color: white; border: none; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <h2>Mot de passe oublié ?</h2>
    <form method="post">
        <label>Votre adresse email :</label><br>
        <input type="email" name="email" required><br>
        <button type="submit">Envoyer le code</button>
    </form>
</div>

<?php if ($error): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Erreur',
        text: '<?= addslashes($error) ?>',
        confirmButtonColor: '#8e44ad'
    });
</script>
<?php endif; ?>
</body>
</html>
