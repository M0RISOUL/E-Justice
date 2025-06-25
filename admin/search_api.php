<?php
/*  admin/search_api.php
    Returns JSON array with up to 100 matching cases
-------------------------------------------------------*/
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$q = '%' . trim($_GET['q'] ?? '') . '%';

$sql = "SELECT c.*, CONCAT(u.first_name,' ',u.last_name) AS client_name
        FROM cases c
        JOIN users u ON u.id = c.client_id
        WHERE case_number LIKE :q
           OR title       LIKE :q
           OR CONCAT(u.first_name,' ',u.last_name) LIKE :q
        ORDER BY created_at DESC
        LIMIT 100";

$stmt = db()->prepare($sql);
$stmt->execute(['q' => $q]);

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

audit('search', 'case', null);
