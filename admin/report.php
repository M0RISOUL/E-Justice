<?php
// admin/report.php  (unchanged)
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$stats = db()->query("
  SELECT
    (SELECT COUNT(*) FROM cases) AS total_cases,
    (SELECT COUNT(*) FROM cases WHERE status='Closed') AS closed,
    (SELECT COUNT(*) FROM users  WHERE role_id = 2) AS total_clients,
    (SELECT COUNT(*) FROM hearings WHERE hearing_date >= NOW()) AS upcoming_hearings
")->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($stats);
audit('report_view');
