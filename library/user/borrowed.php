<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/BorrowedBook.php';

requireLogin();

$borrowedModel = new BorrowedBook();
$borrowedModel->syncOverdueStatuses();

$statusFilter = $_GET['status'] ?? '';
$all          = $borrowedModel->getByUser($_SESSION['user_id'], $statusFilter);

$active  = array_filter($all, fn($r) => in_array($r['status'], ['borrowed', 'overdue']));
$history = array_filter($all, fn($r) => $r['status'] === 'returned');

$dueSoon = array_filter($active, function($r) {
    $daysLeft = (int)((strtotime($r['due_date']) - time()) / 86400);
    return $daysLeft >= 0 && $daysLeft <= 3;
});

$pageTitle = 'My Borrowed Books';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if (!empty($dueSoon)): ?>
<div class="alert alert-warning d-flex gap-2 align-items-start mb-4">
    <i class="bi bi-alarm fs-4 flex-shrink-0 mt-1"></i>
    <div>
        <strong>Upcoming Due Dates!</strong>
        <?php foreach ($dueSoon as $ds): ?>
            <div style="font-size:.88rem">
                <em><?= sanitize($ds['title']) ?></em> is due on
                <strong><?= formatDate($ds['due_date']) ?></strong>
                <?php
                    $dl = (int)((strtotime($ds['due_date']) - time()) / 86400);
                    echo $dl === 0 ? '— <span class="text-danger">due today!</span>' : "— {$dl} day" . ($dl !== 1 ? 's' : '') . ' left.';
                ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Active Borrows -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-book-half me-1"></i> Currently Borrowed (<?= count($active) ?>)</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($active)): ?>
            <div class="text-center py-4 text-muted" style="font-size:.875rem">
                <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i>
                No active borrows. <a href="<?= BASE_URL ?>/user/udashboard.php">Browse books</a> to get started.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>Book</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Days Left</th>
                    <th>Status</th>
                    <th>Penalty</th>
                </tr></thead>
                <tbody>
                <?php foreach ($active as $b):
                    $daysLeft = (int)((strtotime($b['due_date']) - time()) / 86400);
                    $daysOverdue = $daysLeft < 0 ? abs($daysLeft) : 0;
                ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($b['image']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= sanitize($b['image']) ?>" class="book-cover">
                                <?php else: ?>
                                    <div class="book-cover-placeholder"><i class="bi bi-book"></i></div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-500" style="font-size:.88rem"><?= sanitize($b['title']) ?></div>
                                    <small class="text-muted"><?= sanitize($b['author']) ?></small>
                                    <?php if ($b['isbn']): ?>
                                        <small class="text-muted d-block">ISBN: <?= sanitize($b['isbn']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.83rem"><?= formatDate($b['borrow_date']) ?></td>
                        <td style="font-size:.83rem"><?= formatDate($b['due_date']) ?></td>
                        <td>
                            <?php if ($daysLeft > 3): ?>
                                <span class="badge bg-success"><?= $daysLeft ?> days</span>
                            <?php elseif ($daysLeft >= 0): ?>
                                <span class="badge bg-warning text-dark">
                                    <?= $daysLeft === 0 ? 'Due today!' : $daysLeft . ' day' . ($daysLeft !== 1 ? 's' : '') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?= $daysOverdue ?> days late</span>
                            <?php endif; ?>
                        </td>
                        <td><?= badgeStatus($b['status']) ?></td>
                        <td>
                            <?php if ($b['status'] === 'overdue' && $b['penalty'] > 0): ?>
                                <span style="color:#c94455;font-weight:600"><?= formatCurrency($b['penalty']) ?></span>
                                <small class="text-muted d-block" style="font-size:.72rem">₱5/day overdue</small>
                            <?php else: ?>
                                <span class="text-success" style="font-size:.82rem"><i class="bi bi-check"></i> No fee</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Borrow History -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-clock-history me-1"></i> Borrowing History (<?= count($history) ?>)
    </div>
    <div class="card-body p-0">
        <?php if (empty($history)): ?>
            <div class="text-center py-4 text-muted" style="font-size:.875rem">No returned books yet.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th>Book</th>
                    <th>Borrowed</th>
                    <th>Due Date</th>
                    <th>Returned On</th>
                    <th>Penalty Paid</th>
                </tr></thead>
                <tbody>
                <?php foreach ($history as $b): ?>
                    <tr>
                        <td>
                            <div class="fw-500" style="font-size:.88rem"><?= sanitize($b['title']) ?></div>
                            <small class="text-muted"><?= sanitize($b['author']) ?></small>
                        </td>
                        <td style="font-size:.83rem"><?= formatDate($b['borrow_date']) ?></td>
                        <td style="font-size:.83rem"><?= formatDate($b['due_date']) ?></td>
                        <td style="font-size:.83rem"><?= $b['return_date'] ? formatDate($b['return_date']) : '—' ?></td>
                        <td>
                            <?php if ($b['penalty'] > 0): ?>
                                <span style="color:#c94455;font-weight:600"><?= formatCurrency($b['penalty']) ?></span>
                            <?php else: ?>
                                <span class="text-success" style="font-size:.82rem">None</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Penalty Info Box -->
<div class="alert alert-light border mt-3" style="font-size:.82rem">
    <i class="bi bi-info-circle-fill text-primary me-1"></i>
    <strong>Penalty Policy:</strong> A fee of <strong>₱5.00 per day</strong> is charged only when a book is returned
    <em>after</em> its due date. No fee applies to on-time returns.
    Please return books promptly to avoid penalties.
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
