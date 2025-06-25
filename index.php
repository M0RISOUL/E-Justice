<?php
require_once __DIR__ . '/config/db.php';
start_session();
if (!$u = current_user()) {
    header('Location: login.php');
    exit;
}
$dest = $u['role'] === 'admin' ? 'admin/dashboard.php' : 'client/dashboard.php';
header("Location: $dest");
exit;
