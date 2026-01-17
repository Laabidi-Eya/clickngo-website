<?php 
require_once 'C:/xampp/htdocs/projet Web/mvcEvent/Config.php';
require 'C:/xampp/htdocs/projet Web/mvcEvent/Model/Event.php';
require_once 'ChaiseController.php';

class EventController {
private $db;
// Dynamically get user_id from session
private static $global_user_id;

public function __construct($db = null) {
    $this->db = $db ?: Config::getConnexion();

    // Initialiser la session si ce n’est pas déjà fait
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['user']['id_user'])) {
        self::$global_user_id = $_SESSION['user']['id_user'];
    } else {
        self::$global_user_id = null; // ou tu peux gérer une redirection si nécessaire
    }
}

    public function checkForUpdates($lastKnownHash) {
        $sql = "SELECT 
                COUNT(*) as event_count, 
                CASE 
                    WHEN COUNT(*) = 0 THEN MD5('EMPTY_TABLE')
                    ELSE MD5(GROUP_CONCAT(
                        CONCAT_WS('|', id, name, description, price, updated_at) 
                        ORDER BY updated_at DESC
                        SEPARATOR '||'
                    ))
                END as current_hash
                FROM evenements";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
    
            // Debug logging
            error_log("LastHash: $lastKnownHash | CurrentHash: ".$result['current_hash']);
    
            return [
                'has_changes' => ($result['current_hash'] !== $lastKnownHash),
                'new_hash' => $result['current_hash'],
                'event_count' => $result['event_count']
            ];
        } catch (Exception $e) {
            error_log('Error in checkForUpdates: '.$e->getMessage());
            return ['has_changes' => false, 'new_hash' => $lastKnownHash];
        }
    }

    public function getEvents() {
        $sql = "SELECT id, category, name, description, price, duration, date, longitude, latitude, place_name,
       image_url AS imageUrl, total_seats AS totalSeats, reserved_seats AS reservedSeats,
       UNIX_TIMESTAMP(created_at) AS created_at, UNIX_TIMESTAMP(updated_at) AS updated_at
FROM evenements";
    
        try {
            $query = $this->db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getEvents: ' . $e->getMessage());
        }
    }

    public function addEvent($event) {
        error_log('addEvent called with data: ' . json_encode([
            'category' => $event->getCategory(),
            'name' => $event->getName(),
            'description' => $event->getDescription(),
            'price' => $event->getPrice(),
            'duration' => $event->getDuration(),
            'date' => $event->getDate(),
            'longitude' => $event->getLongitude(),
            'latitude' => $event->getLatitude(),
            'place_name' => $event->getPlaceName(),
            'image_url' => $event->getImageUrl(),
            'total_seats' => $event->getTotalSeats(),
            'reserved_seats' => $event->getReservedSeats()
        ]));
    
        $longitude = $event->getLongitude();
        $latitude = $event->getLatitude();
        $placeName = $event->getPlaceName();
    
        if (empty($longitude) || empty($latitude) || !is_numeric($longitude) || !is_numeric($latitude)) {
            error_log('Validation failed: Invalid GPS coordinates');
            throw new Exception('Les coordonnées GPS doivent être des nombres valides.');
        }
    
        if (empty($placeName) || strlen($placeName) < 3) {
            error_log('Validation failed: Invalid place name');
            throw new Exception('Le nom du lieu est requis et doit contenir au moins 3 caractères.');
        }
    
        try {
            $this->db->beginTransaction();
            $sql = "INSERT INTO evenements (
                category, name, description, price, duration, date, longitude, latitude, place_name,
                image_url, total_seats, reserved_seats, created_at, updated_at
            ) VALUES (
                :category, :name, :description, :price, :duration, :date, :longitude, :latitude, :place_name,
                :imageUrl, :totalSeats, :reservedSeats, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )";
            $query = $this->db->prepare($sql);
            $query->bindValue(':category', $event->getCategory());
            $query->bindValue(':name', $event->getName());
            $query->bindValue(':description', $event->getDescription());
            $query->bindValue(':price', $event->getPrice());
            $query->bindValue(':duration', $event->getDuration());
            $query->bindValue(':date', $event->getDate());
            $query->bindValue(':longitude', $longitude);
            $query->bindValue(':latitude', $latitude);
            $query->bindValue(':place_name', $placeName);
            $query->bindValue(':imageUrl', $event->getImageUrl());
            $query->bindValue(':totalSeats', $event->getTotalSeats());
            $query->bindValue(':reservedSeats', $event->getReservedSeats() ?? 0);
            $query->execute();
    
            $eventId = $this->db->lastInsertId();
            $chaiseController = new ChaiseController($this->db);
            $chaiseController->addMultipleChaises($eventId, $event->getTotalSeats());
    
            $this->db->commit();
            error_log('Event added successfully with ID: ' . $eventId);
            return $eventId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error in addEvent: ' . $e->getMessage());
            throw new Exception('Erreur lors de l\'ajout de l\'événement : ' . $e->getMessage());
        }
    }

    public function getAllEvents()
{
    $db = Config::getConnexion();
    $stmt = $db->query("SELECT id, name FROM evenements");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function updateEvent($event, $id) {
        error_log('updateEvent called with data: ' . json_encode([
            'id' => $id,
            'category' => $event->getCategory(),
            'name' => $event->getName(),
            'description' => $event->getDescription(),
            'price' => $event->getPrice(),
            'duration' => $event->getDuration(),
            'date' => $event->getDate(),
            'longitude' => $event->getLongitude(),
            'latitude' => $event->getLatitude(),
            'place_name' => $event->getPlaceName(),
            'image_url' => $event->getImageUrl(),
            'total_seats' => $event->getTotalSeats()
        ]));
    
        $longitude = $event->getLongitude();
        $latitude = $event->getLatitude();
        $placeName = $event->getPlaceName();
    
        if (empty($longitude) || empty($latitude) || !is_numeric($longitude) || !is_numeric($latitude)) {
            error_log('Validation failed: Invalid GPS coordinates');
            throw new Exception('Les coordonnées GPS doivent être des nombres valides.');
        }
    
        if (empty($placeName) || strlen($placeName) < 3) {
            error_log('Validation failed: Invalid place name');
            throw new Exception('Le nom du lieu est requis et doit contenir au moins 3 caractères.');
        }
    
        $oldEvent = $this->getEventById($id);
        $oldTotalSeats = $oldEvent['totalSeats'];
        $newTotalSeats = $event->getTotalSeats();
    
        try {
            $this->db->beginTransaction();
    
            $sql = "UPDATE evenements SET
                    category = :category, name = :name, description = :description, price = :price,
                    duration = :duration, date = :date, longitude = :longitude, latitude = :latitude,
                    place_name = :place_name, image_url = :imageUrl, total_seats = :totalSeats,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
    
            $query = $this->db->prepare($sql);
            $query->bindValue(':id', $id);
            $query->bindValue(':category', $event->getCategory());
            $query->bindValue(':name', $event->getName());
            $query->bindValue(':description', $event->getDescription());
            $query->bindValue(':price', $event->getPrice());
            $query->bindValue(':duration', $event->getDuration());
            $query->bindValue(':date', $event->getDate());
            $query->bindValue(':longitude', $longitude);
            $query->bindValue(':latitude', $latitude);
            $query->bindValue(':place_name', $placeName);
            $query->bindValue(':imageUrl', $event->getImageUrl());
            $query->bindValue(':totalSeats', $newTotalSeats);
            $query->execute();
    
            $chaiseController = new ChaiseController($this->db);
            
            if ($newTotalSeats < $oldTotalSeats) {
                $chaiseController->removeLastAvailableChaises($id, $oldTotalSeats - $newTotalSeats);
            } elseif ($newTotalSeats > $oldTotalSeats) {
                $chaiseController->addMultipleChaises($id, $newTotalSeats - $oldTotalSeats, $oldTotalSeats + 1);
            }
    
            $this->db->commit();
            error_log('Event updated successfully with ID: ' . $id);
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error in updateEvent: ' . $e->getMessage());
            throw new Exception('Erreur lors de la mise à jour de l\'événement : ' . $e->getMessage());
        }
    }

    public function deleteEvent($id) {
        try {
            $this->db->beginTransaction();
            
            $chaiseController = new ChaiseController();
            $chaiseController->deleteAllChaisesForEvent($id);
            
            $update = $this->db->prepare("UPDATE evenements 
                                         SET updated_at = CURRENT_TIMESTAMP 
                                         WHERE id = :id");
            $update->bindValue(':id', $id);
            $update->execute();
            
            $delete = $this->db->prepare('DELETE FROM evenements WHERE id = :id');
            $delete->bindValue(':id', $id);
            $result = $delete->execute();
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Erreur deleteEvent: ' . $e->getMessage());
        }
    }

    public function getEventsByCategory($category) {
        $sql = "SELECT 
                    id, 
                    name, 
                    description, 
                    price, 
                    duration, 
                    image_url AS imageUrl,
                    UNIX_TIMESTAMP(updated_at) AS last_updated
                FROM evenements
                WHERE category = :category
                ORDER BY date DESC";
        
        try {
            $query = $this->db->prepare($sql);
            $query->bindValue(':category', $category);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getEventsByCategory: ' . $e->getMessage());
        }
    }

    public function getEventById($id) {
        $sql = "SELECT id, category, name, description, price, duration, date, longitude, latitude, place_name,
       image_url AS imageUrl, total_seats AS totalSeats, reserved_seats AS reservedSeats
FROM evenements WHERE id = :id";
        
        try {
            $query = $this->db->prepare($sql);
            $query->bindValue(':id', $id);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getEventById: ' . $e->getMessage());
        }
    }

    public function getAllReservations() {
        $sql = "SELECT e.id, e.name, e.date, e.place_name, e.price, e.image_url AS image,
       GROUP_CONCAT(c.numero ORDER BY CAST(c.numero AS UNSIGNED)) AS seats,
       e.date > DATE_ADD(NOW(), INTERVAL 1 DAY) AS can_modify,
       e.date > DATE_ADD(NOW(), INTERVAL 1 DAY) AS can_cancel
FROM chaise c JOIN evenements e ON c.event_id = e.id
WHERE c.statut = 'reserve'
GROUP BY e.id, e.name, e.date, e.place_name, e.price, e.image_url
ORDER BY e.date ASC";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute();
            
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getAllReservations: ' . $e->getMessage());
        }
    }

    public function getReservationsByUser() {
        $sql = "
            SELECT 
                e.id,
                e.name AS title,
                e.price,
                e.date,
                e.image_url AS imageUrl,
                e.category,
                GROUP_CONCAT(c.numero SEPARATOR ', ') AS seats  -- Combine all seat numbers
            FROM evenements e
            LEFT JOIN chaise c ON e.id = c.event_id AND c.statut = 'reserve' AND c.id_user = ?
            WHERE EXISTS (
                SELECT 1 FROM chaise c2 
                WHERE c2.event_id = e.id AND c2.statut = 'reserve' AND c2.id_user = ?
            )
            GROUP BY e.id
            ORDER BY e.date DESC
        ";
        try {
            $query = $this->db->prepare($sql);
            $query->execute([self::$global_user_id, self::$global_user_id]);
            $reservations = $query->fetchAll(PDO::FETCH_ASSOC);

            // Calculate seat count for each reservation
            foreach ($reservations as &$reservation) {
                $seatList = explode(', ', $reservation['seats']);
                $reservation['seat_count'] = count(array_filter($seatList)); // Count non-empty seats
            }
            return $reservations;
        } catch (Exception $e) {
            throw new Exception('Erreur getReservationsByUser: ' . $e->getMessage());
        }
    }

    public function trackClick($user_id, $event_id) {
        try {
            if (!is_numeric($user_id) || $user_id <= 0) {
                throw new Exception('Invalid user_id');
            }
            $event = $this->getEventById($event_id);
            if (!$event) {
                throw new Exception('Événement non trouvé');
            }

            $sql = "INSERT INTO user_clicks (user_id, event_id, category, click_time)
                    VALUES (:user_id, :event_id, :category, CURRENT_TIMESTAMP)";
            $query = $this->db->prepare($sql);
            $query->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $query->bindValue(':event_id', $event_id, PDO::PARAM_INT);
            $query->bindValue(':category', $event['category']);
            $query->execute();
            error_log("Click tracked: user_id=$user_id, event_id=$event_id, category={$event['category']}");
            return true;
        } catch (Exception $e) {
            error_log('Error in trackClick: ' . $e->getMessage());
            return false;
        }
    }

    public function getRecommendations($user_id, $limit = 5) {
        try {
            if (!is_numeric($user_id) || $user_id <= 0) {
                throw new Exception('Invalid user_id');
            }

            // Get user reservations
            $sqlReservations = "
                SELECT e.category
                FROM chaise c
                JOIN evenements e ON c.event_id = e.id
                WHERE c.statut = 'reserve' AND c.id_user = :user_id
                GROUP BY e.category
            ";
            $queryReservations = $this->db->prepare($sqlReservations);
            $queryReservations->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $queryReservations->execute();
            $reservations = $queryReservations->fetchAll(PDO::FETCH_ASSOC);
            $reservation_categories = array_column($reservations, 'category');

            // Get user clicks (last 30 days)
            $sqlClicks = "
                SELECT category, COUNT(*) as click_count
                FROM user_clicks
                WHERE user_id = :user_id
                AND click_time > DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY category
            ";
            $queryClicks = $this->db->prepare($sqlClicks);
            $queryClicks->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $queryClicks->execute();
            $clicks = $queryClicks->fetchAll(PDO::FETCH_ASSOC);

            // Calculate category scores
            $category_scores = [];
            foreach ($reservations as $res) {
                $category_scores[$res['category']] = ($category_scores[$res['category']] ?? 0) + 10; // Weight for reservations
            }
            foreach ($clicks as $click) {
                $category_scores[$click['category']] = ($category_scores[$click['category']] ?? 0) + $click['click_count'] * 3; // Weight for clicks
            }

            // Get available events
            $sqlEvents = "
                SELECT id, category, name, price, image_url AS imageUrl, total_seats, reserved_seats,
                       (total_seats - reserved_seats) AS available_seats, date
                FROM evenements
                WHERE total_seats > reserved_seats AND date > NOW()
                ORDER BY date ASC
            ";
            $queryEvents = $this->db->prepare($sqlEvents);
            $queryEvents->execute();
            $events = $queryEvents->fetchAll(PDO::FETCH_ASSOC);

            // Score events
            $event_scores = [];
            foreach ($events as $event) {
                $score = 0;
                if (isset($category_scores[$event['category']])) {
                    $score += $category_scores[$event['category']];
                }
                $availability_ratio = $event['available_seats'] / $event['total_seats'];
                $score += $availability_ratio * 5; // Availability bonus
                $event_date = strtotime($event['date']);
                $days_until_event = ($event_date - time()) / (60 * 60 * 24);
                if ($days_until_event <= 30) {
                    $score += 3 * (1 - $days_until_event / 30); // Recency bonus
                }
                $event_scores[] = [
                    'event' => [
                        'id' => $event['id'],
                        'name' => $event['name'],
                        'price' => $event['price'],
                        'image' => $event['imageUrl'],
                        'available_seats' => $event['available_seats']
                    ],
                    'category' => $event['category'],
                    'score' => $score
                ];
            }

            // Sort and limit
            usort($event_scores, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            $recommendations = array_slice($event_scores, 0, $limit);
            return array_map(function($item) {
                return [
                    'category' => $item['category'],
                    'event' => $item['event'],
                    'image' => $item['event']['image']
                ];
            }, $recommendations);
        } catch (Exception $e) {
            error_log('Error in getRecommendations: ' . $e->getMessage());
            return [];
        }
    }
    public function getEventStats() {
        $sql = "SELECT 
                    category,
                    COUNT(*) as event_count,
                    SUM(total_seats) as total_seats,
                    SUM(reserved_seats) as reserved_seats,
                    SUM(total_seats - reserved_seats) as available_seats
                FROM evenements
                GROUP BY category";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getEventStats: ' . $e->getMessage());
        }
    }
    public function getReservationTrends() {
        $sql = "SELECT 
                    DATE_FORMAT(date, '%Y-%m') as month,
                    SUM(total_seats) as total_seats,
                    SUM(reserved_seats) as reserved_seats,
                    COUNT(*) as event_count
                FROM evenements
                WHERE date > DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(date, '%Y-%m')
                ORDER BY month ASC";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getReservationTrends: ' . $e->getMessage());
        }
    }
}

?>