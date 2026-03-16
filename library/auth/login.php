<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/User.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . ($_SESSION['role'] === 'borrower' ? '/user/udashboard.php' : '/admin/adashboard.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $userModel = new User();
        $user = $userModel->login($email, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];
            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            header('Location: ' . BASE_URL . ($user['role'] === 'borrower' ? '/user/udashboard.php' : '/admin/adashboard.php'));
            exit;
        } else {
            $error = 'Invalid email or password.';
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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,500&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --navy:#0d1b2a; --gold:#c9a84c; --cream:#faf7f0; }
        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0d1b2a 0%, #1b2e45 50%, #0d1b2a 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .auth-wrapper {
            width: 100%; max-width: 460px;
        }
        .brand-box {
            text-align: center; margin-bottom: 32px;
        }
        .brand-box h1 {
            font-family: 'Playfair Display', serif;
            color: var(--gold); font-size: 2.5rem; margin: 0;
        }
        .brand-box p { color: rgba(255,255,255,0.45); font-size: 0.8rem; letter-spacing: 2px; text-transform: uppercase; margin: 0; }
        .auth-card {
            background: #fff; border-radius: 20px;
            padding: 40px; box-shadow: 0 32px 80px rgba(0,0,0,0.5);
        }
        .auth-card h2 { font-family: 'Playfair Display', serif; color: var(--navy); font-size: 1.6rem; }
        .form-control {
            border: 2px solid #e8eaf0; border-radius: 10px;
            padding: 12px 16px; font-size: 0.9rem;
        }
        .form-control:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.15); }
        .form-label { font-weight: 500; font-size: 0.85rem; color: #555; }
        .btn-login {
            background: linear-gradient(135deg, var(--navy), #243751);
            color: #fff; border: none; border-radius: 10px;
            padding: 13px; font-size: 0.95rem; font-weight: 600;
            width: 100%; letter-spacing: 0.3px;
        }
        .btn-login:hover { background: linear-gradient(135deg, #1b2e45, #2f4a6a); color: #fff; }
        .divider { color: #aaa; font-size: 0.8rem; text-align: center; margin: 16px 0; }
        .register-link { text-align: center; font-size: 0.875rem; }
        .register-link a { color: var(--gold); font-weight: 600; }
        .demo-creds {
            background: rgba(201,168,76,0.1); border: 1px solid rgba(201,168,76,0.3);
            border-radius: 10px; padding: 12px 16px; margin-top: 20px;
            font-size: 0.78rem;
        }
        .demo-creds strong { color: var(--navy); }
        .demo-creds code { background: rgba(13,27,42,0.08); padding: 1px 5px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="brand-box">
        <h1>LibraryPro Services</h1>
    </div>
    <div class="auth-card">
        <h2 class="mb-1">Welcome Back</h2>
        <p class="text-muted mb-4" style="font-size:.875rem">Sign in to your account to continue.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2" style="font-size:.85rem"><i class="bi bi-exclamation-circle"></i> <?= sanitize($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control"
                       value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>"
                       placeholder="" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="pwdField" class="form-control" placeholder="" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePwd()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-login">Sign In</button>
        </form>

        <div class="divider">— or —</div>
        <div class="register-link">
            Don't have an account? <a href="<?= BASE_URL ?>/auth/register.php">Create one</a>
        </div>
    
    </div>
</div>
<script>
function togglePwd() {
    const f = document.getElementById('pwdField');
    const i = document.getElementById('eyeIcon');
    if (f.type === 'password') { f.type = 'text'; i.className = 'bi bi-eye-slash'; }
    else { f.type = 'password'; i.className = 'bi bi-eye'; }
}
</script>
</body>
</html>
