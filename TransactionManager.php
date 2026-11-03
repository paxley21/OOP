<?php
require_once __DIR__ . '/BorrowTransaction.php';
class TransactionManager {
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }
    public function borrow(int $equipmentId, int $userId, int $quantity): void {
        if ($quantity < 1) throw new Exception('Quantity must be at least 1.');
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT quantity FROM equipment WHERE id=? FOR UPDATE'); $stmt->execute([$equipmentId]); $equipment = $stmt->fetch();
            if (!$equipment) throw new Exception('Equipment not found.');
            if ((int)$equipment['quantity'] < $quantity) throw new Exception('Not enough equipment available.');
            $stmt = $this->db->prepare('INSERT INTO transactions(equipment_id,user_id,quantity,borrow_date,status) VALUES(?,?,?,?,?)');
            $stmt->execute([$equipmentId,$userId,$quantity,date('Y-m-d H:i:s'),'Borrowed']);
            $newQty = (int)$equipment['quantity'] - $quantity; $status = $newQty > 0 ? 'Available' : 'Out of Stock';
            $stmt = $this->db->prepare('UPDATE equipment SET quantity=?, status=? WHERE id=?'); $stmt->execute([$newQty,$status,$equipmentId]);
            $this->db->commit();
        } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
    }
    public function returnItem(int $transactionId): void {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT * FROM transactions WHERE id=? FOR UPDATE'); $stmt->execute([$transactionId]); $t = $stmt->fetch();
            if (!$t || $t['status'] !== 'Borrowed') throw new Exception('Transaction is not active.');
            $stmt = $this->db->prepare('UPDATE transactions SET status="Returned", return_date=? WHERE id=?'); $stmt->execute([date('Y-m-d H:i:s'),$transactionId]);
            $stmt = $this->db->prepare('UPDATE equipment SET quantity=quantity+?, status="Available" WHERE id=?'); $stmt->execute([$t['quantity'],$t['equipment_id']]);
            $this->db->commit();
        } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
    }
    public function all(): array {
        return $this->db->query('SELECT t.*, e.name equipment_name, u.name borrower_name FROM transactions t JOIN equipment e ON e.id=t.equipment_id JOIN users u ON u.id=t.user_id ORDER BY t.id DESC')->fetchAll();
    }
}
