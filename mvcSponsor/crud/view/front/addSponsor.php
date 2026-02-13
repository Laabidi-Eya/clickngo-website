<?php
session_start();
require_once(__DIR__ . "/../../controller/controller.php");
require_once(__DIR__ . "/../../model/model.php");
require_once(__DIR__ . "../../../../../mvcEvent/Config.php");

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_user'])) {
    header("Location: /mvcUtilisateur/View/BackOffice/login/login.php");
    exit();
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

$errors = [];
$fieldErrors = [];

$controller = new SponsorController();
$offers = $controller->listOffers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize data
    $nom_entreprise = sanitizeInput($_POST['companyName'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $telephone = sanitizeInput($_POST['phone'] ?? '');
    $montant = (float) filter_var($_POST['amount'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $duree = sanitizeInput($_POST['duration'] ?? '');
    $avantage = sanitizeInput($_POST['benefits'] ?? '');
    $status = sanitizeInput($_POST['status'] ?? 'pending');
    $description = sanitizeInput($_POST['description'] ?? '');
    $id_offre = (int)($_POST['id_offre'] ?? 0);
    $id_user = $_SESSION['user']['id_user']; // Get user ID from session
    $payment_code = bin2hex(random_bytes(4)); // Generate random payment code

    // Validate email and phone match session
    if ($email !== $_SESSION['user']['email']) {
        $fieldErrors['email'] = "L'email doit correspondre à celui de votre compte.";
    }
    if ($telephone !== $_SESSION['user']['num_user']) {
        $fieldErrors['phone'] = "Le numéro de téléphone doit correspondre à celui de votre compte.";
    }

// Handle file upload for logo
    $logoFilename = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['logo']['type'];
        $fileSize = $_FILES['logo']['size'];
        $maxSize = 2 * 1024 * 1024; // 2MB max size

        if (!in_array($fileType, $allowedTypes)) {
            $fieldErrors['logo'] = "Le format du logo doit être JPEG, PNG, GIF ou WEBP.";
        } elseif ($fileSize > $maxSize) {
            $fieldErrors['logo'] = "La taille du logo ne doit pas dépasser 2 Mo.";
        } else {
            $uploadDir = __DIR__ . '/images/sponsors';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $logoFilename = basename($_FILES['logo']['name']);
            $uploadPath = $uploadDir . $logoFilename;

            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                $fieldErrors['logo'] = "Erreur lors de l'upload du logo.";
            }
        }
    } else {
        $fieldErrors['logo'] = "Le logo est obligatoire.";
}

    // Validate fields
    if (empty($nom_entreprise)) {
        $fieldErrors['companyName'] = "Le nom de l'entreprise est obligatoire";
    } elseif (!preg_match('/^[A-Za-z0-9À-ÿ\s\-&]{2,100}$/', $nom_entreprise)) {
        $fieldErrors['companyName'] = "Le nom doit contenir entre 2 et 100 caractères (lettres, chiffres, espaces ou &-)";
    }

    if (empty($email)) {
        $fieldErrors['email'] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = "Format d'email invalide (ex: exemple@domaine.com)";
    }

    if (empty($telephone)) {
        $fieldErrors['phone'] = "Le téléphone est obligatoire";
    } elseif (!preg_match('/^(\+216\s)?\d{8}$/', $telephone)) {
        $fieldErrors['phone'] = "Format de téléphone invalide (doit être +216 XXXXXXXX ou XXXXXXXX)";
    }

    if (empty($montant)) {
        $fieldErrors['amount'] = "Le montant est obligatoire";
    } elseif ($montant < 100 || $montant > 100000) {
        $fieldErrors['amount'] = "Le montant doit être compris entre 100 et 100 000 DT (vous avez saisi $montant DT)";
    }

    if (empty($duree)) {
        $fieldErrors['duration'] = "La durée est obligatoire";
    } elseif (!preg_match('/^[0-9]+\s*(mois|an|ans|jours|semaines)$/i', $duree)) {
        $fieldErrors['duration'] = "Format de durée invalide (ex: '3 mois', '1 an', '2 semaines')";
    }

    if (empty($avantage)) {
        $fieldErrors['benefits'] = "Les avantages sont obligatoires";
    } elseif (strlen($avantage) < 10 || strlen($avantage) > 500) {
        $fieldErrors['benefits'] = "Les avantages doivent contenir entre 10 et 500 caractères (vous avez ".strlen($avantage)." caractères)";
    }

    if (!empty($status) && !in_array($status, ['pending', 'approved', 'rejected'])) {
        $fieldErrors['status'] = "Statut invalide (doit être: pending, approved ou rejected)";
    }

    if (strlen($description) > 1000) {
        $fieldErrors['description'] = "La description ne doit pas dépasser 1000 caractères";
    }

    // Validate id_offre
    $validOfferIds = array_map('intval', array_column($offers, 'id_offre'));
    if ($id_offre === 0 || !in_array($id_offre, $validOfferIds, true)) {
        $fieldErrors['id_offre'] = "Veuillez sélectionner une offre valide.";
    }

    // If no errors, proceed with insertion
    if (empty($fieldErrors)) {
        try {
            $sponsor = new sponsor(
                $nom_entreprise,
                $email,
                $telephone,
                $montant,
                $duree,
                $avantage,
                $status,
                $id_offre,
                $id_user, // Pass id_user
                $logoFilename,
                $payment_code // Pass payment_code
            );

            $controller = new SponsorController();
            $success = $controller->addSponsor($sponsor);

            if ($success) {
                $_SESSION['sponsor_success'] = "Votre proposition de sponsoring a été envoyée avec succès !";
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['sponsor_error'] = "Une erreur s'est produite lors de l'enregistrement dans la base de données";
                header("Location: index.php");
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['sponsor_error'] = "Erreur technique: " . $e->getMessage();
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['sponsor_error'] = "Veuillez corriger les erreurs dans le formulaire.";
        $_SESSION['form_errors'] = $fieldErrors;
        header("Location: index.php");
        exit();
    }
}

// If method is not POST
http_response_code(405);
echo "Accès refusé : méthode non autorisée";
exit();
?>