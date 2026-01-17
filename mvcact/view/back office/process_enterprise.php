<?php
require_once __DIR__ . '/../../model/EnterpriseModel.php';
// Ajouter l'inclusion du fichier d'aide pour l'upload d'images
require_once __DIR__ . '/../../utils/upload_helper.php';

session_start();

$enterpriseModel = new EnterpriseModel();

// Vérifier si c'est une requête GET pour la suppression
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['operation']) && $_GET['operation'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    
    // Récupérer l'activité avant suppression
    $activity = $enterpriseModel->getEnterpriseActivityById($id, $category);
    
    // Supprimer l'image du serveur si elle existe (seulement pour les images locales)
    if ($activity && !empty($activity['image']) && strpos($activity['image'], 'cloudinary.com') === false) {
        $imagePath = __DIR__ . '/../front office/' . $activity['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // Supprimer l'activité de la base de données
    $success = $enterpriseModel->deleteEnterpriseActivity($id);
    
    if ($success) {
        $_SESSION['success'] = "Activité d'entreprise supprimée avec succès.";
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression de l'activité d'entreprise.";
    }
    
    // Rediriger vers le dashboard
    header('Location: dashboard.php?action=enterprise');
    exit;
}

// Vérifier si le formulaire a été soumis en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $operation = $_POST['operation'] ?? '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    
    // Récupérer et valider les données du formulaire
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $price_type = filter_input(INPUT_POST, 'price_type', FILTER_SANITIZE_STRING);
    
    // Gestion de l'image
    $imagePath = '';
    
    if ($operation == 'add' || (!empty($_FILES['image']['name']) && $operation == 'edit')) {
        // Vérifier si un fichier a été téléchargé
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            $maxSize = 5 * 1024 * 1024; // 5 MB
            
            if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] <= $maxSize) {
                // Télécharger l'image vers Cloudinary et obtenir l'URL
                $imagePath = uploadImage($_FILES['image'], true);
                
                if (empty($imagePath)) {
                    $_SESSION['error'] = "Erreur lors du téléchargement de l'image vers Cloudinary.";
                    header('Location: dashboard.php?action=enterprise');
                    exit;
                }
                    
                // Si c'est une modification et qu'il y a une ancienne image locale, la supprimer
                if ($operation === 'edit' && isset($_POST['current_image']) && !empty($_POST['current_image']) && strpos($_POST['current_image'], 'cloudinary.com') === false) {
                        $oldImagePath = __DIR__ . '/../front office/' . $_POST['current_image'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                }
            } else {
                $_SESSION['error'] = "Format d'image non valide ou taille trop grande.";
                header('Location: dashboard.php?action=enterprise');
                exit;
            }
        } else if ($operation == 'add') {
            $_SESSION['error'] = "Veuillez sélectionner une image.";
            header('Location: dashboard.php?action=enterprise');
            exit;
        }
    } else if ($operation == 'edit' && isset($_POST['current_image']) && !empty($_POST['current_image'])) {
        // Conserver l'image actuelle lors de la modification
        $imagePath = $_POST['current_image'];
    }
    
    // Exécuter l'opération
    $success = false;
    
    if ($operation == 'add') {
        // Ajouter une nouvelle activité d'entreprise
        $success = $enterpriseModel->addEnterpriseActivity(
            $name, 
            $description, 
            $price, 
            $price_type,
            $category, 
            $imagePath
        );
        
        if ($success) {
            $_SESSION['success'] = "Activité d'entreprise ajoutée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'activité d'entreprise.";
        }
    } else if ($operation == 'edit' && $id) {
        // Modifier une activité d'entreprise existante
        error_log("Modification de l'activité d'entreprise: ID=$id, Nom=$name, Prix=$price, Type=$price_type, Catégorie=$category, Image=$imagePath");
        
        $success = $enterpriseModel->updateEnterpriseActivity(
            $id,
            $name, 
            $description, 
            $price, 
            $price_type,
            $category, 
            $imagePath
        );
        
        if ($success) {
            $_SESSION['success'] = "Activité d'entreprise mise à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de l'activité d'entreprise.";
        }
    }
    
    // Rediriger vers le dashboard
    header('Location: dashboard.php?action=enterprise');
    exit;
} else {
    // Si ce n'est pas une requête POST ou GET valide, rediriger vers le dashboard
    header('Location: dashboard.php?action=enterprise');
    exit;
}
?> 