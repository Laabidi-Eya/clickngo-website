<?php
require_once(__DIR__ . "/../model/AvisModel.php");
require_once(__DIR__ . "../../config.php");


error_reporting(E_ALL);
ini_set('display_errors', 1);

class AvisController {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Config::getConnexion();
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - AvisController: Database connected successfully' . "\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - AvisController: Database connection error: ' . $e->getMessage() . "\n", FILE_APPEND);
            die('Erreur de connexion à la base de données: ' . $e->getMessage());
        }
    }

    public function getAllAvis() {
        try {
            $sql = "SELECT a.*, p.name as product_name 
                    FROM avis a 
                    JOIN products p ON a.product_id = p.id";
            
            $params = [];
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = '%' . $_GET['search'] . '%';
                $sql .= " WHERE p.name LIKE :search OR a.email LIKE :search OR a.comment LIKE :search";
                $params[':search'] = $search;
            }

            $sql .= " ORDER BY a.created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $avis = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $avis[] = [
                    'id' => $row['id'],
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'stars' => (int)$row['stars'],
                    'email' => $row['email'],
                    'comment' => $row['comment'],
                    'created_at' => $row['created_at'],
                    'status' => $row['status'],
                    'rejection_reason' => $row['rejection_reason']
                ];
            }
            
            return ['success' => true, 'avis' => $avis];
        } catch (PDOException $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - getAllAvis: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getPendingAvis() {
        try {
            $sql = "SELECT a.*, p.name as product_name 
                    FROM avis a 
                    JOIN products p ON a.product_id = p.id 
                    WHERE a.status = 'pending' 
                    ORDER BY a.created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $avis = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $avis[] = [
                    'id' => $row['id'],
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'stars' => (int)$row['stars'],
                    'email' => $row['email'],
                    'comment' => $row['comment'],
                    'created_at' => $row['created_at'],
                    'status' => $row['status'],
                    'rejection_reason' => $row['rejection_reason']
                ];
            }
            
            return ['success' => true, 'avis' => $avis];
        } catch (PDOException $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - getPendingAvis: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getPendingReviewsCount() {
        try {
            $sql = "SELECT COUNT(*) as pending_count 
                    FROM avis 
                    WHERE status = 'pending'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'pending_count' => (int)$row['pending_count']
            ];
        } catch (PDOException $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - getPendingReviewsCount: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getAvisById($id) {
        try {
            $sql = "SELECT a.*, p.name as product_name 
                    FROM avis a 
                    JOIN products p ON a.product_id = p.id 
                    WHERE a.id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                throw new Exception('Avis non trouvé');
            }
            
            return [
                'success' => true,
                'avis' => [
                    'id' => $row['id'],
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'stars' => (int)$row['stars'],
                    'email' => $row['email'],
                    'comment' => $row['comment'],
                    'created_at' => $row['created_at'],
                    'status' => $row['status'],
                    'rejection_reason' => $row['rejection_reason']
                ]
            ];
        } catch (Exception $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - getAvisById: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getAvisByProductId($product_id) {
        try {
            $sql = "SELECT a.*, p.name as product_name 
                    FROM avis a 
                    JOIN products p ON a.product_id = p.id 
                    WHERE a.product_id = :product_id 
                    AND a.status = 'approved' 
                    ORDER BY a.created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':product_id' => $product_id]);
            
            $avis = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $avis[] = [
                    'id' => $row['id'],
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'stars' => (int)$row['stars'],
                    'email' => $row['email'],
                    'comment' => $row['comment'],
                    'created_at' => $row['created_at'],
                    'status' => $row['status'],
                    'rejection_reason' => $row['rejection_reason']
                ];
            }
            
            return ['success' => true, 'avis' => $avis];
        } catch (PDOException $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - getAvisByProductId: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getApprovedReviews() {
        try {
            $sql = "SELECT a.*, p.name as product_name, p.photo as product_photo 
                    FROM avis a 
                    JOIN products p ON a.product_id = p.id 
                    WHERE a.status = 'approved' 
                    ORDER BY a.created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $avis = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $avis[] = [
                    'id' => $row['id'],
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'product_photo' => $row['product_photo'],
                    'stars' => (int)$row['stars'],
                    'email' => $row['email'],
                    'comment' => $row['comment'],
                    'created_at' => $row['created_at'],
                    'status' => $row['status'],
                    'rejection_reason' => $row['rejection_reason']
                ];
            }
            
            return ['success' => true, 'avis' => $avis];
        } catch (PDOException $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - getApprovedReviews: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function addAvis($product_id, $stars, $email, $comment = null) {
        try {
            // Verify product exists
            $stmt = $this->pdo->prepare("SELECT id FROM products WHERE id = :id");
            $stmt->execute([':id' => $product_id]);
            if (!$stmt->fetch()) {
                throw new Exception("Produit non trouvé");
            }

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Adresse email invalide");
            }

            // Validate stars
            $stars = (int)$stars;
            if ($stars < 1 || $stars > 5) {
                throw new Exception("Les étoiles doivent être entre 1 et 5");
            }

            $sql = "INSERT INTO avis 
                    (product_id, stars, email, comment, created_at, status) 
                    VALUES (:product_id, :stars, :email, :comment, NOW(), 'pending')";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':product_id' => $product_id,
                ':stars' => $stars,
                ':email' => $email,
                ':comment' => $comment
            ]);

            return ['success' => true, 'message' => 'Avis ajouté avec succès, en attente d\'approbation'];
        } catch (Exception $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - addAvis: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateAvis($id, $product_id, $stars, $email, $comment = null, $status = null, $rejection_reason = null) {
        try {
            // Verify product exists
            $stmt = $this->pdo->prepare("SELECT id FROM products WHERE id = :id");
            $stmt->execute([':id' => $product_id]);
            if (!$stmt->fetch()) {
                throw new Exception("Produit non trouvé");
            }

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Adresse email invalide");
            }

            // Validate stars
            $stars = (int)$stars;
            if ($stars < 1 || $stars > 5) {
                throw new Exception("Les étoiles doivent être entre 1 et 5");
            }

            // Validate status if provided
            if ($status !== null) {
                $allowedStatuses = ['pending', 'approved', 'rejected'];
                if (!in_array($status, $allowedStatuses)) {
                    throw new Exception("Statut invalide. Valeurs autorisées : " . implode(', ', $allowedStatuses));
                }
            }

            $sql = "UPDATE avis SET 
                    product_id = :product_id, 
                    stars = :stars, 
                    email = :email, 
                    comment = :comment";
            $params = [
                ':id' => $id,
                ':product_id' => $product_id,
                ':stars' => $stars,
                ':email' => $email,
                ':comment' => $comment
            ];

            if ($status !== null) {
                $sql .= ", status = :status";
                $params[':status'] = $status;
            }

            if ($rejection_reason !== null) {
                $sql .= ", rejection_reason = :rejection_reason";
                $params[':rejection_reason'] = $rejection_reason;
            }

            $sql .= " WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return ['success' => true, 'message' => 'Avis mis à jour avec succès'];
        } catch (Exception $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - updateAvis: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function approveAvis($id) {
        try {
            $sql = "UPDATE avis SET status = 'approved', rejection_reason = NULL WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Avis non trouvé");
            }

            return ['success' => true, 'message' => 'Avis approuvé avec succès'];
        } catch (Exception $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - approveAvis: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function rejectAvis($id, $rejection_reason = null) {
        try {
            $sql = "UPDATE avis SET status = 'rejected', rejection_reason = :rejection_reason WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':rejection_reason' => $rejection_reason
            ]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Avis non trouvé");
            }

            return ['success' => true, 'message' => 'Avis rejeté avec succès'];
        } catch (Exception $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - rejectAvis: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteAvis($id) {
        try {
            $sql = "DELETE FROM avis WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Avis non trouvé");
            }

            return ['success' => true, 'message' => 'Avis supprimé avec succès'];
        } catch (PDOException $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - deleteAvis: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getAverageRating($product_id) {
        try {
            $sql = "SELECT AVG(stars) as average_rating, COUNT(*) as review_count 
                    FROM avis 
                    WHERE product_id = :product_id AND status = 'approved'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':product_id' => $product_id]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'average_rating' => round($row['average_rating'], 1) ?? 0,
                'review_count' => (int)$row['review_count']
            ];
        } catch (PDOException $e) {
            file_put_contents('../debug_log.txt', date('Y-m-d H:i:s') . ' - getAverageRating: Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

$controller = new AvisController();

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    
    try {
        if (isset($_GET['action']) && $_GET['action'] === 'get_all') {
            $avis = $controller->getAllAvis();
            echo json_encode($avis);
            exit;
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'get_pending') {
            $avis = $controller->getPendingAvis();
            echo json_encode($avis);
            exit;
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'get_pending_count') {
            $result = $controller->getPendingReviewsCount();
            echo json_encode($result);
            exit;
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'get_one' && isset($_GET['id'])) {
            $avis = $controller->getAvisById($_GET['id']);
            echo json_encode($avis);
            exit;
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'get_by_product' && isset($_GET['product_id'])) {
            $avis = $controller->getAvisByProductId($_GET['product_id']);
            echo json_encode($avis);
            exit;
        }

        if (isset($_GET['action']) && $_GET['action'] === 'get_approved') {
            $avis = $controller->getApprovedReviews();
            echo json_encode($avis);
            exit;
        }

        if (isset($_GET['action']) && $_GET['action'] === 'get_average_rating' && isset($_GET['product_id'])) {
            $result = $controller->getAverageRating($_GET['product_id']);
            echo json_encode($result);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
            $result = $controller->addAvis(
                $_POST['product_id'],
                $_POST['stars'],
                $_POST['email'],
                $_POST['comment'] ?? null
            );
            echo json_encode($result);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
            $result = $controller->updateAvis(
                $_POST['id'],
                $_POST['product_id'],
                $_POST['stars'],
                $_POST['email'],
                $_POST['comment'] ?? null,
                $_POST['status'] ?? null,
                $_POST['rejection_reason'] ?? null
            );
            echo json_encode($result);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve') {
            $result = $controller->approveAvis($_POST['id']);
            echo json_encode($result);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reject') {
            $result = $controller->rejectAvis(
                $_POST['id'],
                $_POST['rejection_reason'] ?? null
            );
            echo json_encode($result);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
            $result = $controller->deleteAvis($_POST['id']);
            echo json_encode($result);
            exit;
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>