<?php


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/User.php';

class BookRequest {
    private PDO $db;
    private User $userModel;

    public function __construct() {
        $this->db        = Database::getConnection();
        $this->userModel = new User();
    }

    public function create(int $bookId, int $userId): bool|string {
        $stmt = $this->db->prepare(
            "SELECT id FROM book_requests
             WHERE book_id = :bid AND user_id = :uid AND status IN ('pending','approved')
             LIMIT 1"
        );
        $stmt->execute([':bid' => $bookId, ':uid' => $userId]);
        if ($stmt->fetch()) {
            return 'You already have an active request for this book.';
        }

        $stmt = $this->db->prepare(
            "SELECT id FROM borrowed_books
             WHERE book_id = :bid AND user_id = :uid AND status IN ('borrowed','overdue')
             LIMIT 1"
        );
        $stmt->execute([':bid' => $bookId, ':uid' => $userId]);
        if ($stmt->fetch()) {
            return 'You currently have this book borrowed.';
        }

        $stmt = $this->db->prepare(
            "INSERT INTO book_requests (book_id, user_id, request_date, status)
             VALUES (:bid, :uid, NOW(), 'pending')"
        );
        $ok = $stmt->execute([':bid' => $bookId, ':uid' => $userId]);

        if ($ok) {
            $admins = $this->db->query("SELECT id FROM users WHERE role IN ('admin') AND status='active'")->fetchAll();
            $userRow = $this->userModel->getById($userId);
            foreach ($admins as $admin) {
                $this->userModel->addNotification(
                    $admin['id'],
                    'New Borrow Request',
                    "{$userRow['name']} has requested to borrow a book. Please review.",
                    'info'
                );
            }
        }
        return $ok;
    }

    public function approve(int $requestId, int $adminId, string $note = ''): bool|string {
        $request = $this->getById($requestId);
        if (!$request || $request['status'] !== 'pending') {
            return 'Invalid or already processed request.';
        }

        $stmt = $this->db->prepare("SELECT available_quantity FROM books WHERE book_id = :bid LIMIT 1");
        $stmt->execute([':bid' => $request['book_id']]);
        $book = $stmt->fetch();
        if (!$book || $book['available_quantity'] < 1) {
            return 'Book is not available.';
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE book_requests SET status='approved', admin_note=:note,
                 processed_by=:admin, processed_at=NOW() WHERE id=:id"
            );
            $stmt->execute([':note' => $note, ':admin' => $adminId, ':id' => $requestId]);

            $borrowDate = date('Y-m-d');
            $dueDate    = date('Y-m-d', strtotime('+14 days'));
            $stmt = $this->db->prepare(
                "INSERT INTO borrowed_books
                 (book_id, user_id, request_id, borrow_date, due_date, penalty, status, issued_by)
                 VALUES (:bid, :uid, :rid, :bd, :dd, 0, 'borrowed', :admin)"
            );
            $stmt->execute([
                ':bid'   => $request['book_id'],
                ':uid'   => $request['user_id'],
                ':rid'   => $requestId,
                ':bd'    => $borrowDate,
                ':dd'    => $dueDate,
                ':admin' => $adminId,
            ]);

            $this->db->prepare(
                "UPDATE books SET available_quantity = available_quantity - 1
                 WHERE book_id = :bid AND available_quantity > 0"
            )->execute([':bid' => $request['book_id']]);

            $this->userModel->addNotification(
                $request['user_id'],
                'Borrow Request Approved',
                "Your request for \"{$request['title']}\" has been approved. Due date: $dueDate.",
                'success'
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return 'Error: ' . $e->getMessage();
        }
    }

    public function reject(int $requestId, int $adminId, string $note = ''): bool {
        $request = $this->getById($requestId);
        if (!$request) return false;

        $stmt = $this->db->prepare(
            "UPDATE book_requests SET status='rejected', admin_note=:note,
             processed_by=:admin, processed_at=NOW() WHERE id=:id"
        );
        $ok = $stmt->execute([':note' => $note, ':admin' => $adminId, ':id' => $requestId]);

        if ($ok) {
            $this->userModel->addNotification(
                $request['user_id'],
                'Borrow Request Rejected',
                "Your request for \"{$request['title']}\" was rejected. " . ($note ? "Reason: $note" : ''),
                'danger'
            );
        }
        return $ok;
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT br.*, b.title, b.author, b.image, u.name AS user_name, u.email AS user_email,
                    a.name AS admin_name
             FROM book_requests br
             JOIN books b ON br.book_id = b.book_id
             JOIN users u ON br.user_id = u.id
             LEFT JOIN users a ON br.processed_by = a.id
             WHERE br.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getAll(string $status = ''): array {
        $sql = "SELECT br.*, b.title, b.author, b.image, u.name AS user_name, u.email AS user_email,
                       a.name AS admin_name
                FROM book_requests br
                JOIN books b ON br.book_id = b.book_id
                JOIN users u ON br.user_id = u.id
                LEFT JOIN users a ON br.processed_by = a.id";
        $params = [];
        if ($status) {
            $sql .= " WHERE br.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY br.request_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT br.*, b.title, b.author, b.image, a.name AS admin_name
             FROM book_requests br
             JOIN books b ON br.book_id = b.book_id
             LEFT JOIN users a ON br.processed_by = a.id
             WHERE br.user_id = :uid
             ORDER BY br.request_date DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }
}
