<?php
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../Model/User.php';
// Autoload Composer (à la racine du projet, pas dans mvcUtilisateur)
$rootAutoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (file_exists($rootAutoload)) {
    require_once $rootAutoload;
}


class UserController
{
    private $userModel;



    public function getUserById($id) {
        require_once(__DIR__ . '/../Model/User.php');
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM users WHERE id_user = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            // Assuming your User model has a constructor that accepts an array or individual fields
            return new User(
                $row['id_user'],
                $row['full_name'],
                $row['email'],
                $row['password'],
                $row['date_inscription'],
                $row['num_user'],
                $row['role'],
                $row['profile_picture']
            );
        }
        return null;
    }
    


    public function handleSocialLogin(array $socialUser, string $provider)
    {
        // Example implementation for handling social login
        // Save or update the user in the database based on $socialUser and $provider
        // This is a placeholder; adapt it to your application's logic
        if (!isset($socialUser['facebook_id'])) {
            throw new Exception('Invalid social user data');
        }

        // Example: Log the user data (replace with actual database logic)
        error_log("Social login via {$provider}: " . json_encode($socialUser));
    }

    public function emailExists($email)
    {
        // Example logic to check if the email exists in the database
        $db = Config::getConnexion();
        $query = $db->prepare("SELECT COUNT(*) FROM user WHERE email = :email");
        $query->bindParam(':email', $email);
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    // Method to check if a phone number exists
    public function phoneExists($phone)
    {
        // Replace with actual database logic
        $db = Config::getConnexion();
        $query = $db->prepare("SELECT COUNT(*) FROM user WHERE num_user = :num_user");
        $query->bindParam(':num_user', $phone, PDO::PARAM_STR);
        $query->execute();
        return $query->fetchColumn() > 0;
    }

    public function addUser()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['fullName'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $dateInscription = $_POST['dateInscription'];
            $numUser = $_POST['numUser'];
            $role = $_POST['role'];
            $profilePicture = $_FILES['profilePicture'];

            // Upload image
            $uploadDir = 'uploads/';
            $fileName = basename($profilePicture['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($profilePicture['tmp_name'], $targetPath)) {
                $user = new User();
                $user->addUser($fullName, $email, $password, $dateInscription, $role, $numUser, null, null, $fileName);

                // Redirection après ajout
                header('Location: index.php');
                exit;
            } else {
                echo "Erreur lors de l’upload de l’image.";
            }
        }
    }
    
    public function supprimerUser($id)
    {
        try {
            $db = Config::getConnexion();
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // Vérifier si l'utilisateur existe
            $stmt = $db->prepare("SELECT id_user FROM user WHERE id_user = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            if (!$stmt->fetch()) {
                error_log("❌ Utilisateur ID $id non trouvé.");
                header("Location: indeex.php?delete_error=not_found&id=$id");
                exit;
            }
    
            // Supprimer l'utilisateur
            $stmt = $db->prepare("DELETE FROM user WHERE id_user = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $success = $stmt->execute();
    
            if ($success) {
                error_log("✅ Utilisateur ID $id supprimé.");
                header("Location: indeex.php?deleted=1&id=$id");
                exit;
            } else {
                error_log("❌ Échec de suppression pour ID $id.");
                header("Location: indeex.php?delete_error=failed&id=$id");
                exit;
            }
    
        } catch (PDOException $e) {
            error_log("❌ Erreur SQL : " . $e->getMessage());
            header("Location: indeex.php?delete_error=sql&id=$id");
            exit;
        }
    }
    
    


    public function debannirUser($id)
    {
        $db = Config::getConnexion();
        $user = User::findById($db, $id);
    
        if ($user) {
            $user->setRole('user');
            $user->setBanReason(null);
    
            if ($user->updateUser($db, $id)) {
                header("Location: indeex.php?unban_success=1&id=$id&email=" . urlencode($user->getEmail()));
                exit;                
            } else {
                echo "Échec de la mise à jour.";
            }
        } else {
            echo "Utilisateur introuvable.";
        }
    }
    


    public function bannirUser($id, $raison)
    {
        $db = Config::getConnexion();
        $user = User::findById($db, $id);
    
        if ($user) {
            $user->setRole('banni');
            $user->setBanReason($raison);
            $success = $user->updateUser($db, $id);
    
            if ($success) {
                // 🔁 Utilise l'email depuis l'objet $user (si c'est un tableau, adapte avec $user['email'])
                $email = $user->getEmail(); // ou $user['email'] si c’est un tableau
                header("Location: indeex.php?ban_success=1&id=$id&email=" . urlencode($user->getEmail()) . "&raison=" . urlencode($raison));
                exit;
            } else {
                header("Location: indeex.php?ban_error=1&message=" . urlencode("Erreur lors de la mise à jour."));
                exit;
            }
        } else {
            header("Location: indeex.php?ban_error=1&message=" . urlencode("Utilisateur non trouvé."));
            exit;
        }
    }
    
    




    public function changerRole($id, $nouveauRole)
    {
        $db = Config::getConnexion();
        $user = User::findById($db, $id);

        if ($user) {
            if ($user->getRole() === $nouveauRole) {
                // Pas de changement
                header("Location: indeex.php?role_no_change=1&name=" . urlencode($user->getFullName()) . "&role=" . urlencode($nouveauRole));
                exit;
            }

            $user->setRole($nouveauRole);

            // Si on débannit, on vide la raison
            if ($nouveauRole !== 'banni') {
                $user->setBanReason(null);
            }

            if ($user->updateUser($db, $id)) {
                header("Location: indeex.php?role_update_success=1&name=" . urlencode($user->getFullName()) . "&id=$id&role=" . urlencode($nouveauRole));
                exit;
            } else {
                echo "❌ Échec de mise à jour.";
            }
        } else {
            echo "Utilisateur non trouvé.";
        }
    }







    // This code block was removed because it was outside of a function and caused a syntax error.

    public function getAllUsers()
    {
        $db = Config::getConnexion(); // récupère la connexion PDO
        return User::getAll($db); // appel de la méthode statique
    }

    public function deleteUser($id)
    {
        $db = Config::getConnexion(); // Get the database connection
        User::deleteUser($db, $id); // Pass the database connection as the first argument
        header("Location: index.php?action=listUsers");
        exit();
    }

    public static function deleteById($db, $id)
    {
        // Préparer la requête SQL pour supprimer un utilisateur par ID
        $stmt = $db->prepare("DELETE FROM user WHERE id = ?");

        // Exécuter la requête avec l'ID
        return $stmt->execute([$id]);
    }



    public function index()
    {
        $db = Config::getConnexion();
        $users = $this->userModel->findAll($db); // méthode findAll à ajouter dans ton modèle si elle n'existe pas

        // Démarrer session si besoin
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Inclure la vue et rendre $users dispo
        require_once __DIR__ . '/../View/BackOffice/indeex.php';
    }



    public function __construct()
    {
        $this->userModel = new User();
    }

    public function register($userData)
    {
        $db = Config::getConnexion();

        // Démarrer la session si elle n'est pas déjà active
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        try {
            // Validation des données avant création de l'utilisateur
            if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("L'adresse email n'est pas valide.");
            }

            if (strlen($userData['password']) < 8) {
                throw new Exception("Le mot de passe doit contenir au moins 8 caractères.");
            }

            $user = new User();
            $user->setFullName($userData['full_name']);
            $user->setEmail($userData['email']);
            $user->setPassword(password_hash($userData['password'], PASSWORD_DEFAULT));
            $user->setNumUser($userData['phone']);
            $user->setRole('user');

            if ($user->save($db)) {
                $_SESSION['register_success'] = "Votre compte a été créé avec succès !";
                header("Location: /mvcUtilisateur/View/BackOffice/login/login.php");
                exit;
            }

            throw new Exception("Erreur lors de l'enregistrement.");
        } catch (PDOException $e) {
            // Gestion des erreurs SQL
            if ($e->getCode() == '23000') {
                // Erreur de contrainte d'intégrité (clé dupliquée)
                if (strpos($e->getMessage(), 'email') !== false) {
                    $_SESSION['register_error'] = "Cet email est déjà utilisé.";
                } elseif (strpos($e->getMessage(), 'num_user') !== false) {
                    $_SESSION['register_error'] = "Ce numéro de téléphone est déjà utilisé.";
                } else {
                    $_SESSION['register_error'] = "Une donnée existe déjà en base.";
                }
            } else {
                $_SESSION['register_error'] = "Erreur de base de données : " . $e->getMessage();
            }
        } catch (Exception $e) {
            // Gestion des autres exceptions
            $_SESSION['register_error'] = $e->getMessage();
        }

        // Redirection en cas d'erreur
        header("Location: /mvcUtilisateur/View/BackOffice/login/login.php");
        exit;
    }


    
public function faceLogin($face_data) {
    $db = Config::getConnexion();
    
    // Récupérer toutes les données faciales enregistrées
    $query = "SELECT user_id, face_data FROM user_faces";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $registered_faces = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ici, vous devriez implémenter une logique de comparaison faciale
    // Ceci est un exemple simplifié - dans la réalité, utilisez une bibliothèque comme FaceAPI ou OpenCV
    
    foreach ($registered_faces as $registered_face) {
        if ($this->compareFaces($face_data, $registered_face['face_data'])) {
            // Visage reconnu - retourner les données de l'utilisateur
            return User::getUserById($db, $registered_face['user_id']);
        }
    }
    
    return false;
}

private function compareFaces($face1, $face2) {
    // Implémentez une logique de comparaison faciale réelle ici
    // Pour cet exemple, nous faisons une simple comparaison des données
    // En réalité, utilisez une bibliothèque spécialisée
    
    // Seuil de similarité (à ajuster)
    $similarity_threshold = 0.8;
    
    // Calcul de similarité simplifié (remplacez par un vrai algorithme)
    $similarity = $this->calculateFaceSimilarity($face1, $face2);
    
    return $similarity >= $similarity_threshold;
}

private function calculateFaceSimilarity($face1, $face2) {
    // Implémentation simplifiée - utilisez une vraie bibliothèque de reconnaissance faciale
    // Cette fonction devrait retourner un score de similarité entre 0 et 1
    
    // Pour l'exemple, nous retournons une valeur aléatoire
    return mt_rand(700, 1000) / 1000;
}




public function saveFaceData($user_id, $face_data, $face_image_path) {
    $db = Config::getConnexion();
    $query = "INSERT INTO user_faces (user_id, face_data, image_path) VALUES (:user_id, :face_data, :image_path)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':face_data', $face_data);
    $stmt->bindParam(':image_path', $face_image_path);
    return $stmt->execute();
}



    public function login($email, $password)
    {
        $db = Config::getConnexion();

        try {
            $user = User::findByEmail($db, $email);

            if (!$user || !password_verify($password, $user->getPassword())) {
                throw new Exception("Email ou mot de passe incorrect");
            }

            if ($user->getRole() === 'banni') {
                $raison = $user->getBanReason() ?? 'Aucune raison fournie.';
                throw new Exception("Votre compte a été banni. Raison : $raison");
            }

            // Démarrer la session si elle n’est pas déjà démarrée
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Sauvegarder les infos utilisateur dans la session
            $_SESSION['user'] = [
                'id_user' => $user->getIdUser(),
                'full_name' => $user->getFullName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'num_user' => $user->getNumUser(),
                'profile_picture' => $user->getProfilePicture(),
                'is_verified' => $user->getIsVerified()

            ];

            // Redirection selon le rôle
            if ($user->getRole() === 'admin') {
                header("Location: /mvcUtilisateur/View/BackOffice/indeex.php");
            } else {
                header("Location: /mvcUtilisateur/View/FrontOffice/index.php");
            }
            exit;
        } catch (Exception $e) {
            // Redirection vers la page login avec le message d’erreur
            header("Location: /mvcUtilisateur/View/BackOffice/login/login.php?error=" . urlencode($e->getMessage()));
            exit;
        }
    }






    public function handleFacebookLogin($facebookUser)
    {
        $db = Config::getConnexion();
        session_start();

        try {
            // Validation des données Facebook
            if (empty($facebookUser['id'])) {
                throw new Exception("Données Facebook invalides");
            }

            // 1. Recherche par Facebook ID
            $user = User::findByFacebookId($db, $facebookUser['id']);

            // 2. Si non trouvé, recherche par email
            if (!$user && !empty($facebookUser['email'])) {
                $user = User::findByEmail($db, $facebookUser['email']);

                // Si trouvé par email mais sans Facebook ID, on met à jour
                if ($user && empty($user->getFacebookId())) {
                    $user->setFacebookId($facebookUser['id']);
                    $user->save($db);
                }
            }

            // 3. Création si nécessaire
            if (!$user) {
                $newUser = new User();
                $newUser->setFullName($facebookUser['name'] ?? 'Utilisateur Facebook');
                $newUser->setEmail($facebookUser['email'] ?? '');
                $newUser->setFacebookId($facebookUser['id']);
                $newUser->setRole('user');

                if (!$newUser->save($db)) {
                    throw new Exception("Échec de la création du compte");
                }
                $user = $newUser;
            }

            // 4. Connexion
            $_SESSION['user'] = [
                'id_user' => $user->getIdUser(),
                'full_name' => $user->getFullName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'is_social' => true,
                'profile_picture' => $user->getProfilePicture()
            ];


            header("Location: /View/FrontOffice/index.php");
            exit;
        } catch (Exception $e) {
            error_log("Facebook Login Error: " . $e->getMessage());
            header("Location: /View/BackOffice/login/login.php?error=facebook_login");
            exit;
        }
    }

    public function countUsers()
    {
        $db = config::getConnexion();
        $query = $db->query("SELECT COUNT(*) as total FROM user");
        $result = $query->fetch();
        return $result['total'];
    }

    public function countUsersByRole()
    {
        $db = config::getConnexion();
        $query = $db->query("SELECT role, COUNT(*) as total FROM user GROUP BY role");
        return $query->fetchAll();
    }

    public function getInscriptionTrends($period = '1 MONTH')
    {
        $db = config::getConnexion();
        $select = "";
        $groupBy = "";
        $intervalSQL = $period; // valeur par défaut

        switch ($period) {
            case '7 DAY':
                $select = "DATE_FORMAT(date_inscription, '%d %b') AS label";
                $groupBy = "DATE(date_inscription)";
                $intervalSQL = '7 DAY';
                break;

            case '1 MONTH':
                $select = "CONCAT('Semaine ', WEEK(date_inscription), '/', YEAR(date_inscription)) AS label";
                $groupBy = "WEEK(date_inscription), YEAR(date_inscription)";
                $intervalSQL = '1 MONTH';
                break;


            case '4 MONTH':
            case '6 MONTH':
                $select = "DATE_FORMAT(date_inscription, '%b %Y') AS label";
                $groupBy = "YEAR(date_inscription), MONTH(date_inscription)";
                // intervalSQL reste identique
                break;

            case '1 YEAR':
                $select = "CONCAT('T', QUARTER(date_inscription), '-', YEAR(date_inscription)) AS label";
                $groupBy = "YEAR(date_inscription), QUARTER(date_inscription)";
                break;

            case '3 YEAR':
                $select = "YEAR(date_inscription) AS label";
                $groupBy = "YEAR(date_inscription)";
                break;

            default:
                $select = "CONCAT('Semaine ', WEEK(date_inscription)) AS label";
                $groupBy = "WEEK(date_inscription)";
                break;
        }

        $stmt = $db->prepare("
            SELECT 
                $select,
                COUNT(*) AS total
            FROM user
            WHERE date_inscription >= DATE_SUB(CURDATE(), INTERVAL $intervalSQL)
            GROUP BY $groupBy
            ORDER BY MIN(date_inscription) ASC
        ");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Traitement des requêtes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new UserController();

    try {
        if (!isset($_POST['action'])) {
            throw new Exception("Action non spécifiée");
        }

        switch ($_POST['action']) {
            case 'register':
                $required = ['full_name', 'email', 'password', 'phone'];
                foreach ($required as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("Le champ $field est requis");
                    }
                }

                // ✅ CAPTCHA obligatoire
                if (empty($_POST['g-recaptcha-response'])) {
                    throw new Exception("Veuillez valider le CAPTCHA.");
                }

                $secretKey = '6LfnLy4rAAAAAFYzJror47CTbIt1eP5OEZPSgZFl';
                $captcha = $_POST['g-recaptcha-response'];
                $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha");
                $data = json_decode($response);

                if (!$data->success) {
                    throw new Exception("Échec de vérification CAPTCHA.");
                }

                // Inscription
                $controller->register($_POST);
                $_SESSION['register_success'] = "Inscription réussie ! Vous pouvez vous connecter.";
                header("Location: login.php");
                exit();

            case 'login':
                // Vérification des champs
                if (empty($_POST['email']) || empty($_POST['password'])) {
                    throw new Exception("Email et mot de passe requis");
                }

                // Vérification CAPTCHA
                if (empty($_POST['g-recaptcha-response'])) {
                    throw new Exception("Veuillez valider le CAPTCHA.");
                }

                $secretKey = '6LfnLy4rAAAAAFYzJror47CTbIt1eP5OEZPSgZFl';
                $captcha = $_POST['g-recaptcha-response'];
                $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha");
                $data = json_decode($response);

                if (!$data || !$data->success) {
                    throw new Exception("Échec de vérification CAPTCHA.");
                }

                // Connexion
                if ($controller->login($_POST['email'], $_POST['password'])) {
                    // Connexion réussie → redirection vers le dashboard
                    header("Location: /View/BackOffice/dashboard.php");
                    exit();
                } else {
                    // Connexion échouée → on lance une erreur
                    throw new Exception("Email ou mot de passe incorrect.");
                }


            default:
                throw new Exception("Action non reconnue");
        }
    } catch (Exception $e) {
        header("Location: login.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
