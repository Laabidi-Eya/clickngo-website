<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codeInput = $_POST['code'] ?? '';

    if (isset($_SESSION['reset_code']) && time() < $_SESSION['code_expire']) {
        if ($codeInput == $_SESSION['reset_code']) {
            header("Location: reset_password.php");
            exit;
        } else {
            $_SESSION['error'] = "Code incorrect.";
        }
    } else {
        $_SESSION['error'] = "Code expiré. Réessayez.";
        header("Location: reset_request.php");
        exit;
    }
}
?>

<form method="POST">
    <input type="text" name="code" required placeholder="Entrez le code reçu">
    <button type="submit">Vérifier</button>
</form>
