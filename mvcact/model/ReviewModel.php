<?php
require_once __DIR__ . '/../Database.php';

class ReviewModel {
    private $db;
    private $conn;

    public function __construct() {
        try {
            $this->db = new Database();
            $this->conn = $this->db->connect();
            $this->createReviewsTable();
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données dans ReviewModel: " . $e->getMessage());
            throw new Exception("Erreur de connexion à la base de données");
        }
    }

    private function createReviewsTable() {
        try {
            // First, check if the activities table exists, if not create it
            $checkTableSql = "SHOW TABLES LIKE 'activities'";
            $stmt = $this->conn->query($checkTableSql);
            
            if ($stmt->rowCount() == 0) {
                // Activities table doesn't exist, create it
                $this->createActivitiesTable();
            }
            
            $sql = "CREATE TABLE IF NOT EXISTS reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                activity_id INT,
                activity_name VARCHAR(255) NOT NULL,
                customer_name VARCHAR(255) NOT NULL,
                customer_email VARCHAR(255) NOT NULL,
                rating INT NOT NULL,
                comment TEXT NOT NULL,
                image_path VARCHAR(255),
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (activity_id),
                CONSTRAINT fk_activity_id FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE SET NULL ON UPDATE CASCADE
            )";
            
            $this->conn->exec($sql);
        } catch(PDOException $e) {
            error_log("Erreur lors de la création de la table reviews: " . $e->getMessage());
            // Create without foreign key constraint if there's an error
            try {
                $sqlWithoutFK = "CREATE TABLE IF NOT EXISTS reviews (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    activity_id INT,
                    activity_name VARCHAR(255) NOT NULL,
                    customer_name VARCHAR(255) NOT NULL,
                    customer_email VARCHAR(255) NOT NULL,
                    rating INT NOT NULL,
                    comment TEXT NOT NULL,
                    image_path VARCHAR(255),
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                $this->conn->exec($sqlWithoutFK);
            } catch(PDOException $e2) {
                error_log("Deuxième erreur lors de la création de la table reviews: " . $e2->getMessage());
                // Continue execution
            }
        }
    }
    
    private function createActivitiesTable() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS activities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                price DECIMAL(10,2),
                image VARCHAR(255),
                category VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            
            $this->conn->exec($sql);
            error_log("Table activities créée avec succès");
        } catch(PDOException $e) {
            error_log("Erreur lors de la création de la table activities: " . $e->getMessage());
            // Continue execution
        }
    }

    public function getAllReviews() {
        $query = "SELECT * FROM reviews ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->query($query);
            if ($stmt === false) {
                throw new PDOException("Erreur d'exécution de la requête");
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des avis: " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des avis");
        }
    }

    public function getApprovedReviews() {
        $query = "SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->query($query);
            if ($stmt === false) {
                throw new PDOException("Erreur d'exécution de la requête");
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des avis approuvés: " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des avis approuvés");
        }
    }

    public function getReviewById($id) {
        $query = "SELECT * FROM reviews WHERE id = :id";
        $params = [':id' => $id];
        
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt->execute($params)) {
                throw new PDOException("Erreur d'exécution de la requête");
            }
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'avis #$id: " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération de l'avis");
        }
    }

    public function addReview($activityId, $activityName, $customerName, $customerEmail, $rating, $comment, $imagePath = null) {
        try {
            // Check if the activities table exists and has the referenced activity
            if ($activityId > 0) {
                $checkActivity = "SELECT id FROM activities WHERE id = :id";
                $stmtCheck = $this->conn->prepare($checkActivity);
                $stmtCheck->bindParam(':id', $activityId, PDO::PARAM_INT);
                $stmtCheck->execute();
                
                if ($stmtCheck->rowCount() == 0) {
                    // Activity ID doesn't exist, create a dummy activity
                    $this->createDummyActivity($activityId, $activityName);
                }
            }
            
            $sql = "INSERT INTO reviews (activity_id, activity_name, customer_name, customer_email, rating, comment, image_path) 
                    VALUES (:activity_id, :activity_name, :customer_name, :customer_email, :rating, :comment, :image_path)";
            
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new PDOException("Erreur de préparation de la requête");
            }
            
            // If activityId is 0 or null, set it to NULL in the database
            if (empty($activityId)) {
                $stmt->bindValue(':activity_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':activity_id', $activityId, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(':activity_name', $activityName, PDO::PARAM_STR);
            $stmt->bindParam(':customer_name', $customerName, PDO::PARAM_STR);
            $stmt->bindParam(':customer_email', $customerEmail, PDO::PARAM_STR);
            $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':image_path', $imagePath, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                throw new PDOException("Erreur d'exécution de la requête: " . $errorInfo[2]);
            }
            
            return true;
        } catch(PDOException $e) {
            error_log("Erreur lors de l'ajout d'un avis: " . $e->getMessage());
            throw new Exception("Erreur lors de l'ajout de l'avis");
        }
    }
    
    private function createDummyActivity($activityId, $activityName) {
        try {
            $sql = "INSERT INTO activities (id, name) VALUES (:id, :name)";
            $stmt = $this->conn->prepare($sql);
            
            // Only try to set the ID if it's a positive number
            if ($activityId > 0) {
                $stmt->bindParam(':id', $activityId, PDO::PARAM_INT);
            } else {
                // Use a default ID
                $defaultId = 1;
                $stmt->bindParam(':id', $defaultId, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(':name', $activityName, PDO::PARAM_STR);
            $stmt->execute();
            
            error_log("Activité temporaire créée avec ID: $activityId, Nom: $activityName");
        } catch(PDOException $e) {
            error_log("Erreur lors de la création de l'activité temporaire: " . $e->getMessage());
            // Continue execution
        }
    }

    public function updateReviewStatus($id, $status) {
        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
            return false;
        }
        
        $query = "UPDATE reviews SET status = :status WHERE id = :id";
        $params = [':id' => $id, ':status' => $status];
        
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                throw new PDOException("Erreur de préparation de la requête");
            }
            
            if (!$stmt->execute($params)) {
                throw new PDOException("Erreur d'exécution de la requête");
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du statut de l'avis #$id: " . $e->getMessage());
            throw new Exception("Erreur lors de la mise à jour du statut de l'avis");
        }
    }

    public function deleteReview($id) {
        $query = "DELETE FROM reviews WHERE id = :id";
        $params = [':id' => $id];
        
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt === false) {
                throw new PDOException("Erreur de préparation de la requête");
            }
            
            if (!$stmt->execute($params)) {
                throw new PDOException("Erreur d'exécution de la requête");
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'avis #$id: " . $e->getMessage());
            throw new Exception("Erreur lors de la suppression de l'avis");
        }
    }

    public function getAverageRating() {
        $query = "SELECT AVG(rating) as average FROM reviews WHERE status = 'approved'";
        
        try {
            $stmt = $this->conn->query($query);
            if ($stmt === false) {
                throw new PDOException("Erreur d'exécution de la requête");
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return !empty($result) && $result['average'] !== null ? round($result['average'], 1) : 0;
        } catch (PDOException $e) {
            error_log("Erreur lors du calcul de la note moyenne: " . $e->getMessage());
            throw new Exception("Erreur lors du calcul de la note moyenne");
        }
    }

    public function getRatingsDistribution() {
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        
        try {
            for ($i = 1; $i <= 5; $i++) {
                $query = "SELECT COUNT(*) as count FROM reviews WHERE rating = :rating AND status = 'approved'";
                $params = [':rating' => $i];
                
                $stmt = $this->conn->prepare($query);
                if ($stmt === false) {
                    throw new PDOException("Erreur de préparation de la requête");
                }
                
                if (!$stmt->execute($params)) {
                    throw new PDOException("Erreur d'exécution de la requête");
                }
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $distribution[$i] = $result ? (int)$result['count'] : 0;
            }
            return $distribution;
        } catch (PDOException $e) {
            error_log("Erreur lors du calcul de la distribution des notes: " . $e->getMessage());
            throw new Exception("Erreur lors du calcul de la distribution des notes");
        }
    }

    public function getDb() {
        return $this->conn;
    }

    public function addReviewWithoutActivityId($activityName, $customerName, $customerEmail, $rating, $comment, $imagePath = null) {
        try {
            $sql = "INSERT INTO reviews (activity_id, activity_name, customer_name, customer_email, rating, comment, image_path) 
                    VALUES (NULL, :activity_name, :customer_name, :customer_email, :rating, :comment, :image_path)";
            
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                throw new PDOException("Erreur de préparation de la requête");
            }
            
            $stmt->bindParam(':activity_name', $activityName, PDO::PARAM_STR);
            $stmt->bindParam(':customer_name', $customerName, PDO::PARAM_STR);
            $stmt->bindParam(':customer_email', $customerEmail, PDO::PARAM_STR);
            $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':image_path', $imagePath, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                throw new PDOException("Erreur d'exécution de la requête sans activity_id: " . $errorInfo[2]);
            }
            
            return true;
        } catch(PDOException $e) {
            error_log("Erreur lors de l'ajout d'un avis sans activity_id: " . $e->getMessage());
            // Dernière tentative - ignorer toutes les contraintes
            try {
                // Vérifier si la table a le bon format
                $this->ensureTableExists();
                
                // Insérer directement avec une requête SQL brute
                $sql = "INSERT INTO reviews (activity_name, customer_name, customer_email, rating, comment, image_path) 
                        VALUES ('".addslashes($activityName)."', '".addslashes($customerName)."', 
                        '".addslashes($customerEmail)."', ".(int)$rating.", '".addslashes($comment)."', 
                        ".($imagePath ? "'".addslashes($imagePath)."'" : "NULL").")";
                
                error_log("Requête de dernier recours: " . $sql);
                $result = $this->conn->exec($sql);
                
                if ($result === false) {
                    throw new PDOException("Échec de la requête de dernier recours");
                }
                
                return true;
            } catch (Exception $e2) {
                error_log("ÉCHEC FINAL pour ajouter un avis: " . $e2->getMessage());
                throw $e2;
            }
        }
    }
    
    private function ensureTableExists() {
        try {
            // Vérifier si la table reviews existe
            $stmt = $this->conn->query("SHOW TABLES LIKE 'reviews'");
            if ($stmt->rowCount() == 0) {
                // La table n'existe pas, on la crée
                $sql = "CREATE TABLE reviews (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    activity_id INT NULL,
                    activity_name VARCHAR(255) NOT NULL,
                    customer_name VARCHAR(255) NOT NULL,
                    customer_email VARCHAR(255) NOT NULL,
                    rating INT NOT NULL,
                    comment TEXT NOT NULL,
                    image_path VARCHAR(255),
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $this->conn->exec($sql);
                error_log("Table reviews créée avec succès");
                return true;
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification/création de la table reviews: " . $e->getMessage());
            return false;
        }
    }
}
?> 