<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/BookRequest.php';

requireLogin();

$requestModel = new BookRequest();
$requests     = $requestModel->getByUser($_SESSION['user_id']);
$pageTitle    = 'My Requests';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>My Borrow Requests (<?= count($requests) ?>)</span>
        <a href="<?= BASE_URL ?>/user/udashboard.php" class="btn btn-sm btn-navy">
            <i class="bi bi-plus-circle me-1"></i> Browse Books
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($requests)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                <p>You haven't made any borrow requests yet.</p>
                <a href="<?= BASE_URL ?>/user/udashboard.php" class="btn btn-navy btn-sm">Browse Books</a>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>Book</th>
                    <th>Requested On</th>
                    <th>Status</th>
                    <th>Note from Admin</th>
                    <th>Processed</th>
                </tr></thead>
                <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($r['image']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= sanitize($r['image']) ?>" class="book-cover">
                                <?php else: ?>
                                    <div class="book-cover-placeholder"><i class="bi bi-book"></i></div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-500" style="font-size:.88rem"><?= sanitize($r['title']) ?></div>
                                    <small class="text-muted"><?= sanitize($r['author']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.83rem"><?= formatDate($r['request_date']) ?></td>
                        <td><?= badgeStatus($r['status']) ?></td>
                        <td style="font-size:.82rem;color:#666">
                            <?= $r['admin_note'] ? sanitize($r['admin_note']) : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td style="font-size:.82rem">
                            <?= $r['admin_name']
                                ? sanitize($r['admin_name']) . '<br><small class="text-muted">' . formatDate($r['processed_at']) . '</small>'
                                : '<span class="text-muted">Pending review</span>' ?>
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
