<?php
class Product {
    private $id;
    private $name;
    private $price;
    private $stock;
    private $category;
    private $purchase_available;
    private $rental_available;
    private $photo;
    private $created_at;

    public function __construct($name, $price, $stock, $category, $purchase_available, $rental_available, $photo = null, $created_at = null, $id = null) {
        $this->id = $id;
        $this->setName($name);
        $this->setPrice($price);
        $this->setStock($stock);
        $this->setCategory($category);
        $this->setPurchaseAvailable($purchase_available);
        $this->setRentalAvailable($rental_available);
        $this->setPhoto($photo);
        $this->created_at = $created_at ? new DateTime($created_at) : new DateTime();
    }

    // Create from database row
    public static function fromArray(array $data): Product {
        return new Product(
            $data['name'] ?? '',
            $data['price'] ?? 0,
            $data['stock'] ?? 0,
            $data['category'] ?? '',
            $data['purchase_available'] ?? false,
            $data['rental_available'] ?? false,
            $data['photo'] ?? null,
            $data['created_at'] ?? null,
            $data['id'] ?? null
        );
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getStock(): int {
        return $this->stock;
    }

    public function getCategory(): string {
        return $this->category;
    }

    public function getPurchaseAvailable(): bool {
        return $this->purchase_available;
    }

    public function getRentalAvailable(): bool {
        return $this->rental_available;
    }

    public function getPhoto(): ?string {
        return $this->photo;
    }

    public function getCreatedAt(): string {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    public function getFormattedPrice(): string {
        return number_format($this->price, 2, '.', '') . ' TND';
    }

    // Setters with validation
    public function setName(string $name): void {
        if (empty($name)) {
            throw new InvalidArgumentException("Le nom du produit ne peut pas être vide");
        }
        $this->name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    }

    public function setPrice($price): void {
        $price = (float)$price;
        if ($price < 0) {
            throw new InvalidArgumentException("Le prix ne peut pas être négatif");
        }
        $this->price = round($price, 2);
    }

    public function setStock($stock): void {
        $stock = (int)$stock;
        if ($stock < 0) {
            throw new InvalidArgumentException("Le stock ne peut pas être négatif");
        }
        $this->stock = $stock;
    }

    

    public function setCategory(string $category): void {
        $allowedCategories = [
            'Équipements Sportifs',
            'Vêtements et Accessoires', 
            'Gadgets & Technologies',
            'Articles de Bien-être & Récupération',
            'Nutrition & Hydratation',
            'Accessoires de Voyage & Mobilité',
            'Supports et accessoires d\'atelier',
            'Univers du cerveau'
        ];
        
        if (!in_array($category, $allowedCategories)) {
            throw new InvalidArgumentException("Catégorie invalide: " . $category);
        }
        $this->category = $category;
    }

    public function setPurchaseAvailable($purchase_available): void {
        $this->purchase_available = (bool)$purchase_available;
    }

    public function setRentalAvailable($rental_available): void {
        $this->rental_available = (bool)$rental_available;
    }

    public function setPhoto(?string $photo): void {
        // Default image if none provided
        $this->photo = $photo ?? 'images/default-product.jpg';
    }

    // Business logic methods
    public function isAvailable(): bool {
        return $this->stock > 0;
    }

    public function canBePurchased(): bool {
        return $this->purchase_available && $this->isAvailable();
    }

    public function canBeRented(): bool {
        return $this->rental_available && $this->isAvailable();
    }

    // Convert to array for database operations
    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,
            'category' => $this->category,
            'purchase_available' => (int)$this->purchase_available,
            'rental_available' => (int)$this->rental_available,
            'photo' => $this->photo,
            'created_at' => $this->getCreatedAt()
        ];
    }
}
?>