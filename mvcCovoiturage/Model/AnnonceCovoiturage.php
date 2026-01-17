<?php
require_once '../Controller/AnnonceCovoiturageController.php';
require_once '../config.php';

class annonce_covoiturage {
    private $id_conducteur;
    private $prenom_conducteur;
    private $nom_conducteur;
    private $tel_conducteur;
    private $date_depart;
    private $lieu_depart;
    private $lieu_arrivee;
    private $nombre_places;
    private $type_voiture;
    private $prix_estime;
    private $description;
    private $status;
    private $date_creation;
    private $date_modification;
    private $user_id; // New property

    // Constructor
    public function __construct(
        $prenom_conducteur,
        $nom_conducteur,
        $tel_conducteur,
        $date_depart,
        $lieu_depart,
        $lieu_arrivee,
        $nombre_places,
        $type_voiture,
        $prix_estime,
        $description,
        $status = 'disponible',
        $user_id = null // Default to null for now
    ) {
        $this->prenom_conducteur = $prenom_conducteur;
        $this->nom_conducteur = $nom_conducteur;
        $this->tel_conducteur = $tel_conducteur;
        $this->date_depart = new DateTime($date_depart);
        $this->lieu_depart = $lieu_depart;
        $this->lieu_arrivee = $lieu_arrivee;
        $this->nombre_places = $nombre_places;
        $this->type_voiture = $type_voiture;
        $this->prix_estime = $prix_estime;
        $this->description = $description;
        $this->status = $status;
        $this->date_creation = new DateTime();
        $this->date_modification = null;
        $this->user_id = $user_id; // Initialize user_id
    }

    // Getters
    public function getIdConducteur() {
        return $this->id_conducteur;
    }

    public function getPrenomConducteur() {
        return $this->prenom_conducteur;
    }

    public function getNomConducteur() {
        return $this->nom_conducteur;
    }

    public function getTelConducteur() {
        return $this->tel_conducteur;
    }

    public function getDateDepart() {
        return $this->date_depart;
    }

    public function getLieuDepart() {
        return $this->lieu_depart;
    }

    public function getLieuArrivee() {
        return $this->lieu_arrivee;
    }

    public function getNombrePlaces() {
        return $this->nombre_places;
    }

    public function getTypeVoiture() {
        return $this->type_voiture;
    }

    public function getPrixEstime() {
        return $this->prix_estime;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getDateCreation() {
        return $this->date_creation;
    }

    public function getDateModification() {
        return $this->date_modification;
    }

    public function getUserId() {
        return $this->user_id;
    }

    // Setters
    public function setIdConducteur($id) {
        $this->id_conducteur = $id;
    }

    public function setPrenomConducteur($prenom) {
        $this->prenom_conducteur = $prenom;
        $this->date_modification = new DateTime();
    }

    public function setNomConducteur($nom) {
        $this->nom_conducteur = $nom;
        $this->date_modification = new DateTime();
    }

    public function setTelConducteur($tel) {
        $this->tel_conducteur = $tel;
        $this->date_modification = new DateTime();
    }

    public function setDateDepart($date) {
        $this->date_depart = $date;
        $this->date_modification = new DateTime();
    }

    public function setLieuDepart($lieu) {
        $this->lieu_depart = $lieu;
        $this->date_modification = new DateTime();
    }

    public function setLieuArrivee($lieu) {
        $this->lieu_arrivee = $lieu;
        $this->date_modification = new DateTime();
    }

    public function setNombrePlaces($nombre) {
        $this->nombre_places = $nombre;
        $this->date_modification = new DateTime();
    }

    public function setTypeVoiture($type) {
        $this->type_voiture = $type;
        $this->date_modification = new DateTime();
    }

    public function setPrixEstime($prix) {
        $this->prix_estime = $prix;
        $this->date_modification = new DateTime();
    }

    public function setDescription($description) {
        $this->description = $description;
        $this->date_modification = new DateTime();
    }

    public function setStatus($status) {
        $this->status = $status;
        $this->date_modification = new DateTime();
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
        $this->date_modification = new DateTime();
    }

    // Other methods
    public function getAnnoncesByConducteurId($id_conducteur) {
        try {
            $pdo = config::getConnexion();
            $controller = new AnnonceCovoiturageController($pdo);
            return $controller->getAnnoncesByConducteurId($id_conducteur);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des annonces par conducteur: " . $e->getMessage());
        }
    }

    public static function getRecentAnnonces($limit = 8) {
        try {
            $pdo = config::getConnexion();
            $controller = new AnnonceCovoiturageController($pdo);
            return $controller->getRecentAnnonces($limit);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des annonces récentes: " . $e->getMessage());
        }
    }

    public function generateImageUrl() {
        $imageKeyword = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $this->lieu_arrivee));
        return "https://source.unsplash.com/300x200/?" . urlencode($imageKeyword . ",city,travel");
    }

    public function toArrayForInsert() {
        return [
            'prenom_conducteur' => $this->prenom_conducteur,
            'nom_conducteur' => $this->nom_conducteur,
            'tel_conducteur' => $this->tel_conducteur,
            'date_depart' => $this->date_depart->format('Y-m-d H:i:s'),
            'lieu_depart' => $this->lieu_depart,
            'lieu_arrivee' => $this->lieu_arrivee,
            'nombre_places' => $this->nombre_places,
            'type_voiture' => $this->type_voiture,
            'prix_estime' => $this->prix_estime,
            'description' => $this->description,
            'status' => $this->status,
            'date_creation' => $this->date_creation->format('Y-m-d H:i:s'),
            'user_id' => $this->user_id // Include user_id
        ];
    }
}
?>