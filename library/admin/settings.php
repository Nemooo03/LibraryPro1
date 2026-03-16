<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$db = Database::getConnection();


$db->exec("CREATE TABLE IF NOT EXISTS system_settings (
    setting_key   VARCHAR(100) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL,
    label         VARCHAR(200),
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB");


$db->exec("INSERT IGNORE INTO system_settings (setting_key, setting_value, label) VALUES
    ('loan_period_days',  '14',  'Default Loan Period (days)'),
    ('penalty_per_day',   '5',   'Penalty per Overdue Day (₱)'),
    ('max_borrows',       '3',   'Max Books per Borrower at Once'),
    ('penalty_grace',     '0',   'Grace Period After Due Date (days, 0 = none)')
");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['loan_period_days', 'penalty_per_day', 'max_borrows', 'penalty_grace'];
    $stmt   = $db->prepare("UPDATE system_settings SET setting_value=:val WHERE setting_key=:key");
    foreach ($fields as $key) {
        $val = (float)($_POST[$key] ?? 0);
        if ($val < 0) $val = 0;
        $stmt->execute([':val' => $val, ':key' => $key]);
    }
    setFlash('success', 'Settings saved successfully.');
    header('Location: ' . BASE_URL . '/admin/settings.php'); exit;
}

$settingsRaw = $db->query("SELECT * FROM system_settings")->fetchAll();
$settings    = [];
foreach ($settingsRaw as $s) $settings[$s['setting_key']] = $s;

$pageTitle = 'Borrow Settings';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-sliders me-1"></i> Deadline & Penalty Configuration
            </div>
            <div class="card-body">
                <form method="POST">
                    <!-- Loan Period -->
                    <div class="mb-4">
                        <label class="form-label fw-600">
                            Default Loan Period
                            <span class="text-muted fw-normal" style="font-size:.8rem"> — how many days a borrower keeps a book</span>
                        </label>
                        <div class="input-group" style="max-width:220px">
                            <input type="number" name="loan_period_days" class="form-control"
                                   value="<?= (int)($settings['loan_period_days']['setting_value'] ?? 14) ?>"
                                   min="1" max="365" required>
                            <span class="input-group-text">days</span>
                        </div>
                        <div class="form-text">Due date = borrow date + this many days.</div>
                    </div>

                    <!-- Grace Period -->
                    <div class="mb-4">
                        <label class="form-label fw-600">
                            Grace Period
                            <span class="text-muted fw-normal" style="font-size:.8rem"> — extra days after due date before penalty starts</span>
                        </label>
                        <div class="input-group" style="max-width:220px">
                            <input type="number" name="penalty_grace" class="form-control"
                                   value="<?= (int)($settings['penalty_grace']['setting_value'] ?? 0) ?>"
                                   min="0" max="30" required>
                            <span class="input-group-text">days</span>
                        </div>
                        <div class="form-text">Set to 0 for no grace period — penalty starts the day after the due date.</div>
                    </div>

                    <!-- Penalty Per Day -->
                    <div class="mb-4">
                        <label class="form-label fw-600">
                            Penalty per Overdue Day
                            <span class="text-muted fw-normal" style="font-size:.8rem"> — fee charged per day past the due date</span>
                        </label>
                        <div class="input-group" style="max-width:220px">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="penalty_per_day" class="form-control"
                                   value="<?= number_format((float)($settings['penalty_per_day']['setting_value'] ?? 5), 2, '.', '') ?>"
                                   min="0" step="0.50" required>
                            <span class="input-group-text">/ day</span>
                        </div>
                        <div class="form-text">
                            Penalty only applies when a book is <strong>overdue</strong>. No fee is charged while the book is within its loan period.
                        </div>
                    </div>

                    <!-- Max Borrows -->
                    <div class="mb-4">
                        <label class="form-label fw-600">
                            Maximum Books per Borrower
                            <span class="text-muted fw-normal" style="font-size:.8rem"> — concurrent active borrows allowed</span>
                        </label>
                        <div class="input-group" style="max-width:220px">
                            <input type="number" name="max_borrows" class="form-control"
                                   value="<?= (int)($settings['max_borrows']['setting_value'] ?? 3) ?>"
                                   min="1" max="20" required>
                            <span class="input-group-text">books</span>
                        </div>
                    </div>

                    <hr class="my-4">
                    <button type="submit" class="btn btn-navy px-4">
                        <i class="bi bi-save me-1"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>