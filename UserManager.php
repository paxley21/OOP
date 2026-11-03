<?php
require_once __DIR__ . '/Student.php';
require_once __DIR__ . '/Staff.php';
class UserManager {
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }
    public function all(): array { return $this->db->query('SELECT * FROM users ORDER BY id DESC')->fetchAll(); }
    public function add(string $name, string $email, string $type, string $extra): void {
        $stmt = $this->db->prepare('INSERT INTO users(name,email,type,extra) VALUES(?,?,?,?)'); $stmt->execute([$name,$email,$type,$extra]);
    }
    public function delete(int $id): void { $stmt = $this->db->prepare('DELETE FROM users WHERE id=?'); $stmt->execute([$id]); }
    public function makeObject(array $row): User {
        return $row['type'] === 'Student'
            ? new Student((int)$row['id'], $row['name'], $row['email'], $row['extra'])
            : new Staff((int)$row['id'], $row['name'], $row['email'], $row['extra']);
    }
}
