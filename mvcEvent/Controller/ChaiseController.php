<?php
require_once 'C:/xampp/htdocs/Projet Web/mvcEvent/Config.php';
require 'C:/xampp/htdocs/Projet Web/mvcEvent/Model/Chaise.php';

class ChaiseController {
private $db;
// Dynamically get user_id from session
private static $global_user_id;

public function __construct($db = null) {
    $this->db = $db ?: Config::getConnexion();

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['user']['id_user'])) {
        self::$global_user_id = $_SESSION['user']['id_user'];
    } else {
        self::$global_user_id = null;
    }
}

public function getReservationsByUserAndEvent($eventId, $userId) {
    $sql = "SELECT * FROM chaise WHERE event_id = :event_id AND id_user = :user_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':event_id' => $eventId,
        ':user_id' => $userId
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    private function generateUniqueId() {
        $maxAttempts = 10;
        $attempt = 0;
        
        do {
            $id = mt_rand(1000000, 9999999);
            $stmt = $this->db->prepare("SELECT id FROM chaise WHERE id = ?");
            $stmt->execute([$id]);
            $attempt++;
            
            if ($attempt >= $maxAttempts) {
                throw new Exception("Impossible de générer un ID unique après $maxAttempts tentatives");
            }
        } while ($stmt->rowCount() > 0);
        
        return $id;
    }

    public function addMultipleChaises($event_id, $quantity, $startFrom = 1) {
        try {
            for ($i = 0; $i < $quantity; $i++) {
                $numero = $startFrom + $i;
                $chaise = new Chaise($event_id, $numero);
                $this->addChaise($chaise);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception('Erreur addMultipleChaises: ' . $e->getMessage());
        }
    }
    
    public function addChaise(Chaise $chaise) {
        $sql = "INSERT INTO chaise (id, event_id, numero, statut, id_user) 
                VALUES (:id, :event_id, :numero, :statut, :id_user)";
        
        try {
            $id = $this->generateUniqueId();
            $query = $this->db->prepare($sql);
            $query->execute([
                ':id' => $id,
                ':event_id' => $chaise->getEventId(),
                ':numero' => $chaise->getNumero(),
                ':statut' => $chaise->getStatut(),
                ':id_user' => $chaise->getIdUser()
            ]);
            return $id;
        } catch (Exception $e) {
            throw new Exception('Erreur addChaise: ' . $e->getMessage());
        }
    }

    public function getChaisesByEvent($event_id) {
        $sql = "SELECT id, numero, statut, id_user 
                FROM chaise 
                WHERE event_id = :event_id
                ORDER BY CAST(numero AS UNSIGNED)";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute([':event_id' => $event_id]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getChaisesByEvent: ' . $e->getMessage());
        }
    }

    public function deleteAllChaisesForEvent($event_id) {
        $sql = "DELETE FROM chaise WHERE event_id = :event_id";
        
        try {
            $query = $this->db->prepare($sql);
            return $query->execute([':event_id' => $event_id]);
        } catch (Exception $e) {
            throw new Exception('Erreur deleteAllChaisesForEvent: ' . $e->getMessage());
        }
    }

    public function reserverChaise($chaise_id , $user_id) {
        // Modified: Use global_user_id instead of parameter
        if (!self::$global_user_id) {
            throw new Exception("Global User ID is required for reservation");
        }
        $checkSql = "SELECT event_id, statut FROM chaise WHERE id = :chaise_id";
        $updateChaiseSql = "UPDATE chaise SET statut = 'reserve', id_user = :user_id 
                           WHERE id = :chaise_id AND statut = 'libre'";
        $updateEventSql = "UPDATE evenements SET reserved_seats = reserved_seats + 1 
                          WHERE id = :event_id";
        
        try {
            $this->db->beginTransaction();
            
            // Vérifier si la chaise existe et récupérer event_id
            error_log("Vérification chaise ID: $chaise_id");
            $checkQuery = $this->db->prepare($checkSql);
            $checkQuery->execute([':chaise_id' => $chaise_id]);
            $result = $checkQuery->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                error_log("Échec: La chaise $chaise_id n'existe pas");
                throw new Exception("La chaise $chaise_id n'existe pas");
            }
            
            $event_id = $result['event_id'];
            $current_status = $result['statut'];
            error_log("Chaise $chaise_id trouvée, event_id: $event_id, statut: $current_status");
            
            if ($current_status !== 'libre') {
                error_log("Échec: La chaise $chaise_id n'est pas libre (statut: $current_status)");
                throw new Exception("La chaise $chaise_id n'est pas disponible");
            }
            
            // Mettre à jour le statut de la chaise
            error_log("Mise à jour chaise $chaise_id à 'reserve' pour user_id: " . (self::$global_user_id ?? 'NULL'));
            $updateChaiseQuery = $this->db->prepare($updateChaiseSql);
            $updateChaiseQuery->execute([
                ':chaise_id' => $chaise_id,
                ':user_id' => self::$global_user_id
            ]);
            
            if ($updateChaiseQuery->rowCount() === 0) {
                error_log("Échec: Aucune chaise mise à jour pour ID: $chaise_id");
                throw new Exception("La chaise $chaise_id n'a pas pu être réservée");
            }
            
            // Incrémenter reserved_seats dans la table evenements
            error_log("Incrémentation reserved_seats pour event_id: $event_id");
            $updateEventQuery = $this->db->prepare($updateEventSql);
            $updateEventQuery->execute([':event_id' => $event_id]);
            
            if ($updateEventQuery->rowCount() === 0) {
                error_log("Échec: Aucun événement mis à jour pour event_id: $event_id");
                throw new Exception("Impossible de mettre à jour le compteur de places réservées pour l'événement $event_id");
            }
            
            error_log("Succès: Chaise $chaise_id réservée et reserved_seats incrémenté pour event_id: $event_id");
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur reserverChaise: " . $e->getMessage());
            throw $e;
        }
    }

    public function libererChaise($chaise_id) {
        $sql = "UPDATE chaise 
                SET statut = 'libre', id_user = NULL 
                WHERE id = :chaise_id AND statut = 'reserve'";
        
        try {
            $query = $this->db->prepare($sql);
            return $query->execute([':chaise_id' => $chaise_id]);
        } catch (Exception $e) {
            throw new Exception('Erreur libererChaise: ' . $e->getMessage());
        }
    }

    public function removeLastAvailableChaises($event_id, $quantity) {
        // D'abord, compter combien de chaises libres existent avec les numéros les plus élevés
        $sqlCount = "SELECT COUNT(*) as available_count 
                     FROM chaise 
                     WHERE event_id = :event_id 
                     AND statut = 'libre'
                     ORDER BY CAST(numero AS UNSIGNED) DESC";
        
        $query = $this->db->prepare($sqlCount);
        $query->execute([':event_id' => $event_id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $availableCount = $result['available_count'] ?? 0;
    
        if ($availableCount < $quantity) {
            // S'il n'y a pas assez de chaises libres, supprimer celles qui sont disponibles
            $quantityToDelete = min($availableCount, $quantity);
            if ($quantityToDelete > 0) {
                $sqlDelete = "DELETE FROM chaise 
                              WHERE event_id = :event_id
                              AND statut = 'libre'
                              ORDER BY CAST(numero AS UNSIGNED) DESC
                              LIMIT :limit";
                
                $query = $this->db->prepare($sqlDelete);
                $query->bindValue(':event_id', $event_id);
                $query->bindValue(':limit', (int)$quantityToDelete, PDO::PARAM_INT);
                $query->execute();
                
                return $query->rowCount();
            }
            return 0;
        } else {
            // S'il y a assez de chaises libres, toutes les supprimer
            $sqlDelete = "DELETE FROM chaise 
                          WHERE event_id = :event_id
                          AND statut = 'libre'
                          ORDER BY CAST(numero AS UNSIGNED) DESC
                          LIMIT :limit";
            
            $query = $this->db->prepare($sqlDelete);
            $query->bindValue(':event_id', $event_id);
            $query->bindValue(':limit', (int)$quantity, PDO::PARAM_INT);
            $query->execute();
            
            return $query->rowCount();
        }
    }

    private function getLastChaiseNumber($event_id) {
        $sql = "SELECT MAX(CAST(numero AS UNSIGNED)) as last_num 
                FROM chaise 
                WHERE event_id = :event_id";
        
        $query = $this->db->prepare($sql);
        $query->execute([':event_id' => $event_id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        
        return $result['last_num'] ?? 0;
    }

    public function getEventSeatStats($eventId = null) {
        $sql = "SELECT 
                    e.id, 
                    e.name, 
                    COUNT(c.id) as total, 
                    SUM(CASE WHEN c.statut = 'reserve' THEN 1 ELSE 0 END) as reserved
                FROM evenements e
                LEFT JOIN chaise c ON c.event_id = e.id
                " . ($eventId ? "WHERE e.id = :event_id" : "") . "
                GROUP BY e.id, e.name";
        try {
            $query = $this->db->prepare($sql);
            if ($eventId) {
                $query->bindValue(':event_id', $eventId);
            }
            $query->execute();
            return $eventId ? $query->fetch(PDO::FETCH_ASSOC) : $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getEventSeatStats: ' . $e->getMessage());
        }
    }

    public function updateReservation($eventId, $seatNumber) {
        // Implémentez votre logique de mise à jour
        // Par exemple :
        $sql = "UPDATE chaise SET statut = 'reserve' 
                WHERE event_id = :event_id AND numero = :numero";
        
        try {
            $query = $this->db->prepare($sql);
            return $query->execute([
                ':event_id' => $eventId,
                ':numero' => $seatNumber
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur updateReservation: ' . $e->getMessage());
        }
    }

  public function updateMultipleReservations($eventId, $seatNumbers, $userId) {
    if (!$userId) {
        throw new Exception("User ID requis pour la réservation");
    }

    try {
        $this->db->beginTransaction();

        // Obtenir les sièges déjà réservés par cet utilisateur pour cet événement
        $currentSeats = $this->getChaisesByEvent($eventId);
        $currentReserved = array_filter($currentSeats, function($seat) use ($userId) {
            return $seat['statut'] === 'reserve' && $seat['id_user'] == $userId;
        });
        $currentReservedNumbers = array_column($currentReserved, 'numero');

        // Déterminer les sièges à réserver et à libérer
        $seatsToReserve = array_diff($seatNumbers, $currentReservedNumbers);
        $seatsToFree = array_diff($currentReservedNumbers, $seatNumbers);

        // Vérification de la disponibilité
        $eventStats = $this->getEventSeatStats($eventId);
        if (!$eventStats || !isset($eventStats['total'], $eventStats['reserved'])) {
            throw new Exception("Statistiques d'événement introuvables");
        }

        $availableSeats = $eventStats['total'] - $eventStats['reserved'];
        if (count($seatsToReserve) > $availableSeats) {
            throw new Exception('Pas assez de sièges disponibles');
        }

        // Réserver les nouveaux sièges
        foreach ($seatsToReserve as $seatNumber) {
            $sql = "UPDATE chaise 
                    SET statut = 'reserve', id_user = :user_id 
                    WHERE event_id = :event_id AND numero = :numero AND statut = 'libre'";
            $query = $this->db->prepare($sql);
            $query->execute([
                ':event_id' => $eventId,
                ':numero' => $seatNumber,
                ':user_id' => $userId
            ]);

            if ($query->rowCount() === 0) {
                throw new Exception("Le siège $seatNumber n'est pas disponible");
            }
        }

        // Libérer les anciens sièges
        foreach ($seatsToFree as $seatNumber) {
            $sql = "UPDATE chaise 
                    SET statut = 'libre', id_user = NULL 
                    WHERE event_id = :event_id AND numero = :numero 
                    AND statut = 'reserve' AND id_user = :user_id";
            $query = $this->db->prepare($sql);
            $query->execute([
                ':event_id' => $eventId,
                ':numero' => $seatNumber,
                ':user_id' => $userId
            ]);
        }

        // Mettre à jour le nombre total de réservations
        $newReservedCount = $this->countReservedSeats($eventId);
        $sql = "UPDATE evenements 
                SET reserved_seats = :reserved_seats 
                WHERE id = :event_id";
        $query = $this->db->prepare($sql);
        $query->execute([
            ':event_id' => $eventId,
            ':reserved_seats' => $newReservedCount
        ]);

        $this->db->commit();
        return true;

    } catch (Exception $e) {
        $this->db->rollBack();
        throw new Exception('Erreur updateMultipleReservations: ' . $e->getMessage());
    }
}


public function countReservedSeats($eventId) {
    $sql = "SELECT COUNT(*) FROM chaise WHERE event_id = :event_id AND statut = 'reserve'";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':event_id' => $eventId]);
    return (int) $stmt->fetchColumn();
}

    public function cancelReservation($eventId) {
        // Modified: Use global_user_id
        if (!self::$global_user_id) {
            throw new Exception("Global User ID is required for cancellation");
        }
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE chaise SET statut = 'libre', id_user = NULL 
                    WHERE event_id = :event_id AND statut = 'reserve' AND id_user = :user_id";
            $query = $this->db->prepare($sql);
            $query->execute([
                ':event_id' => $eventId,
                ':user_id' => self::$global_user_id
            ]);

            // Reset reserved_seats in evenements table
            $sql = "UPDATE evenements SET reserved_seats = (
                        SELECT COUNT(*) FROM chaise 
                        WHERE event_id = :event_id AND statut = 'reserve'
                    ) WHERE id = :event_id";
            $query = $this->db->prepare($sql);
            $query->execute([':event_id' => $eventId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Erreur cancelReservation: ' . $e->getMessage());
        }
    }

    public function getUserReservations($eventId) {
        // Added: Function to fetch reservations for global_user_id
        if (!self::$global_user_id) {
            throw new Exception("Global User ID is required for fetching reservations");
        }
        $sql = "SELECT id, numero, statut, id_user 
                FROM chaise 
                WHERE event_id = :event_id AND id_user = :user_id AND statut = 'reserve'";
        
        try {
            $query = $this->db->prepare($sql);
            $query->execute([
                ':event_id' => $eventId,
                ':user_id' => self::$global_user_id
            ]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur getUserReservations: ' . $e->getMessage());
        }
    }
}
?>