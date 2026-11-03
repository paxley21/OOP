<?php
class Equipment {
    private int $id;
    private string $name;
    private string $category;
    private int $quantity;
    private string $status;

    public function __construct(int $id, string $name, string $category, int $quantity, string $status = 'Available') {
        $this->id = $id; $this->name = $name; $this->category = $category; $this->quantity = $quantity; $this->status = $status;
    }
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = trim($name); }
    public function getCategory(): string { return $this->category; }
    public function setCategory(string $category): void { $this->category = trim($category); }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): void { $this->quantity = max(0, $quantity); }
    public function getStatus(): string { return $this->status; }
    public function updateStatus(): void { $this->status = $this->quantity > 0 ? 'Available' : 'Out of Stock'; }
}
