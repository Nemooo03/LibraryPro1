<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/Book.php';

requireAdmin();

$bookModel = new Book();
$db        = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'add') {
    $name = trim($_POST['category_name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if (!$name) {
        setFlash('danger', 'Category name is required.');
    } elseif ($bookModel->addCategory($name, $desc)) {
        setFlash('success', "Category \"$name\" added.");
    } else {
        setFlash('danger', 'Failed to add category (may already exist).');
    }
    header('Location: ' . BASE_URL . '/admin/categories.php'); exit;
}

// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'edit') {
    $id   = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['category_name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $stmt = $db->prepare("UPDATE categories SET category_name=:n, description=:d WHERE category_id=:id");
    if ($stmt->execute([':n' => $name, ':d' => $desc, ':id' => $id])) {
        setFlash('success', 'Category updated.');
    } else {
        setFlash('danger', 'Update failed.');
    }
    header('Location: ' . BASE_URL . '/admin/categories.php'); exit;
}

// Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $db->prepare("DELETE FROM categories WHERE category_id = :id");
    $stmt->execute([':id' => $id]);
    setFlash('success', 'Category deleted.');
    header('Location: ' . BASE_URL . '/admin/categories.php'); exit;
}

$categories = $bookModel->getCategories();

// Attach book counts
$counts = $db->query(
    "SELECT category_id, COUNT(*) AS cnt FROM books GROUP BY category_id"
)->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Categories';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0" style="font-size:.875rem"><?= count($categories) ?> categories</p>
    <button class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#catModal">
        <i class="bi bi-plus-circle me-1"></i> Add Category
    </button>
</div>

<div class="row g-3">
    <?php foreach ($categories as $c): ?>
    <div class="col-md-4 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1 fw-600"><?= sanitize($c['category_name']) ?></h6>
                        <p class="text-muted mb-2" style="font-size:.82rem"><?= sanitize($c['description'] ?? '') ?: '<em>No description</em>' ?></p>
                        <span class="badge bg-secondary"><?= $counts[$c['category_id']] ?? 0 ?> book<?= ($counts[$c['category_id']] ?? 0) !== 1 ? 's' : '' ?></span>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#catModal"
                                data-id="<?= $c['category_id'] ?>"
                                data-name="<?= sanitize($c['category_name']) ?>"
                                data-desc="<?= sanitize($c['description'] ?? '') ?>"
                                onclick="loadCatEdit(this)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="?action=delete&id=<?= $c['category_id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete this category?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Category Modal -->
<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--navy);color:#fff">
                <h5 class="modal-title" id="catModalTitle">Add Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="form_action" id="catFormAction" value="add">
                    <input type="hidden" name="category_id" id="catId">
                    <div class="mb-3">
                        <label class="form-label fw-500">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" id="catName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Description</label>
                        <textarea name="description" id="catDesc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-navy">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraJs = "<script>
function loadCatEdit(btn) {
    document.getElementById('catModalTitle').textContent = 'Edit Category';
    document.getElementById('catFormAction').value = 'edit';
    document.getElementById('catId').value   = btn.dataset.id;
    document.getElementById('catName').value = btn.dataset.name;
    document.getElementById('catDesc').value = btn.dataset.desc;
}
document.getElementById('catModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('catModalTitle').textContent = 'Add Category';
    document.getElementById('catFormAction').value = 'add';
    document.querySelector('#catModal form').reset();
});
</script>";
require_once __DIR__ . '/../includes/footer.php';
