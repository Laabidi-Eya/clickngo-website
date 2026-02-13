    <?php if (isset($_SESSION['login_error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur !',
                    text: <?= json_encode($_SESSION['login_error']) ?>,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#6c63ff'
                });
            });
        </script>

        $python_output = shell_exec('python ' . escapeshellarg(PYTHON_SCRIPT));



    <?php unset($_SESSION['login_error']);
    endif; ?>


    <?php if (isset($_GET['error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: <?= json_encode($_GET['error']) ?>,
                    confirmButtonColor: '#6c63ff'
                }).then(() => {
                    // ✅ Recharge proprement sans paramètre
                    window.location.href = window.location.pathname;
                });
            });
        </script>
    <?php endif; ?>


    <?php
    session_start();

    require_once '../../../Controller/UserController.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'register') {

            $full_name = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            try {
                $userController = new UserController();

                // Vérifier format numéro (8 chiffres exactement)
                if (!preg_match('/^\d{8}$/', $phone)) {
                    $_SESSION['register_error'] = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
                    header("Location: /View/BackOffice/login/login.php");
                    exit();
                }

                // Vérifier si le numéro de téléphone existe déjà
                if ($userController->phoneExists($phone)) {
                    $_SESSION['register_error'] = "Ce numéro de téléphone est déjà utilisé.";
                    header("Location: /View/BackOffice/login/login.php");
                    exit();
                }

                // Vérifier si l'email existe déjà
                if ($userController->emailExists($email)) {
                    $_SESSION['register_error'] = "Cet email est déjà utilisé. Veuillez en choisir un autre.";
                    header("Location: /View/BackOffice/login/login.php");
                    exit();
                }

                // Vérifier force du mot de passe
                if (!isStrongPassword($password)) {
                    $_SESSION['register_error'] = "Votre mot de passe n'est pas assez fort.";
                    header("Location: /View/BackOffice/login/login.php");
                    exit();
                }

                // Si tout est bon, inscription
                $userController->register($full_name, $phone, $email, $password);

                // Succès
                $_SESSION['register_success'] = "Inscription réussie ! Vous pouvez vous connecter.";
                header("Location: /View/BackOffice/login/login.php");
                exit();
            } catch (Exception $e) {
                $_SESSION['register_error'] = "Une erreur s'est produite lors de l'inscription. Veuillez réessayer.";
                header("Location: /View/BackOffice/login/login.php");
                exit();
            }
        } elseif ($action === 'login') {



            if (empty($_POST['g-recaptcha-response'])) {
                $_SESSION['login_error'] = "Veuillez valider le CAPTCHA.";
                header("Location: /View/BackOffice/login/login.php");
                exit();
            }

            $secretKey = '6LfnLy4rAAAAAFYzJror47CTbIt1eP5OEZPSgZFl';
            $captcha = $_POST['g-recaptcha-response'];
            $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha");
            $data = json_decode($response);

            if (!$data->success) {
                $_SESSION['login_error'] = "Échec de vérification CAPTCHA.";
                header("Location: /View/BackOffice/login/login.php");
                exit();
            }

            // Connexion
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $userController = new UserController();
        // Après une connexion réussie
    // Après une connexion réussie
    if ($userController->login($email, $password)) {
        $db = Config::getConnexion();
        require_once '../../../Model/User.php';
        $_SESSION['user'] = User::getUserByEmail($db, $email);
        
        // Rediriger vers la page demandée ou vers une page par défaut
        $redirect_url = $_SESSION['redirect_url'] ?? '/mvcUtilisateur/View/FrontOffice/index.php';
        unset($_SESSION['redirect_url']); // Nettoyer
        header("Location: $redirect_url");
        exit();
    }
        } elseif ($action === 'faceLogin') {
    try {
        $userController = new UserController();
        $userController->faceLogin();
    } catch (Exception $e) {
        $_SESSION['login_error'] = $e->getMessage();
        header('Location: login.php');
        exit();
    }
}
    }

    // Fonction pour vérifier la force du mot de passe
    function isStrongPassword($password)
    {
        $hasUpper = preg_match('@[A-Z]@', $password);
        $hasLower = preg_match('@[a-z]@', $password);
        $hasNumber = preg_match('@[0-9]@', $password);
        $hasSpecial = preg_match('@[!@#$%^&*(),.?":{}|<> ]@', $password);
        $longEnough = strlen($password) >= 8;

        return $hasUpper && $hasLower && $hasNumber && $hasSpecial && $longEnough;
    }
    ?>


    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Click'N'Go/login</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.9/css/unicons.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="styles.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


        <script src="https://www.google.com/recaptcha/api.js" async defer></script>


    </head>

    <body>

        <?php if (isset($_SESSION['register_success'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès !',
                        text: <?= json_encode($_SESSION['register_success']) ?>,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#6c63ff'
                    });
                });
            </script>
        <?php unset($_SESSION['register_success']);
        endif; ?>

        <?php if (isset($_SESSION['register_error'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur !',
                        text: <?= json_encode($_SESSION['register_error']) ?>,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#6c63ff'
                    });
                });
            </script>
        <?php unset($_SESSION['register_error']);
        endif; ?>


        <div class="section">
            <div class="container">
                <div class="row full-height justify-content-center">
                    <div class="col-12 text-center align-self-center py-5">
                        <div class="section pb-5 pt-5 pt-sm-2 text-center">
                            <h6 class="mb-0 pb-3"><span>Se connecter</span><span>S’inscrire</span></h6>
                            <input class="checkbox" type="checkbox" id="reg-log" name="reg-log" />
                            <label for="reg-log"></label>
                            <div class="card-3d-wrap mx-auto">
                                <div class="card-3d-wrapper">
                                    <!-- Section "Se connecter" -->
                                    <div class="card-front">
                                        <div class="center-wrap">
                                            <div class="section text-center">
                                                <h4 class="pb-3">Se connecter</h4>
                                                <form method="POST" action="login.php">
                                                    <div class="form-group">
                                                        <input type="email" class="form-style" name="email" placeholder="Email" required>
                                                        <i class="input-icon uil uil-at"></i>
                                                    </div>
                                                    <div class="form-group mt-2" style="position: relative;">
                                                        <input type="password" class="form-style" name="password" id="login-password" placeholder="Password" required>
                                                        <i class="input-icon uil uil-lock-alt"></i>

                                                        <!-- Icône œil par défaut en mode caché -->
                                                        <i class="toggle-password uil uil-eye-slash"
                                                            onclick="togglePassword('login-password', this)"
                                                            style="position: absolute; top: 10px; right: 15px; cursor: pointer;"></i>
                                                    </div>

                                                    <div class="text-right mt-1">
                                                        <a href="/mvcUtilisateur/View/FrontOffice/reset_request.php" class="link">Mot de passe oublié ?</a>
                                                    </div>

                                                    <div class="form-group mt-2">
                                                        <div class="g-recaptcha" data-sitekey="6LfnLy4rAAAAAJmaQD20P5qeEAZvck9pVgfRUJxT"></div>
                                                    </div>
    

                                                    <div class="btn-login-zone">
                                                        <button type="submit" class="btn mt-4" name="action" value="login" id="login-btn">SE CONNECTER</button>
                                                    </div>
<p>Ou</p>
    <button type="button" class="btn" onclick="initiateFaceLogin()">
        <i class="fa-solid fa-face-smile"></i> Connexion faciale
    </button>
                                                    <br>
                                                    <div class="form-group mt-2">
                                                        <p>Ou</p>
                                                        
                                                        <a href="../../../auth/facebook.php" class="btn"><i class="fa-brands fa-facebook-f"></i></a>
                                                        <a href="/mvcUtilisateur/auth/google.php" class="btn"><i class="fa-brands fa-google"></i></a>
                                                        <a href="#" class="btn"><i class="fa-brands fa-github"></i></a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Section "S’inscrire" -->
                                    <div class="card-back">
                                        <div class="center-wrap">
                                            <div class="section text-center">
                                                <h4 class="mb-3 pb-3">S’inscrire</h4>
                                                <form method="POST" action="login.php">
                                                    <div class="form-group">
                                                        <input type="text" class="form-style" name="full_name" placeholder="Full Name" required>
                                                        <i class="input-icon uil uil-user"></i>
                                                    </div>
                                                    <div class="form-group mt-2">
                                                        <input type="tel" class="form-style" name="phone" placeholder="Phone Number" required>
                                                        <i class="input-icon uil uil-phone"></i>
                                                    </div>
                                                    <div class="form-group mt-2">
                                                        <input type="email" class="form-style" name="email" placeholder="Email" required>
                                                        <i class="input-icon uil uil-at"></i>
                                                    </div>
                                                    <div class="form-group mt-2" style="position: relative;">
                                                        <input type="password" class="form-style" name="password" id="password" placeholder="Password" required>
                                                        <i class="input-icon uil uil-lock-alt"></i>

                                                        <!-- 👁 Icône œil pour afficher/cacher -->
                                                        <i class="toggle-password uil uil-eye-slash"
                                                            onclick="togglePassword('password', this)"
                                                            style="position: absolute; top: 10px; right: 15px; cursor: pointer;"></i>

                                                        <!-- Générer un mot de passe -->
                                                        <button type="button"
                                                            onclick="generateStrongPassword()"
                                                            style="position: absolute; top: 10px; right: 45px; background: none; border: none; cursor: pointer; color: white;"
                                                            title="Générer un mot de passe">
                                                            <i class="uil uil-sync"></i>
                                                        </button>

                                                        </button>
                                                        <script>
                                                            function generateStrongPassword() {
                                                                const upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                                                const lower = "abcdefghijklmnopqrstuvwxyz";
                                                                const numbers = "0123456789";
                                                                const special = "!@#$%^&*(),.?\":{}|<>";

                                                                let password = "";

                                                                // Garantir au moins 1 caractère de chaque type
                                                                password += upper.charAt(Math.floor(Math.random() * upper.length));
                                                                password += lower.charAt(Math.floor(Math.random() * lower.length));
                                                                password += numbers.charAt(Math.floor(Math.random() * numbers.length));
                                                                password += special.charAt(Math.floor(Math.random() * special.length));

                                                                // Remplir le reste aléatoirement jusqu'à 12 caractères
                                                                const all = upper + lower + numbers + special;
                                                                for (let i = password.length; i < 12; i++) {
                                                                    password += all.charAt(Math.floor(Math.random() * all.length));
                                                                }

                                                                // Mélanger le mot de passe pour pas que l’ordre soit toujours pareil
                                                                password = password.split('').sort(() => 0.5 - Math.random()).join('');

                                                                const input = document.getElementById("password");
                                                                input.value = password;

                                                                // Déclencher l'événement d'entrée (utile si bouton submit désactivé sans input)
                                                                input.dispatchEvent(new Event('input', {
                                                                    bubbles: true
                                                                }));

                                                                // Petit effet visuel
                                                                input.style.backgroundColor = "#e0ffe0";
                                                                setTimeout(() => input.style.backgroundColor = "", 500);
                                                            }
                                                        </script>

                                                        <div id="passwordHint" class="password-hint"></div>
                                                        <div class="password-strength-bar">
                                                            <div id="passwordStrength" class="strength-bar-inner"></div>
                                                        </div>
                                                    </div>



                                                    <div class="form-group mt-2">
                                                        <div class="g-recaptcha" data-sitekey="6LfnLy4rAAAAAJmaQD20P5qeEAZvck9pVgfRUJxT"></div>
                                                    </div>


                                                    <button type="submit" class="btn mt-4" name="action" value="register">S’inscrire</button>
                                                    <div class="form-group mt-2">
                                                        <p>Ou</p>
                                                        <a href="../../../auth/facebook.php" class="btn"><i class="fa-brands fa-facebook-f"></i></a>
                                                        <a href="/mvcUtilisateur/auth/google.php" class="btn"><i class="fa-brands fa-google"></i></a>
                                                        <a href="#" class="btn"><i class="fa-brands fa-github"></i></a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="script.js"> </script>
        <script>
            function togglePassword(inputId, icon) {
                const input = document.getElementById(inputId);
                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.remove("uil-eye-slash");
                    icon.classList.add("uil-eye");
                } else {
                    input.type = "password";
                    icon.classList.remove("uil-eye");
                    icon.classList.add("uil-eye-slash");
                }
            }
        </script>
        <script>
function initiateFaceLogin() {
    // Afficher un message d'attente
    Swal.fire({
        title: 'Reconnaissance faciale',
        text: 'Veuillez patienter pendant que nous vérifions votre identité...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            
            // Envoyer la requête AJAX pour la reconnaissance faciale
            fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=faceLogin'
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes("Aucun visage reconnu") || data.trim() === "") {
                    throw new Error('Échec de reconnaissance faciale');
                }
                return data;
            })
            .then(userId => {
                // Redirection sera gérée par le serveur
                window.location.reload();
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.message || 'Échec de la reconnaissance faciale',
                    confirmButtonColor: '#6c63ff'
                });
            });
        }
    });
}
</script>
    </body>

    </html> 
    