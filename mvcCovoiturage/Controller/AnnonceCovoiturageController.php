<?php
require_once '../config.php';
require_once '../Model/AnnonceCovoiturage.php';

date_default_timezone_set('Africa/Tunis');

class AnnonceCovoiturageController {
    private $pdo;
    private $id_user;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user']['id_user'])) {
            $this->id_user = $_SESSION['user']['id_user'];
        } else {
            $this->id_user = null;
        }
    }

    public function ajouterAnnonce($data) {
        $errors = $this->validateInputs($data, true);
        if (!empty($errors)) {
            error_log("Validation Errors: " . implode("; ", $errors));
            throw new Exception(implode("\n", $errors));
        }

        try {
            $annonce = new annonce_covoiturage(
                $data['prenom_conducteur'],
                $data['nom_conducteur'],
                $data['tel_conducteur'],
                $data['date_depart'],
                $data['lieu_depart'],
                $data['lieu_arrivee'],
                $data['nombre_places'],
                $data['type_voiture'],
                $data['prix_estime'],
                $data['description'] ?? '',
                'disponible',
                $this->id_user
            );

            $query = "INSERT INTO annonce_covoiturage 
                    (prenom_conducteur, nom_conducteur, tel_conducteur, date_depart, 
                    lieu_depart, lieu_arrivee, nombre_places, type_voiture, 
                    prix_estime, description, status, date_creation, user_id) 
                    VALUES 
                    (:prenom_conducteur, :nom_conducteur, :tel_conducteur, :date_depart, 
                    :lieu_depart, :lieu_arrivee, :nombre_places, :type_voiture, 
                    :prix_estime, :description, :status, :date_creation, :user_id)";

            $stmt = $this->pdo->prepare($query);
            $params = $annonce->toArrayForInsert();
            error_log("SQL Query Parameters: " . print_r($params, true));
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                error_log("Insertion failed: No rows affected.");
                throw new Exception("L'insertion a échoué. Veuillez réessayer.");
            }

            error_log("Insertion successful");
            return true;
        } catch (PDOException $e) {
            error_log("PDO Exception: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            throw new Exception("Erreur lors de l'ajout de l'annonce: " . $e->getMessage());
        }
    }

    public function searchAnnonces($depart, $arrivee) {
        try {
            $query = "SELECT * FROM annonce_covoiturage 
                    WHERE LOWER(lieu_depart) LIKE LOWER(:depart) 
                    AND LOWER(lieu_arrivee) LIKE LOWER(:arrivee) 
                    AND status = 'disponible'
                    AND date_depart >= NOW()
                    ORDER BY date_depart ASC";
    
            $stmt = $this->pdo->prepare($query);
            $params = [
                'depart' => "%$depart%",
                'arrivee' => "%$arrivee%"
            ];
    
            $stmt->execute($params);
    
            $annonces = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $annonce = new annonce_covoiturage(
                    $row['prenom_conducteur'],
                    $row['nom_conducteur'],
                    $row['tel_conducteur'],
                    $row['date_depart'],
                    $row['lieu_depart'],
                    $row['lieu_arrivee'],
                    $row['nombre_places'],
                    $row['type_voiture'],
                    $row['prix_estime'],
                    $row['description'] ?? '',
                    $row['status'] ?? 'disponible',
                    $row['user_id']
                );
                $annonce->setIdConducteur($row['id_conducteur']);
                $annonces[] = $annonce;
            }
    
            return $annonces;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche des annonces: " . $e->getMessage());
        }
    }

    private function validateInputs($data, $isInsert = false) {
        $errors = [];

        if (empty($data['prenom_conducteur']) || !preg_match("/^[a-zA-ZÀ-ÿ -]{2,}$/", $data['prenom_conducteur'])) {
            $errors[] = "Prénom invalide (minimum 2 caractères alphabétiques)";
        }
        if (empty($data['nom_conducteur']) || !preg_match("/^[a-zA-ZÀ-ÿ -]{2,}$/", $data['nom_conducteur'])) {
            $errors[] = "Nom invalide (minimum 2 caractères alphabétiques)";
        }
        if (empty($data['tel_conducteur']) || !preg_match("/^[0-9]{8}$/", $data['tel_conducteur'])) {
            $errors[] = "Numéro de téléphone invalide (8 chiffres requis)";
        }
        if (empty($data['date_depart'])) {
            $errors[] = "Date de départ requise";
        } else {
            try {
                $departDate = new DateTime($data['date_depart']);
                if ($isInsert && $departDate <= new DateTime()) {
                    $errors[] = "La date de départ doit être dans le futur";
                }
            } catch (Exception $e) {
                $errors[] = "Format de date invalide";
            }
        }
        if (empty($data['nombre_places']) || $data['nombre_places'] < 1 || $data['nombre_places'] > 8) {
            $errors[] = "Nombre de places invalide (1-8)";
        }
        if ($isInsert && (empty($data['prix_estime']) || $data['prix_estime'] <= 0)) {
            $errors[] = "Prix estimé invalide (doit être positif)";
        } elseif (!$isInsert && isset($data['prix_estime']) && $data['prix_estime'] !== '' && $data['prix_estime'] < 0) {
            $errors[] = "Prix estimé invalide (ne peut pas être négatif)";
        }
        if (empty($data['lieu_depart']) || strlen(trim($data['lieu_depart'])) < 2) {
            $errors[] = "Le lieu de départ est requis (minimum 2 caractères)";
        }
        if (empty($data['lieu_arrivee']) || strlen(trim($data['lieu_arrivee'])) < 2) {
            $errors[] = "Le lieu d'arrivée est requis (minimum 2 caractères)";
        }
        if (empty($data['type_voiture']) || !preg_match("/^[a-zA-ZÀ-ÿ -]{2,}$/", $data['type_voiture'])) {
            $errors[] = "Type de voiture invalide (minimum 2 caractères alphabétiques)";
        }

        return $errors;
    }

    private function updateArchivedAnnonces() {
        try {
            $this->pdo->exec("SET time_zone = '+01:00';");

            $currentDate = new DateTime();
            $query = "UPDATE annonce_covoiturage SET status = 'archivée', date_modification = NOW() WHERE date_depart < :currentDate AND status = 'disponible'";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['currentDate' => $currentDate->format('Y-m-d H:i:s')]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour des annonces archivées: " . $e->getMessage());
        }
    }

    public function getAllAnnonces($forPassenger = false) {
        try {
            $this->updateArchivedAnnonces();

            $query = "SELECT * FROM annonce_covoiturage ORDER BY date_depart ASC";
            if ($forPassenger) {
                $query = "SELECT * FROM annonce_covoiturage WHERE status = 'disponible' AND date_depart >= NOW() ORDER BY date_depart ASC";
            }
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            $annonces = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $annonce = new annonce_covoiturage(
                    $row['prenom_conducteur'],
                    $row['nom_conducteur'],
                    $row['tel_conducteur'],
                    $row['date_depart'],
                    $row['lieu_depart'],
                    $row['lieu_arrivee'],
                    $row['nombre_places'],
                    $row['type_voiture'],
                    $row['prix_estime'],
                    $row['description'] ?? '',
                    $row['status'] ?? 'disponible',
                    $row['user_id']
                );
                $annonce->setIdConducteur($row['id_conducteur']);
                $annonces[] = $annonce;
            }

            return $annonces;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des annonces: " . $e->getMessage());
        }
    }

    public function getAnnonceById($id) {
        try {
            $query = "SELECT * FROM annonce_covoiturage WHERE id_conducteur = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $annonce = new annonce_covoiturage(
                    $row['prenom_conducteur'],
                    $row['nom_conducteur'],
                    $row['tel_conducteur'],
                    $row['date_depart'],
                    $row['lieu_depart'],
                    $row['lieu_arrivee'],
                    $row['nombre_places'],
                    $row['type_voiture'],
                    $row['prix_estime'],
                    $row['description'] ?? '',
                    $row['status'] ?? 'disponible',
                    $row['user_id']
                );
                $annonce->setIdConducteur($row['id_conducteur']);
                return $annonce;
            }

            return null;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de l'annonce: " . $e->getMessage());
        }
    }

    public function deleteAnnonce($id) {
        try {
            $query = "DELETE FROM annonce_covoiturage WHERE id_conducteur = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $success = $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de l'annonce: " . $e->getMessage());
        }
    }

    public function getAnnoncesByConducteurId($id_conducteur) {
        try {
            $sql = "SELECT * FROM annonce_covoiturage WHERE id_conducteur = :id_conducteur";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id_conducteur', $id_conducteur, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function archiverAnnonce($id) {
        try {
            $query = "UPDATE annonce_covoiturage SET status = 'archivée', date_modification = NOW() WHERE id_conducteur = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
    
            return true;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'archivage de l'annonce: " . $e->getMessage());
        }
    }
    
    public function updateAnnonce($data) {
        $errors = $this->validateInputs($data, false);
        if (!empty($errors)) {
            error_log("Erreurs de validation : " . implode("; ", $errors));
            throw new Exception(implode("\n", $errors));
        }

        try {
            if (!isset($data['id_conducteur']) || $data['id_conducteur'] <= 0) {
                error_log("ID Conducteur invalide : " . print_r($data['id_conducteur'], true));
                throw new Exception("ID de l'annonce invalide");
            }

            $query = "SELECT status FROM annonce_covoiturage WHERE id_conducteur = :id_conducteur";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':id_conducteur' => $data['id_conducteur']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                error_log("Aucune annonce trouvée pour id_conducteur : " . $data['id_conducteur']);
                throw new Exception("L'annonce n'existe pas dans la base de données");
            }

            if ($row['status'] === 'archivée') {
                error_log("Tentative de modification d'une annonce archivée : id_conducteur = " . $data['id_conducteur']);
                throw new Exception("L'annonce est archivée et ne peut pas être modifiée");
            }

            error_log("Mise à jour de l'annonce avec id_conducteur : " . $data['id_conducteur']);
            error_log("Données envoyées : " . print_r($data, true));

            $sql = "UPDATE annonce_covoiturage SET 
                    prenom_conducteur = :prenom_conducteur, 
                    nom_conducteur = :nom_conducteur, 
                    tel_conducteur = :tel_conducteur, 
                    date_depart = :date_depart, 
                    lieu_depart = :lieu_depart, 
                    lieu_arrivee = :lieu_arrivee, 
                    nombre_places = :nombre_places, 
                    type_voiture = :type_voiture, 
                    prix_estime = :prix_estime, 
                    description = :description,
                    user_id = :user_id,
                    date_modification = NOW()
                    WHERE id_conducteur = :id_conducteur";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':prenom_conducteur' => $data['prenom_conducteur'],
                ':nom_conducteur' => $data['nom_conducteur'],
                ':tel_conducteur' => $data['tel_conducteur'],
                ':date_depart' => $data['date_depart'],
                ':lieu_depart' => $data['lieu_depart'],
                ':lieu_arrivee' => $data['lieu_arrivee'],
                ':nombre_places' => $data['nombre_places'],
                ':type_voiture' => $data['type_voiture'],
                ':prix_estime' => $data['prix_estime'],
                ':description' => $data['description'] ?? '',
                ':user_id' => $this->id_user,
                ':id_conducteur' => $data['id_conducteur']
            ]);

            if ($stmt->rowCount() === 0) {
                error_log("Aucune ligne affectée pour id_conducteur : " . $data['id_conducteur']);
                throw new Exception("Aucune modification effectuée - vérifiez les données envoyées");
            }

            error_log("Mise à jour réussie pour id_conducteur : " . $data['id_conducteur']);
            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour: " . $e->getMessage() . "\nTrace : " . $e->getTraceAsString());
            throw new Exception("Erreur lors de la mise à jour: " . $e->getMessage());
        }
    }

    public function getRecentAnnonces($limit = 8) {
        try {
            $this->updateArchivedAnnonces();
            
            $query = "SELECT * FROM annonce_covoiturage 
                      WHERE date_depart >= NOW() AND status = 'disponible'
                      ORDER BY date_creation DESC 
                      LIMIT :limit";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $annonces = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $annonce = new annonce_covoiturage(
                    $row['prenom_conducteur'],
                    $row['nom_conducteur'],
                    $row['tel_conducteur'],
                    $row['date_depart'],
                    $row['lieu_depart'],
                    $row['lieu_arrivee'],
                    $row['nombre_places'],
                    $row['type_voiture'],
                    $row['prix_estime'],
                    $row['description'] ?? '',
                    $row['status'] ?? 'disponible',
                    $row['user_id']
                );
                $annonce->setIdConducteur($row['id_conducteur']);
                $annonces[] = $annonce;
            }
            
            return $annonces;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des annonces récentes: " . $e->getMessage());
        }
    }

    public function getAnnoncesByUserId($user_id) {
        try {
            $this->updateArchivedAnnonces();

            $query = "SELECT * FROM annonce_covoiturage 
                      WHERE user_id = :user_id 
                      ORDER BY date_depart ASC";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $annonces = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $annonce = new annonce_covoiturage(
                    $row['prenom_conducteur'],
                    $row['nom_conducteur'],
                    $row['tel_conducteur'],
                    $row['date_depart'],
                    $row['lieu_depart'],
                    $row['lieu_arrivee'],
                    $row['nombre_places'],
                    $row['type_voiture'],
                    $row['prix_estime'],
                    $row['description'] ?? '',
                    $row['status'] ?? 'disponible',
                    $row['user_id']
                );
                $annonce->setIdConducteur($row['id_conducteur']);
                $annonces[] = $annonce;
            }

            return $annonces;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des annonces: " . $e->getMessage());
        }
    }
}
?>