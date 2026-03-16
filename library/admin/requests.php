<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/BookRequest.php';

requireAdmin();

$requestModel = new BookRequest();
$statusFilter = $_GET['status'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid    = (int)($_POST['request_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $note   = trim($_POST['admin_note'] ?? '');
    $userId = $_SESSION['user_id'];

    if ($action === 'approve') {
        $result = $requestModel->approve($rid, $userId, $note);
        setFlash(is_string($result) ? 'danger' : 'success', is_string($result) ? $result : 'Request approved and borrow record created.');
    } elseif ($action === 'reject') {
        $requestModel->reject($rid, $userId, $note);
        setFlash('warning', 'Request rejected.');
    }
    header('Location: ' . BASE_URL . '/admin/requests.php');
    exit;
}

$requests  = $requestModel->getAll($statusFilter);
$pageTitle = 'Borrow Requests';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Filter Tabs -->
<div class="mb-4 d-flex gap-2 flex-wrap">
    <a href="?" class="btn <?= !$statusFilter ? 'btn-navy' : 'btn-outline-secondary' ?> btn-sm">All</a>
    <a href="?status=pending" class="btn <?= $statusFilter==='pending' ? 'btn-warning' : 'btn-outline-warning' ?> btn-sm">Pending</a>
    <a href="?status=approved" class="btn <?= $statusFilter==='approved' ? 'btn-success' : 'btn-outline-success' ?> btn-sm">Approved</a>
    <a href="?status=rejected" class="btn <?= $statusFilter==='rejected' ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm">Rejected</a>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <input type="text" id="tableSearch" class="form-control" placeholder="🔍  Search requests...">
    </div>
</div>

<div class="card">
    <div class="card-header">
        <?= count($requests) ?> Request<?= count($requests) !== 1 ? 's' : '' ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="mainTable">
                <thead><tr>
                    <th>#</th>
                    <th>Book</th>
                    <th>Requested By</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Processed By</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td style="font-size:.82rem;color:#999">#<?= $r['id'] ?></td>
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
                        <td>
                            <div style="font-size:.88rem;font-weight:500"><?= sanitize($r['user_name']) ?></div>
                            <small class="text-muted"><?= sanitize($r['user_email']) ?></small>
                        </td>
                        <td style="font-size:.83rem"><?= formatDate($r['request_date']) ?></td>
                        <td><?= badgeStatus($r['status']) ?></td>
                        <td style="font-size:.83rem"><?= $r['admin_name'] ? sanitize($r['admin_name']) : '<span class="text-muted">—</span>' ?></td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                                <button class="btn btn-sm btn-success me-1"
                                        data-bs-toggle="modal" data-bs-target="#actionModal"
                                        data-id="<?= $r['id'] ?>" data-action="approve"
                                        data-book="<?= sanitize($r['title']) ?>"
                                        onclick="prepareAction(this)">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal" data-bs-target="#actionModal"
                                        data-id="<?= $r['id'] ?>" data-action="reject"
                                        data-book="<?= sanitize($r['title']) ?>"
                                        onclick="prepareAction(this)">
                                    <i class="bi bi-x-lg"></i> Reject
                                </button>
                            <?php else: ?>
                                <?php if ($r['admin_note']): ?>
                                    <span class="text-muted" style="font-size:.8rem" title="<?= sanitize($r['admin_note']) ?>">
                                        <i class="bi bi-chat-quote"></i> Note
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Action Modal -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionTitle">Approve Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="actionRequestId">
                    <input type="hidden" name="action" id="actionType">
                    <p id="actionDesc" class="mb-3"></p>
                    <label class="form-label">Note to borrower (optional)</label>
                    <textarea name="admin_note" class="form-control" rows="2" placeholder="Add a note..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="actionBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraJs = "<script>
function prepareAction(btn) {
    const action = btn.dataset.action;
    const book   = btn.dataset.book;
    document.getElementById('actionRequestId').value = btn.dataset.id;
    document.getElementById('actionType').value      = action;
    if (action === 'approve') {
        document.getElementById('actionTitle').textContent = 'Approve Request';
        document.getElementById('actionDesc').textContent  = 'Approve borrow request for: ' + book + '. A 14-day loan period will be created.';
        document.getElementById('actionBtn').className = 'btn btn-success';
        document.getElementById('actionBtn').textContent = 'Approve';
    } else {
        document.getElementById('actionTitle').textContent = 'Reject Request';
        document.getElementById('actionDesc').textContent  = 'Reject borrow request for: ' + book + '?';
        document.getElementById('actionBtn').className = 'btn btn-danger';
        document.getElementById('actionBtn').textContent = 'Reject';
    }
}
</script>";
require_once __DIR__ . '/../includes/footer.php';
