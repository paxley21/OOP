<?php
require_once __DIR__ . '/User.php';
class Student extends User {
    private string $course;
    public function __construct(int $id, string $name, string $email, string $course) {
        parent::__construct($id, $name, $email);
        $this->course = $course;
    }
    public function getCourse(): string { return $this->course; }
    public function setCourse(string $course): void { $this->course = trim($course); }
    public function getRole(): string { return 'Student'; }
    public function displayInfo(): string { return $this->getRole() . ': ' . $this->getName() . ' - ' . $this->course; }
}
