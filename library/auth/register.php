<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/User.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/user/udashboard.php');
    exit;
}

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'     => trim($_POST['name'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'phone'    => trim($_POST['phone'] ?? ''),
        'role'     => 'borrower',
    ];

    if (!$data['name'] || !$data['email'] || !$data['password']) {
        $error = 'Name, email, and password are required.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($data['password']) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $userModel = new User();
        $result = $userModel->register($data);
        if ($result === true) {
            $success = 'Account created successfully! You can now sign in.';
        } else {
            $error = is_string($result) ? $result : 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibraryPro Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --navy:#0d1b2a; --gold:#c9a84c; }
        body { font-family:'DM Sans',sans-serif; min-height:100vh; background:linear-gradient(135deg,#0d1b2a,#1b2e45,#0d1b2a); display:flex; align-items:center; justify-content:center; padding:20px; }
        .auth-card { background:#fff; border-radius:20px; padding:40px; width:100%; max-width:480px; box-shadow:0 32px 80px rgba(0,0,0,.5); }
        .brand { font-family:'Playfair Display',serif; color:var(--gold); font-size:1.8rem; text-align:center; margin-bottom:4px; }
        .form-control { border:2px solid #e8eaf0; border-radius:10px; padding:11px 14px; }
        .form-control:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(201,168,76,.15); }
        .btn-reg { background:linear-gradient(135deg,var(--navy),#243751); color:#fff; border:none; border-radius:10px; padding:13px; width:100%; font-weight:600; }
        .btn-reg:hover { background:linear-gradient(135deg,#1b2e45,#2f4a6a); color:#fff; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="brand">LibraryPro Services</div>
    <p class="text-center text-muted mb-4" style="font-size:.8rem">Create a borrower account</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2" style="font-size:.85rem"><?= sanitize($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success py-2" style="font-size:.85rem"><?= sanitize($success) ?>
            <a href="<?= BASE_URL ?>/auth/login.php">Sign in →</a>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label class="form-label fw-500" style="font-size:.85rem">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= sanitize($_POST['name'] ?? '') ?>" placeholder="" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-500" style="font-size:.85rem">Email Address</label>
            <input type="email" name="email" class="form-control" value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-500" style="font-size:.85rem">Phone (optional)</label>
            <input type="text" name="phone" class="form-control" value="<?= sanitize($_POST['phone'] ?? '') ?>" placeholder="">
        </div>
        <div class="mb-4">
            <label class="form-label fw-500" style="font-size:.85rem">Password</label>
            <input type="password" name="password" class="form-control" placeholder="" required>
        </div>
        <button type="submit" class="btn btn-reg">Create Account</button>
    </form>
    <p class="text-center mt-3" style="font-size:.85rem">Already have an account? <a href="<?= BASE_URL ?>/auth/login.php" style="color:var(--gold);font-weight:600">Sign in</a></p>
</div>
</body>
</html>
