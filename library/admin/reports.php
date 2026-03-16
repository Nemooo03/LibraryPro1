<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/Book.php';
require_once __DIR__ . '/../classes/BorrowedBook.php';

requireAdmin();

$bookModel     = new Book();
$borrowedModel = new BorrowedBook();


if (isset($_GET['export'])) {
    $type = $_GET['export'];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');

    if ($type === 'inventory') {
        fputcsv($out, ['Title', 'Author', 'Category', 'ISBN', 'Total Qty', 'Available', 'Borrowed']);
        foreach ($bookModel->getInventoryReport() as $r) {
            fputcsv($out, [$r['title'], $r['author'], $r['category_name'], $r['isbn'], $r['quantity'], $r['available_quantity'], $r['borrowed_count']]);
        }
    } elseif ($type === 'history') {
        fputcsv($out, ['Book Title', 'Author', 'Borrower', 'Email', 'Borrow Date', 'Due Date', 'Return Date', 'Penalty', 'Status']);
        foreach ($borrowedModel->getBorrowingHistory(500) as $r) {
            fputcsv($out, [$r['title'], $r['author'], $r['user_name'], $r['user_email'], $r['borrow_date'], $r['due_date'], $r['return_date'] ?? '', $r['penalty'], $r['status']]);
        }
    } elseif ($type === 'overdue') {
        fputcsv($out, ['Book', 'Author', 'Borrower', 'Email', 'Phone', 'Due Date', 'Days Overdue', 'Penalty']);
        foreach ($borrowedModel->getOverdueReport() as $r) {
            fputcsv($out, [$r['title'], $r['author'], $r['user_name'], $r['user_email'], $r['user_phone'], $r['due_date'], $r['days_overdue'], $r['penalty']]);
        }
    }
    fclose($out);
    exit;
}

$inventory  = $bookModel->getInventoryReport();
$overdue    = $borrowedModel->getOverdueReport();
$stats      = $bookModel->getDashboardStats();
$penalties  = $borrowedModel->getTotalPenalties();

$pageTitle = 'Reports & Analytics';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-navy">
            <div class="stat-val"><?= number_format($stats['total_titles']) ?></div>
            <div class="stat-lbl">Book Titles</div>
            <i class="bi bi-journal-bookmark stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-gold">
            <div class="stat-val"><?= number_format($stats['borrowed_count'] + $stats['overdue_count']) ?></div>
            <div class="stat-lbl">Total Borrowed</div>
            <i class="bi bi-book-half stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-rose">
            <div class="stat-val"><?= number_format($stats['overdue_count']) ?></div>
            <div class="stat-lbl">Overdue</div>
            <i class="bi bi-clock-history stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-teal">
            <div class="stat-val"><?= formatCurrency($penalties) ?></div>
            <div class="stat-lbl">Total Penalties</div>
            <i class="bi bi-currency-dollar stat-icon"></i>
        </div>
    </div>
</div>

<!-- Top Overdue -->
<div class="card mb-4">
    <div class="card-header text-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> Top Overdue
    </div>
    <div class="card-body p-0">
        <?php foreach (array_slice($overdue, 0, 5) as $o): ?>
            <div class="px-3 py-2 border-bottom" style="font-size:.82rem">
                <div class="fw-500"><?= sanitize(substr($o['title'],0,28)) ?>...</div>
                <div class="text-muted"><?= sanitize($o['user_name']) ?> · <span class="text-danger"><?= $o['days_overdue'] ?> days late</span></div>
                <div style="color:#c94455;font-weight:600"><?= formatCurrency($o['penalty']) ?></div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($overdue)): ?>
            <div class="text-center py-4 text-muted">No overdue books!</div>
        <?php endif; ?>
    </div>
</div>

<!-- Inventory Table -->
<div class="card mb-4">
    <div class="card-header">Inventory Report — <?= count($inventory) ?> Titles</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.83rem">
                <thead><tr>
                    <th>Title</th><th>Author</th><th>Category</th><th>Total</th><th>Available</th><th>Borrowed</th><th>Utilization</th>
                </tr></thead>
                <tbody>
                <?php foreach ($inventory as $b): ?>
                    <tr>
                        <td class="fw-500"><?= sanitize($b['title']) ?></td>
                        <td><?= sanitize($b['author']) ?></td>
                        <td><span class="badge bg-secondary"><?= sanitize($b['category_name'] ?? '—') ?></span></td>
                        <td><?= $b['quantity'] ?></td>
                        <td><?= $b['available_quantity'] ?></td>
                        <td><?= $b['borrowed_count'] ?></td>
                        <td>
                            <?php $pct = $b['quantity'] > 0 ? round(($b['borrowed_count'] / $b['quantity']) * 100) : 0; ?>
                            <div class="progress" style="height:6px;width:80px">
                                <div class="progress-bar" style="width:<?= $pct ?>%;background:var(--navy)"></div>
                            </div>
                            <small><?= $pct ?>%</small>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';