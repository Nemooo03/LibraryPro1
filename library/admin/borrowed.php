<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/BorrowedBook.php';

requireAdmin();

$borrowedModel = new BorrowedBook();
$borrowedModel->syncOverdueStatuses();
$statusFilter = $_GET['status'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'return') {
    $bid    = (int)($_POST['borrow_id'] ?? 0);
    $result = $borrowedModel->returnBook($bid, $_SESSION['user_id']);
    if ($result) {
        $msg = 'Book returned successfully!';
        if ($result['penalty'] > 0) $msg .= ' Penalty collected: ' . formatCurrency($result['penalty']);
        setFlash('success', $msg);
    } else {
        setFlash('danger', 'Failed to process return.');
    }
    header('Location: ' . BASE_URL . '/admin/borrowed.php');
    exit;
}

$records   = $borrowedModel->getAll($statusFilter);
$pageTitle = 'Borrowed Books';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4 d-flex gap-2 flex-wrap">
    <a href="?" class="btn <?= !$statusFilter ? 'btn-navy' : 'btn-outline-secondary' ?> btn-sm">All</a>
    <a href="?status=borrowed" class="btn <?= $statusFilter==='borrowed' ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">Borrowed</a>
    <a href="?status=overdue" class="btn <?= $statusFilter==='overdue' ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm">Overdue</a>
    <a href="?status=returned" class="btn <?= $statusFilter==='returned' ? 'btn-secondary' : 'btn-outline-secondary' ?> btn-sm">Returned</a>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <input type="text" id="tableSearch" class="form-control" placeholder="🔍  Search borrowed books...">
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><?= count($records) ?> Record<?= count($records) !== 1 ? 's' : '' ?></span>
        <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-download me-1"></i>Export Report
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="mainTable">
                <thead><tr>
                    <th>Book</th>
                    <th>Borrower</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Penalty</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr></thead>
                <tbody>
                <?php foreach ($records as $r): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($r['image']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= sanitize($r['image']) ?>" class="book-cover">
                                <?php else: ?>
                                    <div class="book-cover-placeholder"><i class="bi bi-book"></i></div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-500" style="font-size:.87rem"><?= sanitize($r['title']) ?></div>
                                    <small class="text-muted"><?= sanitize($r['author']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:.87rem;font-weight:500"><?= sanitize($r['user_name']) ?></div>
                            <small class="text-muted"><?= sanitize($r['user_email']) ?></small>
                        </td>
                        <td style="font-size:.83rem"><?= formatDate($r['borrow_date']) ?></td>
                        <td style="font-size:.83rem">
                            <?= formatDate($r['due_date']) ?>
                            <?php if ($r['status'] !== 'returned' && strtotime($r['due_date']) < time()): ?>
                                <br><small class="text-danger">
                                    <?= abs((int)((time() - strtotime($r['due_date'])) / 86400)) ?> days late
                                </small>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:.83rem">
                            <?= $r['return_date'] ? formatDate($r['return_date']) : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td style="font-weight:600;color:<?= $r['penalty'] > 0 ? '#c94455' : '#666' ?>">
                            <?= $r['penalty'] > 0 ? formatCurrency($r['penalty']) : '—' ?>
                        </td>
                        <td><?= badgeStatus($r['status']) ?></td>
                        <td>
                            <?php if ($r['status'] !== 'returned'): ?>
                                <form method="POST" class="d-inline"
                                      onsubmit="return confirm('Mark this book as returned?')">
                                    <input type="hidden" name="action" value="return">
                                    <input type="hidden" name="borrow_id" value="<?= $r['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-arrow-return-left"></i> Return
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:.8rem">Completed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
