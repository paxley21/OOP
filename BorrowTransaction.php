<?php
class BorrowTransaction {
    private int $id;
    private int $equipmentId;
    private int $borrowerId;
    private int $quantity;
    private string $borrowDate;
    private ?string $returnDate;
    private string $status;

    public function __construct(int $id, int $equipmentId, int $borrowerId, int $quantity, string $borrowDate, ?string $returnDate, string $status) {
        $this->id = $id; $this->equipmentId = $equipmentId; $this->borrowerId = $borrowerId; $this->quantity = $quantity;
        $this->borrowDate = $borrowDate; $this->returnDate = $returnDate; $this->status = $status;
    }
    public function getId(): int { return $this->id; }
    public function getEquipmentId(): int { return $this->equipmentId; }
    public function getBorrowerId(): int { return $this->borrowerId; }
    public function getQuantity(): int { return $this->quantity; }
    public function getStatus(): string { return $this->status; }
    public function isActive(): bool { return $this->status === 'Borrowed'; }
}
