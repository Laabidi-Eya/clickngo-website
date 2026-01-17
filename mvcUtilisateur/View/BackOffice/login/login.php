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









// Gestion de l'image faciale
$faceImageData = $_POST['face_image'] ?? '';
$faceImagePath = '';

if (!empty($faceImageData)) {
    try {
        // Vérifier si le dossier existe, sinon le créer
        $faceImagesDir = 'C:/xampp/htdocs/Projet Web/mvcUtilisateur/database/face_images';
        if (!file_exists($faceImagesDir)) {
            mkdir($faceImagesDir, 0777, true);
        }
        
        // Extraire les données de l'image
        list($type, $data) = explode(';', $faceImageData);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);
        
        // Enregistrer l'image avec le numéro de téléphone comme nom
        $filename = $phone . '.png';
        $filepath = $faceImagesDir . '/' . $filename;
        
        if (file_put_contents($filepath, $data)) {
            $faceImagePath = $filepath;
        } else {
            throw new Exception("Erreur lors de l'enregistrement de l'image");
        }
    } catch (Exception $e) {
        $_SESSION['register_error'] = "Erreur lors du traitement de l'image faciale: " . $e->getMessage();
        header("Location: /View/BackOffice/login/login.php");
        exit();
    }
}

// Modifiez l'appel à register pour inclure le chemin de l'image
$userController->register($full_name, $phone, $email, $password, $faceImagePath);









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


        $userController->faceLogin();

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
    $redirect_url = $_SESSION['redirect_url'] ?? '/Projet Web/mvcUtilisateur/View/FrontOffice/index.php';
    unset($_SESSION['redirect_url']); // Nettoyer
    header("Location: $redirect_url");
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
                                                    <a href="/Projet Web/mvcUtilisateur/View/FrontOffice/reset_request.php" class="link">Mot de passe oublié ?</a>
                                                </div>

                                                <div class="form-group mt-2">
                                                    <div class="g-recaptcha" data-sitekey="6LfnLy4rAAAAAJmaQD20P5qeEAZvck9pVgfRUJxT"></div>
                                                </div>



                                                <div class="btn-login-zone">
                                                    <button type="submit" class="btn mt-4" name="action" value="login" id="login-btn">SE CONNECTER</button>
                                                </div>

                                                <br>
                                                <div class="form-group mt-2">
                                                    <p>Ou</p>
                                                    <a href="../../../auth/facebook.php" class="btn"><i class="fa-brands fa-facebook-f"></i></a>
                                                    <a href="/Projet Web/mvcUtilisateur/auth/google.php" class="btn"><i class="fa-brands fa-google"></i></a>
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

    <div id="face-preview" style="margin-top: 10px; display: none;">
        <img id="face-image" src="" style="max-width: 100px; max-height: 100px; border-radius: 50%;">
        <button type="button" onclick="clearFaceImage()" class="btn btn-sm btn-danger">
            <i class="uil uil-trash-alt"></i>
        </button>
    </div>
    <input type="hidden" id="face-image-data" name="face_image">
</div>


                                                <div class="form-group mt-2">
                                                    <div class="g-recaptcha" data-sitekey="6LfnLy4rAAAAAJmaQD20P5qeEAZvck9pVgfRUJxT"></div>
                                                </div>


                                                <button type="submit" class="btn mt-4" name="action" value="register">S’inscrire</button>
                                                <div class="form-group mt-2">
                                                    <p>Ou</p>
                                                    <a href="../../../auth/facebook.php" class="btn"><i class="fa-brands fa-facebook-f"></i></a>
                                                    <a href="/Projet Web/mvcUtilisateur/auth/google.php" class="btn"><i class="fa-brands fa-google"></i></a>
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


<!-- Modal pour la capture faciale -->
<div class="modal fade" id="faceCaptureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Capture Faciale</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="camera-container">
                    <video id="video" width="100%" autoplay></video>
                    <canvas id="canvas" style="display:none;"></canvas>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="capture-btn">Capturer</button>
                <button type="button" class="btn btn-success" id="confirm-btn" style="display:none;">Confirmer</button>
                <button type="button" class="btn btn-warning" id="retake-btn" style="display:none;">Reprendre</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.min.js"></script>

<script>
// Variables globales
let stream = null;

// Ouvrir le modal de capture
document.getElementById('capture-face-btn').addEventListener('click', function() {
    $('#faceCaptureModal').modal('show');
    startCamera();
});

// Démarrer la caméra
function startCamera() {
    navigator.mediaDevices.getUserMedia({ video: true, audio: false })
        .then(function(s) {
            stream = s;
            const video = document.getElementById('video');
            video.srcObject = stream;
        })
        .catch(function(err) {
            console.error("Erreur caméra: ", err);
            Swal.fire('Erreur', 'Impossible d\'accéder à la caméra', 'error');
        });
}

// Capturer l'image
document.getElementById('capture-btn').addEventListener('click', function() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Afficher les boutons de confirmation
    document.getElementById('capture-btn').style.display = 'none';
    document.getElementById('confirm-btn').style.display = 'block';
    document.getElementById('retake-btn').style.display = 'block';
    
    // Arrêter le flux vidéo
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
});

// Reprendre la photo
document.getElementById('retake-btn').addEventListener('click', function() {
    document.getElementById('capture-btn').style.display = 'block';
    document.getElementById('confirm-btn').style.display = 'none';
    document.getElementById('retake-btn').style.display = 'none';
    startCamera();
});

// Confirmer la photo
document.getElementById('confirm-btn').addEventListener('click', function() {
    const canvas = document.getElementById('canvas');
    const imageData = canvas.toDataURL('image/png');
    
    // Afficher la prévisualisation
    document.getElementById('face-image').src = imageData;
    document.getElementById('face-preview').style.display = 'block';
    document.getElementById('face-image-data').value = imageData;
    
    // Fermer le modal
    $('#faceCaptureModal').modal('hide');
});

// Effacer l'image capturée
function clearFaceImage() {
    document.getElementById('face-image').src = '';
    document.getElementById('face-preview').style.display = 'none';
    document.getElementById('face-image-data').value = '';
}

// Nettoyer quand le modal se ferme
$('#faceCaptureModal').on('hidden.bs.modal', function () {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
});
</script>

<style>
    .camera-container {
    width: 100%;
    background: #000;
    margin-bottom: 15px;
}

.password-hint {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.password-strength-bar {
    width: 100%;
    height: 5px;
    background: #eee;
    margin-top: 5px;
    border-radius: 3px;
    overflow: hidden;
}

.strength-bar-inner {
    height: 100%;
    width: 0%;
    transition: width 0.3s;
    background: #6c63ff;
}

.modal-content {
    background: #2c2f33;
    color: white;
}

.close {
    color: white;
}
</style>


</body>

</html> 