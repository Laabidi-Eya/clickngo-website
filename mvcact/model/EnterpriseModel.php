<?php 
require_once __DIR__ . '/../Database.php';

class EnterpriseModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAllEnterpriseActivities() {
        $sql = "SELECT * FROM enterprise_activities";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getActivitiesByCategory($category) {
        $stmt = $this->db->prepare("SELECT * FROM enterprise_activities WHERE category = ?");
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }

    public function getEnterpriseActivityById($id, $category = '') {
        $sql = "SELECT * FROM enterprise_activities WHERE id = ?";
        $params = [$id];
        
        if (!empty($category)) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: false;
    }

    /**
     * Récupère les activités d'entreprise pour une date spécifique
     */
    public function getEnterpriseActivitiesByDate($date) {
        // Format de date attendu: YYYY-MM-DD
        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';
        
        $stmt = $this->db->prepare("SELECT * FROM enterprise_activities WHERE created_at BETWEEN ? AND ? ORDER BY created_at ASC");
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll();
    }

    public function searchEnterpriseActivities($searchTerm, $category = '') {
        // Préparer le terme de recherche pour la requête LIKE
        $searchPattern = "%" . $searchTerm . "%";
        
        if (empty($category)) {
            // Recherche dans toutes les catégories
            if (is_numeric($searchTerm)) {
                // Recherche par nom ou par prix
                $sql = "SELECT * FROM enterprise_activities WHERE name LIKE ? OR price = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$searchPattern, floatval($searchTerm)]);
            } else {
                // Recherche par nom uniquement
                $sql = "SELECT * FROM enterprise_activities WHERE name LIKE ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$searchPattern]);
            }
        } else {
            // Recherche filtrée par catégorie
            if (is_numeric($searchTerm)) {
                // Recherche par nom ou par prix dans une catégorie spécifique
                $sql = "SELECT * FROM enterprise_activities WHERE (name LIKE ? OR price = ?) AND category = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$searchPattern, floatval($searchTerm), $category]);
            } else {
                // Recherche par nom uniquement dans une catégorie spécifique
                $sql = "SELECT * FROM enterprise_activities WHERE name LIKE ? AND category = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$searchPattern, $category]);
            }
        }
        
        return $stmt->fetchAll();
    }

    public function addEnterpriseActivity($name, $description, $price, $price_type, $category, $image = null) {
        $sql = "INSERT INTO enterprise_activities (name, description, price, price_type, category, image) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $description, $price, $price_type, $category, $image]);
    }

    public function updateEnterpriseActivity($id, $name, $description, $price, $price_type, $category, $image) {
        // Debugging
        error_log("updateEnterpriseActivity: ID=$id, Name=$name, Price=$price, Type=$price_type, Category=$category, Image=$image");
        
        // Vérifier si l'activité existe avant la mise à jour
        $activity = $this->getEnterpriseActivityById($id);
        if (!$activity) {
            error_log("Aucune activité trouvée avec l'ID: $id");
            return false;
        }
        
        try {
            $sql = "UPDATE enterprise_activities SET name = ?, description = ?, price = ?, price_type = ?, category = ?, image = ? 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$name, $description, $price, $price_type, $category, $image, $id]);
            
            if (!$result) {
                error_log("Échec de la mise à jour SQL: " . implode(' ', $stmt->errorInfo()));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Exception PDO lors de la mise à jour: " . $e->getMessage());
            return false;
        }
    }

    public function deleteEnterpriseActivity($id) {
        $stmt = $this->db->prepare("DELETE FROM enterprise_activities WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getDb() {
        return $this->db;
    }
}
?> 