<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

$isAdmin    = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'librarian']);
$pageTitle  = $pageTitle ?? 'LibraFlow';
$bodyClass  = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?> - LibraryPro Services</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,500&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* ── Design System ─────────────────────────────────────── */
        :root {
            --navy:      #0d1b2a;
            --navy-mid:  #1b2e45;
            --navy-soft: #243751;
            --gold:      #c9a84c;
            --gold-lite: #f0d596;
            --cream:     #faf7f0;
            --muted:     #8a9ab5;
            --border:    rgba(201,168,76,0.2);
            --radius:    12px;
            --shadow:    0 4px 24px rgba(13,27,42,0.12);
        }

        * { box-sizing: border-box; }
        html, body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--navy);
            min-height: 100vh;
        }

        h1,h2,h3,.brand-text { font-family: 'Playfair Display', serif; }

        /* ── Sidebar ────────────────────────────────────────────── */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: 260px;
            background: var(--navy);
            display: flex; flex-direction: column;
            z-index: 1000;
            border-right: 1px solid rgba(201,168,76,0.15);
        }
        .sidebar-brand {
            padding: 24px 24px 16px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-brand h1 {
            font-size: 1.5rem; color: var(--gold); margin: 0;
            letter-spacing: -0.5px;
        }
        .sidebar-brand p {
            font-size: 0.7rem; color: var(--muted); margin: 0;
            text-transform: uppercase; letter-spacing: 2px;
        }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 16px 0; }
        .nav-section {
            font-size: 0.65rem; color: var(--muted);
            text-transform: uppercase; letter-spacing: 2px;
            padding: 8px 24px 4px;
        }
        .sidebar a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 24px;
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: 0.875rem; font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover { color: #fff; background: rgba(255,255,255,0.05); }
        .sidebar a.active { color: var(--gold); background: rgba(201,168,76,0.1); border-left-color: var(--gold); }
        .sidebar a i { font-size: 1rem; width: 20px; }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
        }
        .sidebar-footer .user-info small { display: block; font-size: 0.7rem; color: var(--muted); }
        .sidebar-footer .user-info strong { color: #fff; font-size: 0.875rem; }
        .role-badge {
            display: inline-block;
            background: var(--gold); color: var(--navy);
            font-size: 0.6rem; font-weight: 700;
            padding: 2px 7px; border-radius: 20px;
            text-transform: uppercase; letter-spacing: 1px;
        }

        /* ── Main Content ───────────────────────────────────────── */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .topbar {
            background: #fff;
            border-bottom: 1px solid rgba(13,27,42,0.08);
            padding: 14px 32px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }
        .topbar-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem; color: var(--navy); margin: 0;
        }
        .page-body { padding: 28px 32px; flex: 1; }

        /* ── Cards ──────────────────────────────────────────────── */
        .card {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            background: #fff;
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(13,27,42,0.08);
            padding: 16px 20px;
            font-weight: 600;
        }
        .stat-card {
            border-radius: var(--radius);
            padding: 24px;
            position: relative;
            overflow: hidden;
            color: #fff;
        }
        .stat-card .stat-val {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem; font-weight: 700; line-height: 1;
        }
        .stat-card .stat-lbl { font-size: 0.8rem; opacity: 0.85; margin-top: 4px; font-weight: 500; }
        .stat-card .stat-icon {
            position: absolute; right: 20px; top: 50%;
            transform: translateY(-50%);
            font-size: 3.5rem; opacity: 0.15;
        }
        .stat-navy { background: linear-gradient(135deg, #0d1b2a, #243751); }
        .stat-gold  { background: linear-gradient(135deg, #b8862a, #c9a84c); }
        .stat-teal  { background: linear-gradient(135deg, #1a6b6b, #2aa8a8); }
        .stat-rose  { background: linear-gradient(135deg, #8b2635, #c94455); }

        /* ── Tables ─────────────────────────────────────────────── */
        .table { font-size: 0.875rem; }
        .table th {
            background: var(--navy);
            color: rgba(255,255,255,0.8);
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }
        .table-hover tbody tr:hover { background: rgba(201,168,76,0.05); }
        .table td { vertical-align: middle; border-color: rgba(13,27,42,0.06); }

        /* ── Buttons ────────────────────────────────────────────── */
        .btn-gold {
            background: var(--gold); color: var(--navy);
            border: none; font-weight: 600;
        }
        .btn-gold:hover { background: #b8932e; color: var(--navy); }
        .btn-navy {
            background: var(--navy); color: #fff; border: none;
        }
        .btn-navy:hover { background: var(--navy-mid); color: #fff; }

        /* ── Misc ───────────────────────────────────────────────── */
        .badge { font-weight: 500; }
        .book-cover {
            width: 44px; height: 60px; object-fit: cover;
            border-radius: 4px; box-shadow: 2px 2px 8px rgba(0,0,0,0.2);
        }
        .book-cover-placeholder {
            width: 44px; height: 60px;
            background: linear-gradient(135deg, #243751, #0d1b2a);
            border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            color: var(--gold); font-size: 1.2rem;
        }
        .notif-dot {
            position: absolute; top: 6px; right: 6px;
            width: 8px; height: 8px; background: #e74c3c;
            border-radius: 50%; border: 2px solid #fff;
        }

        /* ── No-sidebar page ─────────────────────────────────────── */
        .auth-page {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-mid) 60%, #162234 100%);
            display: flex; align-items: center; justify-content: center;
        }
        .auth-card {
            background: #fff; border-radius: 20px;
            padding: 48px; width: 100%; max-width: 440px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.4);
        }

        /* ── Responsive ─────────────────────────────────────────── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: 0.3s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body class="<?= $bodyClass ?>">
<?php if (isLoggedIn()): ?>
<?php
    require_once __DIR__ . '/../classes/User.php';
    $userModel  = new User();
    $unreadNotif = $userModel->countUnreadNotifications($_SESSION['user_id']);
    $currentPage = basename($_SERVER['PHP_SELF']);
    $currentDir  = basename(dirname($_SERVER['PHP_SELF']));

    function navLink(string $href, string $icon, string $label, string $currentPage, string $targetPage): string {
        $active = ($currentPage === $targetPage) ? ' active' : '';
        return "<a href=\"$href\" class=\"$active\"><i class=\"bi bi-$icon\"></i> $label</a>";
    }
?>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h1>LibraryPro Services</h1>
    </div>

    <div class="sidebar-nav">
        <?php if ($isAdmin): ?>
            <div class="nav-section">Dashboard</div>
            <?= navLink(BASE_URL.'/admin/adashboard.php', 'speedometer2', 'Overview', $currentPage, 'index.php') ?>

            <div class="nav-section">Catalog</div>
            <?= navLink(BASE_URL.'/admin/books.php', 'book', 'Books', $currentPage, 'books.php') ?>
            <?= navLink(BASE_URL.'/admin/categories.php', 'tags', 'Categories', $currentPage, 'categories.php') ?>

            <div class="nav-section">Circulation</div>
            <?= navLink(BASE_URL.'/admin/requests.php', 'inbox', 'Borrow Requests', $currentPage, 'requests.php') ?>
            <?= navLink(BASE_URL.'/admin/borrowed.php', 'book-half', 'Borrowed Books', $currentPage, 'borrowed.php') ?>

            <div class="nav-section">Management</div>
            <?= navLink(BASE_URL.'/admin/users.php', 'people', 'Users', $currentPage, 'users.php') ?>
            <?= navLink(BASE_URL.'/admin/reports.php', 'bar-chart', 'Reports', $currentPage, 'reports.php') ?>
            <?= navLink(BASE_URL.'/admin/settings.php', 'sliders', 'Borrow Settings', $currentPage, 'settings.php') ?>

        <?php else: ?>
            <div class="nav-section">Browse</div>
            <?= navLink(BASE_URL.'/user/udashboard.php', 'search', 'Browse Books', $currentPage, 'index.php') ?>

            <div class="nav-section">My Library</div>
            <?= navLink(BASE_URL.'/user/requests.php', 'send', 'My Requests', $currentPage, 'requests.php') ?>
            <?= navLink(BASE_URL.'/user/borrowed.php', 'book-half', 'Borrowed Books', $currentPage, 'borrowed.php') ?>
            <?= navLink(BASE_URL.'/user/profile.php', 'person', 'My Profile', $currentPage, 'profile.php') ?>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--navy);">
                <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
            </div>
            <div class="user-info">
                <strong><?= sanitize($_SESSION['name']) ?></strong>
                <small><?= sanitize($_SESSION['email']) ?></small>
                <div><span class="role-badge"><?= $_SESSION['role'] ?></span></div>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/auth/logout.php" style="padding:8px 12px;background:rgba(255,255,255,0.08);border-radius:8px;width:100%;justify-content:center;">
            <i class="bi bi-box-arrow-right"></i> Sign Out
        </a>
    </div>
</nav>

<!-- Main -->
<div class="main-content">
    <!-- Topbar -->
    <div class="topbar">
        <h2 class="topbar-title"><?= $pageTitle ?></h2>
        <div class="d-flex align-items-center gap-3">
            <!-- Notifications -->
            <div class="position-relative">
                <button class="btn btn-sm btn-light position-relative" onclick="location.href='<?= BASE_URL ?>/<?= $isAdmin ? 'admin' : 'user' ?>/notifications.php'">
                    <i class="bi bi-bell fs-5"></i>
                    <?php if ($unreadNotif > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem">
                            <?= $unreadNotif > 9 ? '9+' : $unreadNotif ?>
                        </span>
                    <?php endif; ?>
                </button>
            </div>
            <span style="color:var(--muted);font-size:.8rem"><?= date('D, M j Y') ?></span>
        </div>
    </div>

    <div class="page-body">
        <?= flashHtml() ?>
<?php endif; // isLoggedIn ?>
