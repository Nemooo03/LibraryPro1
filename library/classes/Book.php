<?php


require_once __DIR__ . '/../config/database.php';

class Book {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function search(array $filters = []): array {
        $sql = "SELECT b.*, c.category_name,
                    CASE
                        WHEN b.available_quantity > 0 THEN 'available'
                        ELSE 'unavailable'
                    END AS availability
                FROM books b
                LEFT JOIN categories c ON b.category_id = c.category_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (b.title LIKE :kw OR b.author LIKE :kw OR b.isbn LIKE :kw)";
            $params[':kw'] = '%' . $filters['keyword'] . '%';
        }
        if (!empty($filters['category'])) {
            $sql .= " AND b.category_id = :cat";
            $params[':cat'] = $filters['category'];
        }
        if (!empty($filters['availability'])) {
            if ($filters['availability'] === 'available') {
                $sql .= " AND b.available_quantity > 0";
            } elseif ($filters['availability'] === 'unavailable') {
                $sql .= " AND b.available_quantity = 0";
            }
        }

        $sql .= " ORDER BY b.title ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT b.*, c.category_name FROM books b
             LEFT JOIN categories c ON b.category_id = c.category_id
             ORDER BY b.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT b.*, c.category_name FROM books b
             LEFT JOIN categories c ON b.category_id = c.category_id
             WHERE b.book_id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }


    public function add(array $data): int|false {
        $stmt = $this->db->prepare(
            "INSERT INTO books (title, author, publication, category_id, isbn, description,
                               quantity, available_quantity, image, year_published)
             VALUES (:title, :author, :pub, :cat, :isbn, :desc, :qty, :avail, :img, :year)"
        );
        $ok = $stmt->execute([
            ':title'  => $data['title'],
            ':author' => $data['author'],
            ':pub'    => $data['publication'] ?? null,
            ':cat'    => $data['category_id'] ?? null,
            ':isbn'   => $data['isbn'] ?? null,
            ':desc'   => $data['description'] ?? null,
            ':qty'    => $data['quantity'],
            ':avail'  => $data['quantity'],
            ':img'    => $data['image'] ?? null,
            ':year'   => $data['year_published'] ?? null,
        ]);
        return $ok ? (int) $this->db->lastInsertId() : false;
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        $allowed = ['title', 'author', 'publication', 'category_id', 'isbn',
                    'description', 'quantity', 'available_quantity', 'image', 'year_published'];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) return false;
        $sql = "UPDATE books SET " . implode(', ', $fields) . " WHERE book_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM books WHERE book_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function decrementAvailable(int $bookId): bool {
        $stmt = $this->db->prepare(
            "UPDATE books SET available_quantity = available_quantity - 1
             WHERE book_id = :id AND available_quantity > 0"
        );
        return $stmt->execute([':id' => $bookId]);
    }

    public function incrementAvailable(int $bookId): bool {
        $stmt = $this->db->prepare(
            "UPDATE books SET available_quantity = LEAST(available_quantity + 1, quantity)
             WHERE book_id = :id"
        );
        return $stmt->execute([':id' => $bookId]);
    }


    public function setAvailability(int $id, string $status): bool {
        if ($status === 'unavailable') {
            $stmt = $this->db->prepare(
                "UPDATE books SET available_quantity = 0 WHERE book_id = :id"
            );
            return $stmt->execute([':id' => $id]);
        }

        $stmt = $this->db->prepare(
            "UPDATE books
             SET available_quantity = GREATEST(
                 0,
                 quantity - (
                     SELECT COUNT(*) FROM borrowed_books
                     WHERE book_id = :id2 AND status IN ('borrowed', 'overdue')
                 )
             )
             WHERE book_id = :id"
        );
        return $stmt->execute([':id' => $id, ':id2' => $id]);
    }


    public function getCategories(): array {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY category_name");
        return $stmt->fetchAll();
    }

    public function addCategory(string $name, string $description = ''): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO categories (category_name, description) VALUES (:name, :desc)"
        );
        return $stmt->execute([':name' => $name, ':desc' => $description]);
    }

    public function getDashboardStats(): array {
        $stats = [];
        $stats['total_books']     = $this->db->query("SELECT SUM(quantity) FROM books")->fetchColumn();
        $stats['available_books'] = $this->db->query("SELECT SUM(available_quantity) FROM books")->fetchColumn();
        $stats['total_titles']    = $this->db->query("SELECT COUNT(*) FROM books WHERE available_quantity > 0")->fetchColumn();
        $stats['overdue_count']   = $this->db->query("SELECT COUNT(*) FROM borrowed_books WHERE status = 'overdue'")->fetchColumn();
        $stats['borrowed_count']  = $this->db->query("SELECT COUNT(*) FROM borrowed_books WHERE status = 'borrowed'")->fetchColumn();
        $stats['total_users']     = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'borrower'")->fetchColumn();
        $stats['pending_requests']= $this->db->query("SELECT COUNT(*) FROM book_requests WHERE status = 'pending'")->fetchColumn();
        return $stats;
    }

    public function getInventoryReport(): array {
        $stmt = $this->db->query(
            "SELECT b.*, c.category_name,
                (b.quantity - b.available_quantity) AS borrowed_count
             FROM books b
             LEFT JOIN categories c ON b.category_id = c.category_id
             ORDER BY c.category_name, b.title"
        );
        return $stmt->fetchAll();
    }
}