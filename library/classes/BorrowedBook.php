<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/User.php';

class BorrowedBook {
    private PDO $db;
    private User $userModel;

    public function __construct() {
        $this->db        = Database::getConnection();
        $this->userModel = new User();
    }

    public function calculatePenalty(string $dueDate, ?string $returnDate = null): float {
        $due    = new DateTime($dueDate);
        $actual = $returnDate ? new DateTime($returnDate) : new DateTime();
        if ($actual <= $due) return 0.0;
        $days = (int) $due->diff($actual)->days;
        return round($days * PENALTY_PER_DAY, 2);
    }

    public function syncOverdueStatuses(): void {
        $stmt = $this->db->query(
            "SELECT id, due_date FROM borrowed_books WHERE status = 'borrowed' AND due_date < CURDATE()"
        );
        $overdue = $stmt->fetchAll();

        foreach ($overdue as $row) {
            $penalty = $this->calculatePenalty($row['due_date']);
            $this->db->prepare(
                "UPDATE borrowed_books SET status='overdue', penalty=:pen WHERE id=:id"
            )->execute([':pen' => $penalty, ':id' => $row['id']]);
        }
    }

    public function returnBook(int $borrowId, int $adminId): array|false {
        $record = $this->getById($borrowId);
        if (!$record || $record['status'] === 'returned') return false;

        $returnDate = date('Y-m-d');
        $penalty    = $this->calculatePenalty($record['due_date'], $returnDate);

        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                "UPDATE borrowed_books SET status='returned', return_date=:rd, penalty=:pen WHERE id=:id"
            )->execute([':rd' => $returnDate, ':pen' => $penalty, ':id' => $borrowId]);

            $this->db->prepare(
                "UPDATE books SET available_quantity = LEAST(available_quantity+1, quantity)
                 WHERE book_id = :bid"
            )->execute([':bid' => $record['book_id']]);

            $msg = "You have returned \"{$record['title']}\".";
            if ($penalty > 0) $msg .= " Penalty: ₱{$penalty}.";
            $this->userModel->addNotification($record['user_id'], 'Book Returned', $msg, $penalty > 0 ? 'warning' : 'success');

            $this->db->commit();
            return ['penalty' => $penalty, 'return_date' => $returnDate];
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT bb.*, b.title, b.author, b.image, b.isbn,
                    u.name AS user_name, u.email AS user_email,
                    i.name AS issued_by_name
             FROM borrowed_books bb
             JOIN books b ON bb.book_id = b.book_id
             JOIN users u ON bb.user_id = u.id
             LEFT JOIN users i ON bb.issued_by = i.id
             WHERE bb.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getAll(string $status = ''): array {
        $sql = "SELECT bb.*, b.title, b.author, b.image,
                       u.name AS user_name, u.email AS user_email,
                       i.name AS issued_by_name
                FROM borrowed_books bb
                JOIN books b ON bb.book_id = b.book_id
                JOIN users u ON bb.user_id = u.id
                LEFT JOIN users i ON bb.issued_by = i.id";
        $params = [];
        if ($status) {
            $sql .= " WHERE bb.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY bb.borrow_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByUser(int $userId, string $status = ''): array {
        $sql = "SELECT bb.*, b.title, b.author, b.image, b.isbn
                FROM borrowed_books bb
                JOIN books b ON bb.book_id = b.book_id
                WHERE bb.user_id = :uid";
        $params = [':uid' => $userId];
        if ($status) {
            $sql .= " AND bb.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY bb.borrow_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getOverdueReport(): array {
        $stmt = $this->db->query(
            "SELECT bb.*, b.title, b.author, u.name AS user_name, u.email AS user_email,
                    u.phone AS user_phone,
                    DATEDIFF(CURDATE(), bb.due_date) AS days_overdue
             FROM borrowed_books bb
             JOIN books b ON bb.book_id = b.book_id
             JOIN users u ON bb.user_id = u.id
             WHERE bb.status = 'overdue'
             ORDER BY bb.due_date ASC"
        );
        return $stmt->fetchAll();
    }

    public function getBorrowingHistory(int $limit = 100): array {
        $stmt = $this->db->prepare(
            "SELECT bb.*, b.title, b.author, u.name AS user_name, u.email AS user_email,
                    c.category_name
             FROM borrowed_books bb
             JOIN books b ON bb.book_id = b.book_id
             JOIN users u ON bb.user_id = u.id
             LEFT JOIN categories c ON b.category_id = c.category_id
             ORDER BY bb.borrow_date DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalPenalties(): float {
        $val = $this->db->query("SELECT SUM(penalty) FROM borrowed_books WHERE status IN ('overdue','returned')")->fetchColumn();
        return (float)($val ?? 0);
    }

    public function getMonthlySummary(): array {
        $stmt = $this->db->query(
            "SELECT DATE_FORMAT(borrow_date, '%Y-%m') AS month,
                    COUNT(*) AS total_borrowed,
                    SUM(CASE WHEN status='returned' THEN 1 ELSE 0 END) AS total_returned,
                    SUM(penalty) AS total_penalties
             FROM borrowed_books
             GROUP BY month
             ORDER BY month DESC
             LIMIT 12"
        );
        return $stmt->fetchAll();
    }
}
