<?php
class Avis {
    private $id;
    private $product_id;
    private $stars;
    private $email;
    private $comment;
    private $created_at;
    private $status; // Nouveau champ
    private $rejection_reason; // Nouveau champ

    public function __construct(
        $product_id,
        $stars,
        $email,
        $comment = null,
        $created_at = null,
        $id = null,
        $status = 'pending', // Valeur par défaut
        $rejection_reason = null
    ) {
        $this->id = $id;
        $this->setProductId($product_id);
        $this->setStars($stars);
        $this->setEmail($email);
        $this->setComment($comment);
        $this->created_at = $created_at ? new DateTime($created_at) : new DateTime();
        $this->setStatus($status);
        $this->setRejectionReason($rejection_reason);
    }

    // Create from database row
    public static function fromArray(array $data): Avis {
        return new Avis(
            $data['product_id'] ?? 0,
            $data['stars'] ?? 1,
            $data['email'] ?? '',
            $data['comment'] ?? null,
            $data['created_at'] ?? null,
            $data['id'] ?? null,
            $data['status'] ?? 'pending', // Nouveau champ
            $data['rejection_reason'] ?? null // Nouveau champ
        );
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getProductId(): int {
        return $this->product_id;
    }

    public function getStars(): int {
        return $this->stars;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getComment(): ?string {
        return $this->comment;
    }

    public function getCreatedAt(): string {
        return $this->created_at->format('Y-m-d H:i:s');
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getRejectionReason(): ?string {
        return $this->rejection_reason;
    }

    // Setters with validation
    public function setProductId($product_id): void {
        $product_id = (int)$product_id;
        if ($product_id <= 0) {
            throw new InvalidArgumentException("L'ID du produit doit être positif");
        }
        $this->product_id = $product_id;
    }

    public function setStars($stars): void {
        $stars = (int)$stars;
        if ($stars < 1 || $stars > 5) {
            throw new InvalidArgumentException("Les étoiles doivent être entre 1 et 5");
        }
        $this->stars = $stars;
    }

    public function setEmail(string $email): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("L'email n'est pas valide");
        }
        $this->email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    }

    public function setComment(?string $comment): void {
        $this->comment = $comment ? htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') : null;
    }

    public function setStatus(string $status): void {
        $allowedStatuses = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $allowedStatuses)) {
            throw new InvalidArgumentException("Statut invalide. Valeurs autorisées : " . implode(', ', $allowedStatuses));
        }
        $this->status = $status;
    }

    public function setRejectionReason(?string $rejection_reason): void {
        $this->rejection_reason = $rejection_reason ? htmlspecialchars($rejection_reason, ENT_QUOTES, 'UTF-8') : null;
    }

    // Convert to array for database operations
    public function toArray(): array {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'stars' => $this->stars,
            'email' => $this->email,
            'comment' => $this->comment,
            'created_at' => $this->getCreatedAt(),
            'status' => $this->status, // Nouveau champ
            'rejection_reason' => $this->rejection_reason // Nouveau champ
        ];
    }
}