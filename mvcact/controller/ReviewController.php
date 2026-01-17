<?php
require_once __DIR__ . '/../model/ReviewModel.php';
// Ajouter l'inclusion du fichier d'aide pour l'upload d'images
require_once __DIR__ . '/../utils/upload_helper.php';

class ReviewController {
    private $model;

    public function __construct() {
        $this->model = new ReviewModel();
    }

    public function index() {
        try {
            // Récupérer tous les avis approuvés pour l'affichage public
            return $this->model->getApprovedReviews();
        } catch (Exception $e) {
            error_log("Erreur dans ReviewController::index: " . $e->getMessage());
            return [];
        }
    }

    public function getApprovedReviews() {
        try {
            return $this->model->getApprovedReviews();
        } catch (Exception $e) {
            error_log("Erreur dans ReviewController::getApprovedReviews: " . $e->getMessage());
            return [];
        }
    }

    public function getAverageRating() {
        try {
            return $this->model->getAverageRating();
        } catch (Exception $e) {
            error_log("Erreur dans ReviewController::getAverageRating: " . $e->getMessage());
            return 0;
        }
    }

    public function getRatingsDistribution() {
        try {
            return $this->model->getRatingsDistribution();
        } catch (Exception $e) {
            error_log("Erreur dans ReviewController::getRatingsDistribution: " . $e->getMessage());
            return [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        }
    }

    public function addReview($activityId, $activityName, $customerName, $customerEmail, $rating, $comment, $image = null) {
        // Gestion de l'image
        $imagePath = null;
        
        if ($image && $image['error'] == 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            $maxSize = 2 * 1024 * 1024; // 2 MB
            
            if (in_array($image['type'], $allowedTypes) && $image['size'] <= $maxSize) {
                try {
                    // Télécharger l'image vers Cloudinary et obtenir l'URL
                    $imagePath = uploadImage($image, true); // true = sauvegarder aussi en local
                    
                    if (empty($imagePath)) {
                        error_log("Échec du téléchargement de l'image vers Cloudinary");
                        // Continue sans image
                    } else {
                        error_log("Image téléchargée avec succès vers Cloudinary: " . $imagePath);
                    }
                } catch (Exception $e) {
                    error_log("Exception lors du téléchargement d'image: " . $e->getMessage());
                    // Continue sans image
                }
            } else {
                error_log("Type ou taille d'image non valide. Type: " . $image['type'] . ", Taille: " . $image['size']);
            }
        }
        
        // Tente d'abord avec la méthode normale
        try {
            // Malgré l'échec du téléchargement de l'image, on continue avec l'ajout de l'avis
            $success = $this->model->addReview($activityId, $activityName, $customerName, $customerEmail, $rating, $comment, $imagePath);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Votre avis a été enregistré et sera publié après validation.'
                ];
            }
        } catch (Exception $e) {
            error_log("Première tentative d'ajout d'avis échouée: " . $e->getMessage());
            // On va essayer avec une autre méthode si la première a échoué
        }
        
        // Si on arrive ici, c'est que la première méthode a échoué, essayons d'insérer sans l'activity_id
        try {
            error_log("Tentative d'ajout d'avis sans activity_id");
            $success = $this->model->addReviewWithoutActivityId($activityName, $customerName, $customerEmail, $rating, $comment, $imagePath);
            
            return [
                'success' => $success,
                'message' => $success ? 'Votre avis a été enregistré et sera publié après validation.' : 'Une erreur est survenue lors de l\'enregistrement de votre avis.'
            ];
        } catch (Exception $e) {
            error_log("Erreur dans ReviewController::addReview (deuxième tentative): " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement de votre avis.'
            ];
        }
    }

    public function getAllReviews() {
        try {
            // Pour l'administration, récupérer tous les avis
            return $this->model->getAllReviews();
        } catch (Exception $e) {
            error_log("Erreur dans ReviewController::getAllReviews: " . $e->getMessage());
            return [];
        }
    }

    public function approveReview($id) {
        try {
            $review = $this->model->getReviewById($id);
            
            if (!$review) {
                return [
                    'success' => false,
                    'message' => 'Avis non trouvé'
                ];
            }
            
            $result = $this->model->updateReviewStatus($id, 'approved');
            
            return [
                'success' => $result,
                'message' => $result ? 'Avis approuvé avec succès' : 'Erreur lors de l\'approbation de l\'avis'
            ];
        } catch (Exception $e) {
            error_log("Erreur dans ReviewController::approveReview: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'approbation de l\'avis.'
            ];
        }
    }

    public function rejectReview($id) {
        try {
            $review = $this->model->getReviewById($id);
            
            if (!$review) {
                return [
                    'success' => false,
                    'message' => 'Avis non trouvé'
                ];
            }
            
            $result = $this->model->updateReviewStatus($id, 'rejected');
            
            return [
                'success' => $result,
                'message' => $result ? 'Avis rejeté avec succès' : 'Erreur lors du rejet de l\'avis'
            ];
        } catch (Exception $e) {
            error_log("Erreur dans ReviewController::rejectReview: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors du rejet de l\'avis.'
            ];
        }
    }

    public function deleteReview($id) {
        try {
            $review = $this->model->getReviewById($id);
            
            if (!$review) {
                return [
                    'success' => false,
                    'message' => 'Avis non trouvé'
                ];
            }
            
            $result = $this->model->deleteReview($id);
            
            return [
                'success' => $result,
                'message' => $result ? 'Avis supprimé avec succès' : 'Erreur lors de la suppression de l\'avis'
            ];
        } catch (Exception $e) {
            error_log("Erreur dans ReviewController::deleteReview: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression de l\'avis.'
            ];
        }
    }
}
?> 