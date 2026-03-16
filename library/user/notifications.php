<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/User.php';

requireLogin();

$userModel = new User();
$userModel->markNotificationsRead($_SESSION['user_id']);
$notifs    = $userModel->getNotifications($_SESSION['user_id']);
$pageTitle = 'Notifications';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:680px">
    <div class="card-header"><i class="bi bi-bell me-1"></i> Notifications</div>
    <div class="card-body p-0">
        <?php if (empty($notifs)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                No notifications yet.
            </div>
        <?php else: ?>
            <?php foreach ($notifs as $n): ?>
                <?php
                $icons = [
                    'success' => 'check-circle-fill text-success',
                    'danger'  => 'x-circle-fill text-danger',
                    'warning' => 'exclamation-triangle-fill text-warning',
                    'info'    => 'info-circle-fill text-primary',
                ];
                $icon = $icons[$n['type']] ?? 'bell-fill text-secondary';
                ?>
                <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom">
                    <i class="bi bi-<?= $icon ?> fs-5 mt-1 flex-shrink-0"></i>
                    <div>
                        <div class="fw-600" style="font-size:.9rem"><?= sanitize($n['title']) ?></div>
                        <div style="font-size:.85rem;color:#555"><?= sanitize($n['message']) ?></div>
                        <small class="text-muted"><?= formatDate($n['created_at']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
