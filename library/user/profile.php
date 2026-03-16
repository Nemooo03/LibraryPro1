<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/BorrowedBook.php';

requireLogin();

$userModel     = new User();
$borrowedModel = new BorrowedBook();
$userId        = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';

    if ($action === 'update_profile') {
        $data = [
            'name'    => trim($_POST['name'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
            'phone'   => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
        ];
        if (!$data['name'] || !$data['email']) {
            setFlash('danger', 'Name and email are required.');
        } elseif ($userModel->update($userId, $data)) {
            $_SESSION['name']  = $data['name'];
            $_SESSION['email'] = $data['email'];
            setFlash('success', 'Profile updated successfully.');
        } else {
            setFlash('danger', 'Update failed. Email may already be in use.');
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = $userModel->getById($userId);
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $row  = $stmt->fetch();

        if (!password_verify($current, $row['password'])) {
            setFlash('danger', 'Current password is incorrect.');
        } elseif (strlen($new) < 6) {
            setFlash('danger', 'New password must be at least 6 characters.');
        } elseif ($new !== $confirm) {
            setFlash('danger', 'New passwords do not match.');
        } elseif ($userModel->update($userId, ['password' => $new])) {
            setFlash('success', 'Password changed successfully.');
        } else {
            setFlash('danger', 'Password change failed.');
        }
    }

    header('Location: ' . BASE_URL . '/user/profile.php'); exit;
}

$user    = $userModel->getById($userId);
$history = $borrowedModel->getByUser($userId);

$totalBorrowed  = count($history);
$totalReturned  = count(array_filter($history, fn($r) => $r['status'] === 'returned'));
$totalOverdue   = count(array_filter($history, fn($r) => $r['status'] === 'overdue'));
$totalPenalties = array_sum(array_column($history, 'penalty'));

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-4">
                <div style="width:80px;height:80px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:2rem;font-weight:700;color:var(--gold);">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h5 class="mb-1"><?= sanitize($user['name']) ?></h5>
                <p class="text-muted mb-1" style="font-size:.85rem"><?= sanitize($user['email']) ?></p>
                <span class="badge" style="background:var(--navy);color:var(--gold)">
                    <?= ucfirst($user['role']) ?>
                </span>
                <hr>
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div style="font-size:1.4rem;font-weight:700;color:var(--navy)"><?= $totalBorrowed ?></div>
                        <div style="font-size:.7rem;color:#888">Borrowed</div>
                    </div>
                    <div class="col-4">
                        <div style="font-size:1.4rem;font-weight:700;color:#2aa8a8"><?= $totalReturned ?></div>
                        <div style="font-size:.7rem;color:#888">Returned</div>
                    </div>
                    <div class="col-4">
                        <div style="font-size:1.4rem;font-weight:700;color:#c94455"><?= formatCurrency($totalPenalties) ?></div>
                        <div style="font-size:.7rem;color:#888">Penalties</div>
                    </div>
                </div>
                <div class="mt-2" style="font-size:.78rem;color:#888">
                    Member since <?= formatDate($user['created_at']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Forms -->
    <div class="col-md-8">
        <!-- Update Profile -->
        <div class="card mb-3">
            <div class="card-header fw-600">
                <i class="bi bi-person me-1"></i> Personal Information
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="form_action" value="update_profile">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600">Full Name</label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= sanitize($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600">Email Address</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= sanitize($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="<?= sanitize($user['phone'] ?? '') ?>" placeholder="09XXXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600">Address</label>
                            <input type="text" name="address" class="form-control"
                                   value="<?= sanitize($user['address'] ?? '') ?>" placeholder="Optional">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-navy btn-sm px-4">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-header fw-600">
                <i class="bi bi-lock me-1"></i> Change Password
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="form_action" value="change_password">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" style="font-size:.85rem;font-weight:600">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600">New Password</label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Min 6 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:.85rem;font-weight:600">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat new password">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-outline-danger btn-sm px-4">
                            <i class="bi bi-shield-lock me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Full Borrow History -->
<div class="card mt-4">
    <div class="card-header fw-600">
        <i class="bi bi-clock-history me-1"></i> Complete Borrowing History
    </div>
    <div class="card-body p-0">
        <?php if (empty($history)): ?>
            <div class="text-center py-4 text-muted" style="font-size:.875rem">No borrowing history yet.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.84rem">
                <thead><tr>
                    <th>Book</th><th>Borrowed</th><th>Due Date</th><th>Returned</th><th>Status</th><th>Penalty</th>
                </tr></thead>
                <tbody>
                <?php foreach ($history as $b): ?>
                    <tr>
                        <td>
                            <div class="fw-500"><?= sanitize($b['title']) ?></div>
                            <small class="text-muted"><?= sanitize($b['author']) ?></small>
                        </td>
                        <td><?= formatDate($b['borrow_date']) ?></td>
                        <td><?= formatDate($b['due_date']) ?></td>
                        <td><?= $b['return_date'] ? formatDate($b['return_date']) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= badgeStatus($b['status']) ?></td>
                        <td style="color:<?= $b['penalty'] > 0 ? '#c94455' : '#888' ?>;font-weight:<?= $b['penalty'] > 0 ? '600' : '400' ?>">
                            <?= $b['penalty'] > 0 ? formatCurrency($b['penalty']) : 'None' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
