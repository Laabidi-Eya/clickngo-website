<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/DemandeCovoiturage.php';

class DemandeCovoiturageController {
    private $pdo;
    private $id_user; // Global static user_id

public function __construct($pdo) {
    $this->pdo = $pdo; // Initialize PDO
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['user']['id_user'])) {
        $this->id_user = $_SESSION['user']['id_user'];
    } else {
        $this->id_user = null;
    }
}

  public function reserverAnnonce($data) {
    $errors = $this->validateReservationInputs($data);
    if (!empty($errors)) {
        throw new Exception(implode('<br>', $errors));
    }

    try {
        $annonceController = new AnnonceCovoiturageController($this->pdo); // Now $this->pdo is set
        $annonce = $annonceController->getAnnonceById($data['id_conducteur']);
        
        if (!$annonce) {
            throw new Exception("Annonce non trouvée");
        }
        
        $prix_total = $annonce->getPrixEstime() * $data['nbr_places_reservees'];

        $query = "INSERT INTO demande_covoiturage 
                 (prenom_passager, nom_passager, tel_passager, id_conducteur, 
                  date_demande, status_demande, nbr_places_reservees, message, 
                  moyen_paiement, prix_total, date_creation, user_id) 
                 VALUES 
                 (:prenom_passager, :nom_passager, :tel_passager, :id_conducteur, 
                  NOW(), 'en cours', :nbr_places_reservees, :message, 
                  :moyen_paiement, :prix_total, NOW(), :user_id)";

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':prenom_passager', $data['prenom_passager']);
        $stmt->bindParam(':nom_passager', $data['nom_passager']);
        $stmt->bindParam(':tel_passager', $data['tel_passager']);
        $stmt->bindParam(':id_conducteur', $data['id_conducteur']);
        $stmt->bindParam(':nbr_places_reservees', $data['nbr_places_reservees']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':moyen_paiement', $data['moyen_paiement']);
        $stmt->bindParam(':prix_total', $prix_total);
        $stmt->bindParam(':user_id', $this->id_user);

        $stmt->execute();

        return "Votre réservation a été enregistrée avec succès!";
    } catch (PDOException $e) {
        throw new Exception("Erreur lors de l'enregistrement de la réservation: " . $e->getMessage());
    }
}

    private function validateReservationInputs($data) {
        $errors = [];
        
        if (empty($data['prenom_passager']) || !preg_match("/^[a-zA-ZÀ-ÿ -]{2,}$/", $data['prenom_passager'])) {
            $errors[] = "Prénom invalide (minimum 2 caractères alphabétiques)";
        }
        
        if (empty($data['nom_passager']) || !preg_match("/^[a-zA-ZÀ-ÿ -]{2,}$/", $data['nom_passager'])) {
            $errors[] = "Nom invalide (minimum 2 caractères alphabétiques)";
        }
        
        if (empty($data['tel_passager']) || !preg_match("/^[0-9]{8}$/", $data['tel_passager'])) {
            $errors[] = "Numéro de téléphone invalide (8 chiffres requis)";
        }
        
        if (empty($data['nbr_places_reservees']) || $data['nbr_places_reservees'] < 1 || $data['nbr_places_reservees'] > 4) {
            $errors[] = "Nombre de places invalide (1-4)";
        }
        
        $validPayments = ['espèces', 'carte bancaire', 'virement'];
        if (empty($data['moyen_paiement']) || !in_array($data['moyen_paiement'], $validPayments)) {
            $errors[] = "Moyen de paiement invalide";
        }
        
        return $errors;
    }

    public function getAllPendingDemandes() {
        try {
            if (!$this->pdo) {
                error_log("Database connection is not established");
                return [];
            }

            $query = "SELECT * FROM demande_covoiturage 
                     WHERE status_demande = 'en cours'
                     ORDER BY date_creation DESC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($results)) {
                error_log("Available columns: " . implode(", ", array_keys($results[0])));
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAllDemandes() {
        try {
            if (!$this->pdo) {
                error_log("Database connection is not established");
                return [];
            }

            $query = "SELECT * FROM demande_covoiturage 
                     ORDER BY date_creation DESC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($results)) {
                error_log("Available columns: " . implode(", ", array_keys($results[0])));
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    public function getDemandesByAnnonceId($annonceId) {
        try {
            if (!$this->pdo) {
                error_log("Database connection is not established");
                throw new Exception("Erreur de connexion à la base de données");
            }

            $query = "SELECT * FROM demande_covoiturage 
                     WHERE id_conducteur = :annonceId 
                     ORDER BY date_creation DESC";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':annonceId', $annonceId, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($results)) {
                error_log("Demandes found for annonce ID {$annonceId}: " . count($results));
                error_log("Available columns: " . implode(", ", array_keys($results[0])));
            } else {
                error_log("No demandes found for annonce ID {$annonceId}");
            }

            $demandes = [];
            foreach ($results as $row) {
                $demande = new stdClass();
                $demande->id_demande = $row['id_passager'];
                $demande->prenom_passager = $row['prenom_passager'];
                $demande->nom_passager = $row['nom_passager'];
                $demande->tel_passager = $row['tel_passager'];
                $demande->status = $row['status_demande'];
                $demande->created_at = new DateTime($row['date_creation']);
                $demande->user_id = $row['user_id'];
                $demandes[] = $demande;
            }

            return $demandes;
        } catch (PDOException $e) {
            error_log("Database error in getDemandesByAnnonceId: " . $e->getMessage());
            throw new Exception("Erreur lors de la récupération des demandes: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Error in getDemandesByAnnonceId: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateDemandeStatus($demandeId, $newStatus) {
        try {
            $query = "UPDATE demande_covoiturage SET status_demande = :status WHERE id_passager = :id";
            
            error_log("Executing SQL: {$query} with status={$newStatus} and id={$demandeId}");
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':status', $newStatus);
            $stmt->bindParam(':id', $demandeId);
            
            $result = $stmt->execute();
            $rowCount = $stmt->rowCount();
            
            error_log("Update result: " . ($result ? "Success" : "Failed") . ", Rows affected: {$rowCount}");
            
            return $result;
        } catch (PDOException $e) {
            error_log('Error updating demande: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteDemande($id) {
        try {
            $query = "DELETE FROM demande_covoiturage WHERE id_passager = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error deleting demande: ' . $e->getMessage());
            return false;
        }
    }

    public function getDemandeById($id_demande) {
        try {
            if (!is_numeric($id_demande) || $id_demande <= 0) {
                throw new Exception("ID de demande invalide");
            }
    
            $query = "SELECT * FROM demande_covoiturage WHERE id_passager = :id_demande";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id_demande', $id_demande, PDO::PARAM_INT);
            $stmt->execute();
            
            $demande = $stmt->fetch(PDO::FETCH_OBJ);
            
            if (!$demande) {
                return null;
            }
            
            if (isset($demande->date_creation)) {
                $demande->created_at = new DateTime($demande->date_creation);
            }
            if (isset($demande->date_modification)) {
                $demande->updated_at = new DateTime($demande->date_modification);
            }
            
            return $demande;
            
        } catch (PDOException $e) {
            error_log("Erreur PDO dans getDemandeById: " . $e->getMessage());
            throw new Exception("Erreur technique lors de la récupération de la demande");
        }
    }

    public function updateDemande($data) {
        try {
            $query = "UPDATE demande_covoiturage SET 
                     prenom_passager = :prenom,
                     nom_passager = :nom,
                     tel_passager = :tel,
                     nbr_places_reservees = :places,
                     message = :message,
                     moyen_paiement = :paiement,
                     prix_total = :prix,
                     date_modification = NOW(),
                     user_id = :user_id
                     WHERE id_passager = :id";
            
            $stmt = $this->pdo->prepare($query);
            
            $prix_total = $data['prix_total'] ?? null;
            
            $stmt->bindParam(':prenom', $data['prenom_passager']);
            $stmt->bindParam(':nom', $data['nom_passager']);
            $stmt->bindParam(':tel', $data['tel_passager']);
            $stmt->bindParam(':places', $data['nbr_places_reservees']);
            $stmt->bindParam(':message', $data['message']);
            $stmt->bindParam(':paiement', $data['moyen_paiement']);
            $stmt->bindParam(':prix', $prix_total);
            $stmt->bindParam(':user_id', $this->id_user); // Use global id_user
            $stmt->bindParam(':id', $data['id_passager']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error updating demande: ' . $e->getMessage());
            return false;
        }
    }
    public function getDemandesByUserId($user_id) {
    try {
        if (!$this->pdo) {
            error_log("Database connection is not established");
            return [];
        }

        $query = "SELECT * FROM demande_covoiturage 
                  WHERE user_id = :user_id 
                  ORDER BY date_creation DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($results)) {
            error_log("Available columns: " . implode(", ", array_keys($results[0])));
        }
        
        return $results;
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}
}
?>