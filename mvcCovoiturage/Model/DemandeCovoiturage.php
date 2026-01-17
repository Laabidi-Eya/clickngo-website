<?php

class demande_covoiturage {
    private $id_passager;
    private $prenom_passager;
    private $nom_passager;
    private $tel_passager;
    private $id_conducteur;
    private $date_demande;
    private $status_demande;
    private $nbr_places_reservees;
    private $message;
    private $moyen_paiement;
    private $prix_total;
    private $date_creation;
    private $date_modification;
    private $user_id; // New property

    // Constructor
    public function __construct(
        $prenom_passager,
        $nom_passager,
        $tel_passager,
        $id_conducteur,
        $nbr_places_reservees,
        $message,
        $moyen_paiement,
        $prix_total = null,
        $status_demande = 'en cours',
        $user_id = null // Default to null for now
    ) {
        $this->prenom_passager = $prenom_passager;
        $this->nom_passager = $nom_passager;
        $this->tel_passager = $tel_passager;
        $this->id_conducteur = $id_conducteur;
        $this->nbr_places_reservees = $nbr_places_reservees;
        $this->message = $message;
        $this->moyen_paiement = $moyen_paiement;
        $this->prix_total = $prix_total;
        $this->status_demande = $status_demande;
        $this->date_demande = new DateTime();
        $this->date_creation = new DateTime();
        $this->date_modification = null;
        $this->user_id = $user_id; // Initialize user_id
    }

    // Getters
    public function getIdPassager() {
        return $this->id_passager;
    }

    public function getPrenomPassager() {
        return $this->prenom_passager;
    }

    public function getNomPassager() {
        return $this->nom_passager;
    }

    public function getTelPassager() {
        return $this->tel_passager;
    }

    public function getIdConducteur() {
        return $this->id_conducteur;
    }

    public function getDateDemande() {
        return $this->date_demande;
    }

    public function getStatusDemande() {
        return $this->status_demande;
    }

    public function getNbrPlacesReservees() {
        return $this->nbr_places_reservees;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getMoyenPaiement() {
        return $this->moyen_paiement;
    }

    public function getPrixTotal() {
        return $this->prix_total;
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
    public function setIdPassager($id) {
        $this->id_passager = $id;
    }

    public function setPrenomPassager($prenom) {
        $this->prenom_passager = $prenom;
        $this->date_modification = new DateTime();
    }

    public function setNomPassager($nom) {
        $this->nom_passager = $nom;
        $this->date_modification = new DateTime();
    }

    public function setTelPassager($tel) {
        $this->tel_passager = $tel;
        $this->date_modification = new DateTime();
    }

    public function setIdConducteur($id) {
        $this->id_conducteur = $id;
        $this->date_modification = new DateTime();
    }

    public function setStatusDemande($status) {
        $this->status_demande = $status;
        $this->date_modification = new DateTime();
    }

    public function setNbrPlacesReservees($nombre) {
        $this->nbr_places_reservees = $nombre;
        $this->date_modification = new DateTime();
    }

    public function setMessage($message) {
        $this->message = $message;
        $this->date_modification = new DateTime();
    }

    public function setMoyenPaiement($moyen) {
        $this->moyen_paiement = $moyen;
        $this->date_modification = new DateTime();
    }

    public function setPrixTotal($prix) {
        $this->prix_total = $prix;
        $this->date_modification = new DateTime();
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
        $this->date_modification = new DateTime();
    }

    // Method to convert object to array for database operations
    public function toArray() {
        return [
            'prenom_passager' => $this->prenom_passager,
            'nom_passager' => $this->nom_passager,
            'tel_passager' => $this->tel_passager,
            'id_conducteur' => $this->id_conducteur,
            'date_demande' => $this->date_demande->format('Y-m-d H:i:s'),
            'status_demande' => $this->status_demande,
            'nbr_places_reservees' => $this->nbr_places_reservees,
            'message' => $this->message,
            'moyen_paiement' => $this->moyen_paiement,
            'prix_total' => $this->prix_total,
            'date_creation' => $this->date_creation->format('Y-m-d H:i:s'),
            'date_modification' => $this->date_modification ? $this->date_modification->format('Y-m-d H:i:s') : null,
            'user_id' => $this->user_id // Include user_id
        ];
    }
}
?>