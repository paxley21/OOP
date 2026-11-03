<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Equipment.php';
class EquipmentManager {
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }
    public function all(string $search = ''): array {
        if ($search !== '') {
            $stmt = $this->db->prepare('SELECT * FROM equipment WHERE name LIKE ? OR category LIKE ? ORDER BY id DESC');
            $term = "%$search%"; $stmt->execute([$term, $term]);
        } else { $stmt = $this->db->query('SELECT * FROM equipment ORDER BY id DESC'); }
        return $stmt->fetchAll();
    }
    public function add(string $name, string $category, int $quantity): void {
        $stmt = $this->db->prepare('INSERT INTO equipment(name, category, quantity, status) VALUES (?, ?, ?, ?)');
        $status = $quantity > 0 ? 'Available' : 'Out of Stock'; $stmt->execute([$name, $category, $quantity, $status]);
    }
    public function update(int $id, string $name, string $category, int $quantity): void {
        $stmt = $this->db->prepare('UPDATE equipment SET name=?, category=?, quantity=?, status=? WHERE id=?');
        $status = $quantity > 0 ? 'Available' : 'Out of Stock'; $stmt->execute([$name, $category, $quantity, $status, $id]);
    }
    public function delete(int $id): void { $stmt = $this->db->prepare('DELETE FROM equipment WHERE id=?'); $stmt->execute([$id]); }
}
