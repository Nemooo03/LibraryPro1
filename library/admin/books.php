<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/Book.php';

requireAdmin();

$bookModel  = new Book();
$categories = $bookModel->getCategories();
$action     = $_GET['action'] ?? 'list';
$editBook   = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    if (($_POST['form_action'] ?? '') === 'toggle_availability') {
        $id      = (int)($_POST['book_id'] ?? 0);
        $desired = ($_POST['set_status'] ?? '') === 'available' ? 'available' : 'unavailable';
        if ($bookModel->setAvailability($id, $desired)) {
            setFlash('success', 'Book availability updated.');
        } else {
            setFlash('danger', 'Failed to update availability.');
        }
        header('Location: ' . BASE_URL . '/admin/books.php');
        exit;
    }
    

    $data = [
        'title'          => trim($_POST['title'] ?? ''),
        'author'         => trim($_POST['author'] ?? ''),
        'publication'    => trim($_POST['publication'] ?? ''),
        'category_id'    => (int)($_POST['category_id'] ?? 0),
        'isbn'           => trim($_POST['isbn'] ?? ''),
        'description'    => trim($_POST['description'] ?? ''),
        'quantity'       => (int)($_POST['quantity'] ?? 1),
        'year_published' => (int)($_POST['year_published'] ?? 0),
        'image'          => null,
    ];

    if (!empty($_FILES['image']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'book_' . time() . '_' . uniqid() . '.' . $ext;
            $destDir  = __DIR__ . '/../uploads/';
            if (!is_dir($destDir)) mkdir($destDir, 0755, true);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destDir . $filename)) {
                $data['image'] = $filename;
            }
        }
    }

    if ($_POST['form_action'] === 'add') {
        if (!$data['title'] || !$data['author']) {
            setFlash('danger', 'Title and author are required.');
        } else {
            $id = $bookModel->add($data);
            if ($id) {
                setFlash('success', 'Book added successfully!');
            } else {
                setFlash('danger', 'Failed to add book.');
            }
        }
    } elseif ($_POST['form_action'] === 'edit') {
        $id = (int)($_POST['book_id'] ?? 0);

        if (!$data['image']) {
            unset($data['image']);
        }
        if ($bookModel->update($id, $data)) {
            setFlash('success', 'Book updated successfully!');
        } else {
            setFlash('danger', 'Failed to update book.');
        }
    }

    header('Location: ' . BASE_URL . '/admin/books.php');
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($bookModel->delete($id)) {
        setFlash('success', 'Book deleted.');
    } else {
        setFlash('danger', 'Could not delete book (it may have active borrowings).');
    }
    header('Location: ' . BASE_URL . '/admin/books.php');
    exit;
}

if ($action === 'edit' && isset($_GET['id'])) {
    $editBook = $bookModel->getById((int)$_GET['id']);
}

$books     = $bookModel->getAll();
$pageTitle = 'Book Catalog';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Header Row -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0" style="font-size:.875rem"><?= count($books) ?> titles in catalog</p>
    </div>
    <button class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#bookModal">
        <i class="bi bi-plus-circle me-1"></i> Add Book
    </button>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body py-3">
        <input type="text" id="tableSearch" class="form-control" placeholder="🔍  Search by title, author, category, ISBN...">
    </div>
</div>

<!-- Books Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="mainTable">
                <thead><tr>
                    <th data-sort></th>
                    <th data-sort>Title / Author</th>
                    <th data-sort>Category</th>
                    <th data-sort>ISBN</th>
                    <th data-sort>Qty</th>
                    <th data-sort>Available</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($books as $b): ?>
                    <tr>
                        <td>
                            <?php if ($b['image']): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= sanitize($b['image']) ?>" class="book-cover">
                            <?php else: ?>
                                <div class="book-cover-placeholder"><i class="bi bi-book"></i></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-600" style="font-size:.9rem"><?= sanitize($b['title']) ?></div>
                            <small class="text-muted"><?= sanitize($b['author']) ?></small>
                            <?php if ($b['year_published']): ?>
                                <small class="text-muted"> · <?= $b['year_published'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?= sanitize($b['category_name'] ?? 'Uncategorized') ?></span></td>
                        <td style="font-size:.82rem;color:#666"><?= sanitize($b['isbn'] ?? '—') ?></td>
                        <td><?= $b['quantity'] ?></td>
                        <td><?= $b['available_quantity'] ?></td>
                        <td>
                            <?php $isAvail = $b['available_quantity'] > 0; ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="form_action" value="toggle_availability">
                                <input type="hidden" name="book_id"    value="<?= $b['book_id'] ?>">
                                <input type="hidden" name="set_status" value="<?= $isAvail ? 'unavailable' : 'available' ?>">
                                <button type="submit"
                                        class="btn btn-sm <?= $isAvail ? 'btn-success' : 'btn-secondary' ?>"
                                        style="min-width:110px"
                                        title="Click to mark as <?= $isAvail ? 'Unavailable' : 'Available' ?>"
                                        onclick="return confirm('Mark this book as <?= $isAvail ? 'Unavailable' : 'Available' ?>?')">
                                    <i class="bi <?= $isAvail ? 'bi-check-circle' : 'bi-x-circle' ?> me-1"></i>
                                    <?= $isAvail ? 'Available' : 'Unavailable' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <a href="?action=edit&id=<?= $b['book_id'] ?>" class="btn btn-sm btn-outline-primary me-1"
                               data-bs-toggle="modal" data-bs-target="#bookModal"
                               data-id="<?= $b['book_id'] ?>"
                               data-title="<?= sanitize($b['title']) ?>"
                               data-author="<?= sanitize($b['author']) ?>"
                               data-pub="<?= sanitize($b['publication'] ?? '') ?>"
                               data-cat="<?= $b['category_id'] ?>"
                               data-isbn="<?= sanitize($b['isbn'] ?? '') ?>"
                               data-desc="<?= sanitize($b['description'] ?? '') ?>"
                               data-qty="<?= $b['quantity'] ?>"
                               data-avail="<?= $b['available_quantity'] ?>"
                               data-year="<?= $b['year_published'] ?>"
                               onclick="loadEdit(this)">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="?action=delete&id=<?= $b['book_id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this book?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Book Modal (Add/Edit) -->
<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--navy);color:#fff">
                <h5 class="modal-title" id="modalTitle">Add New Book</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="form_action" id="formAction" value="add">
                    <input type="hidden" name="book_id" id="bookId" value="">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-500">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="fTitle" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Year Published</label>
                            <input type="number" name="year_published" id="fYear" class="form-control" min="1000" max="2099">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Author <span class="text-danger">*</span></label>
                            <input type="text" name="author" id="fAuthor" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Publication / Publisher</label>
                            <input type="text" name="publication" id="fPub" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">Category</label>
                            <select name="category_id" id="fCat" class="form-select">
                                <option value="">— Select Category —</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['category_id'] ?>"><?= sanitize($c['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-500">ISBN</label>
                            <input type="text" name="isbn" id="fIsbn" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-500">Total Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="fQty" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-500">Cover Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-500">Description</label>
                            <textarea name="description" id="fDesc" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-navy">Save Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraJs = "<script>
function loadEdit(btn) {
    document.getElementById('modalTitle').textContent  = 'Edit Book';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('bookId').value     = btn.dataset.id;
    document.getElementById('fTitle').value     = btn.dataset.title;
    document.getElementById('fAuthor').value    = btn.dataset.author;
    document.getElementById('fPub').value       = btn.dataset.pub;
    document.getElementById('fIsbn').value      = btn.dataset.isbn;
    document.getElementById('fDesc').value      = btn.dataset.desc;
    document.getElementById('fQty').value       = btn.dataset.qty;
    document.getElementById('fYear').value      = btn.dataset.year;
    const catSel = document.getElementById('fCat');
    catSel.value = btn.dataset.cat;
}
document.getElementById('bookModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('modalTitle').textContent  = 'Add New Book';
    document.getElementById('formAction').value = 'add';
    document.getElementById('bookId').value     = '';
    document.querySelector('#bookModal form').reset();
});
</script>";
require_once __DIR__ . '/../includes/footer.php';