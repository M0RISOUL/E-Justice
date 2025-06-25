<?php
require_once __DIR__ . '/../../config/db.php';
require_role('admin');

header('Content-Type: application/json');

// Get all notifications regardless of user
$rows = db()->query("
    SELECT id, user_id, content, is_read, created_at
    FROM notifications
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows);
