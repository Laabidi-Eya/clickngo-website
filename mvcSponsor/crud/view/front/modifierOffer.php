<?php
require_once(__DIR__ . "/../../controller/controller.php");
require_once(__DIR__ . "/../../model/model.php");
require_once(__DIR__ . "../../../../../mvcEvent/Config.php");

// Récupérer l'offre à modifier
if (!isset($_GET['id'])) {
    header("Location: ../back/back.php?error=missing_id");
    exit();
}

$id_offer = (int)$_GET['id'];
$controller = new sponsorController();
$offer = $controller->getOfferById($id_offer);

if (!$offer) {
    header("Location: ../back/back.php?error=offer_not_found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre_offre = htmlspecialchars($_POST['titre_offre']);
    $description_offre = htmlspecialchars($_POST['description_offre']);
    $evenement = htmlspecialchars($_POST['evenement']);
    $montant_offre = (float)$_POST['montant_offre'];
    $status = htmlspecialchars($_POST['status']);

    // Handle image upload
    $imageFileName = $offer['image']; // default to existing image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../view/front/images/';
        $tmpName = $_FILES['image']['tmp_name'];
        $originalName = basename($_FILES['image']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($extension, $allowedExtensions)) {
            // Use original filename instead of unique id
            $imageFileName = $originalName;
            $destination = $uploadDir . $imageFileName;

            if (!move_uploaded_file($tmpName, $destination)) {
                $error = "Erreur lors de l'upload de l'image.";
            }
        } else {
            $error = "Le format de l'image n'est pas supporté. Formats acceptés: jpg, jpeg, png, gif.";
        }
    }

    $offerObj = new Offre($titre_offre, $description_offre, $evenement, $montant_offre, $status, $imageFileName);
    $offerObj->setId_offre($id_offer);

    if (!isset($error)) {
        $success = $controller->updateOffer($offerObj);

        if ($success) {
            header("Location: ../back/back.php?update_success=1");
            exit();
        } else {
            $error = "Une erreur est survenue lors de la mise à jour.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Offre</title>
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
    </style>
</head>
<body>
    <h1>Modifier l'Offre</h1>
    
    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div>
            <label for="titre_offre">Titre de l'offre:</label>
            <input type="text" id="titre_offre" name="titre_offre" value="<?= htmlspecialchars($offer['titre_offre']) ?>" required>
        </div>
        
        <div>
            <label for="description_offre">Description:</label>
            <textarea id="description_offre" name="description_offre" required><?= htmlspecialchars($offer['description_offre']) ?></textarea>
        </div>
        
        <div>
            <label for="evenement">Événement:</label>
            <input type="text" id="evenement" name="evenement" value="<?= htmlspecialchars($offer['evenement']) ?>" required>
        </div>
        
        <div>
            <label for="montant_offre">Montant (dt):</label>
            <input type="number" step="0.01" id="montant_offre" name="montant_offre" value="<?= htmlspecialchars($offer['montant_offre']) ?>" required>
        </div>
        
        <div>
            <label for="status">Statut:</label>
            <select id="status" name="status" required>
                <option value="libre" <?= $offer['status'] === 'libre' ? 'selected' : '' ?>>Libre</option>
                <option value="occupé" <?= $offer['status'] === 'occupé' ? 'selected' : '' ?>>Occupé</option>
            </select>
        </div>
        
        <div>
            <label for="image">Image actuelle:</label><br>
            <?php if (!empty($offer['image'])): ?>
                <img src="../../view/front/images/<?= htmlspecialchars($offer['image']) ?>" alt="Image de l'offre" style="max-width: 200px; height: auto; border-radius: 8px; margin-bottom: 12px;" />
            <?php else: ?>
                <p>Aucune image associée</p>
            <?php endif; ?>
        </div>
        
        <div>
            <label for="image">Changer l'image (optionnel):</label>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif" />
        </div>
        
        <button type="submit">Mettre à jour</button>
        <a href="../back/back.php">Annuler</a>
    </form>
</body>
</html>