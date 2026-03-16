<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!in_array($_SESSION['role'], ['admin', 'librarian'])) {
        header('Location: ' . BASE_URL . '/user/udashboard.php');
        exit;
    }
}

function requireBorrower(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'borrower') {
        header('Location: ' . BASE_URL . '/admin/adashboard.php');
        exit;
    }
}

function currentUser(): array {
    return [
        'id'    => $_SESSION['user_id'] ?? 0,
        'name'  => $_SESSION['name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role'  => $_SESSION['role'] ?? '',
    ];
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function flashHtml(): string {
    $flash = getFlash();
    if (!$flash) return '';
    $icons = ['success' => '✓', 'danger' => '✕', 'warning' => '⚠', 'info' => 'ℹ'];
    $icon  = $icons[$flash['type']] ?? 'ℹ';
    return "<div class=\"alert alert-{$flash['type']} alert-dismissible fade show\" role=\"alert\">
                <strong>$icon</strong> {$flash['msg']}
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
            </div>";
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatDate(string $date): string {
    return date('M d, Y', strtotime($date));
}

function formatCurrency(float $amount): string {
    return '₱' . number_format($amount, 2);
}

function badgeStatus(string $status): string {
    $map = [
        'pending'     => 'warning',
        'approved'    => 'success',
        'rejected'    => 'danger',
        'borrowed'    => 'primary',
        'returned'    => 'secondary',
        'overdue'     => 'danger',
        'available'   => 'success',
        'unavailable' => 'secondary',
        'active'      => 'success',
        'inactive'    => 'secondary',
        'suspended'   => 'danger',
    ];
    $color = $map[$status] ?? 'secondary';
    return "<span class=\"badge bg-{$color}\">" . ucfirst($status) . "</span>";
}
