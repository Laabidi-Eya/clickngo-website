<?php
class Activity {
    private $id;
    private $name;
    private $description;
    private $price;
    private $location;
    private $date;
    private $category;
    private $capacity;
    private $image;

    // Constructeur
    public function __construct($id = null, $name = '', $description = '', $price = 0.0, $location = '', $date = '', $category = '', $capacity = 0, $image = null) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->location = $location;
        $this->date = $date;
        $this->category = $category;
        $this->capacity = $capacity;
        $this->image = $image;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getDate() {
        return $this->date;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getCapacity() {
        return $this->capacity;
    }

    public function getImage() {
        return $this->image;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        if (empty($name)) {
            throw new InvalidArgumentException("Le nom de l'activité ne peut pas être vide.");
        }
        $this->name = $name;
    }

    public function setDescription($description) {
        if (empty($description)) {
            throw new InvalidArgumentException("La description de l'activité ne peut pas être vide.");
        }
        $this->description = $description;
    }

    public function setPrice($price) {
        if (!is_numeric($price) || $price < 0) {
            throw new InvalidArgumentException("Le prix doit être un nombre positif.");
        }
        $this->price = $price;
    }

    public function setLocation($location) {
        if (empty($location)) {
            throw new InvalidArgumentException("Le lieu de l'activité ne peut pas être vide.");
        }
        $this->location = $location;
    }

    public function setDate($date) {
        // Validation simple de la date
        if (strtotime($date) === false) {
            throw new InvalidArgumentException("La date est invalide.");
        }
        $this->date = $date;
    }

    public function setCategory($category) {
        $validCategories = ['sport', 'bien-etre', 'culture', 'autre'];
        if (!in_array($category, $validCategories)) {
            throw new InvalidArgumentException("Catégorie invalide.");
        }
        $this->category = $category;
    }

    public function setCapacity($capacity) {
        if (!is_numeric($capacity) || $capacity < 1) {
            throw new InvalidArgumentException("La capacité doit être un entier positif.");
        }
        $this->capacity = $capacity;
    }

    public function setImage($image) {
        $this->image = $image;
    }
}
?>