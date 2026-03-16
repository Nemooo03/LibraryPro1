<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/Book.php';
require_once __DIR__ . '/../classes/BorrowedBook.php';

requireAdmin();

$bookModel     = new Book();
$borrowedModel = new BorrowedBook();


$borrowedModel->syncOverdueStatuses();

$stats      = $bookModel->getDashboardStats();
$overdue    = $borrowedModel->getOverdueReport();
$monthly    = $borrowedModel->getMonthlySummary();
$recentBorr = $borrowedModel->getBorrowingHistory(8);

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card stat-navy">
            <div class="stat-val"><?= number_format($stats['total_titles']) ?></div>
            <div class="stat-lbl">Total Books</div>
            <i class="bi bi-book stat-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-gold">
            <div class="stat-val"><?= number_format($stats['borrowed_count']) ?></div>
            <div class="stat-lbl">Currently Borrowed</div>
            <i class="bi bi-book-half stat-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-rose">
            <div class="stat-val"><?= number_format($stats['overdue_count']) ?></div>
            <div class="stat-lbl">Overdue Books</div>
            <i class="bi bi-exclamation-triangle stat-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card stat-teal">
            <div class="stat-val"><?= number_format($stats['total_users']) ?></div>
            <div class="stat-lbl">Borrowers</div>
            <i class="bi bi-people stat-icon"></i>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Quick Actions -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-lightning-fill text-warning"></i> Quick Actions
            </div>
            <div class="card-body d-grid gap-2">
                <a href="<?= BASE_URL ?>/admin/books.php?action=add" class="btn btn-navy">
                    <i class="bi bi-plus-circle me-1"></i> Add New Book
                </a>
                <a href="<?= BASE_URL ?>/admin/requests.php?status=pending" class="btn btn-gold">
                    <i class="bi bi-inbox me-1"></i>
                    Pending Requests
                    <?php if ($stats['pending_requests'] > 0): ?>
                        <span class="badge bg-danger ms-1"><?= $stats['pending_requests'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/admin/borrowed.php?status=overdue" class="btn btn-outline-danger">
                    <i class="bi bi-clock-history me-1"></i> View Overdue
                </a>
                <a href="<?= BASE_URL ?>/admin/reports.php" class="btn btn-outline-secondary">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Monthly Chart -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header">Monthly Borrowing Activity</div>
            <div class="card-body">
                <canvas id="monthlyChart" height="160"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity + Overdue -->
<div class="row g-4">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Recent Borrowing Activity</span>
                <a href="<?= BASE_URL ?>/admin/borrowed.php" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr>
                            <th>Book</th><th>Borrower</th><th>Due Date</th><th>Status</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($recentBorr as $b): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:500;font-size:.85rem"><?= sanitize($b['title']) ?></div>
                                    <small class="text-muted"><?= sanitize($b['author']) ?></small>
                                </td>
                                <td style="font-size:.85rem"><?= sanitize($b['user_name']) ?></td>
                                <td style="font-size:.85rem"><?= formatDate($b['due_date']) ?></td>
                                <td><?= badgeStatus($b['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Summary -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span style="color:#c94455"><i class="bi bi-exclamation-triangle-fill me-1"></i>Overdue Books</span>
                <a href="<?= BASE_URL ?>/admin/borrowed.php?status=overdue" class="btn btn-sm btn-outline-danger">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($overdue)): ?>
                    <div class="text-center py-4 text-muted" style="font-size:.875rem">
                        <i class="bi bi-check-circle fs-3 text-success d-block mb-2"></i>
                        No overdue books!
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:.82rem">
                        <thead><tr><th>Book</th><th>Days Late</th><th>Penalty</th></tr></thead>
                        <tbody>
                        <?php foreach (array_slice($overdue, 0, 6) as $o): ?>
                            <tr>
                                <td>
                                    <div class="fw-500"><?= sanitize(substr($o['title'],0,25)) ?>...</div>
                                    <small class="text-muted"><?= sanitize($o['user_name']) ?></small>
                                </td>
                                <td><span class="badge bg-danger"><?= $o['days_overdue'] ?> days</span></td>
                                <td style="color:#c94455;font-weight:600"><?= formatCurrency($o['penalty']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php

$chartLabels  = [];
$chartBorrow  = [];
$chartReturn  = [];
foreach (array_reverse($monthly) as $m) {
    $chartLabels[]  = date('M Y', strtotime($m['month'] . '-01'));
    $chartBorrow[]  = $m['total_borrowed'];
    $chartReturn[]  = $m['total_returned'];
}

$extraJs = "<script>
const ctx = document.getElementById('monthlyChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: " . json_encode($chartLabels) . ",
        datasets: [
            { label:'Borrowed', data:" . json_encode($chartBorrow) . ", backgroundColor:'rgba(13,27,42,0.8)', borderRadius:4 },
            { label:'Returned', data:" . json_encode($chartReturn) . ", backgroundColor:'rgba(201,168,76,0.8)', borderRadius:4 }
        ]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{ position:'top' } },
        scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } }
    }
});
</script>";

require_once __DIR__ . '/../includes/footer.php';
