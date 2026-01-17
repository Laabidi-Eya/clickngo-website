<?php

class LouerModel {
    private $id;
    private $id_user;
    private $produit;
    private $nom;
    private $prenom;
    private $date_location;
    private $heure_debut;
    private $heure_fin;
    private $telephone;
    private $carte_identite;
    private $created_at;
    private $statut_location;

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_CONFIRMEE = 'confirmee';
    public const STATUT_LIVREE = 'livree';
    public const STATUT_ANNULEE = 'annulee';

    public function __construct(
        ?int $id = null,
        ?int $id_user = null,
        ?string $produit = null,
        ?string $nom = null,
        ?string $prenom = null,
        ?string $date_location = null,
        ?string $heure_debut = null,
        ?string $heure_fin = null,
        ?string $telephone = null,
        ?string $carte_identite = null,
        ?string $created_at = null,
        ?string $statut_location = null
    ) {
        $this->id = $id;
        $this->id_user = $id_user;
        $this->produit = $produit;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->date_location = $date_location;
        $this->heure_debut = $heure_debut;
        $this->heure_fin = $heure_fin;
        $this->telephone = $telephone;
        $this->carte_identite = $carte_identite;
        $this->created_at = $created_at ? new DateTime($created_at) : new DateTime();
        $this->statut_location = $statut_location;
    }

    public static function fromArray(array $data): LouerModel {
        return new LouerModel(
            $data['id'] ?? null,
            $data['id_user'] ?? null,
            $data['produit'] ?? null,
            $data['nom'] ?? null,
            $data['prenom'] ?? null,
            $data['date_location'] ?? null,
            $data['heure_debut'] ?? null,
            $data['heure_fin'] ?? null,
            $data['telephone'] ?? null,
            $data['carte_identite'] ?? null,
            $data['created_at'] ?? null,
            $data['statut_location'] ?? null
        );
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): ?int { return $this->id_user; }
    public function getProduit(): ?string { return $this->produit; }
    public function getNom(): ?string { return $this->nom; }
    public function getPrenom(): ?string { return $this->prenom; }
    public function getDateLocation(): ?string { return $this->date_location; }
    public function getHeureDebut(): ?string { return $this->heure_debut; }
    public function getHeureFin(): ?string { return $this->heure_fin; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function getCarteIdentite(): ?string { return $this->carte_identite; }
    public function getCreatedAt(): string { return $this->created_at->format('Y-m-d H:i:s'); }
    public function getStatutLocation(): ?string { return $this->statut_location; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setUserId(?int $userId): void { $this->id_user = $userId; }
    public function setProduit(?string $produit): void { $this->produit = $produit; }
    public function setNom(?string $nom): void { $this->nom = htmlspecialchars($nom ?? '', ENT_QUOTES, 'UTF-8'); }
    public function setPrenom(?string $prenom): void { $this->prenom = htmlspecialchars($prenom ?? '', ENT_QUOTES, 'UTF-8'); }
    public function setDateLocation(?string $dateLocation): void { $this->date_location = $dateLocation; }
    public function setHeureDebut(?string $heureDebut): void { $this->heure_debut = $heureDebut; }
    public function setHeureFin(?string $heureFin): void { $this->heure_fin = $heureFin; }
    public function setTelephone(?string $telephone): void { $this->telephone = htmlspecialchars($telephone ?? '', ENT_QUOTES, 'UTF-8'); }
    public function setCarteIdentite(?string $carteIdentite): void { $this->carte_identite = htmlspecialchars($carteIdentite ?? '', ENT_QUOTES, 'UTF-8'); }
    public function setCreatedAt(?string $createdAt): void { $this->created_at = $createdAt ? new DateTime($createdAt) : new DateTime(); }
    public function setStatutLocation(?string $statutLocation): void {
        $allowedStatuses = [
            self::STATUT_EN_ATTENTE,
            self::STATUT_CONFIRMEE,
            self::STATUT_LIVREE,
            self::STATUT_ANNULEE,
            // Ajoutez les autres statuts si nécessaire
        ];
        if ($statutLocation && !in_array($statutLocation, $allowedStatuses)) {
            throw new InvalidArgumentException("Statut de location invalide: " . $statutLocation);
        }
        $this->statut_location = $statutLocation;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'id_user' => $this->id_user,
            'produit' => $this->produit,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'date_location' => $this->date_location,
            'heure_debut' => $this->heure_debut,
            'heure_fin' => $this->heure_fin,
            'telephone' => $this->telephone,
            'carte_identite' => $this->carte_identite,
            'created_at' => $this->getCreatedAt(),
            'statut_location' => $this->statut_location,
        ];
    }
}

?>