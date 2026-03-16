<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/Book.php';
require_once __DIR__ . '/../classes/BookRequest.php';
require_once __DIR__ . '/../classes/BorrowedBook.php';

requireLogin();
if ($_SESSION['role'] !== 'borrower') {
    header('Location: ' . BASE_URL . '/admin/adashboard.php'); exit;
}

$bookModel     = new Book();
$requestModel  = new BookRequest();
$borrowedModel = new BorrowedBook();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request') {
    $bookId = (int)($_POST['book_id'] ?? 0);
    $result = $requestModel->create($bookId, $_SESSION['user_id']);
    if ($result === true) {
        setFlash('success', 'Borrow request submitted! You will be notified once approved.');
    } else {
        setFlash('danger', is_string($result) ? $result : 'Could not submit request.');
    }
    header('Location: ' . BASE_URL . '/user/udashboard.php'); exit;
}

$filters = [
    'keyword'      => trim($_GET['q'] ?? ''),
    'category'     => (int)($_GET['cat'] ?? 0),
    'availability' => $_GET['avail'] ?? '',
];
$books      = $bookModel->search($filters);
$categories = $bookModel->getCategories();

$myRequests = $requestModel->getByUser($_SESSION['user_id']);
$activeBookIds = [];
foreach ($myRequests as $r) {
    if (in_array($r['status'], ['pending', 'approved'])) {
        $activeBookIds[] = $r['book_id'];
    }
}
$myBorrowed = $borrowedModel->getByUser($_SESSION['user_id']);
foreach ($myBorrowed as $b) {
    if (in_array($b['status'], ['borrowed', 'overdue'])) {
        $activeBookIds[] = $b['book_id'];
    }
}

$pageTitle = 'Browse Books';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label" style="font-size:.8rem;font-weight:600">Search</label>
                <input type="text" name="q" class="form-control"
                       placeholder="Title, author, or ISBN..."
                       value="<?= sanitize($filters['keyword']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:.8rem;font-weight:600">Category</label>
                <select name="cat" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['category_id'] ?>" <?= $filters['category'] == $c['category_id'] ? 'selected' : '' ?>>
                            <?= sanitize($c['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" style="font-size:.8rem;font-weight:600">Availability</label>
                <select name="avail" class="form-select">
                    <option value="">All</option>
                    <option value="available"   <?= $filters['availability']==='available'   ? 'selected' : '' ?>>Available</option>
                    <option value="unavailable" <?= $filters['availability']==='unavailable' ? 'selected' : '' ?>>Unavailable</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-navy flex-fill">
                    <i class="bi bi-search"></i> Search
                </button>
                <a href="?" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Results Count -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="mb-0 text-muted" style="font-size:.875rem">
        <?= count($books) ?> book<?= count($books) !== 1 ? 's' : '' ?> found
        <?= $filters['keyword'] ? '— <em>searching for "' . sanitize($filters['keyword']) . '"</em>' : '' ?>
    </p>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" onclick="setView('grid')" id="btnGrid">
            <i class="bi bi-grid"></i>
        </button>
        <button class="btn btn-sm btn-outline-secondary" onclick="setView('list')" id="btnList">
            <i class="bi bi-list-ul"></i>
        </button>
    </div>
</div>

<!-- Book Grid -->
<div class="row g-3" id="bookGrid">
    <?php foreach ($books as $b): ?>
    <?php $alreadyRequested = in_array($b['book_id'], $activeBookIds); ?>
    <div class="col-6 col-md-4 col-lg-3 book-item">
        <div class="card h-100" style="cursor:pointer" onclick="showBookDetail(<?= htmlspecialchars(json_encode($b), ENT_QUOTES) ?>)">
            <!-- Cover -->
            <div style="height:180px;overflow:hidden;border-radius:12px 12px 0 0;background:linear-gradient(135deg,#243751,#0d1b2a);display:flex;align-items:center;justify-content:center;">
                <?php if ($b['image']): ?>
                    <img src="<?= BASE_URL ?>/uploads/<?= sanitize($b['image']) ?>"
                         style="width:100%;height:100%;object-fit:cover">
                <?php else: ?>
                    <i class="bi bi-book" style="font-size:3.5rem;color:var(--gold);opacity:.6"></i>
                <?php endif; ?>
            </div>
            <div class="card-body p-3 d-flex flex-column">
                <div class="mb-1">
                    <?= $b['available_quantity'] > 0
                        ? '<span class="badge bg-success" style="font-size:.65rem">Available</span>'
                        : '<span class="badge bg-secondary" style="font-size:.65rem">Unavailable</span>' ?>
                    <?php if ($b['category_name']): ?>
                        <span class="badge" style="font-size:.62rem;background:rgba(13,27,42,.1);color:#555"><?= sanitize($b['category_name']) ?></span>
                    <?php endif; ?>
                </div>
                <h6 class="mb-1" style="font-size:.88rem;font-weight:600;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                    <?= sanitize($b['title']) ?>
                </h6>
                <p class="text-muted mb-2" style="font-size:.78rem"><?= sanitize($b['author']) ?></p>
                <div class="mt-auto">
                    <?php if ($alreadyRequested): ?>
                        <button class="btn btn-sm w-100" style="background:#f0f0f0;color:#999;cursor:not-allowed" disabled>
                            <i class="bi bi-clock me-1"></i> Requested
                        </button>
                    <?php elseif ($b['available_quantity'] > 0): ?>
                        <form method="POST" onclick="event.stopPropagation()">
                            <input type="hidden" name="action" value="request">
                            <input type="hidden" name="book_id" value="<?= $b['book_id'] ?>">
                            <button type="submit" class="btn btn-gold btn-sm w-100">
                                <i class="bi bi-send me-1"></i> Request Borrow
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-sm w-100 btn-outline-secondary" disabled>
                            <i class="bi bi-x-circle me-1"></i> Unavailable
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($books)): ?>
    <div class="col-12 text-center py-5 text-muted">
        <i class="bi bi-search fs-1 d-block mb-3 opacity-25"></i>
        <h5>No books found</h5>
        <p>Try adjusting your search or filters.</p>
        <a href="?" class="btn btn-outline-secondary">Clear Filters</a>
    </div>
    <?php endif; ?>
</div>

<!-- Book Detail Modal -->
<div class="modal fade" id="bookDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--navy);color:#fff">
                <h5 class="modal-title" id="detailTitle">Book Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="detailAction"></div>
            </div>
        </div>
    </div>
</div>

<?php
$activeJson = json_encode($activeBookIds);
$baseUrl    = BASE_URL;
$extraJs    = <<<JS
<script>
const activeBookIds = $activeJson;

function showBookDetail(book) {
    document.getElementById('detailTitle').textContent = book.title;
    const cover = book.image
        ? `<img src="{$baseUrl}/uploads/\${book.image}" style="width:120px;height:160px;object-fit:cover;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.2)">`
        : `<div style="width:120px;height:160px;background:linear-gradient(135deg,#243751,#0d1b2a);border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-book" style="font-size:2.5rem;color:var(--gold)"></i></div>`;
    document.getElementById('detailBody').innerHTML = `
        <div class="d-flex gap-4">
            <div class="flex-shrink-0">\${cover}</div>
            <div>
                <h5 class="mb-1">\${book.title}</h5>
                <p class="text-muted mb-2"><i class="bi bi-person"></i> \${book.author}</p>
                \${book.publication ? `<p class="text-muted mb-1" style="font-size:.85rem"><i class="bi bi-building"></i> \${book.publication}</p>` : ''}
                \${book.isbn ? `<p class="text-muted mb-1" style="font-size:.85rem"><i class="bi bi-upc"></i> ISBN: \${book.isbn}</p>` : ''}
                \${book.year_published ? `<p class="text-muted mb-1" style="font-size:.85rem"><i class="bi bi-calendar"></i> \${book.year_published}</p>` : ''}
                <p class="mb-2"><span class="badge bg-secondary">\${book.category_name || 'Uncategorized'}</span>
                   \${book.available_quantity > 0 ? '<span class="badge bg-success ms-1">Available</span>' : '<span class="badge bg-secondary ms-1">Unavailable</span>'}
                </p>
                <p style="font-size:.82rem;color:#666">\${book.available_quantity} of \${book.quantity} copies available</p>
                \${book.description ? `<p style="font-size:.88rem;line-height:1.6;color:#555">\${book.description}</p>` : ''}
            </div>
        </div>`;

    const actionDiv = document.getElementById('detailAction');
    const already   = activeBookIds.includes(parseInt(book.book_id));
    if (already) {
        actionDiv.innerHTML = `<button class="btn btn-secondary" disabled><i class="bi bi-clock me-1"></i> Already Requested</button>`;
    } else if (book.available_quantity > 0) {
        actionDiv.innerHTML = `
            <form method="POST">
                <input type="hidden" name="action" value="request">
                <input type="hidden" name="book_id" value="\${book.book_id}">
                <button type="submit" class="btn btn-gold"><i class="bi bi-send me-1"></i> Request to Borrow</button>
            </form>`;
    } else {
        actionDiv.innerHTML = `<button class="btn btn-outline-secondary" disabled>Unavailable</button>`;
    }

    new bootstrap.Modal(document.getElementById('bookDetailModal')).show();
}

function setView(v) {
    const grid = document.getElementById('bookGrid');
    document.querySelectorAll('.book-item').forEach(el => {
        if (v === 'list') { el.className = 'col-12 book-item'; }
        else              { el.className = 'col-6 col-md-4 col-lg-3 book-item'; }
    });
}
</script>
JS;
require_once __DIR__ . '/../includes/footer.php';
