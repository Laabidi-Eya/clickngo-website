<?php 
require_once __DIR__ . '/../Database.php';

class ActivityModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAllActivities() {
        $sql = "SELECT * FROM activities";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getActivityById($id) {
        $stmt = $this->db->prepare("SELECT * FROM activities WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: false;
    }

    public function addActivity($name, $description, $price, $location, $date, $category, $capacity, $image = null) {
        $sql = "INSERT INTO activities (name, description, price, location, date, category, capacity, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $description, $price, $location, $date, $category, $capacity, $image]);
    }

    public function updateActivity($id, $name, $description, $price, $location, $date, $category, $capacity, $image) {
        $sql = "UPDATE activities SET name = ?, description = ?, price = ?, location = ?, date = ?, category = ?, capacity = ?, image = ? 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $description, $price, $location, $date, $category, $capacity, $image, $id]);
    }

    public function deleteActivity($id) {
        $stmt = $this->db->prepare("DELETE FROM activities WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getUpcomingActivities() {
        $today = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("SELECT * FROM activities WHERE date >= ? ORDER BY date ASC");
        $stmt->execute([$today]);
        return $stmt->fetchAll();
    }

    public function getDailyActivity() {
        $start = date('Y-m-d 00:00:00');
        $end = date('Y-m-d 23:59:59');
        $stmt = $this->db->prepare("SELECT * FROM activities WHERE date BETWEEN ? AND ? ORDER BY date ASC LIMIT 1");
        $stmt->execute([$start, $end]);
        return $stmt->fetch() ?: false;
    }

    public function getActivityHistory() {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("SELECT * FROM activities WHERE date < ? ORDER BY date DESC");
        $stmt->execute([$now]);
        return $stmt->fetchAll();
    }

    public function getStatistics() {
        $stats = [];

        $stmt = $this->db->query("SELECT COUNT(*) FROM activities");
        $stats['total_activities'] = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT SUM(capacity) FROM activities");
        $stats['total_participants'] = $stmt->fetchColumn() ?: 0;

        $stmt = $this->db->query("SELECT COUNT(DISTINCT location) FROM activities");
        $stats['total_cities'] = $stmt->fetchColumn();

        return $stats;
    }

    public function getNotifications() {
        return [
            ['message' => '📅 Rappel : Yoga du matin demain à 8h00 !'],
            ['message' => '⭐ Nouvelle suggestion : Essayez la Zumba en plein air !'],
        ];
    }

    public function getParticipantsByMonth() {
        $sql = "SELECT 
                    DATE_FORMAT(date, '%Y-%m') AS month, 
                    SUM(capacity) AS total_participants,
                    COUNT(*) AS total_activities
                FROM activities 
                GROUP BY DATE_FORMAT(date, '%Y-%m')
                ORDER BY month ASC";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetchAll();
        
        // Si aucune donnée n'est disponible, ajouter des données de test
        if (empty($result)) {
            $currentMonth = date('Y-m');
            $previousMonth = date('Y-m', strtotime('-1 month'));
            $twoMonthsAgo = date('Y-m', strtotime('-2 months'));
            
            $result = [
                ['month' => $twoMonthsAgo, 'total_participants' => 120, 'total_activities' => 5],
                ['month' => $previousMonth, 'total_participants' => 150, 'total_activities' => 7],
                ['month' => $currentMonth, 'total_participants' => 200, 'total_activities' => 10]
            ];
        }
        
        return $result;
    }

    public function getActivitiesByCategory() {
        $sql = "SELECT category, COUNT(*) AS count FROM activities GROUP BY category";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetchAll();
        
        // Si aucune donnée n'est disponible, ajouter des données de test
        if (empty($result)) {
            $result = [
                ['category' => 'sport', 'count' => 8],
                ['category' => 'bien-etre', 'count' => 5],
                ['category' => 'culture', 'count' => 3],
                ['category' => 'Famille', 'count' => 4],
                ['category' => 'Aquatique', 'count' => 6]
            ];
        }
        
        return $result;
    }

    public function searchActivitiesByName($searchTerm) {
        $term = "%" . $searchTerm . "%";
        $stmt = $this->db->prepare("SELECT * FROM activities WHERE name LIKE ?");
        $stmt->execute([$term]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les activités pour une date spécifique
     */
    public function getActivitiesByDate($date) {
        // Format de date attendu: YYYY-MM-DD
        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';
        
        $stmt = $this->db->prepare("SELECT * FROM activities WHERE date BETWEEN ? AND ? ORDER BY date ASC");
        $stmt->execute([$start, $end]);
        return $stmt->fetchAll();
    }

    /**
     * Ajoute un nouvel avis client
     */
    public function addReview($activity_name, $customer_name, $rating, $comment) {
        // Créer la table des avis si elle n'existe pas
        $this->createReviewsTable();
        
        $sql = "INSERT INTO reviews (activity_name, customer_name, rating, comment, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$activity_name, $customer_name, $rating, $comment]);
    }
    
    /**
     * Récupère tous les avis clients
     */
    public function getAllReviews() {
        // Créer la table des avis si elle n'existe pas
        $this->createReviewsTable();
        
        $sql = "SELECT * FROM reviews ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère la note moyenne de tous les avis
     */
    public function getAverageRating() {
        // Créer la table des avis si elle n'existe pas
        $this->createReviewsTable();
        
        $sql = "SELECT AVG(rating) as average_rating, COUNT(*) as total_reviews FROM reviews";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        
        return [
            'average' => $result['average_rating'] ? round($result['average_rating'], 1) : 0,
            'count' => (int)$result['total_reviews']
        ];
    }
    
    /**
     * Crée la table des avis si elle n'existe pas
     */
    private function createReviewsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS reviews (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            activity_name VARCHAR(255) NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            rating INT(1) NOT NULL,
            comment TEXT NOT NULL,
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log("Erreur lors de la création de la table reviews: " . $e->getMessage());
        }
    }
}
