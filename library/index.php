<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . ($_SESSION['role'] === 'borrower' ? '/user/udashboard.php' : '/admin/adashboard.php'));
} else {
    header('Location: ' . BASE_URL . '/auth/login.php');
}
exit;
