<?php

require_once(__DIR__ . '/../Model/LouerModel.php');
require_once(__DIR__ . "../../config.php");


class LouerController
{

    private $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // CREATE
    public function create(LouerModel $louer)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO louer (id_user, produit, nom, prenom, date_location, heure_debut, heure_fin, telephone, carte_identite, created_at, statut_location)
            VALUES (:id_user, :produit, :nom, :prenom, :date_location, :heure_debut, :heure_fin, :telephone, :carte_identite, :created_at, :statut_location)
        ");
        return $stmt->execute($louer->toArray());
    }

    // READ (by ID)
    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM louer WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? LouerModel::fromArray($data) : null;
    }

    // READ ALL
    public function findAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM louer ORDER BY created_at DESC");
        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = LouerModel::fromArray($row);
        }
        return $results;
    }

    // UPDATE
    public function update(LouerModel $louer)
    {
        $stmt = $this->pdo->prepare("
            UPDATE louer SET 
                id_user = :id_user,
                produit = :produit,
                nom = :nom,
                prenom = :prenom,
                date_location = :date_location,
                heure_debut = :heure_debut,
                heure_fin = :heure_fin,
                telephone = :telephone,
                carte_identite = :carte_identite,
                statut_location = :statut_location
            WHERE id = :id
        ");
        $data = $louer->toArray();
        return $stmt->execute($data);
    }

    // DELETE
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM louer WHERE id = ?");
        return $stmt->execute([$id]);
    }



    public function validerLocation()
    {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (
                    isset(
                        $_POST['produit'],
                        $_POST['nom'],
                        $_POST['prenom'],
                        $_POST['telephone'],
                        $_POST['carte_identite'],
                        $_POST['heure_debut'],
                        $_POST['heure_fin'],
                        $_POST['produit_id']
                    )
                ) {
                    $produit = $_POST['produit'];
                    $nom = $_POST['nom'];
                    $prenom = $_POST['prenom'];
                    $telephone = $_POST['telephone'];
                    $carteIdentite = $_POST['carte_identite'];
                    $dateLocation = date('Y-m-d');
                    $heureDebut = $_POST['heure_debut'];
                    $heureFin = $_POST['heure_fin'];
                    $produitId = $_POST['produit_id'];

                    // Remplacer par $_SESSION['id_user'] si la session utilisateur est active
                    $utilisateurId = $_SESSION['user']['id_user'] ?? null;
                    if (isset($_SESSION['user']['id_user'])) {
    $utilisateurId = $_SESSION['user']['id_user'];
} else {
    // Gérer le cas où l'utilisateur n'est pas connecté
    $utilisateurId = null;
    error_log("⚠️ Aucun utilisateur connecté.");
    // Ou : redirige vers login
    // header('Location: /login.php');
    // exit;
}


                    // Vérifier le stock
                    $stmt = $this->pdo->prepare("SELECT stock FROM products WHERE id = :produit_id");
                    $stmt->bindParam(':produit_id', $produitId);
                    $stmt->execute();
                    $stock = $stmt->fetchColumn();

                    if ($stock <= 0) {
                        echo "Stock insuffisant pour la location.";
                        exit;
                    }

                    // Insérer la location
                    $stmt = $this->pdo->prepare("INSERT INTO louer (
                        produit, nom, prenom, date_location, heure_debut, heure_fin, 
                        telephone, carte_identite, id_user, created_at, statut_location
                    ) VALUES (
                        :produit, :nom, :prenom, :date_location, :heure_debut, :heure_fin, 
                        :telephone, :carte_identite, :id_user, NOW(), 'en_attente'
                    )");

                    $stmt->bindParam(':produit', $produit);
                    $stmt->bindParam(':nom', $nom);
                    $stmt->bindParam(':prenom', $prenom);
                    $stmt->bindParam(':date_location', $dateLocation);
                    $stmt->bindParam(':heure_debut', $heureDebut);
                    $stmt->bindParam(':heure_fin', $heureFin);
                    $stmt->bindParam(':telephone', $telephone);
                    $stmt->bindParam(':carte_identite', $carteIdentite);
                    $stmt->bindParam(':id_user', $utilisateurId);

                    if ($stmt->execute()) {
                        // Mise à jour du stock
                        $updateStockStmt = $this->pdo->prepare("UPDATE products SET stock = stock - 1 WHERE id = :produit_id");
                        $updateStockStmt->bindParam(':produit_id', $produitId);
                        if ($updateStockStmt->execute()) {
                            // Stocker les données de location dans la session
                            $_SESSION['location_data'] = [
                                'nom' => $nom,
                                'prenom' => $prenom,
                                'produit' => $produit,
                                'date_location' => $dateLocation,
                                'heure_debut' => $heureDebut,
                                'heure_fin' => $heureFin,
                                'telephone' => $telephone,
                                'carte_identite' => $carteIdentite
                            ];
                            $_SESSION['location_success'] = 'Location effectuée avec succès!';
                            header("Location: confirmation.php");
                            exit;
                        } else {
                            echo "Erreur lors de la mise à jour du stock.";
                        }
                    } else {
                        echo "Une erreur est survenue pendant l'enregistrement.";
                    }
                } else {
                    echo "Tous les champs du formulaire doivent être remplis.";
                }
            } catch (PDOException $e) {
                echo "Erreur de base de données : " . $e->getMessage();
            }
        }
    }
}
