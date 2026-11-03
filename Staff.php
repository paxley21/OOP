<?php
require_once __DIR__ . '/User.php';
class Staff extends User {
    private string $department;
    public function __construct(int $id, string $name, string $email, string $department) {
        parent::__construct($id, $name, $email);
        $this->department = $department;
    }
    public function getDepartment(): string { return $this->department; }
    public function setDepartment(string $department): void { $this->department = trim($department); }
    public function getRole(): string { return 'Staff'; }
    public function displayInfo(): string { return $this->getRole() . ': ' . $this->getName() . ' - ' . $this->department; }
}
