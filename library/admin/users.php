<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/User.php';

requireAdmin();

$userModel = new User();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';
    $data = [
        'name'     => trim($_POST['name'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'role'     => $_POST['role'] ?? 'borrower',
        'phone'    => trim($_POST['phone'] ?? ''),
        'address'  => trim($_POST['address'] ?? ''),
        'status'   => $_POST['status'] ?? 'active',
    ];
    if (!empty($_POST['password'])) {
        $data['password'] = $_POST['password'];
    }

    if ($action === 'add') {
        if (!$data['name'] || !$data['email'] || empty($_POST['password'])) {
            setFlash('danger', 'Name, email and password are required.');
        } else {
            $data['password'] = $_POST['password'];
            $result = $userModel->register($data);
            if ($result === true) {
                setFlash('success', 'User created successfully!');
            } else {
                setFlash('danger', is_string($result) ? $result : 'Failed to create user.');
            }
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['user_id'] ?? 0);
        if ($userModel->update($id, $data)) {
            setFlash('success', 'User updated.');
        } else {
            setFlash('danger', 'Update failed.');
        }
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id === $_SESSION['user_id']) {
        setFlash('danger', 'You cannot delete your own account.');
    } elseif ($userModel->delete($id)) {
        setFlash('success', 'User deleted.');
    } else {
        setFlash('danger', 'Could not delete user.');
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$users     = $userModel->getAll();
$pageTitle = 'User Management';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0" style="font-size:.875rem"><?= count($users) ?> users registered</p>
    <button class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#userModal">
        <i class="bi bi-person-plus me-1"></i> Add User
    </button>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <input type="text" id="tableSearch" class="form-control" placeholder="🔍  Search users...">
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="mainTable">
                <thead><tr>
                    <th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Status</th><th>Joined</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:34px;height:34px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;color:var(--gold);font-weight:700;font-size:.9rem;">
                                    <?= strtoupper(substr($u['name'],0,1)) ?>
                                </div>
                                <span style="font-weight:500;font-size:.88rem"><?= sanitize($u['name']) ?></span>
                            </div>
                        </td>
                        <td style="font-size:.85rem"><?= sanitize($u['email']) ?></td>
                        <td><?= badgeStatus($u['role']) ?></td>
                        <td style="font-size:.83rem"><?= sanitize($u['phone'] ?? '—') ?></td>
                        <td><?= badgeStatus($u['status']) ?></td>
                        <td style="font-size:.83rem"><?= formatDate($u['created_at']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1"
                                    data-bs-toggle="modal" data-bs-target="#userModal"
                                    data-id="<?= $u['id'] ?>"
                                    data-name="<?= sanitize($u['name']) ?>"
                                    data-email="<?= sanitize($u['email']) ?>"
                                    data-role="<?= $u['role'] ?>"
                                    data-phone="<?= sanitize($u['phone'] ?? '') ?>"
                                    data-status="<?= $u['status'] ?>"
                                    onclick="loadEditUser(this)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                            <a href="?action=delete&id=<?= $u['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this user?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--navy);color:#fff">
                <h5 class="modal-title" id="userModalTitle">Add User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="form_action" id="userFormAction" value="add">
                    <input type="hidden" name="user_id" id="uId">
                    <div class="mb-3">
                        <label class="form-label fw-500">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="uName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="uEmail" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-500">Role</label>
                            <select name="role" id="uRole" class="form-select">
                                <option value="borrower">Borrower</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-500">Status</label>
                            <select name="status" id="uStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Phone</label>
                        <input type="text" name="phone" id="uPhone" class="form-control" placeholder="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-500">Password <span id="pwdRequired" class="text-danger">*</span></label>
                        <input type="password" name="password" id="uPwd" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-navy">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraJs = "<script>
function loadEditUser(btn) {
    document.getElementById('userModalTitle').textContent = 'Edit User';
    document.getElementById('userFormAction').value = 'edit';
    document.getElementById('uId').value     = btn.dataset.id;
    document.getElementById('uName').value   = btn.dataset.name;
    document.getElementById('uEmail').value  = btn.dataset.email;
    document.getElementById('uPhone').value  = btn.dataset.phone;
    document.getElementById('uRole').value   = btn.dataset.role;
    document.getElementById('uStatus').value = btn.dataset.status;
    document.getElementById('pwdRequired').textContent = '';
    document.getElementById('uPwd').placeholder = 'Leave blank to keep current';
    document.getElementById('uPwd').required = false;
}
document.getElementById('userModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('userModalTitle').textContent = 'Add User';
    document.getElementById('userFormAction').value = 'add';
    document.getElementById('pwdRequired').textContent = '*';
    document.querySelector('#userModal form').reset();
});
</script>";
require_once __DIR__ . '/../includes/footer.php';
