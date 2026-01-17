<?php
require_once __DIR__ . '/../Database.php';

class ReservationModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        
        // Créer la table si elle n'existe pas
        $this->createReservationsTable();
    }
    
    /**
     * Crée la table des réservations si elle n'existe pas
     */
    private function createReservationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS reservations (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            activity_id INT(11) NOT NULL,
            activity_name VARCHAR(255) NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            reservation_date DATE NOT NULL,
            reservation_time TIME NOT NULL,
            people_count INT(11) NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            payment_status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
            error_log("Erreur lors de la création de la table reservations: " . $e->getMessage());
        }
    }

    /**
     * Ajoute une nouvelle réservation
     */
    public function addReservation($activity_id, $activity_name, $customer_name, $customer_email, $date, $time, $people_count, $total_price, $payment_status = 'pending') {
        $sql = "INSERT INTO reservations (activity_id, activity_name, customer_name, customer_email, reservation_date, 
                reservation_time, people_count, total_price, payment_status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $activity_id, 
                $activity_name, 
                $customer_name, 
                $customer_email, 
                $date, 
                $time, 
                $people_count, 
                $total_price, 
                $payment_status
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout de la réservation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère une réservation par son ID
     */
    public function getReservationById($id) {
        $sql = "SELECT * FROM reservations WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Récupère toutes les réservations
     */
    public function getAllReservations() {
        $sql = "SELECT * FROM reservations ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les réservations d'un client par son email
     */
    public function getReservationsByEmail($email) {
        $sql = "SELECT * FROM reservations WHERE customer_email = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les réservations pour une activité spécifique
     */
    public function getReservationsByActivityId($activity_id) {
        $sql = "SELECT * FROM reservations WHERE activity_id = ? ORDER BY reservation_date, reservation_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$activity_id]);
        return $stmt->fetchAll();
    }

    /**
     * Met à jour le statut de paiement d'une réservation
     */
    public function updatePaymentStatus($id, $status) {
        $sql = "UPDATE reservations SET payment_status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    /**
     * Annule une réservation (change son statut)
     */
    public function cancelReservation($id) {
        $sql = "UPDATE reservations SET payment_status = 'cancelled' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Vérifie la disponibilité d'une activité à une date et heure donnée
     */
    public function checkAvailability($activity_id, $date, $time) {
        // Ici, vous pourriez implémenter une logique pour vérifier si l'activité est disponible
        // en fonction des réservations existantes et de la capacité maximale
        $sql = "SELECT COUNT(*) as count, SUM(people_count) as total_people 
                FROM reservations 
                WHERE activity_id = ? AND reservation_date = ? AND reservation_time = ? 
                AND payment_status IN ('confirmed', 'pending')";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$activity_id, $date, $time]);
            $result = $stmt->fetch();
            
            // Récupérer la capacité maximale depuis la table des activités si possible
            $maxCapacity = $this->getActivityMaxCapacity($activity_id);
            
            // Si la valeur total_people est NULL (aucune réservation), la remplacer par 0
            $totalPeople = (is_null($result['total_people'])) ? 0 : intval($result['total_people']);
            
            return [
                'available' => ($totalPeople < $maxCapacity),
                'remaining' => $maxCapacity - $totalPeople
            ];
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de disponibilité: " . $e->getMessage());
            throw new Exception("Erreur de base de données lors de la vérification de disponibilité");
        }
    }
    
    /**
     * Récupère la capacité maximale d'une activité
     */
    private function getActivityMaxCapacity($activity_id) {
        try {
            $sql = "SELECT capacity FROM activities WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$activity_id]);
            $result = $stmt->fetch();
            
            // Si on trouve la capacité dans la base de données
            if ($result && isset($result['capacity']) && !is_null($result['capacity'])) {
                return intval($result['capacity']);
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de la capacité: " . $e->getMessage());
        }
        
        // Valeur par défaut si on ne trouve pas la capacité en base de données
        return 20;
    }

    /**
     * Récupère les créneaux horaires disponibles pour une activité à une date donnée
     */
    public function getAvailableTimeSlots($activity_id, $date) {
        // Exemple de créneaux horaires (à adapter selon les besoins)
        $allTimeSlots = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'];
        $availableSlots = [];
        
        foreach ($allTimeSlots as $time) {
            $availability = $this->checkAvailability($activity_id, $date, $time);
            if ($availability['available']) {
                $availableSlots[] = [
                    'time' => $time,
                    'remaining' => $availability['remaining']
                ];
            }
        }
        
        return $availableSlots;
    }

    /**
     * Récupère les réservations pour un mois et une année spécifiques
     */
    public function getReservationsByMonth($month, $year) {
        $startDate = sprintf("%04d-%02d-01", $year, $month);
        $endDay = date('t', strtotime($startDate)); // Dernier jour du mois
        $endDate = sprintf("%04d-%02d-%02d", $year, $month, $endDay);
        
        $query = "SELECT * FROM reservations 
                  WHERE reservation_date BETWEEN ? AND ? 
                  ORDER BY reservation_date ASC, reservation_time ASC";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des réservations du mois: " . $e->getMessage());
            return [];
        }
    }

    public function getDb() {
        return $this->db;
    }
}
?> 