<?php

class CommandModel {
    private $id_commande;
    private $id_user;
    private $id_produit;
    private $quantite;
    private $date_commande;
    private $statut_commande = 'en_attente';


    public function __construct($id_user, $id_produit, $quantite, $statut_commande = 'en_attente', $date_commande = null, $id_commande = null) {
        $this->id_commande = $id_commande;
        $this->setIdUser($id_user);
        $this->setIdProduit($id_produit);
        $this->setQuantite($quantite);
        $this->setStatutCommande($statut_commande);
        $this->date_commande = $date_commande ? new DateTime($date_commande) : new DateTime();
    }
    
    

    public static function fromArray(array $data): CommandModel {
        return new CommandModel(
            $data['id_user'] ?? null,
            $data['id_produit'] ?? null,
            $data['quantite'] ?? 0,
            $data['statut_commande'] ?? 'en_attente',
            $data['date_commande'] ?? null,
            $data['id_commande'] ?? null
        );
    }

    public function getIdCommande(): ?int {
        return $this->id_commande;
    }

    public function getIdUser(): ?int {
        return $this->id_user;
    }

    public function getIdProduit(): ?int {
        return $this->id_produit;
    }

    public function getQuantite(): int {
        return $this->quantite;
    }

    public function getDateCommande(): string {
        return $this->date_commande->format('Y-m-d H:i:s');
    }

    public function getStatutCommande(): string {
        return $this->statut_commande;
    }

    public function setIdUser($id_user): void {
        if (!is_int($id_user) || $id_user <= 0) {
            throw new InvalidArgumentException("L'ID de l'utilisateur doit être un entier positif.");
        }
        $this->id_user = $id_user;
    }

    public function setIdProduit($id_produit): void {
        if (!is_int($id_produit) || $id_produit <= 0) {
            throw new InvalidArgumentException("L'ID du produit doit être un entier positif.");
        }
        $this->id_produit = $id_produit;
    }

    public function setQuantite($quantite): void {
        $quantite = (int)$quantite;
        if ($quantite <= 0) {
            throw new InvalidArgumentException("La quantité doit être un entier positif.");
        }
        $this->quantite = $quantite;
    }

    public function setStatutCommande(string $statut_commande): void {
        $allowedStatuses = ['en_attente', 'confirmee', 'livree', 'annulee'];
        if (!in_array($statut_commande, $allowedStatuses)) {
            throw new InvalidArgumentException("Statut de commande invalide: " . $statut_commande);
        }
        $this->statut_commande = $statut_commande;
    }

    public function toArray(): array {
        return [
            'id_commande' => $this->id_commande,
            'id_user' => $this->id_user,
            'id_produit' => $this->id_produit,
            'quantite' => $this->quantite,
            'date_commande' => $this->getDateCommande(),
            'statut_commande' => $this->statut_commande
        ];
    }



    /**
     * Récupère toutes les commandes depuis la base de données.
     * @param PDO $db L'instance de connexion PDO.
     * @return array Un tableau contenant toutes les commandes.
     */
    public static function getAll(PDO $db): array {
        $stmt = $db->query("SELECT * FROM commandes");
        return $stmt->fetchAll();
    }

    /**
     * Récupère une commande spécifique par son ID.
     * @param int $id L'ID de la commande à récupérer.
     * @param PDO $db L'instance de connexion PDO.
     * @return CommandModel|null L'objet CommandModel si trouvé, null sinon.
     */
    public static function getById(int $id, PDO $db): ?CommandModel {
        $stmt = $db->prepare("SELECT * FROM commandes WHERE id_commande = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if ($data) {
            return self::fromArray($data);
        }
        return null;
    }

    /**
     * Met à jour une commande existante dans la base de données.
     * @param CommandModel $commande L'objet CommandModel à mettre à jour.
     * @param PDO $db L'instance de connexion PDO.
     * @return bool True en cas de succès, false en cas d'échec.
     */
    public static function mettreAJour(CommandModel $commande, PDO $db): bool {
        $stmt = $db->prepare("UPDATE commandes SET id_user = ?, id_produit = ?, quantite = ?, statut_commande = ? WHERE id_commande = ?");
        return $stmt->execute([
            $commande->getIdUser(),
            $commande->getIdProduit(),
            $commande->getQuantite(),
            $commande->getStatutCommande(),
            $commande->getIdCommande()
        ]);
    }

    /**
     * Supprime une commande de la base de données par son ID.
     * @param int $id L'ID de la commande à supprimer.
     * @param PDO $db L'instance de connexion PDO.
     * @return bool True en cas de succès, false en cas d'échec.
     */
    public static function supprimer(int $id, PDO $db): bool {
        $stmt = $db->prepare("DELETE FROM commandes WHERE id_commande = ?");
        return $stmt->execute([$id]);
    }



    public static function creerCommandeDepuisTableau($id_user, $panier_data, PDO $db) {
        try {
            $db->beginTransaction();
            $date_commande = date('Y-m-d H:i:s');
            $statut_commande = 'en_attente';

            foreach ($panier_data as $item) {
                $id_produit = $item['id'];
                $quantite = $item['quantite'];

                // Vérifier le stock avant de commander
                $stmtCheckStock = $db->prepare("SELECT stock FROM products WHERE id = :id_produit");
                $stmtCheckStock->bindParam(':id_produit', $id_produit, PDO::PARAM_INT);
                $stmtCheckStock->execute();
                $productStock = $stmtCheckStock->fetchColumn();

                if ($productStock === false || $productStock < $quantite) {
                    $db->rollBack();
                    throw new Exception("Stock insuffisant pour le produit ID " . $id_produit);
                }

                // Insertion de la commande
                $sqlInsertCommande = "INSERT INTO commandes (id_user, id_produit, quantite, date_commande) VALUES (:id_user, :id_produit, :quantite,  :date_commande)";
                $stmtInsertCommande = $db->prepare($sqlInsertCommande);
                $stmtInsertCommande->bindParam(':id_user', $id_user, PDO::PARAM_INT);
                $stmtInsertCommande->bindParam(':id_produit', $id_produit, PDO::PARAM_INT);
                $stmtInsertCommande->bindParam(':quantite', $quantite, PDO::PARAM_INT);
          
                $stmtInsertCommande->bindParam(':date_commande', $date_commande, PDO::PARAM_STR);

                if (!$stmtInsertCommande->execute()) {
                    $errorInfo = $stmtInsertCommande->errorInfo();
                    error_log("Erreur lors de l'insertion de la commande : " . $errorInfo[2] . " SQL: " . $sqlInsertCommande);
                    $db->rollBack();
                    return false;
                }

                // Mise à jour du stock
                $sqlUpdateStock = "UPDATE products SET stock = stock - :quantite WHERE id = :id_produit";
                $stmtUpdateStock = $db->prepare($sqlUpdateStock);
                $stmtUpdateStock->bindParam(':quantite', $quantite, PDO::PARAM_INT);
                $stmtUpdateStock->bindParam(':id_produit', $id_produit, PDO::PARAM_INT);

                if (!$stmtUpdateStock->execute()) {
                    $errorInfo = $stmtUpdateStock->errorInfo();
                    error_log("Erreur lors de la mise à jour du stock : " . $errorInfo[2] . " SQL: " . $sqlUpdateStock);
                    $db->rollBack();
                    return false;
                }
            }

            $db->commit();
            
            return true;

        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Erreur PDO lors de la création de la commande : " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Erreur : " . $e->getMessage());
            return false;
        }
    }

}
?>