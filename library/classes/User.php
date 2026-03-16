<?php

require_once __DIR__ . '/../config/database.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function login(string $email, string $password): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return false;
    }

    public function register(array $data): bool|string {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $data['email']]);
        if ($stmt->fetch()) {
            return 'Email already exists.';
        }

        $hashed = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role, phone, address)
             VALUES (:name, :email, :password, :role, :phone, :address)"
        );
        return $stmt->execute([
            ':name'     => $data['name'],
            ':email'    => $data['email'],
            ':password' => $hashed,
            ':role'     => $data['role'] ?? 'borrower',
            ':phone'    => $data['phone'] ?? null,
            ':address'  => $data['address'] ?? null,
        ]);
    }

    public function getAll(string $role = '', string $status = ''): array {
        $sql = "SELECT id, name, email, role, phone, status, created_at FROM users WHERE 1=1";
        $params = [];

        if ($role) {
            $sql .= " AND role = :role";
            $params[':role'] = $role;
        }
        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, role, phone, address, status, avatar, created_at FROM users WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        $allowed = ['name', 'email', 'role', 'phone', 'address', 'status', 'avatar'];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (!empty($data['password'])) {
            $fields[] = "password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (empty($fields)) return false;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function countByRole(): array {
        $stmt = $this->db->query(
            "SELECT role, COUNT(*) as total FROM users GROUP BY role"
        );
        return $stmt->fetchAll();
    }

    public function getNotifications(int $userId, bool $unreadOnly = false): array {
        $sql = "SELECT * FROM notifications WHERE user_id = :uid";
        if ($unreadOnly) $sql .= " AND is_read = 0";
        $sql .= " ORDER BY created_at DESC LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function markNotificationsRead(int $userId): bool {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :uid"
        );
        return $stmt->execute([':uid' => $userId]);
    }

    public function addNotification(int $userId, string $title, string $message, string $type = 'info'): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, title, message, type) VALUES (:uid, :title, :msg, :type)"
        );
        return $stmt->execute([
            ':uid'   => $userId,
            ':title' => $title,
            ':msg'   => $message,
            ':type'  => $type,
        ]);
    }

    public function countUnreadNotifications(int $userId): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0"
        );
        $stmt->execute([':uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }
}
