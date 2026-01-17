<?php
require_once(__DIR__ . "/../../controller/controller.php");
require_once(__DIR__ . "/../../model/model.php");
require_once(__DIR__ . "../../../../../mvcEvent/Config.php");

// Récupérer le sponsor à modifier
if (!isset($_GET['id'])) {
    header("Location: index.php?error=missing_id");
    exit();
}

$id_sponsor = (int)$_GET['id'];
$controller = new sponsorController();
$sponsor = $controller->getSponsorById($id_sponsor);

// Get offers list for id_offre select input
$offers = $controller->listOffers();

if (!$sponsor) {
    header("Location: index.php?error=sponsor_not_found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et assainchissement des données
    $nom_entreprise = htmlspecialchars($_POST['companyName']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telephone = htmlspecialchars($_POST['phone']);
    $montant = (float) filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $duree = htmlspecialchars($_POST['duration']);
    $avantage = htmlspecialchars($_POST['benefits']);
    $status = isset($_POST['status']) ? htmlspecialchars($_POST['status']) : 'pending';
    $id_offre = isset($_POST['id_offre']) ? (int)$_POST['id_offre'] : 0;
    $payment_code = isset($_POST['payment_code']) ? htmlspecialchars($_POST['payment_code']) : $sponsor['payment_code'];
    $id_user = isset($_POST['id_user']) ? (int)$_POST['id_user'] : $sponsor['id_user'];

    // Validation des champs
    $errors = [];

    if (empty($nom_entreprise)) {
        $errors[] = "Le nom de l'entreprise est obligatoire";
    } elseif (!preg_match('/^[A-Za-z0-9À-ÿ\s\-&]{2,100}$/', $nom_entreprise)) {
        $errors[] = "Le nom doit contenir entre 2 et 100 caractères (lettres, chiffres, espaces ou &-)";
    }

    if (empty($email)) {
        $errors[] = "L'email est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide (ex: exemple@domaine.com)";
    }

    if (empty($telephone)) {
        $errors[] = "Le téléphone est obligatoire";
    } elseif (!preg_match('/^(\+216\s)?\d{8}$/', $telephone)) {
        $errors[] = "Format de téléphone invalide (doit être +216 XXXXXXXX ou XXXXXXXX)";
    }

    if (empty($montant)) {
        $errors[] = "Le montant est obligatoire";
    } elseif ($montant < 100 || $montant > 100000) {
        $errors[] = "Le montant doit être compris entre 100 et 100 000 DT (vous avez saisi $montant DT)";
    }

    if (empty($duree)) {
        $errors[] = "La durée est obligatoire";
    } elseif (!preg_match('/^[0-9]+\s*(mois|an|ans|jours|semaines)$/i', $duree)) {
        $errors[] = "Format de durée invalide (ex: '3 mois', '1 an', '2 semaines')";
    }

    if (empty($avantage)) {
        $errors[] = "Les avantages sont obligatoires";
    } elseif (strlen($avantage) < 10 || strlen($avantage) > 500) {
        $errors[] = "Les avantages doivent contenir entre 10 et 500 caractères (vous avez ".strlen($avantage)." caractères)";
    }

    if (!empty($status) && !in_array($status, ['pending', 'approved', 'rejected'])) {
        $errors[] = "Statut invalide (doit être: pending, approved ou rejected)";
    }

    // Validate id_offre
    $validOfferIds = array_column($offers, 'id_offre');
    if ($id_offre === 0 || !in_array($id_offre, $validOfferIds, true)) {
        $errors[] = "Veuillez sélectionner une offre valide.";
    }

    if (empty($errors)) {
        // Handle logo upload
        $logoFilename = $sponsor['logo'] ?? null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/images/sponsors/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $originalName = basename($_FILES['logo']['name']);
            $targetFilePath = $uploadDir . $originalName;

            // Delete old logo if exists
            if (!empty($sponsor['logo']) && file_exists($uploadDir . $sponsor['logo'])) {
                unlink($uploadDir . $sponsor['logo']);
            }

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFilePath)) {
                $logoFilename = $originalName;
            } else {
                $errors[] = "Erreur lors du téléchargement du logo.";
            }
        }

        if (empty($errors)) {
            $sponsorObj = new sponsor(
                $nom_entreprise,
                $email,
                $telephone,
                $montant,
                $duree,
                $avantage,
                $status,
                $id_offre,
                $id_user,
                $logoFilename,
                $payment_code
            );
            $sponsorObj->setId_sponsor($id_sponsor);

            $success = $controller->updateSponsor($sponsorObj);

            if ($success) {
                header("Location: index.php?update_success=1");
                exit();
            } else {
                $error = "Une erreur est survenue lors de la mise à jour.";
            }
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Sponsor</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 40px;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #3498db;
            outline: none;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        a {
            display: inline-block;
            margin-top: 10px;
            color: #888;
            text-decoration: none;
            font-size: 14px;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .current-logo {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .current-logo img {
            max-width: 150px;
            max-height: 150px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Modifier le Sponsor</h1>

    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div>
            <label for="companyName">Nom de l'entreprise:</label>
            <input type="text" id="companyName" name="companyName" value="<?= htmlspecialchars($sponsor['nom_entreprise']) ?>" required>
        </div>

        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($sponsor['email']) ?>" required>
        </div>

        <div>
            <label for="phone">Téléphone:</label>
            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($sponsor['telephone']) ?>" required>
        </div>

        <div>
            <label for="amount">Montant:</label>
            <input type="number" step="0.01" id="amount" name="amount" value="<?= htmlspecialchars($sponsor['montant']) ?>" required>
        </div>

        <div>
            <label for="duration">Durée:</label>
            <input type="text" id="duration" name="duration" value="<?= htmlspecialchars($sponsor['duree']) ?>" required>
        </div>

        <div>
            <label for="benefits">Avantages:</label>
            <textarea id="benefits" name="benefits" rows="5" required><?= htmlspecialchars($sponsor['avantage']) ?></textarea>
        </div>

        <div>
            <label for="status">Statut:</label>
            <select id="status" name="status" required>
                <option value="pending" <?= $sponsor['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                <option value="approved" <?= $sponsor['status'] === 'approved' ? 'selected' : '' ?>>Approuvé</option>
                <option value="rejected" <?= $sponsor['status'] === 'rejected' ? 'selected' : '' ?>>Rejeté</option>
            </select>
        </div>

        <div>
            <label for="id_offre">Offre associée:</label>
            <select id="id_offre" name="id_offre" required>
                <option value="">-- Choisissez une offre --</option>
                <?php foreach ($offers as $offer): ?>
                    <option value="<?= htmlspecialchars($offer['id_offre']) ?>" <?= ($sponsor['id_offre'] == $offer['id_offre']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($offer['titre_offre']) ?> (<?= htmlspecialchars($offer['montant_offre']) ?> DT)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="id_user">ID Utilisateur:</label>
            <input type="number" id="id_user" name="id_user" value="<?= htmlspecialchars($sponsor['id_user'] ?? '') ?>">
        </div>

        <div>
            <label for="payment_code">Code de paiement:</label>
            <input type="text" id="payment_code" name="payment_code" value="<?= htmlspecialchars($sponsor['payment_code'] ?? '') ?>">
        </div>

        <div>
            <label for="logo">Logo de l'entreprise:</label>
            <input type="file" id="logo" name="logo" accept="image/*">
            
            <?php if (!empty($sponsor['logo'])): ?>
                <div class="current-logo">
                    <p>Logo actuel: <?= htmlspecialchars($sponsor['logo']) ?></p>
                    <img src="images/sponsors/<?= htmlspecialchars($sponsor['logo']) ?>" alt="Logo actuel">
                </div>
            <?php endif; ?>
        </div>

        <button type="submit">Mettre à jour</button>
        <a href="index.php">Annuler</a>
    </form>
</body>
</html>