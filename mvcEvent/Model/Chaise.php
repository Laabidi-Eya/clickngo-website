<?php
class Chaise {
    private $id;
    private $event_id;
    private $numero;
    private $statut;
    private $id_user;
    private $created_at;
    private $updated_at;

    public function __construct($event_id, $numero, $statut = 'libre', $id_user = null) {
        $this->event_id = $event_id;
        $this->numero = $numero;
        $this->statut = $statut;
        $this->id_user = $id_user;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getEventId() {
        return $this->event_id;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function getIdUser() {
        return $this->id_user;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setEventId($event_id) {
        $this->event_id = $event_id;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
    }

    public function setIdUser($id_user) {
        $this->id_user = $id_user;
    }
}
?>