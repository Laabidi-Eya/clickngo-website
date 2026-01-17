<?php
// 📄 reset_page.php
session_start();
require_once __DIR__ . '/../../Config.php';
require_once __DIR__ . '/../../Model/User.php';

if (isset($_SESSION['user'])) {
    $email = $_SESSION['user']['email'];
} elseif (isset($_SESSION['reset_email'])) {
    $email = $_SESSION['reset_email'];
} else {
    header("Location: reset_request.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 40px; }
        .container { max-width: 400px; margin: auto; background: white; padding: 30px; border-radius: 10px; text-align: center; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        button { padding: 10px 20px; background: #8e44ad; color: white; border: none; border-radius: 8px; cursor: pointer; margin: 10px 0; }
        input[type="text"] { padding: 10px; width: 200px; border-radius: 5px; border: 1px solid #ccc; }
    </style>
</head>
<body>
<a href="<?php echo isset($_SESSION['user']) ? '/Projet%20Web/mvcUtilisateur/View/FrontOffice/edit_profile.php' : 'http://localhost/Projet%20Web/mvcUtilisateur/View/FrontOffice/reset_request.php'; ?>">
    <button style="background:#bdc3c7;">⬅️ Retour</button>
</a>
<div class="container">
    <h2>Réinitialiser le mot de passe</h2>
    <p>Un code va être envoyé à votre adresse : <strong><?= htmlspecialchars($email) ?></strong></p>
    <button id="btn-send-code">📧 Envoyer le code</button>
    <div style="margin-top: 20px;">
        <label for="code-input-page">Entrez le code reçu :</label><br>
        <input id="code-input-page" type="text" maxlength="6" inputmode="numeric" placeholder="Ex: 123456">
        <br>
        <button id="btn-verify-code">✅ Vérifier</button>
    </div>
</div>
<script>
let attempts = parseInt(localStorage.getItem('reset_attempts') || '0');
let lockUntil = parseInt(localStorage.getItem('reset_lockUntil') || '0');
const btnSend = document.getElementById('btn-send-code');
const btnVerify = document.getElementById('btn-verify-code');

function lockVerification() {
    btnVerify.disabled = true;
    Swal.fire('Trop de tentatives', 'Réessayez plus tard.', 'error');
}
function resetLockIfExpired() {
    if (lockUntil && Date.now() > lockUntil) {
        attempts = 0;
        lockUntil = 0;
        localStorage.removeItem('reset_attempts');
        localStorage.removeItem('reset_lockUntil');
        btnVerify.disabled = false;
    }
}
resetLockIfExpired();

function showNewPasswordPopup() {
    Swal.fire({
        title: 'Nouveau mot de passe',
        html:
            '<input id="newPass" type="password" class="swal2-input" placeholder="Nouveau mot de passe">' +
            '<input id="confirmPass" type="password" class="swal2-input" placeholder="Confirmer le mot de passe">',
        preConfirm: () => {
            const pass = document.getElementById('newPass').value;
            const confirm = document.getElementById('confirmPass').value;
            if (!pass || !confirm) {
                Swal.showValidationMessage('Tous les champs sont requis');
            } else if (pass !== confirm) {
                Swal.showValidationMessage('Les mots de passe ne correspondent pas');
            }
            return { password: pass };
        }
    })
    .then((mdp) => {
        if (mdp.isConfirmed) {
            fetch('reset_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'new_password=' + encodeURIComponent(mdp.value.password)
            })
            .then(r => r.text())
            .then(msg => {
                if (msg.trim() === 'done') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Mot de passe modifié !',
                        confirmButtonText: 'Continuer',
                        confirmButtonColor: '#8e44ad'
                    }).then(() => {
                        const isLoggedIn = <?= isset($_SESSION['user']) ? 'true' : 'false' ?>;
                        if (isLoggedIn) {
                            window.location.href = '/Projet%20Web/mvcUtilisateur/View/FrontOffice/edit_profile.php';
                        } else {
                            window.location.href = '/Projet%20Web/mvcUtilisateur/View/BackOffice/login/login.php';
                        }
                    });
                } else {
                    Swal.fire('Erreur', msg, 'error');
                }
            });
        }
    });
}

btnSend.addEventListener('click', () => {
    fetch('send_code.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'send_code=1'
    })
    .then(res => res.text())
    .then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Code envoyé avec succès !',
            text: 'Vérifiez votre email.',
            confirmButtonColor: '#8e44ad'
        });
        btnSend.disabled = true;
        let timeLeft = 60;
        const timer = setInterval(() => {
            btnSend.textContent = `⏳ Attendez (${timeLeft}s)`;
            timeLeft--;
            if (timeLeft < 0) {
                clearInterval(timer);
                btnSend.disabled = false;
                btnSend.textContent = '🔁 Renvoyer le code';
            }
        }, 1000);
    });
});

btnVerify.addEventListener('click', () => {
    resetLockIfExpired();
    if (attempts >= 5) {
        lockVerification();
        return;
    }
    const code = document.getElementById('code-input-page').value.trim();
    if (!/^\d{6}$/.test(code)) {
        Swal.fire('Erreur', 'Le code doit contenir exactement 6 chiffres.', 'error');
        return;
    }
    fetch('verify_reset_code.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'entered_code=' + encodeURIComponent(code)
    })
    .then(res => res.text())
    .then(response => {
        if (response.trim() === 'valid') {
            localStorage.removeItem('reset_attempts');
            localStorage.removeItem('reset_lockUntil');
            attempts = 0;
            showNewPasswordPopup();
        } else {
            attempts++;
            localStorage.setItem('reset_attempts', attempts);
            if (attempts >= 5) {
                lockUntil = Date.now() + 300000;
                localStorage.setItem('reset_lockUntil', lockUntil);
                lockVerification();
            } else {
                Swal.fire('Code incorrect', 'Il vous reste ' + (5 - attempts) + ' tentatives.', 'warning');
            }
        }
    });
});
</script>
</body>
</html>
